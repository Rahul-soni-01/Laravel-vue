<?php

namespace App\RepositoryEloquent\Post;

use App\Define\AuthDefine;
use App\Define\CommonDefine;
use App\Helpers\FormatHelper;
use App\Jobs\SendMailUpdateStatusPost;
use App\Mail\PostUpdateStatus;
use App\Mail\RegisterMail;
use App\Models\Post;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;


class PostRepository extends BaseRepository implements PostInterface
{
    public function model()
    {
        return Post::class;
    }

    /**
     * get list post
     *
     * @param $request
     * @return array
     */
    public function getList($request)
    {
        $perPage = (int)$request->get('per_page') ?? CommonDefine::DEFAULT_LIMIT;

        $data = $this->model
            ->select(
                'id',
                'title',
                'content',
                'author_id',
                'category_id',
                'status',
                'plan_id',
                'is_public',
                'url_file',
                'url_file_video',
                'created_at',
                'brand_id'
            )
            ->with([
                'users:id,name',
                'category:id,name',
                'tags:id,name',
            ]);

        if ($request->has('author_id')) {
            $data = $data->where('author_id', $request->get('author_id'));
        }

        if ($request->get('title') && !empty($request->get('title'))) {
            $data = $data->where('title', 'like', "%" . $request->get('title') . "%");
        }

        if ($request->get('category_id') && !empty($request->get('category_id'))) {
            $data = $data->whereIn('category_id', $request->get('category_id'));
        }

        if ($request->get('tag_name') && !empty($request->get('tag_name'))) {
            $data = $data->where('tag_name', $request->get('tag_name'));
        }

        if ($request->get('status') && !empty($request->get('status'))) {
            $data = $data->whereIn('status', $request->get('status'));
        }

        $data = $data->orderBy('created_at', "DESC")->paginate($perPage);

        return FormatHelper::paginate($data);
    }

    /**
     * get list post
     *
     * @param $request
     * @return array
     */
    public function getDetail($id)
    {
        $data = $this->model
            ->select([
                'id',
                'title',
                'content',
                'url_file',
                'url_file_video',
                'status',
                'plan_id',
                'category_id',
                'author_id',
                'created_at',
                'brand_id',
                'date_public',
                'auto_public',
                'is_public'
            ])
            ->with([
                'category:id,name',
                'users:id,name',
                'tags:id,name'
            ])
            ->findOrFail($id);

        if (!$data) {
            return [
                'success' => false,
                'message' => 'ユーザーが見つかりません'
            ];
        }

        return $data;
    }

    /**
     * create data post
     *
     * @param $request
     * @param $urlFile
     * @return mixed
     */
    public function createData($request, $urlFile, $urlFileVideo)
    {
        $data = [];
        $data['title'] = $request->get('title');
        $data['content'] = $request->get('content');
        $data['is_public'] = $request->get('is_public');
        $data['auto_public'] = $request->get('auto_public');
        $data['category_id'] = $request->get('category_id');
        $data['author_id'] = auth()->user()->id;
        $data['brand_id'] = $request->get('brand_id');
        // $data['plan_id'] = $request->get('plan_id');
        if ($request->get('is_public') == 1) {
            $data['date_public'] =  Carbon::now();
        }
        if ($request->get('auto_public') == 1) {
            $data['date_public'] =  $request->get('date_public');
        }
        if ($urlFile) {
            $data['url_file'] = $urlFile;
        }
        if ($urlFileVideo) {
            $data['url_file_video'] = $urlFileVideo;
        }
        $data['status'] = CommonDefine::POST_STATUS_ENABLE;
        $create = $this->model->create($data);

        return $create;
    }

    /**
     * Update status
     *
     * @param $request
     * @param $urlFile
     * @param $id
     * @return mixed
     */
    public function updateData($request, $urlFile, $id, $urlFileVideo)
    {
        $data = [];
        $data['title'] = $request->get('title');
        $data['content'] = $request->get('content');
        $data['is_public'] = $request->get('is_public');
        $data['auto_public'] = $request->get('auto_public');
        $data['category_id'] = $request->get('category_id');
        $data['author_id'] = auth()->user()->id;
        $data['brand_id'] = $request->get('brand_id');
        // $data['plan_id'] = $request->get('plan_id');
        if ($request->get('is_public') == 1) {
            $data['date_public'] =  Carbon::now();
        }
        if ($request->get('auto_public') == 1) {
            $data['date_public'] =  $request->get('date_public');
        }
        if ($urlFile) {
            $data['url_file'] = $urlFile;
        }
        if ($urlFileVideo) {
            $data['url_file_video'] = $urlFileVideo;
        }
        $data['status'] = CommonDefine::POST_STATUS_ENABLE;
        $update = $this->updateOrCreate(
            [
                'id' => $id
            ],
            $data
        );

        return $update;
    }

