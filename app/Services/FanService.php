<?php

namespace App\Services;

use App\Define\CommonDefine;
use App\RepositoryEloquent\FanFavorite\FanFavoriteInterface;
use App\RepositoryEloquent\NotificationHistory\NotificationHistoryInterface;
use Illuminate\Support\Facades\DB;

class FanService extends BaseService
{
    /**
     * @var NotificationHistoryInterface
     */
    private NotificationHistoryInterface $notificationRepository;

    /**
     * @var UserService
     */
    private UserService $userService;

    /**
     * @var FanFavoriteInterface
     */
    private FanFavoriteInterface $fanFavoriteRepository;

    /**
     * @param NotificationHistoryInterface $notificationRepository
     * @param FanFavoriteInterface $fanFavoriteRepository
     * @param UserService $userService
     */
    public function __construct(
        NotificationHistoryInterface $notificationRepository,
        FanFavoriteInterface $fanFavoriteRepository,
        UserService $userService
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->fanFavoriteRepository = $fanFavoriteRepository;
        $this->userService = $userService;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function favoriteFan(\Illuminate\Http\Request $request)
    {
        DB::beginTransaction();
        try {
            $fanFavorite = $this->fanFavoriteRepository->favoriteFan($request);
            $userAction = $fanFavorite['un_favorite'] ? ' ファンクラブの「いいね」を取り消した' : ' ファンクラブに「いいね」した';
            $authorId = DB::table('fans')->find($request->fan_id)->author_id;

            if ($this->userService->checkUserNotifyStatus($authorId)) {
                $this->notificationRepository->create([
                    'user_id' => $authorId,
                    'content' => auth()->user()->name . ' は ' . DB::table('fans')->find($request->fan_id)->title . $userAction,
                    'type' => CommonDefine::LIKE,
                    'fan_id' => $request->fan_id,
                    'created_by' => auth()->user()->id,
                    'is_read' => CommonDefine::UN_READ
                ]);
            }
            DB::commit();

            return [
                'count' => $fanFavorite['count'],
            ];
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }
}
