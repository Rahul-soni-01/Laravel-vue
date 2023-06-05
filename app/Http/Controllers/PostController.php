<?php

namespace App\Http\Controllers;

use App\Define\CommonDefine;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Post\CreateRequest;
use App\Http\Requests\Post\ListPostRequest;
use App\Http\Requests\Post\UpdateRequest;
use App\RepositoryEloquent\Post\PostInterface;
use App\RepositoryEloquent\PostFavorite\PostFavoriteInterface;
use App\RepositoryEloquent\Tag\TagInterface;
use App\Services\FileService;
use App\Services\PostService;
use Illuminate\Http\Request;
use App\RepositoryEloquent\NotificationHistory\NotificationHistoryInterface;
use App\RepositoryEloquent\Fan\FanInterface;
use Illuminate\Support\Facades\DB;
use App\Services\CommonService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * @var PostInterface
     */
    private PostInterface $postRepository;

    /**
     * @var NotificationHistoryInterface
     */
    private NotificationHistoryInterface $notificationHistoryRepository;

    /**
     * @var FanInterface
     */
    private FanInterface $fanRepository;

    /**
     * @var FileService $service
     */
    private  FileService $service;

    /**
     * @var TagInterface
     */
    private  TagInterface $tagRepository;

    /**
     * @var PostFavoriteInterface
     */
    private PostFavoriteInterface $postFavoriteRepository;

    /**
     * @var PostService
     */
    private PostService $postService;

    /**
     * @var CommonService
     */
    private CommonService $commonService;

    public function __construct(
        PostInterface $postRepository,
        FileService $service,
        TagInterface $tagRepository,
        PostFavoriteInterface $postFavoriteRepository,
        NotificationHistoryInterface $notificationHistoryRepository,
        FanInterface $fanRepository,
        PostService $postService,
        CommonService $commonService
    ) {
        $this->postRepository = $postRepository;
        $this->service = $service;
        $this->tagRepository = $tagRepository;
        $this->postFavoriteRepository = $postFavoriteRepository;
        $this->notificationHistoryRepository = $notificationHistoryRepository;
        $this->fanRepository = $fanRepository;
        $this->postService = $postService;
        $this->commonService = $commonService;
    }

    public function getPreSigned(Request $request)
    {
        $client = Storage::disk('s3')->getClient();
        $bucket = config('filesystems.disks.s3.bucket');
        $fileName = Str::random(20) . '_' . $request->file_name;
        $filePath =  'post/' . $fileName;
        $command = $client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => $filePath
        ]);

        $request = $client->createPresignedRequest($command, '+20 minutes');

        return [
            'file_path' => $filePath,
            'pre_signed' => (string) $request->getUri(),
        ];
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index(ListPostRequest $request)
    {
        $data = $this->postRepository->getList($request);
        return ResponseHelper::ok($data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function detail(Request $request)
    {
        if (!isset($request->is_creator)) {
            $userCurrent = auth()->user();
            $post = $this->postRepository->findOrFail($request->route('id'));

            if ($post->author_id == $userCurrent->id) {
                return ResponseHelper::ok($this->postRepository->getDetail($request->route('id')));
            }

            $checkUserOfPlan = $this->commonService->checkUserInFan($userCurrent->id, $post->plan_id);
            if (!$checkUserOfPlan) {
                return ResponseHelper::forbidden();
            }
        }

        $data = $this->postRepository->getDetail($request->route('id'));

        return ResponseHelper::ok($data);
    }

    public function detailAdmin($id)
    {
        $data = $this->postRepository->getDetail($id);

        return ResponseHelper::ok($data);
    }

    /**
     *  create data post
     *
     * @param CreateRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function create(CreateRequest $request)
    {
        try {
            DB::beginTransaction();

            $urlFile = null;
            if ($request->file !== null) {
                $urlFile = $this->service->storeFileToS3($request->file, 'post');
            }

            if ($request->video_file !== null) {
                $urlFileVideo = $request->video_file;
            } else {
                $urlFileVideo = '';
            }

            $dataCreate = $this->postRepository->createData($request, $urlFile, $urlFileVideo);

            if (!$dataCreate) {
                return ResponseHelper::bad([], 'Create data Post Error');
            }

            if ($request->tags) {
                $listTagId = [];
                foreach ($request->tags as $tag) {
                    $tagName = $this->tagRepository->findTagName($tag);
                    $tagId = $tagName !== null ? $tagName->id : null;
                    if ($tagName == null) {
                        $dataTag = [
                            "author_id" => auth()->user()->id,
                            "name" => $tag,
                        ];
                        $tagCreate = $this->tagRepository->create($dataTag);
                        $tagId = $tagCreate->id;
                    }

                    $listTagId[] = $tagId;
                }
                $dataCreate->tags()->sync($listTagId);
            }

            $fan = $this->fanRepository->getByAuthor(auth()->user()->id);

            if ($fan && $dataCreate->is_public) {
                $fan->users->map(function ($user) use ($fan, $dataCreate) {
                    if ($user->pivot->status && $user->is_notification) {
                        $notifyParams = [
                            'content'  => $fan->title . ' 新しい投稿を投稿しました',
                            'type' => CommonDefine::NEW_PRODUCT_OR_POST,
                            'user_id' => $user->id,
                            'post_id' => $dataCreate->id,
                            'fan_id' => $fan->id,
                            'created_by' => auth()->user()->id
                        ];

                        $this->notificationHistoryRepository->create($notifyParams);
                    }
                });
            }
            DB::commit();

            return ResponseHelper::ok([], 'Create Data Post Success');
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * Update data post
     *
     * @param UpdateRequest $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function update(UpdateRequest $request, $id)
    {
        try {
            $urlFile = null;
            if ($request->file !== null) {
                $urlFile = $this->service->storeFileToS3($request->file, 'post');
            }

            if ($request->video_file !== null) {
                $urlFileVideo = $request->video_file;
            } else {
                $urlFileVideo = '';
            }

            $dataUpdate = $this->postRepository->updateData($request, $urlFile, $id, $urlFileVideo);
            if (!$dataUpdate) {
                return ResponseHelper::bad([], 'Update data Post Error');
            }

            if ($request->tags) {
                $listTagId = [];
                foreach ($request->tags as $tag) {
                    $tagName = $this->tagRepository->findTagName($tag);
                    $tagId = $tagName !== null ? $tagName->id : null;
                    if ($tagName == null) {
                        $dataTag = [
                            "author_id" => auth()->user()->id,
                            "name" => $tag,
                        ];
                        $tagCreate = $this->tagRepository->create($dataTag);
                        $tagId = $tagCreate->id;
                    }

                    $listTagId[] = $tagId;
                }
                $dataUpdate->tags()->sync($listTagId);
            }

            if ($request->delete_all_tag) {
                $dataUpdate->tags()->sync([]);
            }

            return ResponseHelper::ok([], 'Update Data Post Success');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }


    /**
     * update status
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function updateStatus(Request $request)
    {
        try {
            $updateStatus = $this->postRepository->updateStatus($request);
            if (!$updateStatus['success']) {
                return ResponseHelper::bad([], $updateStatus['message']);
            }
            return ResponseHelper::ok($updateStatus['message']);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * delete post
     *
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function adminDeletePost($id)
    {
        $this->postRepository->destroy($id);

        return ResponseHelper::ok();
    }

    /**
     * delete post
     *
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function creatorDeletePost($id)
    {
        $this->postRepository->destroy($id);

        return ResponseHelper::ok();
    }

    /**
     * Update product's public status.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePostPublic(Request $request)
    {
        $data = $this->postRepository->updatePostPublic($request);
        if (!$data) {
            return ResponseHelper::bad();
        }

        return ResponseHelper::ok();
    }

    /**
     * Get list post.
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function creatorListPost(ListPostRequest $request)
    {
        $authorId = auth()->user()->id;
        $request['author_id'] = $authorId;
        $data = $this->postRepository->getList($request);
        return ResponseHelper::ok($data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function favoritePost(Request $request)
    {
        return ResponseHelper::ok($this->postService->favoritePost($request));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function getFavoritePost(Request $request)
    {
        try {
            $data = $this->postFavoriteRepository->getFavoritePost($request);
            if (!$data) {
                return ResponseHelper::bad();
            }

            return ResponseHelper::ok($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getFavoritePosts(Request $request)
    {
        return ResponseHelper::ok($this->postRepository->getFavoritePosts($request));
    }

    public function listPostOnFan($creatorId)
    {
        return ResponseHelper::ok($this->postRepository->getListPosts($creatorId));
    }

    public function getListByKeyword(Request $request)
    {
        $data = $this->postRepository->getListByKeyword($request);

        return ResponseHelper::ok($data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function listPostUser(Request $request)
    {
        $data = $this->postRepository->getListPostPublic($request);
        return ResponseHelper::ok($data);
    }
}
