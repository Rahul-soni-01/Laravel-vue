<?php
namespace App\RepositoryEloquent\MessageDetail;

use App\Models\MessageDetail;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MessageDetailRepository extends BaseRepository implements MessageDetailInterface
{
    public function model()
    {
        return MessageDetail::class;
    }

    /**
     * @param $request
     * @return array
     */
    public function getDetailMessage($request) : array
    {
        $take = Arr::get($request, 'take', 10);
        $data = $this->model
            ->where('message_id', $request->message_id)
            ->with([
                'message',
                'receiver.userInfo',
                'sender.userInfo'
            ])
            ->orderBy('created_at', 'DESC');

        $total = $data->count();
        $data = $data->take($take)->get();
        $response = [
            'total' => $total,
            'list' => $data
        ];

        return $response;
    }

    /**
     * @param $params
     * @return MessageDetail|null
     */
    public function createMessageDetail($params) : MessageDetail|null
    {
        $create = $this->model->create($params);

        return $create;
    }
}
