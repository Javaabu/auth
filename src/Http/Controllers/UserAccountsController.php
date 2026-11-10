<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Javaabu\Auth\Actions\TwoFactor\DisableTwoFactorAuthentication;
use Javaabu\Auth\Contracts\LocksAfterFailedLogins;
use Javaabu\Auth\Contracts\RequiresPasswordUpdate;
use Javaabu\Auth\Routing\UserAccountRoutes;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use Symfony\Component\HttpFoundation\Response;

/**
 * What admins do to other users' accounts, registered with Route::userAccountActions()
 */
class UserAccountsController extends Controller
{
    public function unlock(Request $request, string $account): Response
    {
        [$type, $model] = $this->account($request, $account, 'auth.unlock');

        if ($model instanceof LocksAfterFailedLogins) {
            $model->resetLoginAttempts();
        }

        return $this->respond($request, __('The account has been unlocked.'));
    }

    public function resetTwoFactor(Request $request, string $account, DisableTwoFactorAuthentication $disable): Response
    {
        [$type, $model] = $this->account($request, $account, 'auth.reset-two-factor');

        $disable($model);

        return $this->respond($request, __('Two-factor authentication has been reset for the account.'));
    }

    public function requirePasswordUpdate(Request $request, string $account): Response
    {
        [$type, $model] = $this->account($request, $account, 'auth.require-password-update');

        if ($model instanceof RequiresPasswordUpdate) {
            $model->requirePasswordUpdate();
        }

        return $this->respond($request, __('The user will have to choose a new password when they next log in.'));
    }

    public function logOut(Request $request, string $account): Response
    {
        [$type, $model] = $this->account($request, $account, 'auth.log-out');

        $model->setRememberToken(Str::random(60));
        $model->save();

        if (method_exists($model, 'sessions') && config('session.driver') === 'multi_auth_database') {
            $model->sessions()->delete();
        }

        if (method_exists($model, 'tokens')) {
            $model->tokens()->delete();
        }

        return $this->respond($request, __('The user has been logged out from every device.'));
    }

    /**
     * The managed type and its account, after checking the ability
     *
     * @return array{0: UserType, 1: Model}
     */
    protected function account(Request $request, string $account, string $ability): array
    {
        $type = UserTypes::get($request->route()->getAction(UserAccountRoutes::ROUTE_ACTION));
        $model = $type->newModel()->resolveRouteBinding($account) ?? abort(404);

        Gate::authorize($ability, [$type, $model]);

        return [$type, $model];
    }

    protected function respond(Request $request, string $message): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $message]);
        }

        return back()->with('status', $message);
    }
}
