<?php

namespace App\Console\Commands;

use App\Define\CommonDefine;
use App\Jobs\SendExpiredPlanMail;
use App\RepositoryEloquent\FanUser\FanUserInterface;
use App\RepositoryEloquent\NotificationHistory\NotificationHistoryInterface;
use App\RepositoryEloquent\Plan\PlanInterface;
use App\RepositoryEloquent\PlanUser\PlanUserInterface;
use App\RepositoryEloquent\User\UserInterface;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CheckUserExpireInPlan extends Command
{
    /**
     * @var FanUserInterface
     */

    private FanUserInterface $fanUserRepository;

    /**
     * @var NotificationHistoryInterface
     */

    private NotificationHistoryInterface $notificationHistoryRepository;

    /**
     * @var PlanUserInterface
     */

    private PlanUserInterface $planUserRepository;

    /**
     * @var UserInterface
     */

    private UserInterface $userRepository;

    /**
     * @var PlanInterface
     */

    private PlanInterface $planRepository;

    /**
     * Create a new job instance.
     * @param FanUserInterface $fanUserRepository
     * @param PlanUserInterface $planUserRepository
     * @param UserInterface $userRepository
     * @parent NotificationHistoryInterface $notificationHistoryRepository
     */
    public function __construct(
        FanUserInterface             $fanUserRepository,
        PlanUserInterface            $planUserRepository,
        UserInterface                $userRepository,
        NotificationHistoryInterface $notificationHistoryRepository
    ) {
        $this->fanUserRepository = $fanUserRepository;
        $this->planUserRepository = $planUserRepository;
        $this->userRepository = $userRepository;
        $this->notificationHistoryRepository = $notificationHistoryRepository;
        parent::__construct();
    }

    protected $signature = 'check_user_expire_plan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check user expire in plan';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $userInPlan = $this->planUserRepository->getByStatusAndPrice();
            \Log::info('------RUN COMMAND CHECK USER EXPIRE IN PLAN------');
            $userInPlan->map(function ($planUser) {
                if ($planUser->plan && !empty($planUser->user)) {
                    $now = Carbon::now()->format('Y-m-d');

                    // get planUser detail
                    $dataUserPlan = $this->planUserRepository->findCondition(
                        [
                            'id' => $planUser->id
                        ]
                    );

                    $nowCalMonth = Carbon::parse($planUser->expired_date)->format('Y-m-d');

                    if ($dataUserPlan->progress_payment == 0 && $now == $nowCalMonth && !empty($planUser->telno) && !empty($planUser->email)) {
                        \Log::info('------Start-' . $planUser->id . '-----');

                        // update progress_payment to 1
                        $planUser->progress_payment = 1;
                        $planUser->save();

                        // call api credix
                        $response = Http::timeout(3600)->get('https://secure.credix-web.co.jp/cgi-bin/secure.cgi', [
                            'clientip' => '1011004364',
                            'send' => 'cardsv',
                            'cardnumber' => '9999999999999999',
                            'expyy' => '00',
                            'expmm' => '00',
                            'money' => $planUser->payment_price,
                            'telno' => $planUser->telno,
                            'email' => $planUser->email,
                            'sendid' => 'plan_' . $planUser->user_id . '_' . $planUser->plan_id,
                            'printord' => 'yes',
                            'pubsec' => 'no',
                        ]);
                        \Log::info($response);
                        if (str_contains($response, 'Success_order')) {
                            // update expired date
                            $monthAdd = $planUser->type == 1 ? 1 : 6;
                            $planUser->expired_date = Carbon::parse($planUser->expired_date)->addMonth($monthAdd)->format('Y-m-d 00:00:00');
                            $planUser->progress_payment = 0;
                            $planUser->save();
                            \Log::info('------Update expired date success------');
                        } else {
                            \Log::info('------Payment fail. Change status to CANCELLED------');
                            $planUser->status = CommonDefine::PAYMENT_CANCELLED;
                            $planUser->progress_payment = 0;
                            $planUser->save();
                            \Log::info('------Update status date success------');
                        }
                        \Log::info('------End------');
                    }
                }
            });
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * @param $planUser
     * @return void
     * @throws BindingResolutionException
     */
    public function sendNotifyAndMail($planUser)
    {
        //send notify to user
        $this->notificationHistoryRepository->create([
            'user_id' => $planUser->user_id,
            'content' => 'プランの延長をリクエストする' . $planUser->plan->title,
            'type' => CommonDefine::FOLLOW,
            'fan_id' => $planUser->plan->fan->id,
            'created_by' => $planUser->plan->fan->author_id,
            'is_read' => CommonDefine::UN_READ
        ]);

        //send mail to user
        $dataSendMail = [
            'email' => $planUser->user->email,
            'plan_title' => $planUser->plan->title
        ];
        dispatch(new SendExpiredPlanMail($dataSendMail));
    }

    /**
     * @param $planUser
     * @return void
     */
    public function updateUserExpired($planUser)
    {
        //update plan user
        $this->planUserRepository->update(
            [
                'status' => CommonDefine::PAYMENT_CANCELLED
            ],
            $planUser->id
        );

        //update fan user
        $this->fanUserRepository->updateByCondition(
            [
                'fan_id' => $planUser->plan->fan->id,
                'user_id' => $planUser->user_id
            ],
            [
                'status' => CommonDefine::PAYMENT_CANCELLED
            ]
        );
    }
}
