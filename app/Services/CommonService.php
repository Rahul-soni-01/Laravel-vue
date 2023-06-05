<?php

namespace App\Services;

use App\Define\CommonDefine;
use App\RepositoryEloquent\PlanUser\PlanUserInterface;

class CommonService extends BaseService
{
    /**
     * @var PlanUserInterface
     */
    private PlanUserInterface $planUserRepository;

    /**
     * @param  PlanUserInterface $planUserRepository
     */
    public function __construct(
        PlanUserInterface $planUserRepository
    ) {
        $this->planUserRepository = $planUserRepository;
    }

    /**
     * Check user in plan
     * @param $userId
     * @param $planId
     * @return bool
     * @throws \Exception
     */
    public function checkUserInFan($userId, $planId): bool
    {
        try {
            if (!$planId) return true;
            $userFind = $this->planUserRepository->findCondition(
                [
                    'plan_id' => $planId,
                    'user_id' => $userId,
                ]
            );


            return (bool)$userFind;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getPlanOfCurrentUser($userId)
    {
        try {
            $userFind = $this->planUserRepository->getPlanOfCurrentUser($userId);
            return $userFind;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function checkUserInPlanStreaming($userId, $planIds)
    {
        try {
            if (!$planIds) return true;

            foreach ($planIds as $key => $planId) {
                $userFind = $this->planUserRepository->findCondition(
                    [
                        'plan_id' => $planId,
                        'user_id' => $userId,
                        'status' => 1
                    ]
                );

                if ($userFind) {
                    return (bool)$userFind;
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getPlanOfUser($userId)
    {
        try {
            $userFind = $this->planUserRepository->getPlanOfUser($userId);
            return $userFind;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
