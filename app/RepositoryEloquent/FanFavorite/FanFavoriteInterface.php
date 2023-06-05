<?php

namespace App\RepositoryEloquent\FanFavorite;

interface FanFavoriteInterface
{
    /**
     * @param $request
     * @return array
     */
    public function favoriteFan($request);
}
