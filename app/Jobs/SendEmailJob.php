<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Mail;

class SendEmailJob extends Job
{

    private $details;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details=$details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $details=$this->details;
        Mail::send($details['type'], $details, function($message)use($details){
            $message->to($details['to'], $details['name'])->subject($details['subject']);
            $message->from(env("MAIL_USERNAME"), env("MAIL_FROM_NAME"));
        });
    }
}
