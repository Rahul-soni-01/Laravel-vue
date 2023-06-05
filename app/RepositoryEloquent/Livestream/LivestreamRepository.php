<?php

namespace App\RepositoryEloquent\Livestream;

use App\Define\CommonDefine;
use App\Models\Livestream;
use App\RepositoryEloquent\BaseRepository;


class LivestreamRepository extends BaseRepository implements LivestreamInterface
{
    public function model()
    {
        return Livestream::class;
    }

    /**
     * @param $params
     * @return Livestream|null
     */
    public function createLivestream($params)
    {
        $data = $this->updateOrCreate(
            [
                'author_id' => $params['author_id']
            ],
            $params
        );

        return $data;
    }

    /**
     * @param $params
     * @param $id
     * @return bool
     */
    public function updateLivestream($params, $id): bool
    {
        $update = $this->update($params, $id);

        return  $update;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getList($request): mixed
    {
        $data = $this->model->select([
            'id',
            'title',
            'image_thumbnail',
            'author_id',
            'status'
        ])
            ->with(['user:id,name', 'plan:id,title'])
            ->where('status', CommonDefine::ACTIVE)
            ->orderBy('updated_at', 'DESC')->get();

        return $data;
    }

    public function getDetailLivestream($authId)
    {
        $data = $this->model
            ->select(
                'id',
                'title',
                'author_id',
                'image_thumbnail',
                'status',
                'save_history',
            )
            ->where('author_id', $authId)
            ->with([
                'plan'
            ])
            ->firstOrFail();

        return $data;
    }
}
