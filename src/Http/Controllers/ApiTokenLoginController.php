<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Javaabu\Auth\Actions\IssueApiToken;
use Javaabu\Auth\Support\ApiTokens;

/**
 * Tokens for mobile and other API clients (Sanctum): log in with a username, password and
 * device name, and a two-factor code when the user has one
 */
class ApiTokenLoginController extends Controller
{
    public function store(Request $request, IssueApiToken $issue): JsonResponse
    {
        $type = $this->userType($request);

        $request->validate([
            $type->usernameColumn() => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        return new JsonResponse([
            'token' => $issue($request, $type),
            'token_type' => 'Bearer',
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        if (ApiTokens::driverFor($user) === ApiTokens::SANCTUM) {
            $user->currentAccessToken()?->delete();
        }

        return new JsonResponse('', 204);
    }
}
