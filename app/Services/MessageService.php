<?php

namespace App\Services;

use App\Define\CommonDefine;
use App\Http\Requests\Message\CreateRequest;
use App\RepositoryEloquent\Message\MessageInterface;
use App\RepositoryEloquent\MessageDetail\MessageDetailInterface;
use Illuminate\Support\Facades\DB;

class MessageService extends BaseService
{
    /**
     * @var MessageInterface
     */
    private MessageInterface $messageRepository;

    /**
     * @var MessageDetailInterface
     */
    private MessageDetailInterface $messageDetailRepository;

    /**
     * @var FileService
     */
    private FileService $fileService;

    public function __construct(
        MessageInterface $messageRepository,
        FileService      $fileService,
        MessageDetailInterface $messageDetailRepository
    ) {
        $this->messageRepository = $messageRepository;
        $this->fileService = $fileService;
        $this->messageDetailRepository = $messageDetailRepository;
    }

    public function createMessage(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->only([
                'receiver_id',
                'message'
            ]);

            $messageExist = $this->messageRepository->getByConditions(
                [],
                [
                    'user_id' => $request->receiver_id,
                    'receiver_id' => auth()->user()->id,
                ],
            )->first();

            $paramsCreate = [
                'user_id' => auth()->user()->id,
                'is_read' => CommonDefine::UN_READ,
                'receiver_id' => $request->receiver_id,
                'delete_by' => 0
            ];

            if ($messageExist) {
                $createMessage = $this->messageRepository->update($paramsCreate, $messageExist->id);
            } else {
                $createMessage = $this->messageRepository->updateOrCreate(
                    [
                        'user_id' => auth()->user()->id,
                        'receiver_id' => $request->receiver_id
                    ],
                    $paramsCreate
                );
            }

            if (!$createMessage) {
                return false;
            }
            $paramsDetail = [
                'message_id' => $messageExist ? $messageExist->id : $createMessage->id,
                'user_id' => auth()->user()->id,
                'receiver_id' => $request->receiver_id,
                'content' => $request->message,
                'url_type' => 0
            ];

            $createDetail = $this->messageDetailRepository->createMessageDetail($paramsDetail);
            if (!$createDetail) {
                return false;
            }
            DB::commit();

            return true;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    public function createListMessage(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $listReceiverId = explode(',', $request->list_receiver_id);
            foreach ($listReceiverId as $receiver_id) {
                $messageExist = $this->messageRepository->getByConditions(
                    [],
                    [
                        'user_id' => $receiver_id,
                        'receiver_id' => auth()->user()->id,
                    ],
                )->first();

                $paramsCreate = [
                    'user_id' => auth()->user()->id,
                    'is_read' => CommonDefine::UN_READ,
                    'receiver_id' => $receiver_id,
                    'delete_by' => 0
                ];

                if ($messageExist) {
                    $createMessage = $this->messageRepository->update($paramsCreate, $messageExist->id);
                } else {
                    $createMessage = $this->messageRepository->updateOrCreate(
                        [
                            'user_id' => auth()->user()->id,
                            'receiver_id' => $receiver_id
                        ],
                        $paramsCreate
                    );
                }

                if (!$createMessage) {
                    return false;
                }
                $paramsDetail = [
                    'message_id' => $messageExist ? $messageExist->id : $createMessage->id,
                    'user_id' => auth()->user()->id,
                    'receiver_id' => $receiver_id,
                    'content' => $request->message,
                    'url_type' => 0
                ];

                $createDetail = $this->messageDetailRepository->createMessageDetail($paramsDetail);

                if (!$createDetail) {
                    return false;
                }
            }

            DB::commit();

            return true;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }
}
