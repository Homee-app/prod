<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminTwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $user)
    {
    
    }

    public function build()
    {
        return $this->subject('Your Admin Verification Code')->view('emails.admin_otp')->with(['otp' => $this->user->otp_code]);
    }
}
