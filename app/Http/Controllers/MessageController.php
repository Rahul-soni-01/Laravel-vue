<?php

namespace App\Http\Controllers;

use App\Define\CommonDefine;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Message\CreateRequest;
use App\Http\Requests\MessageDetail\CreateRequestMessageDetail;
use App\RepositoryEloquent\Message\MessageInterface;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\RepositoryEloquent\MessageDetail\MessageDetailInterface;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * @var MessageInterface
     */
    private MessageInterface $messageRepository;

    /**
     * @var MessageService
     */
    private MessageService $messageService;

    /**
     * @var MessageDetailInterface
     */
    private MessageDetailInterface $messageDetailRepository;

    /**
     * @var FileService
     */
    private FileService $service;

    /**
     * @param MessageInterface $messageRepository
     * @param MessageService $messageService
     * @param MessageDetailInterface $messageDetailRepository
     * @param FileService $service
     */
    public function __construct(
        MessageInterface $messageRepository,
        MessageService $messageService,
        MessageDetailInterface $messageDetailRepository,
        FileService $service
    ) {
        $this->messageRepository = $messageRepository;
        $this->messageService = $messageService;
        $this->messageDetailRepository = $messageDetailRepository;
        $this->service = $service;
    }

    /**
     * Get messages between logged-in user and another user
     * @param Request $request
     * @return Response
     */
    public function getMessagesUser(Request $request)
    {
        $messages = $this->messageRepository->getMessages($request);

        return ResponseHelper::ok($messages);
    }

    /**
     * Get messages between logged-in user and another user
     * @param Request $request
     * @return Response
     */
    public function getMessagesCreator(Request $request)
    {
        $messages = $this->messageRepository->getMessagesCreator($request);

        return ResponseHelper::ok($messages);
    }

    /**
     * Get messages between logged-in user and another user
     * @return Response
     */
    public function getMessagesUnread()
    {
        return ResponseHelper::ok($this->messageRepository->getMessagesUnread());
    }

    /**
     * Create message
     * @param CreateRequest $request
     * @return Response
     */
    public function createMessage(CreateRequest $request)
    {
        if (isset($request->receiver_id)) {
            $createMessage = $this->messageService->createMessage($request);
        } else {
            $createMessage = $this->messageService->createListMessage($request);
        }

        if (!$createMessage) {
            ResponseHelper::bad();
        }

        return ResponseHelper::ok();
    }

    /**
     * Update message to is_read status
     * @param $id
     * @return Response
     */
    public function readMessage($id)
    {
        $this->messageRepository->readMessage($id);

        return ResponseHelper::ok();
    }

    /**
     * Delete message
     * @param $id
     * @return Response
     */
    public function deleteMessage($id)
    {
        $update = $this->messageRepository->update(
            [
                'delete_by' => auth()->user()->id
            ],
            $id
        );

        if (!$update) {
            return ResponseHelper::bad();
        }

        return ResponseHelper::ok();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getDetailMessage(Request $request)
    {
        return ResponseHelper::ok($this->messageDetailRepository->getDetailMessage($request));
    }

    /**
     * @param CreateRequestMessageDetail $request
     * @return Response
     * @throw \Exception
     */
    public function createMessageDetail(CreateRequestMessageDetail $request)
    {
        try {
            DB::beginTransaction();
            $paramsDetail = [
                'content' => $request->get('content'),
                'message_id' => $request->message_id,
                'url_type' => $request->url_type
            ];

            if ($request->has('file') && $request->file) {
                $paramsDetail['url'] = $this->service->storeFileToS3($request->file, 'message');
            }

            $messageExist = $this->messageRepository->getByConditions(
                [],
                [
                    'user_id' => $request->receiver_id,
                    'receiver_id' => auth()->user()->id,
                ],
            )->first();

            if ($messageExist) {
                $this->messageRepository->update(
                    [
                        'user_id' => auth()->user()->id,
                        'is_read' => CommonDefine::UN_READ,
                        'receiver_id' => $request->receiver_id,
                        'delete_by' => 0
                    ],
                    $messageExist->id
                );
                $paramsDetail['user_id'] = $messageExist->receiver_id;
                $paramsDetail['receiver_id'] = auth()->user()->id;
            } else {
                $this->messageRepository->updateByCondition(
                    [
                        'user_id' => auth()->user()->id,
                        'receiver_id' => $request->receiver_id,
                    ],
                    [
                        'is_read' => CommonDefine::UN_READ,

                    ]
                );
                $paramsDetail['user_id'] = auth()->user()->id;
                $paramsDetail['receiver_id'] = $request->receiver_id;
            }

            $create = $this->messageDetailRepository->createMessageDetail($paramsDetail);

            if (!$create) {
                return ResponseHelper::bad();
            }
            DB::commit();

            return ResponseHelper::ok();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }
}
