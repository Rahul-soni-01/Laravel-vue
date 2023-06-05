<?php

namespace App\RepositoryEloquent\ProductFavorite;

use App\Models\ProductFavorite;
use App\RepositoryEloquent\BaseRepository;

class ProductFavoriteRepository extends BaseRepository implements ProductFavoriteInterface
{
    public function model()
    {
        return ProductFavorite::class;
    }

    /**
     * @param $request
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function favoriteProduct($request)
    {
        $dataFind = $this->model
            ->where('user_id', auth()->user()->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($dataFind) {
            $dataFind->delete($dataFind->id);
            $unFavorite = true;
        } else {
            $data = [
                'user_id' => auth()->user()->id,
                'product_id' => $request->product_id,
            ];
            $this->create($data);
            $unFavorite = false;
        }

        $countFavorite = $this->model
            ->where('product_id', $request->product_id)
            ->count();
        $data = [
            'count' => $countFavorite,
            'un_favorite' => $unFavorite
        ];

        return $data;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getFavoriteProduct($request)
    {
        return $this->model
            ->where('user_id', auth()->user()->id)
            ->where('product_id', $request->get('product_id'))
            ->get();
    }
}
