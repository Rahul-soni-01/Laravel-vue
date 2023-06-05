<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Define\CommonDefine;
use Illuminate\Http\Request;
use App\Http\Requests\Livestream\CreateRequest;
use App\Services\FileService;
use App\RepositoryEloquent\Livestream\LivestreamInterface;
use App\Jobs\SendMailLivestreamStart;

class LivestreamController extends Controller
{
    /**
     * @var FileService;
     */
    private FileService $fileService;

    /**
     *@var LivestreamInterface
     */
    private LivestreamInterface $livestreamRepository;

    /**
     * @param LivestreamInterface $livestreamRepository
     */
    public function __construct(
        FileService $fileService,
        LivestreamInterface $livestreamRepository,
    ) {
        $this->fileService = $fileService;
        $this->livestreamRepository = $livestreamRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return ResponseHelper::ok($this->livestreamRepository->getList($request));
    }

    public function detail()
    {
        $authId = auth()->user()->id;

        $live = $this->livestreamRepository->getDetailLivestream($authId);

        if (!$live) {
            return ResponseHelper::ok();
        }

        return ResponseHelper::ok($live);
    }

    public function detailViewUser($authId)
    {
        $live = $this->livestreamRepository->findByCondition([
            'author_id' => $authId
        ]);

        if (!$live) {
            return ResponseHelper::bad();
        }

        return ResponseHelper::ok($live);
    }

    /**
     * Create a new livestream support.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateRequest $request)
    {
        $params = $request->all();
        $params['author_id'] = auth()->user()->id;

        if ($request->has('image') && $request->image) {
            $urlPhoto = $this->fileService->storeFileToS3($request->image, 'livestream');
            $params['image_thumbnail'] = $urlPhoto;
        }

        $create = $this->livestreamRepository->createLivestream($params);

        if ($request->plan_ids) {
            $listPlanId = [];
            foreach ($request->plan_ids as $plan_id) {
                $listPlanId[] = $plan_id;
            }
            $create->plan()->sync($listPlanId);
        } else {
            $create->plan()->sync([]);
        }

        if (!$create) {
            return ResponseHelper::bad();
        }

        if ($create->status == 1) {
            $listUserOnFan = auth()->user()->ownerFan->users;
            foreach ($listUserOnFan as $user) {
                if ($user->is_notification == CommonDefine::USER_IS_NOTIFICATION) {
                    dispatch(new SendMailLivestreamStart($user->email, [
                        'email' =>  $user->name ? $user->name : $user->email,
                        'fan_name' => auth()->user()->ownerFan->title,
                        'nickname' => auth()->user()->ownerFan->nickname,
                    ]));
                }
            }
        }

        return ResponseHelper::ok($create);
    }

    /**
     * Create a new livestream support.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $authId = auth()->user()->id;

        $live = $this->livestreamRepository->findByCondition([
            'author_id' => $authId
        ]);

        if (!$live) {
            return ResponseHelper::bad();
        }
        $params = $request->all();

        if ($request->has('image') && $request->image) {
            $urlPhoto = $this->fileService->storeFileToS3($request->image, 'livestream');
            $params['image_thumbnail'] = $urlPhoto;
        }

        $this->livestreamRepository->updateLivestream($params, $live->id);

        $live = $this->livestreamRepository->findByCondition([
            'author_id' => $authId
        ]);

        return ResponseHelper::ok($live);
    }
}
