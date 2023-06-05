<?php

namespace App\RepositoryEloquent\ProductFavorite;

use App\Models\Product;
use Illuminate\Support\Collection;

interface ProductFavoriteInterface
{
    /**
     * @param $request
     * @return array
     */
    public function favoriteProduct($request);

    /**
     * @param $request
     * @return mixed
     */
    public function getFavoriteProduct($request);
}
