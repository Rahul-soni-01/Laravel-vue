<?php

namespace App\RepositoryEloquent\User;

use App\Define\AuthDefine;
use App\Define\CommonDefine;
use App\Helpers\FormatHelper;
use App\Jobs\SendMailApprovedSuccess;
use App\Jobs\SendMailBlockAccountUser;
use App\Models\User;
use App\RepositoryEloquent\BaseRepository;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;


class UserRepository extends BaseRepository implements UserInterface
{
    public function model()
    {
        return User::class;
    }

    /**
     * @param $request
     * @return array
     */
    public function getList($request)
    {
        $perPage = (int)$request->get('per_page') ?? CommonDefine::DEFAULT_LIMIT;

        $data = $this->model->whereIn(
            'role_id',
            [
                AuthDefine::ROLE_USER, AuthDefine::ROLE_CREATE
            ]
        )
            ->with(['userInfo']);
        if ($request->has('confirm_status') && $request->confirm_status) {
            $data = $data->whereIn('confirm_status', $request->confirm_status);
        }
        if ($request->has('search_params') && $request->search_params) {
            $data = $data->where(function ($query) use ($request) {
                $query = $query->where('name', 'LIKE', '%' . $request->search_params . '%')
                    ->orWhere('email', 'LIKE', '%' . $request->search_params . '%');
                return $query;
            });
        }
        $data = $data->orderBy('created_at', "DESC")
            ->paginate($perPage);

        return FormatHelper::paginate($data);
    }

    public function getListByUser($request)
    {
        $data = $this->model
            ->with([
                'userInfo',
                'fan.author.userInfo'
            ])
            ->where('id', auth()->user()->id);

        if ($request->has('keywords') && $request->keywords) {
            $data->whereHas('fan.author.userInfo', function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request->keywords . '%')
                        ->where('email', 'LIKE', '%' . $request->keywords . '%');
                });
            });
        }

        $data = $data->first();

        if ($data && $data->fan) {
            $listAuthor['list'] = $data->fan->map(function ($fan) {

                return $fan->author;
            });
        } else {
            $listAuthor['list'] = [];
        };

        return $listAuthor;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function changeStatusUser($request)
    {
        $userIds = Arr::get($request, 'user_ids');
        $status = Arr::get($request, 'status');
        $userUpdate = $this->model->whereIn('id', $userIds);

        DB::beginTransaction();
        try {
            $update = $userUpdate->update([
                'status' => $status
            ]);

            if (!$update) {
                return null;
            }

            if ($status == 0) {
                $userUpdate = $userUpdate->get();
                $userUpdate->map(function ($item) use ($status) {
                    $data = [
                        'reason' => 'Account is locked',
                        'email' => $item->name ? $item->name : $item->email,
                    ];

                    dispatch(new SendMailBlockAccountUser($item->email, $data));
                });
            }

            DB::commit();

            return $update;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function changeConfirmStatus($request)
    {
        $userIds = Arr::get($request, 'user_ids');

        $userUpdate = $this->model
            ->whereIn('id', $userIds);

        DB::beginTransaction();
        try {
            $update =  $userUpdate->update([
                'confirm_status' => CommonDefine::AUTHENTICATED
            ]);

            if (!$update) {
                return null;
            }

            $userUpdate = $userUpdate->get();
            $userUpdate->map(function ($item) {
                $data = [
                    'email' => $item->name ? $item->name : $item->email,
                ];
                dispatch(new SendMailApprovedSuccess($item->email, $data));
            });
            DB::commit();

            return $update;
        } catch (Exception $exception) {
            DB::commit();
            throw $exception;
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function deleteUser($id)
    {
        $userRole = auth()->user();

        if (!$userRole || $userRole->role_id !== AuthDefine::ROLE_ADMIN) {
            return [
                'success' => false,
                'message' => '管理者ではないユーザー'
            ];
        }

        if ($userRole->id == $id) {
            return [
                'success' => false,
                'message' => 'このユーザーを削除できません'
            ];
        }
        $userDelete = $this->model->find($id);

        if (!$userDelete) {
            return [
                'success' => false,
                'message' => 'ユーザーを削除できません'
            ];
        }
        $userDelete->delete();

        return [
            'success' => true,
            'message' => 'ユーザーを削除する'
        ];
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getDetail($id)
    {
        $userRole = auth()->user();

        if (!$userRole || $userRole->role_id !== AuthDefine::ROLE_ADMIN) {
            return [
                'success' => false,
                'message' => '管理者ではないユーザー'
            ];
        }

        $data = $this->model
            ->select(['*'])
            ->with(['userInfo'])
            ->find($id);

        if (!$data) {
            return [
                'success' => false,
                'message' => 'ユーザーが見つかりません'
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * @param $request
     * @return array
     */
    public function getUserFollowFan($request): array
    {
        $fanId = Arr::get($request, 'fan_id');
        $sortColumn = Arr::get($request, 'sort_column', 'created_at');
        $sortBy = Arr::get($request, 'sort_by', 'DESC');
        $keyWords = Arr::get($request, 'key_words');
        $perPage = Arr::get($request, 'per_page', CommonDefine::DEFAULT_LIMIT);
        $data = $this->model->select([
            'id',
            'name',
            'email',
            'role_id'
        ])->with([
            'userInfo',
            'fan:id,title'
        ])->whereHas('fan', function ($q) use ($fanId) {
            $q->where('fan_id', $fanId);
        })
            ->orderBy($sortColumn, $sortBy);

        if ($keyWords) {
            $data->where(function ($query) use ($keyWords) {
                $query->where('name', $keyWords);
            });
        }

        $data = $data->paginate($perPage);
        $data->map(function ($item) {
            $item->fan_club = $item->fan->first();
            unset($item->fan);
        });

        return FormatHelper::paginate($data);
    }
}
