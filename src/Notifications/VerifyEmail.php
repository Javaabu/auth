<?php

namespace Javaabu\Auth\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Javaabu\Auth\UserTypes;

/**
 * Laravel's verification email, linking to the user type's verification route
 */
class VerifyEmail extends BaseVerifyEmail
{
    public function __construct(
        public ?string $user_type = null,
    ) {}

    protected function verificationUrl($notifiable): string
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        $type = $this->user_type
            ? UserTypes::get($this->user_type)
            : (UserTypes::for($notifiable) ?? UserTypes::default());

        return URL::temporarySignedRoute(
            $type->routeNameFor('verification.verify'),
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );
    }
}
