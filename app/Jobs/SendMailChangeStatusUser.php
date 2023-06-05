<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\MailNotifyChangeStatus;
use Illuminate\Support\Facades\Mail;

class SendMailChangeStatusUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var $user
     */
    private $user;

    /**
     * @var $data
     */
    private $data;



    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $user,
        $data
    ) {
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $email = new MailNotifyChangeStatus($this->data);
        return  Mail::to($this->user)->send($email);
    }
}