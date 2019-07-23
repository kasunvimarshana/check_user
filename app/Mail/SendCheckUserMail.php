<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCheckUserMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user_array;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user_array)
    {
        //
        $this->user_array = $user_array;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //return $this->view('view.name');
        $user_array = $this->user_array;
        $message = $this;
        
        $message = $message->subject("Users");
        $message = $message->view('mail.check_user_mail')->with([
            'user_array' => $user_array
        ]);
        
        return $message;
    }
}
