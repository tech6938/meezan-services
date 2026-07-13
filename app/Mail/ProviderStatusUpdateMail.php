<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProviderStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $message;
    public $status;
    public $role;

    /**
     * Create a new message instance.
     */
    public function __construct($message, $status, $role)
    {
        $this->message = $message;
        $this->status = $status;
        $this->role = $role;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Account Status Update - Meezan Services')
                    ->view('emails.providerAlertNotify');
    }
}
