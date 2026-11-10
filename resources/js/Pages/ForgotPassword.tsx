import { Email, Form, Submit } from "@javaabu/forms";
import { AuthCard } from "../components/AuthCard";
import { StatusAlert } from "../components/StatusAlert";
import { t } from "../lib/strings";
import type { AuthPageProps } from "../types";

/** auth::ForgotPassword */
export default function ForgotPassword({ auth_type, urls, strings, status }: AuthPageProps) {
  return (
    <AuthCard title={t(strings, "forgot_password")} page="forgot-password">
      <p className="text-sm">{t(strings, "forgot_password_help")}</p>

      <StatusAlert status={status} />

      <Form action={urls["password.email"]} className="flex flex-col gap-4">
        <Email name={auth_type.username} label={t(strings, "email")} required autoFocus autoComplete="email" dir="ltr" />
        <Submit className="w-full">{t(strings, "email_reset_link")}</Submit>
      </Form>

      <p className="text-center text-sm">
        <a href={urls.login} className="underline">
          {t(strings, "back_to_login")}
        </a>
      </p>
    </AuthCard>
  );
}
