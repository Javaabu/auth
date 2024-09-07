<?php

namespace Javaabu\Auth\Tests\Feature\Http\Controllers;

use Javaabu\Auth\Tests\Feature\Http\Requests\UsersRequest;
use Javaabu\Auth\Tests\Feature\Models\User;
use Javaabu\Helpers\Http\Controllers\Controller;

class UserController extends Controller
{

    /**
     * Update the account page
     *
     * @param UsersRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UsersRequest $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($action = $request->input('action')) {
            switch ($action) {
                case 'update_password':
                    // update password
                    if ($password = $request->input('password')) {
                        $user->password = $password;
                        $user->save();

                        flash_push('alerts', [
                            'text' => __('Password changed successfully'),
                            'type' => 'success',
                            'title' => __('Success!'),
                        ]);
                    }
                    break;
            }
        } else {
            //update the email
            if (($new_email = $request->email) && $new_email != $user->email) {
                $new_email = $user->requestEmailUpdate($new_email);
                if ($new_email) {
                    flash_push('alerts', [
                        'text' => __('We\'ve sent a verification link to :new_email. Please verify the new email address to update it', compact('new_email')),
                        'type' => 'success',
                        'title' => __('New Email Verification Sent'),
                    ]);
                }
            }

            $user->fill($request->all());
            $user->save();

            //update avatar
            $user->updateSingleMedia('avatar', $request);

            $this->flashSuccessMessage();
        }

        return $this->redirect($request, route('user.account'));
    }
}
