<?php

namespace App\RepositoryEloquent\PostFavorite;

use App\Models\PostFavorite;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;

class PostFavoriteRepository extends BaseRepository implements PostFavoriteInterface
{
    public function model()
    {
        return PostFavorite::class;
    }

    /**
     * Get Favorite Post of logged-in user
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFavoritePost(Request $request)
    {
        return $this->model
            ->where('user_id', auth()->user()->id)
            ->get();
    }

    /**
     * Favorite a post
     * @param Request $request
     * @return array
     * @throws BindingResolutionException
     */
    public function favoritePost(Request $request)
    {
        $dataFind = $this->model
            ->where('user_id', auth()->user()->id)
            ->where('post_id', $request->post_id)
            ->first();

        if ($dataFind) {
            $dataFind->delete($dataFind->id);
            $unFavorite = true;
        } else {
            $data = [
                'user_id' => auth()->user()->id,
                'post_id' => $request->post_id,
            ];
            $this->create($data);
            $unFavorite = false;
        }

        $countLike = $this->model
            ->where('post_id', $request->post_id)
            ->count();
        $data = [
            'count' => $countLike,
            'un_favorite' => $unFavorite
        ];

        return $data;
    }
}
