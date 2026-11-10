<?php

namespace Javaabu\Auth\Policies;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;

/**
 * What admins may do to other users' accounts. The abilities take the managed user type
 * and the account: `Gate::allows('auth.unlock', [$type, $account])`.
 *
 * | Ability                       | Permission                         |
 * | ----------------------------- | ---------------------------------- |
 * | auth.unlock                   | unlock_{type plural}               |
 * | auth.reset-two-factor         | reset_two_factor_{type plural}     |
 * | auth.require-password-update  | require_password_update_{type plural} |
 * | auth.log-out                  | log_out_{type plural}              |
 *
 * Replace it with `javaabu-auth.policy`, or redefine an ability with Gate::define().
 */
class UserAccountPolicy
{
    /** ability => action, the permission being `{action}_{resource}` */
    public const array ABILITIES = [
        'auth.unlock' => 'unlock',
        'auth.reset-two-factor' => 'reset_two_factor',
        'auth.require-password-update' => 'require_password_update',
        'auth.log-out' => 'log_out',
    ];

    /** English descriptions of the permissions, used as translation keys */
    public const array DESCRIPTIONS = [
        'unlock' => 'Unlock locked :resource',
        'reset_two_factor' => 'Reset two-factor authentication of :resource',
        'require_password_update' => 'Make :resource choose a new password',
        'log_out' => 'Log out :resource from every device',
    ];

    public function unlock(Model $user, UserType $type, ?Model $account = null): bool
    {
        return $this->allows($user, 'unlock', $type);
    }

    public function resetTwoFactor(Model $user, UserType $type, ?Model $account = null): bool
    {
        return $this->allows($user, 'reset_two_factor', $type);
    }

    public function requirePasswordUpdate(Model $user, UserType $type, ?Model $account = null): bool
    {
        return $this->allows($user, 'require_password_update', $type);
    }

    public function logOut(Model $user, UserType $type, ?Model $account = null): bool
    {
        return $this->allows($user, 'log_out', $type);
    }

    /**
     * The resource part of the permission names: the plural of the type, e.g. `users`, `admins`
     */
    public static function resource(UserType $type): string
    {
        return str($type->name())->plural()->snake()->toString();
    }

    public static function permission(string $action, UserType $type): string
    {
        return "{$action}_" . static::resource($type);
    }

    protected function allows(Model $user, string $action, UserType $type): bool
    {
        return $user->can(static::permission($action, $type));
    }
}
