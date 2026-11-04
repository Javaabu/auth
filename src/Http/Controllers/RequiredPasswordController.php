<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Javaabu\Auth\Contracts\RequiresPasswordUpdate;
use Javaabu\Auth\Contracts\UpdatesUserPasswords;

/**
 * The page users must pass when an admin made them choose a new password
 */
class RequiredPasswordController extends Controller
{
    public function edit(Request $request): View|Responsable|RedirectResponse
    {
        $type = $this->userType($request);
        $user = $this->user($request);

        if (! $user instanceof RequiresPasswordUpdate || ! $user->requiresPasswordUpdate()) {
            return redirect()->intended($type->homeUrl($user));
        }

        return $this->render($request, 'update-required-password');
    }

    public function update(Request $request, UpdatesUserPasswords $updater): RedirectResponse
    {
        $type = $this->userType($request);
        $user = $this->user($request);

        $updater->update($type, $user, $request->all());

        return redirect()->intended($type->homeUrl($user))
            ->with('status', __('Your password has been updated.'));
    }
}
