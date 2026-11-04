import { Email, Form, Hidden, Password, Submit } from "@javaabu/forms";
import { AuthCard } from "../components/AuthCard";
import { t } from "../lib/strings";
import type { ResetPasswordPageProps } from "../types";

/** auth::ResetPassword */
export default function ResetPassword({ auth_type, urls, strings, token, email }: ResetPasswordPageProps) {
  return (
    <AuthCard title={t(strings, "choose_new_password")} page="reset-password">
      <Form action={urls["password.update"]} className="flex flex-col gap-4" summary>
        <Hidden name="token" default={token} />
        <Email name={auth_type.username} label={t(strings, "email")} default={email} required autoComplete="email" dir="ltr" />
        <Password name="password" label={t(strings, "new_password")} required autoFocus autoComplete="new-password" dir="ltr" />
        <Password name="password_confirmation" label={t(strings, "confirm_password")} required autoComplete="new-password" dir="ltr" />

        <Submit className="w-full">{t(strings, "reset_password")}</Submit>
      </Form>
    </AuthCard>
  );
}
