<?php

namespace App\Http\Controllers;

use App\Define\CommonDefine;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Comment\CreateRequest;
use App\RepositoryEloquent\Comment\CommentInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\RepositoryEloquent\NotificationHistory\NotificationHistoryInterface;
use Illuminate\Support\Facades\DB;
use App\RepositoryEloquent\Post\PostInterface;
use App\RepositoryEloquent\Product\ProductInterface;
use App\Services\UserService;

class CommentController extends Controller
{
    /**
     * @var CommentInterface
     */
    private CommentInterface $commentRepository;

    /**
     * @var UserService
     */
    private UserService $userService;

    /**
     * @var PostInterface
     */
    private PostInterface $postRepository;

    /**
     * @var ProductInterface
     */
    private ProductInterface $productRepository;

    /**
     * @var NotificationHistoryInterface
     */
    private NotificationHistoryInterface $notificationRepository;

    /**
     * @param CommentInterface $commentRepository
     * @param NotificationHistoryInterface $notificationRepository
     * @param PostInterface $postRepository
     * @param ProductInterface $productRepository
     * @param UserService $userService
     */
    public function __construct(
        CommentInterface $commentRepository,
        NotificationHistoryInterface $notificationRepository,
        ProductInterface $productRepository,
        PostInterface $postRepository,
        UserService $userService
    ) {
        $this->commentRepository = $commentRepository;
        $this->notificationRepository = $notificationRepository;
        $this->postRepository = $postRepository;
        $this->productRepository = $productRepository;
        $this->userService = $userService;
    }

    /**
     * Get list comment.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return ResponseHelper::ok($this->commentRepository->list($request));
    }

    /**
     * Create a comment
     *
     * @return Response
     */
    public function create(CreateRequest $request)
    {
        try {
            DB::beginTransaction();
            $create = $this->commentRepository->createComment($request);

            if (!$create) {
                return ResponseHelper::bad();
            }
            $item = $create->product_id
                ? $this->productRepository->findProduct($create->product_id)
                : $this->postRepository->findOrFail($create->post_id);

            if ($this->userService->checkUserNotifyStatus($item->author_id)) {
                $this->notificationRepository->create(
                    [
                        'user_id' => $item->author_id,
                        'content' => auth()->user()->name . ' コメントした',
                        'type' => CommonDefine::COMMENT,
                        'post_id' => $comment->post_id ?? null,
                        'product_id' => $comment->product_id ?? null,
                        'fan_id' => $comment->fan_id ?? null,
                        'created_by' => auth()->user()->id,
                        'is_read' => CommonDefine::UN_READ
                    ]
                );
            }
            //            $listComment = $this->commentRepository->getByConditions(
            //                [],
            //                [
            //                    'post_id' => $create->post_id ?? null,
            //                    'product_id' => $create->product_id,
            //                    'type' => $create->type
            //                ]
            //            );
            //
            //            $listComment = $listComment
            //                ->unique('user_id')
            //                ->where('user_id', '<>', auth()->user()->id);
            //
            //            if ($listComment->isNotEmpty()) {
            //                foreach ($listComment->unique('id') as $comment) {
            //                    $this->notificationRepository->create(
            //                        [
            //                            'user_id' => $comment->user_id,
            //                            'content' => auth()->user()->name . ' comment',
            //                            'type' => CommonDefine::COMMENT,
            //                            'post_id' => $comment->post_id ?? null,
            //                            'product_id' => $comment->product_id ?? null,
            //                            'fan_id' => $comment->fan_id ?? null,
            //                            'created_by' => auth()->user()->id,
            //                            'is_read' => CommonDefine::UN_READ
            //                        ]
            //                    );
            //                }
            //            }

            DB::commit();

            return ResponseHelper::ok();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    public function insert(Request $request)
    {
        if ($request->listComment && count($request->listComment) > 0) {
            $this->commentRepository->insert($request->listComment);
        }

        return ResponseHelper::ok();
    }

    /**
     * Remove a comment.
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function destroy($id)
    {
        $this->commentRepository->destroy($id);

        return ResponseHelper::ok();
    }

    public function deleteByUser(Request $request)
    {
        $userDestroy = $this->commentRepository->deleteComment($request);

        if (!$userDestroy) {
            return  ResponseHelper::bad();
        }

        return  ResponseHelper::ok();
    }
}
