---
title: Email verification
sidebar_position: 7
---

For types with the `email_verification` feature and models that implement `MustVerifyEmail` (the package's `User` does):

- Registering sends a verification email linking to the type's signed `verification.verify` route (`Javaabu\Auth\Notifications\VerifyEmail`).
- `auth.type:{type}` sends unverified users to `verify-email` (`verification.notice`), which can resend the email (`verification.send`, rate limited).
- The link must be opened by the signed-in user it was sent to; it marks the email verified, fires Laravel's `Verified` event and redirects to the intended URL or home.
- Changing the email on the profile page marks it unverified and sends a new link.

Turn it off for a type with `'features' => ['email_verification' => false]`; users of that type are never asked.

Laravel's `verified` middleware also works, given the type's notice route: `verified:admin.verification.notice`. `auth.verified:admin` does the same.
