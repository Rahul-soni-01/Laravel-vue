<?php


namespace App\RepositoryEloquent\Fan;

use Illuminate\Support\Collection;

interface FanInterface
{
    /**
     * @param $params
     * @return \App\Models\Fan|null
     */
    public function createFan($params): \App\Models\Fan|null;

    /**
     * @param $params
     * @param $id
     * @return bool
     */
    public function updateFan($params, $id): bool;

    /**
     * @param $request
     * @return mixed
     */
    public function getList($request): mixed;

    /**
     * @param $request
     * @return \App\Models\Fan|null
     */
    public function userDetailFan($id): \App\Models\Fan|null;

    public function getFromNickname($nickname): \App\Models\Fan|null;

    /**
     * @param $id
     * @return \App\Models\Fan|null
     */
    public function creatorDetailFan($id): \App\Models\Fan|null;

    /**
     * @param $authorId
     * @return \App\Models\Fan|null
     */
    public function getByAuthor($authorId): \App\Models\Fan|null;

    /**
     * @param $request
     * @return array
     */
    public function getFavoriteFans($request): array;

    public function updateStatus($id);
}
