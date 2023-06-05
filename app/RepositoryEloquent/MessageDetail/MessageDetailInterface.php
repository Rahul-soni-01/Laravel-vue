<?php


namespace App\RepositoryEloquent\MessageDetail;

use App\Models\MessageDetail;
use Illuminate\Support\Collection;

interface MessageDetailInterface
{
    /**
     * @param $request
     * @return array
     */
    public function getDetailMessage($request) : array;

    /**
     * @param $params
     * @return MessageDetail|null
     */
    public function createMessageDetail($params) : MessageDetail|null;
}
