/** Props every auth page gets from the server (Javaabu\Auth\Support\PageData) */
export interface AuthType {
  name: string;
  features: Record<string, boolean>;
  two_factor: "off" | "optional" | "required";
  username: string;
  login_methods: { key: string; label: string; url: string }[];
}

/** The URLs of the user type's routes, by route name without the type prefix: `login.store`, `profile.edit`, … */
export type AuthUrls = Record<string, string> & { home: string };

/** The translated strings, keyed as in Javaabu\Auth\Support\Strings */
export type AuthStrings = Record<string, string>;

/** A link in the account navigation: Profile, Security, or one a package added */
export interface AccountLink {
  key: string;
  label: string;
  url: string;
  active: boolean;
}

export interface AuthPageProps {
  auth_type: AuthType;
  urls: AuthUrls;
  account_links: AccountLink[];
  strings: AuthStrings;
  status?: string | null;
}

export interface LoginPageProps extends AuthPageProps {
  can_reset_password: boolean;
  can_register: boolean;
  can_use_passkeys: boolean;
}

export interface ResetPasswordPageProps extends AuthPageProps {
  token: string;
  email: string;
}

export interface VerifyEmailPageProps extends AuthPageProps {
  email: string;
}

export interface ConfirmPasswordPageProps extends AuthPageProps {
  can_use_passkeys: boolean;
}

export interface AccountInactivePageProps extends AuthPageProps {
  message: string;
}

export interface ProfilePageProps extends AuthPageProps {
  profile: Record<string, string | null>;
  must_verify_email: boolean;
  can_delete_account: boolean;
}

export interface TwoFactorState {
  enabled: boolean;
  pending: boolean;
  required: boolean;
  qr_code: string | null;
  secret_key: string | null;
  recovery_codes: string[] | null;
}

export interface PasskeyData {
  id: number;
  name: string;
  authenticator: string | null;
  created_at: string | null;
  last_used_at: string | null;
  destroy_url: string;
}

export interface SessionData {
  id: string;
  ip_address: string | null;
  platform: string | null;
  browser: string | null;
  is_desktop: boolean;
  is_current_device: boolean;
  last_active: string;
}

export interface ApiTokenData {
  id: string;
  name: string;
  last_used_at: string | null;
  created_at: string | null;
  destroy_url: string;
}

export interface SecurityPageProps extends AuthPageProps {
  two_factor: TwoFactorState | null;
  passkeys: PasskeyData[] | null;
  sessions: SessionData[] | null;
  api_tokens: ApiTokenData[] | null;
  plain_text_token?: string | null;
}
