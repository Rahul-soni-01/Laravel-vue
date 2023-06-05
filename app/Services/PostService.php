<?php

namespace App\Services;

use App\Define\CommonDefine;
use App\RepositoryEloquent\NotificationHistory\NotificationHistoryInterface;
use App\RepositoryEloquent\PostFavorite\PostFavoriteInterface;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;


class PostService extends BaseService
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
     * @var PostFavoriteInterface
     */
    private PostFavoriteInterface $postFavoriteRepository;

    /**
     * @param NotificationHistoryInterface $notificationRepository
     * @param PostFavoriteInterface $postFavoriteRepository
     * @param UserService $userService
     */
    public function __construct(
        NotificationHistoryInterface $notificationRepository,
        PostFavoriteInterface $postFavoriteRepository,
        UserService $userService
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->postFavoriteRepository = $postFavoriteRepository;
        $this->userService = $userService;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function favoritePost(\Illuminate\Http\Request $request)
    {
        DB::beginTransaction();
        try {
            $postFavorite = $this->postFavoriteRepository->favoritePost($request);
            $userAction = $postFavorite['un_favorite'] ? ' 投票の「いいね」を取り消した' : ' 投票に「いいね」した';
            $userAuthorPostId = DB::table('posts')->find($request->post_id)->author_id;

            if ($this->userService->checkUserNotifyStatus($userAuthorPostId)) {
                $this->notificationRepository->create([
                    'user_id' => $userAuthorPostId,
                    'content' => auth()->user()->name . ' は ' . DB::table('posts')->find($request->post_id)->title . $userAction,
                    'type' => CommonDefine::LIKE,
                    'post_id' => $request->post_id,
                    'created_by' => auth()->user()->id,
                    'is_read' => CommonDefine::UN_READ
                ]);
            }
            DB::commit();

            return [
                'count' => $postFavorite['count'],
            ];
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }
}
