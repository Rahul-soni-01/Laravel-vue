<?php

namespace App\Http\Controllers;

use App\Define\CommonDefine;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Notification\CreateRequest;
use App\RepositoryEloquent\NotificationHistory\NotificationHistoryInterface;
use http\Client\Response;
use Illuminate\Http\Request;
use App\RepositoryEloquent\Fan\FanInterface;

class NotificationHistoryController extends Controller
{
    /**
     * @var NotificationHistoryInterface
     */
    private NotificationHistoryInterface $notificationHistoryRepository;

    /**
     * @var FanInterface
     */
    private FanInterface $fanRepository;

    /**
     * @param NotificationHistoryInterface $notificationHistoryRepository
     * @param FanInterface $fanRepository
     */
    public function __construct(
        NotificationHistoryInterface $notificationHistoryRepository,
        FanInterface $fanRepository
    ) {
        $this->notificationHistoryRepository = $notificationHistoryRepository;
        $this->fanRepository = $fanRepository;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $dataNotification = $this->notificationHistoryRepository->getList($request);
        return ResponseHelper::ok($dataNotification);
    }

    /**
     * Update notification is read
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function readNotification($id): \Illuminate\Http\Response
    {
        $update = $this->notificationHistoryRepository->readNotification($id);

        if (!$update) return ResponseHelper::bad();

        return ResponseHelper::ok();
    }

    /**
     * Create new notification
     * @param CreateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateRequest $request): \Illuminate\Http\Response
    {
        $create = $this->notificationHistoryRepository->createNotification($request);

        if (!$create) {
            return ResponseHelper::bad();
        }

        return ResponseHelper::ok();
    }
}
