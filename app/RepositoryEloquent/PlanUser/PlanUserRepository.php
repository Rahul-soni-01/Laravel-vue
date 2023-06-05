<?php

namespace App\RepositoryEloquent\PlanUser;

use App\Define\CommonDefine;
use App\Models\PlanUser;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class PlanUserRepository extends BaseRepository implements PlanUserInterface
{
    public function model()
    {
        return PlanUser::class;
    }

    /**
     * Create a new plan user
     * @param \App\Http\Requests\PlanUser\CreateRequest $request
     * @return mixed
     */
    public function createPlanUser(\App\Http\Requests\PlanUser\CreateRequest $request)
    {
        $data = $this->model->join('plans', 'plans.id', 'plan_user.plan_id')->select('plan_user.*')
            ->where('plan_user.user_id', auth()->user()->id)
            ->where('plan_user.status', CommonDefine::PAYMENT_SUCCESS)
            ->where(function ($q) use ($request) {
                $q->where('plan_user.plan_id', $request->plan_id)
                    ->orWhere('plans.fan_id', DB::table('plans')->find($request->plan_id)?->fan_id);
            })
            ->first();

        if ($data) {
            return false;
        }

        $paramsCreate = array_merge($request->validated(), [
            'user_id' => auth()->user()->id,
            'status' => CommonDefine::PAYMENT_SUCCESS,
        ]);

        return $this->model->create($paramsCreate);
    }

    /**
     * User cancel plan
     * @param $id
     * @return mixed
     */
    public function cancelPlanUser($id, $request)
    {
        return $this->updateByCondition(
            [
                'plan_id' => $id,
                'user_id' => auth()->user()->id
            ],
            [
                'reason' => $request->reason,
                'status' => CommonDefine::PAYMENT_CANCELLED,
                'date_out' =>  Carbon::now()
            ],
        );
    }

    /**
     * @return Collection
     */
    public function getByStatusAndPrice(): Collection
    {
        return $this->model
            ->where('status', CommonDefine::PAYMENT_SUCCESS)
            ->where('payment_price', '>', 0)
            ->with([
                'plan',
                'plan.fan',
                'user'
            ])
            ->get();
    }

    public function getPlanOfCurrentUser($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', CommonDefine::PAYMENT_SUCCESS)
            ->where('payment_price', '>', 0)
            ->with([
                'plan',
                'plan.fan',
                'user'
            ])
            ->first();
    }

    public function getPlanOfUser($userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', CommonDefine::PAYMENT_SUCCESS)
            ->with([
                'plan',
                'plan.fan',
                'user'
            ])
            ->first();
    }
}
