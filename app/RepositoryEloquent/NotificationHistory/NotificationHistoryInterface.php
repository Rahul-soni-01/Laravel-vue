<?php

namespace App\RepositoryEloquent\NotificationHistory;

use App\Http\Requests\Notification\CreateRequest;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

interface NotificationHistoryInterface
{
    /**
     * @param $request
     * @return mixed
     */
    public function getList($request) : mixed;

    /**
     * @param $id
     * @return bool
     */
    public function readNotification($id);

    /**
     * @param CreateRequest $request
     * @return Notification
     */
    public function createNotification(CreateRequest $request);
}
