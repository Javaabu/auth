<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Javaabu\Auth\Support\ApiTokens;
use Symfony\Component\HttpFoundation\Response;

/**
 * Personal access tokens, from the security page
 */
class ApiTokensController extends Controller
{
    public function store(Request $request): Response
    {
        $user = $this->user($request);

        abort_unless(ApiTokens::supports($user), 404);

        $request->validate([
            'token_name' => ['required', 'string', 'max:255'],
        ]);

        $token = ApiTokens::create($user, (string) $request->input('token_name'));

        if ($request->wantsJson()) {
            return new JsonResponse(['token' => $token], 201);
        }

        return back()
            ->with('status', __('API token created.'))
            ->with('plain_text_token', $token);
    }

    public function destroy(Request $request, string $token_id): Response
    {
        $user = $this->user($request);

        abort_unless(ApiTokens::supports($user) && ApiTokens::revoke($user, $token_id), 404);

        $message = __('API token deleted.');

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $message]);
        }

        return back()->with('status', $message);
    }
}
