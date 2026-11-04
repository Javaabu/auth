<?php

namespace Javaabu\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Javaabu\Auth\UserTypes;

/**
 * Laravel's password reset email, linking to the user type's reset page
 */
class ResetPassword extends BaseResetPassword
{
    public function __construct(
        $token,
        public ?string $user_type = null,
    ) {
        parent::__construct($token);
    }

    protected function resetUrl($notifiable): string
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        $type = $this->user_type
            ? UserTypes::get($this->user_type)
            : (UserTypes::for($notifiable) ?? UserTypes::default());

        return $type->route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
