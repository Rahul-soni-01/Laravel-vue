<?php

namespace App\RepositoryEloquent\NotificationHistory;

use App\Define\AuthDefine;
use App\Define\CommonDefine;
use App\Helpers\FormatHelper;
use App\Http\Requests\Notification\CreateRequest;
use App\Models\Notification;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class NotificationHistoryRepository extends BaseRepository implements NotificationHistoryInterface
{
    public function model()
    {
        return Notification::class;
    }

    /**
     * Get list of user's notification
     * @param $request
     * @return mixed
     */
    public function getList($request): mixed
    {
        $user = auth()->user();
        $skip = Arr::get($request, 'skip');
        $take = Arr::get($request, 'take');
        $isRead = Arr::get($request, 'is_read');
        $notifications = $this->model
            ->where('user_id', $user->id)
            ->whereNot('created_by', $user->id)
            ->orderBy('created_at', 'DESC');

        if ($user->role_id == AuthDefine::ROLE_CREATE &&
            $user->confirm_status == CommonDefine::AUTHENTICATED
        ) {
            $notifications->whereIn('type', [
                CommonDefine::LIKE,
                CommonDefine::COMMENT,
                CommonDefine::FOLLOW
            ]);
        } else {
            $notifications->where('type', CommonDefine::NEW_PRODUCT_OR_POST);
        }

        if ($request->has('is_read')) {
            $notifications->where('is_read', $isRead);
        }

        $total = $notifications->count();

        if ($skip) {
            $notifications = $notifications->skip($skip);
        }

        if ($take) {
            $notifications = $notifications->take($take);
        }

        $notifications = $notifications->get();

        $data = [
            'total' => $total,
            'list' => $notifications
        ];

        return $data;
    }

    /**
     * Create notification
     * @param CreateRequest $request
     * @return Notification
     */
    public function createNotification(CreateRequest $request)
    {
        $paramsCreate = array_merge($request->validated(), [
            'created_by' => auth()->user()->id,
            'is_read' => CommonDefine::UN_READ,
        ]);

        return $this->model->create($paramsCreate);
    }

    /**
     * Update notification status to is_read
     * @param $id
     * @return bool
     */
    public function readNotification($id)
    {
        $update = $this->update([
            'is_read' => CommonDefine::IS_READ
        ], $id);

        return $update;
    }
}
