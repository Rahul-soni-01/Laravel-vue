<?php


namespace App\RepositoryEloquent\PlanUser;

use Illuminate\Support\Collection;

interface PlanUserInterface
{
    /**
     * @param \App\Http\Requests\PlanUser\CreateRequest $request
     * @return mixed
     */
    public function createPlanUser(\App\Http\Requests\PlanUser\CreateRequest $request);

    /**
     * @param $id
     * @return mixed
     */
    public function cancelPlanUser($id, $request);

    /**
     * @return Collection
     */
    public function getByStatusAndPrice(): Collection;

    public function getPlanOfCurrentUser($userId);

    public function getPlanOfUser($userId);
}
