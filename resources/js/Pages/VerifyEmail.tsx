import { Form, Submit } from "@javaabu/forms";
import { AuthCard } from "../components/AuthCard";
import { StatusAlert } from "../components/StatusAlert";
import { t } from "../lib/strings";
import type { VerifyEmailPageProps } from "../types";

/** auth::VerifyEmail */
export default function VerifyEmail({ urls, strings, status, email }: VerifyEmailPageProps) {
  return (
    <AuthCard title={t(strings, "verify_email_title")} page="verify-email">
      <p className="text-sm">{t(strings, "verify_email_help", { email })}</p>

      <StatusAlert status={status} />

      <div className="flex flex-wrap items-center justify-between gap-2">
        <Form action={urls["verification.send"]}>
          <Submit>{t(strings, "resend_verification")}</Submit>
        </Form>

        <Form action={urls.logout}>
          <Submit color="secondary">{t(strings, "log_out")}</Submit>
        </Form>
      </div>
    </AuthCard>
  );
}
