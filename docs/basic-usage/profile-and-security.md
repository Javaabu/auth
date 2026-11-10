---
title: Profile and security pages
sidebar_position: 11
---

Signed-in users manage their account on two pages, both behind `auth.type:{type}`.

# Profile (`profile.edit`)

- **Profile information:** name and email (`profile.update`), through the `UpdatesUserProfileInformation` action. Changing the email marks it unverified and sends a new verification link, for types with `email_verification`.
- **Delete account**, with the `delete_account` feature: asks for the password, logs out, deletes the user through the `DeletesUsers` action (soft deletes with the package's model; API tokens are deleted), and goes to the login page.

# Security (`security.edit`)

Behind password confirmation.

- **Update password** (`user-password.update`): current password, new password and confirmation, through `UpdatesUserPasswords`. Fires `PasswordUpdated`.
- **Two-factor authentication**, unless the type has it `off`. See [Two-factor authentication](two-factor-authentication.md).
- **Passkeys**, with the `passkeys` feature. See [Passkeys](passkeys.md).
- **Browser sessions**, with the `sessions` feature and the `multi_auth_database` session driver: the user's browsers (platform, browser, IP, last active, "This device"), and "Log out other browser sessions" (`sessions.destroy`), which deletes their other sessions.
- **API tokens**, with the `api_tokens` feature and Sanctum or Passport on the model. See [API authentication](api-authentication.md).

# Required password change

When `require_password_update` is set (by an admin, see [Managing accounts](managing-accounts.md), or with `$user->requirePasswordUpdate()`), `auth.type` sends the user to `update-password` until they choose a new password.
