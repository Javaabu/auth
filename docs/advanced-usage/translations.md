---
title: Dhivehi and translations
sidebar_position: 6
---

Every string the package shows (pages, validation messages, status messages, emails, permission descriptions) is an English string passed through `__()`, with Dhivehi in `lang/dv.json`. Set the app locale to `dv` and the pages, messages and emails are in Dhivehi. The package also translates the strings of Laravel's password reset and verification emails.

To change a wording, add a line with the same English key to your app's `lang/dv.json` (or `lang/en.json`); the app's lines win over the package's. `php artisan vendor:publish --tag=auth-translations` copies the package's files to `lang/vendor/auth/` to start from; Laravel doesn't read JSON files from there, so move the lines you change into `lang/dv.json`.

# React pages

The React pages get their strings translated by the server, as the `strings` prop (keys in `Javaabu\Auth\Support\Strings`), so they need no translation setup of their own and change language with the app locale.

# Right to left

The package layouts set `dir="rtl"` for Dhivehi. The pages use logical utilities only (`ms-`, `pe-`, `text-start`), and a test rejects physical ones. Email, password, code and token inputs are `dir="ltr"`; names and passkey names are `dir="auto"`, so Thaana and Latin both read correctly.

# Permission descriptions

The account-management permissions are registered with English descriptions built from the type: "Unlock locked users", "Unlock locked admins". `lang/dv.json` has them for `users` and `admins`. For other types, add lines to your `lang/dv.json`:

```json
{
    "Unlock locked vendors": "ލޮކްވެފައިވާ ވެންޑަރުންގެ އެކައުންޓް ހުޅުވުން",
    "Reset two-factor authentication of vendors": "ވެންޑަރުންގެ ޓޫ-ފެކްޓަރ އޮތެންޓިކޭޝަން ރީސެޓްކުރުން",
    "Make vendors choose a new password": "ވެންޑަރުންނަށް އައު ޕާސްވޯޑެއް ހަމަޖައްސަން މަޖުބޫރުކުރުން",
    "Log out vendors from every device": "ވެންޑަރުން ހުރިހާ ޑިވައިސްއަކުން ލޮގްއައުޓް ކުރުން"
}
```

# Strings to review

The Dhivehi was written for this release and should be checked by a native speaker, especially:

| English | Dhivehi | Note |
| --- | --- | --- |
| Two-factor authentication | ޓޫ-ފެކްޓަރ އޮތެންޓިކޭޝަން | Transliterated; a Dhivehi term may read better |
| Passkey | ޕާސްކީ | Transliterated |
| Recovery code | ރިކަވަރީ ކޯޑު | Transliterated |
| Approved / Pending / Banned | ހުއްދަ ދީފައި / މަޑުކުރަމުން / މަނާކޮށްފައި | Account statuses |
| Remember me | ހަނދާންބަހައްޓާ | |
| Last active :time / Last used :time | :time ފަހުން ބޭނުންކުރި | Same wording for both; `:time` is English ("2 hours ago") unless Carbon's locale is Dhivehi |
| Passkey not recognized. … | މި ޕާސްކީ ނުވަޒަނެއް. … | |
| Your account is not active | ތިބާގެ އެކައުންޓް ހަރަކާތްތެރިއެއް ނޫން | |
