<?php


namespace App\RepositoryEloquent\Plan;

use Illuminate\Support\Collection;

interface PlanInterface
{
    /**
     * @param $request
     * @return Collection
     */
    public function userListPlan($request): Collection;

    /**
     * @param $request
     * @return Collection
     */
    public function creatorListPlan($request): Collection;

    /**
     * @param $request
     * @param $urlPhoto
     * @param $fanId
     * @return \App\Models\Plan|null
     */
    public function createPlan($params, $urlPhoto, $fanId): \App\Models\Plan|null;

    /**
     * @param $request
     * @param $urlPhoto
     * @param $id
     * @return bool
     */
    public function updatePlan($params, $id, $urlPhoto): bool;

    public function getDetail($request);

    public function findPlan($plantId);
}
