<?php

namespace App\RepositoryEloquent\Plan;

use App\Models\Plan;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PlanRepository extends BaseRepository implements PlanInterface
{
    public function model()
    {
        return Plan::class;
    }

    /**
     * @param $request
     * @return Collection
     */
    public function userListPlan($request): Collection
    {
        $data = $this->model
            ->select([
                'id',
                'title',
                'sub_title',
                'fan_id',
                'price',
                'price_year',
                // 'discount',
                // 'discount_code',
                'type',
                'note',
                'photo',
                'pro_stripe_id',
                'price_stripe_id',
                'product_stripe',
                'price_stripe'
            ])
            ->where('fan_id', $request->fan_id)
            ->with([
                'fan',
                'users:id,name,email'
            ])
            ->orderBy('price', 'ASC')
            ->get();

        $data->map(function ($item) {
            $item->total_user = count($item->users);
        });

        return $data;
    }

    /**
     * @param $request
     * @return Collection
     */
    public function creatorListPlan($request): Collection
    {
        $data = $this->model
            ->select([
                'id',
                'title',
                'type',
                'sub_title',
                'fan_id',
                'price',
                'price_year',
                // 'discount',
                // 'discount_code',
                'note',
                'photo'
            ])
            ->with([
                'fan',
                'users',
                'users.userInfo'
            ])
            ->whereHas('fan', function ($q) {
                $q->where('author_id', auth()->user()->id);
            })
            ->get();

        $data->map(function ($item) {
            $item->total_user = count($item->users);
        });

        return $data;
    }

    /**
     * @param $request
     * @param $urlPhoto
     * @param $fanId
     * @return \App\Models\Plan|null
     */
    public function createPlan($params, $urlPhoto, $fanId): \App\Models\Plan|null
    {
        $create = $this->model->create($params);

        return $create;
    }

    /**
     * @param $params
     * @param $id
     * @param $urlPhoto
     * @return bool
     */
    public function updatePlan($params, $id, $urlPhoto): bool
    {
        if ($urlPhoto) {
            $params['photo'] = $urlPhoto;
        }

        $update = $this->update($params, $id);

        return $update;
    }

    public function getDetail($request)
    {
        $data = $this->model
            ->select([
                'id',
                'title',
                'sub_title',
                'fan_id',
                'photo',
                'type',
                'price',
                'price_year',
                // 'discount',
                // 'discount_code',
                'note',
                'created_at',
                'pro_stripe_id',
                'price_stripe_id',
                'product_stripe',
                'price_stripe'
            ])
            ->findOrFail($request);

        if (!$data) {
            return [
                'success' => false,
                'message' => 'ユーザーが見つかりません'
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findPlan($id)
    {
        return $this->model->findOrFail($id);
    }
}
