<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCheckUserMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $check_user_data_array;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($check_user_data_array)
    {
        //
        $this->check_user_data_array = $check_user_data_array;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //return $this->view('view.name');
        $check_user_data_array = $this->check_user_data_array;
        $message = $this;
        
        $message = (isset($check_user_data_array['message_title'])) ? $message->subject( $check_user_data_array['message_title'] ) : $message->subject("Users");
        $message = $message->view('mail.check_user_mail')->with([
            'check_user_data_array' => $check_user_data_array
        ]);
        
        return $message;
    }
}
