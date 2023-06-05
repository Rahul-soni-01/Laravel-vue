<?php

namespace App\RepositoryEloquent\Comment;

use App\Define\CommonDefine;
use App\Helpers\FormatHelper;
use App\Models\Comment;
use App\Models\User;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CommentRepository extends BaseRepository implements CommentInterface
{
    public function model()
    {
        return Comment::class;
    }

    /**
     * @param $request
     * @return array
     */
    public function list($request)
    {
        $perPage = (int) Arr::get($request, 'per_page', CommonDefine::DEFAULT_LIMIT_FAVORITE_PAGE);
        $comment = $this->model
            ->select('id', 'content', 'type', 'post_id', 'product_id', 'created_at', 'user_id')
            ->with(['user.userInfo'])
            ->where('type', $request->type)
            ->where('post_id', $request->post_id)
            ->where('product_id', $request->product_id)
            ->orderBy('created_at', 'DESC');
        $total = $comment->count();
        if ($request->length_list) {
            $comment = $comment->take($request->length_list)->get();

            return [
                'comment' => $comment,
                'check' => $comment->count() < $total

            ];
        } else {
            $comment = $comment->paginate($perPage);

            return FormatHelper::paginate($comment);
        }
    }

    /**
     * @param $request
     * @return Comment|null
     */
    public function createComment($request): Comment|null
    {
        $params = [
            'content' => $request->content,
            'type' => $request->type,
            'post_id' => $request->post_id,
            'product_id' => $request->product_id,
            'user_id' => auth()->user()->id
        ];

        $create = $this->model->create($params);

        return $create;
    }

    public function deleteComment($request)
    {
        $comment = $this->model->where('id', $request->id)
            ->where('user_id', auth()->user()->id)
            ->first();
        if ($comment) {
            $comment->delete($comment->id);
        }

        return $comment;
    }
}
