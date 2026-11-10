<?php

namespace Javaabu\Auth\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Javaabu\Auth\Contracts\TwoFactorAuthenticatable;
use Javaabu\Auth\Events\TwoFactorAuthenticationChallenged;
use Javaabu\Auth\Http\Responses\LoginResponse;
use Javaabu\Auth\Http\Responses\TwoFactorChallengeResponse;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypeRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * Finishes a login once the user is identified: asks for a two-factor code when the user
 * has turned it on, otherwise logs them in. Alternative login methods (LoginMethod) call this.
 */
class StartAuthenticatedSession
{
    /** The session key that holds a login waiting for its two-factor code */
    public const string CHALLENGE_SESSION_KEY = 'auth.two_factor_challenge';

    public function __construct(
        protected LoginUser $login,
    ) {}

    public function __invoke(
        Request $request,
        UserType $type,
        Authenticatable $user,
        bool $remember = false,
        bool $skip_two_factor = false,
    ): Response {
        $request->attributes->set(UserTypeRegistry::ROUTE_ACTION, $type->name());

        if (! $skip_two_factor && $this->needsTwoFactorChallenge($type, $user)) {
            return $this->challenge($request, $type, $user, $remember);
        }

        ($this->login)($request, $type, $user, $remember);

        return app(LoginResponse::class)->toResponse($request);
    }

    public function needsTwoFactorChallenge(UserType $type, Authenticatable $user): bool
    {
        return $type->hasTwoFactor()
            && $user instanceof TwoFactorAuthenticatable
            && $user->hasEnabledTwoFactorAuthentication();
    }

    protected function challenge(Request $request, UserType $type, Authenticatable $user, bool $remember): Response
    {
        $request->session()->put(self::CHALLENGE_SESSION_KEY, [
            'user_type' => $type->name(),
            'id' => $user->getAuthIdentifier(),
            'remember' => $remember,
        ]);

        TwoFactorAuthenticationChallenged::dispatch($user);

        return app(TwoFactorChallengeResponse::class)->toResponse($request);
    }
}
