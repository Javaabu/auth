<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Javaabu\Auth\Contracts\DeletesUsers;
use Javaabu\Auth\Contracts\UpdatesUserProfileInformation;
use Javaabu\Auth\UserType;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): View|Responsable
    {
        $type = $this->userType($request);
        $user = $this->user($request);

        return $this->render($request, 'settings.profile', [
            'profile' => [
                'name' => $user->getAttribute('name'),
                $type->usernameColumn() => $user->getAttribute($type->usernameColumn()),
            ],
            'must_verify_email' => $type->hasFeature(UserType::EMAIL_VERIFICATION)
                && $user instanceof MustVerifyEmail
                && ! $user->hasVerifiedEmail(),
            'can_delete_account' => $type->hasFeature(UserType::DELETE_ACCOUNT) && $type->hasRoute('profile.destroy'),
        ], 'account');
    }

    public function update(Request $request, UpdatesUserProfileInformation $updater): Response
    {
        $updater->update($this->userType($request), $this->user($request), $request->all());

        $message = __('Your profile has been updated.');

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $message]);
        }

        return back()->with('status', $message);
    }

    public function destroy(Request $request, DeletesUsers $deleter): Response
    {
        $type = $this->userType($request);
        $user = $this->user($request);

        $request->validate([
            'password' => ['required', 'string', "current_password:{$type->guardName()}"],
        ]);

        $type->auth()->logout();

        $deleter->delete($type, $user);

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        return redirect()->to($type->route('login'))
            ->with('status', __('Your account has been deleted.'));
    }
}
