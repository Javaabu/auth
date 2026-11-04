import { Alert, Form, LinkButton, Submit } from "@javaabu/forms";
import { AuthCard } from "../components/AuthCard";
import { t } from "../lib/strings";
import type { AccountInactivePageProps } from "../types";

/** auth::AccountInactive */
export default function AccountInactive({ urls, strings, message }: AccountInactivePageProps) {
  return (
    <AuthCard title={t(strings, "account_inactive_title")} page="account-inactive">
      <Alert type="warning">{message}</Alert>

      <div className="flex flex-wrap items-center justify-between gap-2">
        {urls["password.request"] ? (
          <LinkButton url={urls["password.request"]} color="secondary">
            {t(strings, "reset_your_password")}
          </LinkButton>
        ) : null}

        <Form action={urls.logout}>
          <Submit color="secondary">{t(strings, "log_out")}</Submit>
        </Form>
      </div>
    </AuthCard>
  );
}
