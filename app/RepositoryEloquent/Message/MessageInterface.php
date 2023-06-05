<?php

namespace App\RepositoryEloquent\Message;

use App\Http\Requests\Message\CreateRequest;
use Illuminate\Database\Eloquent\Collection;

interface MessageInterface
{
    /**
     * @param $request
     * @return array
     */
    public function getMessages($request);

    /**
     * @param $request
     * @return array
     */
    public function getMessagesCreator($request);

    /**
     * Get messages list unread
     * @return Collection
     */
    public function getMessagesUnread(): Collection;

    /**
     * @param array $data
     * @return bool
     */
    public function createMessage($data);

    /**
     * @param $id
     * @return bool
     */
    public function readMessage($id);

    /**
     * @param $id
     * @return bool
     */
    public function deleteMessage($id);
}
