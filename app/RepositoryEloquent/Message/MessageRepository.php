<?php

namespace App\RepositoryEloquent\Message;

use App\Define\AuthDefine;
use App\Define\CommonDefine;
use App\Models\Message;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository extends BaseRepository implements MessageInterface
{
    public function model()
    {
        return Message::class;
    }

    /**
     * Get messages betwwen logged-in user and another user
     * @param $request
     * @return array
     */
    public function getMessages($request)
    {
        $user = auth()->user();
        $messages = $this->model
            ->where(function ($query) {
                $query->where('user_id', auth()->user()->id)
                    ->orWhere('receiver_id', auth()->user()->id);
            })
            ->whereNot('delete_by', auth()->user()->id)
            ->with([
                'messageDetail' => function ($query) {
                    $query->orderBy('created_at', 'DESC');
                },
                'receiver.userInfo',
                'receiver.fan',
                'sender.userInfo',
                'sender.ownerFan'
            ])
            ->orderBy('updated_at', 'DESC')
            ->get();

        $listCreator = [];
        $listUser = [];
        foreach ($messages as $message) {
            if (
                $message->user_id == $user->id &&
                $message->sender->fan &&
                $message->sender->fan->where('id', $message->receiver->ownerFan->id)->count()
            ) {
                $listUser['list'][] = $message;
            }

            if (
                $message->receiver_id == $user->id &&
                $message->sender->ownerFan &&
                $message->receiver->fan->where('id', $message->sender->ownerFan->id)->count()
            ) {
                $listUser['list'][] = $message;
            }
        }

        if ($user->role_id == AuthDefine::ROLE_USER) return $listUser;

        return $listCreator;
    }

    /**
     * Get messages betwwen logged-in user and another user
     * @param $request
     * @return array
     */
    public function getMessagesCreator($request)
    {
        $user = auth()->user();
        $messages = $this->model
            ->where(function ($query) {
                $query->where('user_id', auth()->user()->id)
                    ->orWhere('receiver_id', auth()->user()->id);
            })
            ->whereNot('delete_by', auth()->user()->id)
            ->with([
                'messageDetail' => function ($query) {
                    $query->orderBy('created_at', 'DESC');
                },
                'receiver.userInfo',
                'receiver.fan',
                'sender.userInfo',
                'sender.ownerFan'
            ])
            ->orderBy('updated_at', 'DESC')
            ->get();

        $listCreator = [];
        $listUser = [];
        foreach ($messages as $message) {
            if (
                $message->user_id == $user->id &&
                $message->sender->ownerFan->users &&
                $message->sender->ownerFan->users->where('id', $message->receiver_id)->count()
            ) {
                $listCreator['list'][] = $message;
            }

            if (
                $message->receiver_id == $user->id &&
                $message->receiver->ownerFan
            ) {
                $listCreator['list'][] = $message;
            }
        }

        if ($user->role_id == AuthDefine::ROLE_USER) return $listUser;

        return $listCreator;
    }


    /**
     * Get messages betwwen logged-in user and another user
     * @return Collection
     */
    public function getMessagesUnread(): Collection
    {
        return $this->model
            ->where(function ($query) {
                $query->where('receiver_id', auth()->user()->id);
            })
            ->whereNot('delete_by', auth()->user()->id)
            ->where('is_read', 0)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create message
     * @param $data
     * @return bool
     */
    public function createMessage($data)
    {
        return $this->model->create($data);
    }

    /**
     * Update message to is_read
     * @param $id
     * @return bool
     */
    public function readMessage($id)
    {
        $update = $this->updateByCondition(
            [
                'id' => $id,
                'receiver_id' => auth()->user()->id
            ],
            [
                'is_read' => CommonDefine::IS_READ,
            ]
        );

        return $update;
    }

    /**
     * Delete message
     * @param $id
     * @return bool|null
     * @throws BindingResolutionException
     */
    public function deleteMessage($id): bool|null
    {
        $delete = $this->destroy($id);

        return $delete;
    }
}
