<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $token;

    public function __construct($user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function build()
    {
        $appUrl = config('app.url');
        $fallbackLink = URL::temporarySignedRoute(
            'password.reset.web', now()->addMinutes(60), [
                'email' => $this->user->email,
                'token' => $this->token,
            ]
        );

        return $this->subject('Reset Password Instructions')
            ->view('emails.reset-password')
            ->with([
                'name' => $this->user->name,
                'token' => $this->token,
                'fallback_link' => $fallbackLink,
                'app_url' => $appUrl,
            ]);
    }
}
