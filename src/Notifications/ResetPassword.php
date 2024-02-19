<?php

namespace Javaabu\Auth\Notifications;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;

class ResetPassword extends \Illuminate\Auth\Notifications\ResetPassword
{
    /**
     * The callback that should be used to create the reset password URL.
     *
     * @var Closure|null
     */
    public static $createUrlCallback = [self::class, 'createPasswordResetUrl'];

    /**
     *
     * @param $notifiable
     * @param $token
     * @return Application|UrlGenerator|string
     */
    public static function createPasswordResetUrl($notifiable, $token): string|UrlGenerator|Application
    {
        return url(route($notifiable->getRouteForPasswordReset(), [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
