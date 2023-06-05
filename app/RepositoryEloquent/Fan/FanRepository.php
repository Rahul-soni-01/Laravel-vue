<?php

namespace App\RepositoryEloquent\Fan;

use App\Define\CommonDefine;
use App\Helpers\FormatHelper;
use App\Models\Fan;
use App\RepositoryEloquent\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FanRepository extends BaseRepository implements FanInterface
{
    public function model()
    {
        return Fan::class;
    }

    /**
     * @param $params
     * @return Fan|null
     */
    public function createFan($params): Fan|null
    {
        $data = $this->updateOrCreate(
            [
                'author_id' => $params['author_id']
            ],
            $params
        );

        return $data;
    }

    /**
     * @param $params
     * @param $id
     * @return bool
     */
    public function updateFan($params, $id): bool
    {
        $update = $this->update($params, $id);

        return  $update;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getList($request): mixed
    {
        $perPage = $request->input('per_page', CommonDefine::DEFAULT_LIMIT);
        $keyWords = Arr::get($request, 'key_words');
        $nickname = Arr::get($request, 'nickname');
        $orderColumn = Arr::get($request, 'order_column', 'created_at');
        $orderBy = Arr::get($request, 'order_by', 'DESC');
        $data = $this->model->select([
            'id',
            'title',
            'sub_title',
            'nickname',
            'content',
            'category_id',
            'author_id',
            'photo',
            'avt',
            'background',
            'status',
            'brand_id'
        ])->with([
            'users:id,name,email',
            'products:id,author_id,title,thumbnail_url,view',
            'category:id,name',
            'author:id,email,name'
        ]);

        $data = $data->withCount('users', 'userFavorite')
            ->orderBy($orderColumn, $orderBy);

        if ($request->has('public') && $request->public == 1) {
            $data = $data->where(function ($query) {
                $query->where('status', 1);
            });
        }

        if ($request->has('nickname') && $request->nickname) {
            $data = $data->where(function ($query) use ($nickname) {
                $query->where('nickname', 'LIKE', '%' . $nickname . '%');
            });
        }

        if ($request->has('key_words') && $request->key_words) {
            $data = $data->where(function ($query) use ($keyWords) {
                $query->where('title', 'LIKE', '%' . $keyWords . '%')
                    ->orWhere('content', 'LIKE', '%' . $keyWords . '%');
            });
        }

        if ($request->has('author_id') && $request->author_id) {
            $data = $data->where('author_id', $request->author_id);
        }

        if ($request->has('category_ids') && $request->category_ids) {
            $data = $data->whereIn('category_id', $request->category_ids);
        }

        if ($request->has('is_month') && $request->is_month) {
            $data = $data->whereDate('created_at', '>=', Carbon::now()->subMonth(1))
                ->whereDate('created_at', '<=', Carbon::now());
        }

        if ($request->has('is_week') && $request->is_week) {
            $data = $data->whereDate('created_at', '>=', Carbon::now()->subDay(7))
                ->whereDate('created_at', '<=', Carbon::now());
        }

        if ($request->has('is_hour') && $request->is_hour) {
            $data = $data->whereDate('created_at', Carbon::now());
        }

        if ($request->has('status') && $request->status) {
            $data = $data->where('status', $request->status);
        }

        $data = $data->paginate($perPage);

        return FormatHelper::paginate($data);
    }

    /**
     * @param $request
     * @return \App\Models\Fan|null
     */
    public function userDetailFan($id): \App\Models\Fan|null
    {
        $fan = $this->model->select([
            'id',
            'title',
            'sub_title',
            'nickname',
            'content',
            'category_id',
            'author_id',
            'photo',
            'avt',
            'background',
            'status',
            'brand_id'
        ])->with([
            'category:id,name',
            'author:id,email,name',
            'users:id',
            'userFavorite'
        ])->withCount([
            'users'
        ])->findOrFail($id);

        return $fan;
    }

    public function getFromNickname($nickname): \App\Models\Fan|null
    {
        $fan = $this->model
            ->where('nickname', $nickname)
            ->select([
                'id',
                'title',
                'sub_title',
                'nickname',
                'content',
                'category_id',
                'author_id',
                'photo',
                'avt',
                'background',
                'status',
                'brand_id'
            ])->with([
                'category:id,name',
                'author:id,email,name',
                'users:id',
                'userFavorite'
            ])->withCount([
                'users'
            ])->firstOrFail();

        return $fan;
    }

    /**
     * @param $id
     * @return \App\Models\Fan|null
     */
    public function creatorDetailFan($id): \App\Models\Fan|null
    {
        $fan = $this->model->select([
            'id',
            'title',
            'sub_title',
            'nickname',
            'content',
            'category_id',
            'author_id',
            'photo',
            'avt',
            'background',
            'status',
            'brand_id'
        ])->with([
            'category:id,name',
            'author:id,email,name',
            'users:id,name,email'
        ])->findOrFail($id);

        return $fan;
    }

    /**
     * @param $authorId
     * @return \App\Models\Fan|null
     */
    public function getByAuthor($authorId): \App\Models\Fan|null
    {
        $fan = $this->model->select([
            'id',
            'title',
            'nickname',
            'sub_title',
            'content',
            'category_id',
            'author_id',
            'photo',
            'avt',
            'background',
            'status',
            'brand_id'
        ])->with([
            'plans',
            'plans.users',
            'category:id,name',
            'author:id,email,name'
        ])->where('author_id', $authorId)
            ->first();

        if ($fan !== null) {
            $fan->total_user = count($fan->users);
        }

        return $fan;
    }

    /**
     * @param $request
     * @return array
     */
    public function getFavoriteFans($request): array
    {
        $fanPerPage = (int) Arr::get($request, 'per_page', CommonDefine::DEFAULT_LIMIT_FAVORITE_PAGE);

        $data = $this->model
            ->select(['fans.*'])
            ->join('fan_favorite', 'fans.id', '=', 'fan_favorite.fan_id')
            ->join('users', 'users.id', '=', 'fan_favorite.user_id')
            ->where('users.id', '=', auth()->user()->id)
            ->where('fans.status', CommonDefine::FAN_PUBLIC)
            ->with(['userFavorite'])
            ->orderBy('fan_favorite.created_at', 'DESC')
            ->paginate($fanPerPage);

        return FormatHelper::paginate($data);
    }

    public function updateStatus($id)
    {
        $update = $this->updateByCondition(
            [
                'author_id' => $id
            ],
            [
                'status' => CommonDefine::FAN_PRIVATE,
            ]
        );

        return $update;
    }
}
