<?php

namespace Javaabu\Auth\Notifications;

class ResetPassword extends \Illuminate\Auth\Notifications\ResetPassword
{
    /**
     * The callback that should be used to create the reset password URL.
     *
     * @var \Closure|null
     */
    public static $createUrlCallback = [self::class, 'createPasswordResetUrl'];

    /**
     *
     * @param $notifiable
     * @param $token
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public static function createPasswordResetUrl($notifiable, $token)
    {
        return url(route($notifiable->getRouteForPasswordReset(), [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
