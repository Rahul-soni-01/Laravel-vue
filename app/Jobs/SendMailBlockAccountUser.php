<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\MailNotifyBlockAccount;
use Illuminate\Support\Facades\Mail;

class SendMailBlockAccountUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var $email
     */
    private $email;

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
        $email,
        $data
    ) {
        $this->email = $email;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = new MailNotifyBlockAccount($this->data);
        return Mail::to($this->email)->send($data);
    }
}
