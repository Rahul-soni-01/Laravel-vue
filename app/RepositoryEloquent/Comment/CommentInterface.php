<?php


namespace App\RepositoryEloquent\Comment;

use App\Models\Comment;

interface CommentInterface
{
    /**
     * @param $request
     * @return array
     */
    public function list($request);

    /**
     * @param $request
     * @return Comment|null
     */
    public function createComment($request): Comment|null;

    public function deleteComment($request);
}
