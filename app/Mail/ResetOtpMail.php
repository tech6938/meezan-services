<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $role;

    public function __construct($otp, $role)
    {
        $this->otp = $otp;
        $this->role = $role;
    }

    public function build()
    {
        return $this->subject('Password Reset OTP')
                    ->view('emails.reset_otp');
    }
}
