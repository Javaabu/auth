<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Javaabu\Auth\Contracts\UpdatesUserPasswords;
use Symfony\Component\HttpFoundation\Response;

class PasswordController extends Controller
{
    public function update(Request $request, UpdatesUserPasswords $updater): Response
    {
        $updater->update($this->userType($request), $this->user($request), $request->all());

        $message = __('Your password has been updated.');

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $message]);
        }

        return back()->with('status', $message);
    }
}