    /**
     * update status
     *
     * @param $request
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function updateStatus($request)
    {
        try {
            DB::beginTransaction();
            $ids = $request->get('ids');
            foreach ($ids as $id) {
                $postId = $this->findOrFail((int)$id);
                if ($postId) {
                    $data = [];
                    $data['status'] = $request->get('status');
                    $update = $this->update($data, (int)$id);
                    if ($update) {
                        $data = [
                            'title' => $postId->title
                        ];

                        // if (!empty($postId->users->email) && $postId->users->is_notification == CommonDefine::USER_IS_NOTIFICATION) {
                        //     dispatch(new SendMailUpdateStatusPost($postId->users->email, $data));
                        // }
                    }
                }
            }

            DB::commit();
            return [
                'success' => true,
                'message' => "ユーザーの成功に送信されたメール",
            ];
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param $request
     * @return bool
     */
    public function updatePostPublic($request): bool
    {
        $params = [
            'is_public' => $request['public_status']
        ];

        if ($request['public_status'] == 1) {
            $params['date_public'] = Carbon::now();
        }

        $update = $this->update($params, $request['id']);

        return $update;
    }

    /**
     * Get list of favorite posts
     * @param $request
     * @return array
     */
    public function getFavoritePosts($request)
    {
        $postPerPage = (int)$request->get('per_page') ?? CommonDefine::DEFAULT_LIMIT_FAVORITE_PAGE;

        $data = $this->model->select('posts.*')
            ->join('post_favorite', 'posts.id', '=', 'post_favorite.post_id')
            ->where('post_favorite.user_id', auth()->user()->id)
            ->where('posts.is_public', CommonDefine::POST_IS_PUBLIC)
            ->with(['users.userInfo', 'users.ownerFan', 'userFavorite'])
            ->orderBy('post_favorite.created_at', 'DESC')
            ->paginate($postPerPage);

        return FormatHelper::paginate($data);
    }

    public function getListPosts($creatorId)
    {
        $data = $this->model
            ->select(
                'id',
                'title',
                'content',
                'author_id',
                'category_id',
                'status',
                'plan_id',
                'is_public',
                'date_public',
                'auto_public',
                'url_file',
                'url_file_video',
                'created_at',
                'brand_id'
            )
            ->where('author_id', $creatorId)
            ->where('is_public', CommonDefine::ACTIVE)
            ->where('status', CommonDefine::ACTIVE)
            ->with(['tags', 'userFavorite'])
            ->orderBy('created_at', 'DESC')
            ->get();

        return $data;
    }

    public function getListByKeyword($request)
    {
        $data = $this->model
            ->select(
                'id',
                'title',
                'content',
                'author_id',
                'category_id',
                'status',
                'plan_id',
                'is_public',
                'date_public',
                'auto_public',
                'url_file',
                'url_file_video',
                'created_at',
                'brand_id'
            )
            ->where('is_public', CommonDefine::ACTIVE)
            ->where('status', CommonDefine::ACTIVE)
            ->with([
                'users:id,name',
                'category:id,name',
                'tags:id,name',
            ])->with(['users.userInfo', 'users.ownerFan', 'userFavorite']);

        if ($request->has('title') && $request->title) {
            $data = $data->where(function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->title . '%')->orWhere(function ($query) use ($request) {
                    $query->whereHas('tags', function ($q) use ($request) {
                        $q->where('tags.name', 'LIKE', '%' . $request->title . '%');
                    });
                });
            });
        }

        return $data->get();
    }

    public function getListPostPublic($request)
    {
        $perPage = (int)$request->get('per_page') ?? 30;
        $data = $this->model
            ->select(
                'id',
                'title',
                'content',
                'author_id',
                'category_id',
                'status',
                'plan_id',
                'is_public',
                'date_public',
                'auto_public',
                'url_file',
                'url_file_video',
                'created_at',
                'brand_id',
                'created_at',
                'updated_at',
            )
            ->where('is_public', CommonDefine::ACTIVE)
            ->where('status', CommonDefine::ACTIVE)
            ->with([
                'users:id,name',
                'category:id,name',
                'tags:id,name',
                'users.ownerFan',
            ])
            ->withCount('userFavorite');

        if ($request->has('title') && $request->title) {
            $data = $data->where(function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->title . '%')->orWhere(function ($query) use ($request) {
                    $query->whereHas('tags', function ($q) use ($request) {
                        $q->where('tags.name', 'LIKE', '%' . $request->title . '%');
                    });
                });
            });
        }

        if ($request->has('author_id') && $request->author_id) {
            $data = $data->where('author_id', $request->author_id)->orderBy('user_favorite_count', 'desc');
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

        if ($request->has('type') && $request->type) {
            $data = $data->where('type', $request->type);
        }

        if ($request->has('sort_by') && $request->sort_by) {
            $data = $data->orderBy($request->sort_by, $request->sort_type ?? 'desc');
        }

        // if ($request->has('is_top') && $request->is_top) {
        //     $data = $data->orderBy('view', 'desc');
        // }

        if ($request->has('top_favorite') && $request->top_favorite) {
            $data = $data->orderBy('user_favorite_count', 'desc');
        }

        $data = $data->paginate($perPage);

        return FormatHelper::paginate($data);
    }
}
