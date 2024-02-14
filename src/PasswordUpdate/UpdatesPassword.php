<?php

namespace Javaabu\Auth\PasswordUpdate;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Javaabu\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

trait UpdatesPassword
{
    use RedirectsUsers;

    /**
     * Define the guard
     *
     * @return Guard
     */
    protected function guard(): Guard
    {
        return Auth::guard('web_admin');
    }

    /**
     * Get the user
     *
     * @return User|Authenticatable|null
     */
    protected function user()
    {
        return $this->guard()->user();
    }

    /**
     * The user broker
     *
     * @return Builder
     */
    public function broker(): Builder
    {
        return User::query();
    }

    /**
     * Show the password update form
     *
     * @return Factory|View
     */
    public function showPasswordUpdateForm(): Factory|View
    {
        return view('admin.auth.passwords.update');
    }

    /**
     * Update the password
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        // validate
        $this->validatePasswordUpdateRequest($request);

        // update password
        $this->updateUserPassword($request);

        // send password updated response
        return $this->sendPasswordUpdatedResponse();
    }

    /**
     * Validate the password update request
     *
     * @param  Request  $request
     */
    protected function validatePasswordUpdateRequest(Request $request)
    {
        /*$user = $this->user();
        $table = $this->broker()->getModel()->getTable();*/

        $this->validate($request, [
            'current_password' => 'required|current_password:',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ]);
    }

    /**
     * Update the user password
     *
     * @param  Request  $request
     */
    protected function updateUserPassword(Request $request): void
    {
        $user = $this->user();

        $user->password = $request->password;
        $user->clearRequirePasswordUpdate();

        $user->save();
    }

    /**
     * Get the response for a successful password update.
     *
     * @param  string  $message
     * @return RedirectResponse
     */
    protected function sendPasswordUpdatedResponse(string $message = ''): RedirectResponse
    {
        return redirect($this->redirectPath())
            ->with('alerts', [
                [
                    'type' => 'success',
                    'title' => '',
                    'text' => $message ?: __('Your password has been updated successfully!'),
                ]
            ]);
    }
}
