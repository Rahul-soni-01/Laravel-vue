<?php

namespace App\RepositoryEloquent\FanFavorite;

use App\Models\FanFavorite;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Contracts\Container\BindingResolutionException;

class FanFavoriteRepository extends BaseRepository implements FanFavoriteInterface
{
    public function model()
    {
        return FanFavorite::class;
    }

    /**
     * Add a fan to favorites or remove from favorites if existed
     * @param $request
     * @return array
     * @throws BindingResolutionException
     */
    public function favoriteFan($request)
    {
        $dataFind = $this->model
            ->where('user_id', auth()->user()->id)
            ->where('fan_id', $request->fan_id)
            ->first();

        if ($dataFind) {
            $dataFind->delete($dataFind->id);
            $unFavorite = true;
        } else {
            $data = [
                'user_id' => auth()->user()->id,
                'fan_id' => $request->fan_id,
            ];
            $this->create($data);
            $unFavorite = false;
        }

        $countFavorite = $this->model
            ->where('fan_id', $request->fan_id)
            ->count();

        return [
            'count' => $countFavorite,
            'un_favorite' => $unFavorite
        ];
    }
}
