<?php

    namespace App\Services;

    use App\Define\CommonDefine;
    use App\RepositoryEloquent\User\UserInterface;

    class UserService extends BaseService {
        /**
         * @var  UserInterface
         */
        private UserInterface $userRepository;

        /**
         *  @param UserInterface $userRepository
         */
        public function __construct(UserInterface $userRepository)
        {
            $this->userRepository = $userRepository;
        }

        /**
         * @param $userId
         * @return bool
         */
        public function checkUserNotifyStatus($userId) : bool
        {
            $user = $this->userRepository->findOrFail($userId);

            return $user->is_notification == CommonDefine::USER_IS_NOTIFICATION;
        }
    }
