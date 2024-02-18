<?php

namespace Javaabu\Auth;

use Illuminate\View\View;
use App\Events\EmailUpdated;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\Access\AuthorizationException;

trait VerifiesEmails
{
    use \Illuminate\Foundation\Auth\VerifiesEmails;

    /**
     * Show the email verification notice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function show(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $message = $user->status_message;

        return $this->showEmailVerificationForm($request, $user, $message);
    }

    /**
     * Show verified message
     *
     * @param Request $request
     * @param User $user
     * @param string $message
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function showEmailVerificationForm(Request $request, User $user, $message)
    {
        return view('admin.auth.verification.verify')->with(compact('user', 'message'));
    }

    /**
     * Show verification result message
     *
     * @param Request $request
     * @param null $data
     * @param null $errors
     * @return Response|View
     */
    public function showVerificationResult(Request $request, $data = null, $errors = null)
    {
        return view('admin.auth.verification.result')
            ->with($data)
            ->withErrors($errors);
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function verify(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        try {
            if (!hash_equals((string)$request->route('id'), (string)$user->getKey())) {
                throw new AuthorizationException();
            }

            if (!hash_equals((string)$request->route('hash'), sha1($user->getEmailForVerification()))) {
                throw new AuthorizationException();
            }
        } catch (AuthorizationException $e) {
            return $this->showVerificationResult($request, null, ['token' => 'Verification token is invalid.']);
        }

        if (! $user->needsEmailVerification()) {
            return $request->wantsJson()
                        ? new Response('', 204)
                        : redirect($this->redirectPath());
        }

        $new_email = null;

        if ($user->wantsNewEmail()) {
            $new_email = $user->new_email;

            if (! $user->isNewEmailAvailable()) {
                return $request->wantsJson()
                    ? new Response('', 204)
                    : $this->showVerificationResult(
                        $request,
                        ['email_unavailable' => true],
                        ['email' => __('The email :new_email is no longer available.', compact('new_email'))]
                    );
            }

            if ($user->updateEmail()) {
                event(new EmailUpdated($user));
            }
        } else {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            if ($response = $this->verified($request)) {
                return $response;
            }
        }

        if ($request->wantsJson()) {
            return new Response('', 204);
        }

        return $new_email ?
            $this->showVerificationResult($request, [
                'message' => __('Your email has been updated successfully to :new_email.', compact('new_email')),
                'email_updated' => true,
            ]) :
            $this->showVerificationResult($request, [
                'message' => __('Your email has been verified successfully.'),
                'verified' => true,
            ]);
    }

    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->needsEmailVerification()) {
            return $request->wantsJson()
                        ? new Response('', 204)
                        : redirect($this->redirectPath());
        }

        $user->sendEmailVerificationNotification();

        return $request->wantsJson()
                    ? new Response('', 202)
                    : $this->sendVerifyLinkResponse();
    }

    /**
     * Get the response for a successful email verification link.
     *
     * @return \Illuminate\Http\Response|RedirectResponse
     */
    protected function sendVerifyLinkResponse()
    {
        $status = __('We\'ve emailed you the verification link');
        return back()->with([
            'status' => $status,
            'resent' => true
        ]);
    }
}
