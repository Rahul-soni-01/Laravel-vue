<?php

namespace App\Services;

use App\Define\CommonDefine;
use App\RepositoryEloquent\NotificationHistory\NotificationHistoryInterface;
use App\RepositoryEloquent\Product\ProductInterface;
use App\RepositoryEloquent\ProductFavorite\ProductFavoriteInterface;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;

class ProductService extends BaseService
{
    /**
     * @var ProductFavoriteInterface
     */
    private ProductFavoriteInterface $productFavoriteRepository;

    /**
     * @var UserService
     */
    private UserService $userService;

    /**
     * @var NotificationHistoryInterface
     */
    private NotificationHistoryInterface $notificationRepository;

    /**
     * @param ProductFavoriteInterface $productFavoriteRepository
     * @param NotificationHistoryInterface $notificationRepository
     * @param UserService $userService
     */
    public function __construct(
        ProductFavoriteInterface     $productFavoriteRepository,
        NotificationHistoryInterface $notificationRepository,
        UserService $userService
    )
    {
        $this->productFavoriteRepository = $productFavoriteRepository;
        $this->notificationRepository = $notificationRepository;
        $this->userService = $userService;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function favoriteProduct(\Illuminate\Http\Request $request)
    {
        DB::beginTransaction();
        try {
            $productFavorite = $this->productFavoriteRepository->favoriteProduct($request);
            $userAction = $productFavorite['un_favorite'] ? ' 商品の「いいね」を取り消した' : ' 商品に「いいね」した';
            $userAuthorId = DB::table('products')->find($request->product_id)->author_id;

            if ($this->userService->checkUserNotifyStatus($userAuthorId)) {
                $this->notificationRepository->create([
                    'user_id' => $userAuthorId,
                    'content' => auth()->user()->name . ' は ' . DB::table('products')->find($request->product_id)->title . $userAction,
                    'type' => CommonDefine::LIKE,
                    'product_id' => $request->product_id,
                    'created_by' => auth()->user()->id,
                    'is_read' => CommonDefine::UN_READ
                ]);
            }
            DB::commit();

            return [
                'count' => $productFavorite['count'],
            ];
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }
}
