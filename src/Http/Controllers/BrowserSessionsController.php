<?php

namespace Javaabu\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logging out of the user's other browsers (behind password confirmation)
 */
class BrowserSessionsController extends Controller
{
    public function destroy(Request $request): Response
    {
        $user = $this->user($request);

        if (method_exists($user, 'deleteOtherSessionRecords')) {
            $user->deleteOtherSessionRecords();
        }

        $message = __('You have been logged out of your other browser sessions.');

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $message]);
        }

        return back()->with('status', $message);
    }
}
