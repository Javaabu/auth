import { Checkbox, Email, Form, LinkButton, Password, Submit } from "@javaabu/forms";
import { useRef } from "react";
import { AuthCard } from "../components/AuthCard";
import { PasskeyButton } from "../components/PasskeyButton";
import { StatusAlert } from "../components/StatusAlert";
import { t } from "../lib/strings";
import type { LoginPageProps } from "../types";

/** auth::Login */
export default function Login({ auth_type, urls, strings, status, can_reset_password, can_register, can_use_passkeys }: LoginPageProps) {
  const form = useRef<HTMLDivElement>(null);
  const remember = () => ({ remember: form.current?.querySelector<HTMLInputElement>('input[name="remember"]')?.checked ?? false });

  return (
    <AuthCard title={t(strings, "log_in_title")} page="login">
      <StatusAlert status={status} />

      <div ref={form}>
        <Form action={urls["login.store"]} className="flex flex-col gap-4" data-login-form>
          <Email name={auth_type.username} label={t(strings, "email")} required autoFocus autoComplete="username webauthn" dir="ltr" />
          <Password name="password" label={t(strings, "password")} required autoComplete="current-password" dir="ltr" />

          <div className="flex flex-wrap items-center justify-between gap-2">
            <Checkbox name="remember" label={t(strings, "remember_me")} value="1" model={false} />
            {can_reset_password ? (
              <a href={urls["password.request"]} className="text-sm underline">
                {t(strings, "forgot_password")}
              </a>
            ) : null}
          </div>

          <Submit className="w-full">{t(strings, "log_in")}</Submit>
        </Form>
      </div>

      {can_use_passkeys && urls["passkey.login-options"] && urls["passkey.login"] ? (
        <PasskeyButton
          mode="verify"
          optionsUrl={urls["passkey.login-options"]}
          url={urls["passkey.login"]}
          label={t(strings, "log_in_with_passkey")}
          unsupported={t(strings, "passkeys_unsupported")}
          failed={t(strings, "passkey_login_failed")}
          data={remember}
          className="w-full"
        />
      ) : null}

      {auth_type.login_methods.map((method) => (
        <LinkButton key={method.key} url={method.url} color="secondary" className="w-full" data-login-method={method.key}>
          {method.label}
        </LinkButton>
      ))}

      {can_register ? (
        <p className="text-center text-sm">
          {t(strings, "no_account")}{" "}
          <a href={urls.register} className="underline">
            {t(strings, "sign_up")}
          </a>
        </p>
      ) : null}
    </AuthCard>
  );
}
