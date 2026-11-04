<?php

namespace Javaabu\Auth\Support;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Personal access tokens with whichever of Sanctum or Passport the user model uses
 */
class ApiTokens
{
    public const string SANCTUM = 'sanctum';

    public const string PASSPORT = 'passport';

    /** The drivers `javaabu-auth.api` allows */
    public static function enabledDrivers(): array
    {
        return match (config('javaabu-auth.api', 'none')) {
            'sanctum' => [self::SANCTUM],
            'passport' => [self::PASSPORT],
            'both' => [self::SANCTUM, self::PASSPORT],
            default => [],
        };
    }

    public static function isEnabled(string $driver): bool
    {
        return in_array($driver, static::enabledDrivers(), true);
    }

    /**
     * The driver the model's HasApiTokens trait comes from
     */
    public static function driverFor(Model|string $user): ?string
    {
        $traits = class_uses_recursive($user);

        if (in_array('Laravel\Sanctum\HasApiTokens', $traits, true)) {
            return self::SANCTUM;
        }

        if (in_array('Laravel\Passport\HasApiTokens', $traits, true)) {
            return self::PASSPORT;
        }

        return null;
    }

    public static function supports(Model $user): bool
    {
        $driver = static::driverFor($user);

        return $driver !== null && static::isEnabled($driver);
    }

    /**
     * Create a token and return its plain text, which is shown once
     *
     * @param  array<int, string>|null  $abilities  Sanctum abilities or Passport scopes
     */
    public static function create(Model $user, string $name, ?array $abilities = null): string
    {
        $abilities ??= config('javaabu-auth.api_tokens.abilities', ['*']);

        return match (static::driverFor($user)) {
            self::SANCTUM => $user->createToken(
                $name,
                $abilities,
                ($minutes = config('javaabu-auth.api_tokens.expiration')) ? now()->addMinutes((int) $minutes) : null,
            )->plainTextToken,
            self::PASSPORT => $user->createToken($name, $abilities === ['*'] ? [] : $abilities)->accessToken,
            default => throw new RuntimeException('Add the HasApiTokens trait of laravel/sanctum or laravel/passport to ' . $user::class . '.'),
        };
    }

    /**
     * The user's tokens, for the security page
     *
     * @return array<int, array<string, mixed>>
     */
    public static function list(Model $user): array
    {
        $query = $user->tokens();

        if (static::driverFor($user) === self::PASSPORT) {
            $query->where('revoked', false);
        }

        return $query->latest()->get()->map(fn ($token) => [
            'id' => (string) $token->id,
            'name' => $token->name,
            'last_used_at' => $token->last_used_at?->diffForHumans(),
            'created_at' => $token->created_at?->diffForHumans(),
        ])->all();
    }

    public static function revoke(Model $user, string $id): bool
    {
        $token = $user->tokens()->whereKey($id)->first();

        if (! $token) {
            return false;
        }

        static::driverFor($user) === self::PASSPORT
            ? $token->revoke()
            : $token->delete();

        return true;
    }
}
