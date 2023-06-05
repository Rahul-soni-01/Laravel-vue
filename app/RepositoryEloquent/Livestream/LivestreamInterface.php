<?php


namespace App\RepositoryEloquent\Livestream;

interface LivestreamInterface
{
    /**
     * @param $params
     * @return \App\Models\Livestreams|null
     */
    public function createLivestream($params);

    /**
     * @param $params
     * @param $id
     * @return bool
     */
    public function updateLivestream($params, $id): bool;

    /**
     * @param $request
     * @return mixed
     */
    public function getList($request): mixed;

    public function getDetailLivestream($authId);
}
