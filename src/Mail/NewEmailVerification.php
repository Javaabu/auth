<?php

namespace Javaabu\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Javaabu\Auth\User;

class NewEmailVerification extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var User
     */
    protected $user;

    /**
     * Create a new message instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $verificationUrl = $this->verificationUrl($this->user);

        $message = (new MailMessage())
            ->subject(Lang::get('Verify New Email Address'))
            ->greeting(Lang::get('Hi :user,', ['user' => $this->user->name]))
            ->line(Lang::get('You are receiving this email since you have requested to update your email address to this new email.'))
            ->line(Lang::get('Please click the button below to verify your new email address.'))
            ->action(Lang::get('Verify New Email Address'), $verificationUrl)
            ->line(Lang::get('If you did not request to update your email, no further action is required.'));

        return $this->markdown('vendor.notifications.email')->with($message->data());
    }

    /**
     * Get the verification URL for the given user.
     *
     * @param User $user
     * @return string
     */
    protected function verificationUrl(User $user)
    {
        return URL::temporarySignedRoute(
            $user->getRouteForEmailVerification(),
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }
}
