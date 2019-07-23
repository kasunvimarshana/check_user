<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Mail;
use App\Mail\SendCheckUserMail;
use App\User;

class SendCheckUserEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_array;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_array)
    {
        //
        $this->user_array = $user_array;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $user_array = $this->user_array;
        if( isset($user_array) ){
            $toUserArray = array('kasunv@brandix.com', 'Prabhathdh@brandix.com', 'SumithK@brandix.com', 'SamithaSu@brandix.com', 'PoornimalA@brandix.com', 'TharangaWij@brandix.com');
            Mail::to($toUserArray)
                //->subject("Subject")
                //->cc($ccUserArray)
                //->bcc($bccUserArray)
                ->send(new SendCheckUserMail($user_array));
        }
    }
}
