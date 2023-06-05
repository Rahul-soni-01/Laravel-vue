<?php


namespace App\RepositoryEloquent\Post;

interface PostInterface
{
    public function getList($request);

    public function getDetail($id);

    public function createData($request, $urlFile, $urlFileVideo);

    public function updateData($request, $urlFile, $id, $urlFileVideo);

    public function updateStatus($request);

    public function updatePostPublic($request);

    /**
     * @param $request
     * @return array
     */
    public function getFavoritePosts($request);

    public function getListPosts($creatorId);

    public function getListByKeyword($request);

    public function getListPostPublic($request);
}
