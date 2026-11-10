<?php

namespace Javaabu\Auth\Support;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\Events\AccountLocked;
use Javaabu\Auth\Events\PasswordUpdated;
use Javaabu\Auth\Events\TwoFactorAuthenticationConfirmed;
use Javaabu\Auth\Events\TwoFactorAuthenticationDisabled;
use Laravel\Passkeys\Events\PasskeyDeleted;
use Laravel\Passkeys\Events\PasskeyRegistered;

/**
 * Logs auth events with spatie/laravel-activitylog, when it's installed and javaabu-auth.log_activity is on
 */
class ActivityLogger
{
    /** Event class => activity description */
    public const array EVENTS = [
        Login::class => 'login',
        Logout::class => 'logout',
        Failed::class => 'failed_login',
        AccountLocked::class => 'lockout',
        PasswordReset::class => 'password_reset',
        PasswordResetLinkSent::class => 'password_reset_link_sent',
        PasswordUpdated::class => 'password_changed',
        TwoFactorAuthenticationConfirmed::class => 'two_factor_enabled',
        TwoFactorAuthenticationDisabled::class => 'two_factor_disabled',
        PasskeyRegistered::class => 'passkey_added',
        PasskeyDeleted::class => 'passkey_removed',
    ];

    public function __construct(
        protected Dispatcher $events,
    ) {}

    public static function isAvailable(): bool
    {
        return config('javaabu-auth.log_activity', true) && function_exists('activity');
    }

    public function register(): void
    {
        foreach (self::EVENTS as $event => $description) {
            $this->events->listen($event, function (object $event) use ($description) {
                $this->log($event, $description);
            });
        }
    }

    public function log(object $event, string $description): void
    {
        if (! static::isAvailable()) {
            return;
        }

        $user = $event->user ?? null;

        if (! $user instanceof Model) {
            return;
        }

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(array_filter([
                'guard' => $event->guard ?? null,
                'ip' => request()->ip(),
            ]))
            ->log($description);
    }
}
