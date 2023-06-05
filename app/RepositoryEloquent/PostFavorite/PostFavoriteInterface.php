<?php

namespace App\RepositoryEloquent\PostFavorite;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface PostFavoriteInterface
{
    /**
     * @param Request $request
     * @return Collection
     */
    public function getFavoritePost(Request $request);

    /**
     * @param Request $request
     * @return array
     */
    public function favoritePost(Request $request);
}
