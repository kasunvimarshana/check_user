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

    protected $check_user_data_array;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($check_user_data_array)
    {
        //
        $this->check_user_data_array = $check_user_data_array;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $check_user_data_array = $this->check_user_data_array;
        $to_user_array = array();
        if( (isset( $check_user_data_array['check_user_data'] )) && (!empty( $check_user_data_array['check_user_data'] )) ){
            $to_user_array = $check_user_data_array['check_user_data']['mail_user_array_to'];
        }
        if( (isset($check_user_data_array)) && (!empty( $to_user_array )) ){
            $toUserArray = $to_user_array;
            Mail::to($toUserArray)
                //->subject("Subject")
                //->cc($ccUserArray)
                //->bcc($bccUserArray)
                ->send(new SendCheckUserMail($check_user_data_array));
        }
    }
}
