import strings from "./strings.dv.json";
import type { AuthPageProps, AuthType } from "../../resources/js/types";

export { strings };

export const authType: AuthType = {
  name: "user",
  features: { registration: true, passkeys: true },
  two_factor: "optional",
  username: "email",
  login_methods: [],
};

export const urls = {
  home: "/dashboard",
  login: "/login",
  "login.store": "/login",
  register: "/register",
  "register.store": "/register",
  "password.request": "/forgot-password",
  "password.email": "/forgot-password",
  "password.update": "/reset-password",
  "passkey.login-options": "/passkeys/login/options",
  "passkey.login": "/passkeys/login",
  "two-factor.login.store": "/two-factor-challenge",
  logout: "/logout",
  "profile.edit": "/settings/profile",
  "profile.update": "/settings/profile",
  "profile.destroy": "/settings/profile",
  "security.edit": "/settings/security",
  "user-password.update": "/settings/password",
  "two-factor.enable": "/settings/two-factor",
  "two-factor.confirm": "/settings/two-factor/confirm",
  "two-factor.disable": "/settings/two-factor",
  "two-factor.regenerate-recovery-codes": "/settings/two-factor/recovery-codes",
  "passkey.registration-options": "/settings/passkeys/options",
  "passkey.store": "/settings/passkeys",
  "sessions.destroy": "/settings/sessions",
  "api-tokens.store": "/settings/api-tokens",
  "verification.send": "/email/verification-notification",
};

export const page: AuthPageProps = { auth_type: authType, urls, strings, status: null };
