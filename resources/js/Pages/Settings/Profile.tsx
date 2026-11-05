import { Alert, Card, Email, Form, Password, Submit, Text } from "@javaabu/forms";
import { AccountLayout } from "../../components/AccountLayout";
import { StatusAlert } from "../../components/StatusAlert";
import { confirmSubmit } from "../../lib/confirm";
import { t } from "../../lib/strings";
import type { ProfilePageProps } from "../../types";

/** auth::Settings/Profile */
export default function Profile({ auth_type, urls, strings, account_links, status, profile, must_verify_email, can_delete_account }: ProfilePageProps) {
  return (
    <AccountLayout title={t(strings, "profile")} page="settings.profile" urls={urls} strings={strings} links={account_links}>
      <StatusAlert status={status} />

      <Card title={t(strings, "profile_information")}>
        <Form method="PATCH" action={urls["profile.update"]} model={profile} className="flex flex-col gap-4" data-profile-form>
          <Text name="name" label={t(strings, "name")} required maxLength={255} autoComplete="name" dir="auto" />
          <Email name={auth_type.username} label={t(strings, "email")} required autoComplete="email" dir="ltr" />

          {must_verify_email ? <Alert type="warning">{t(strings, "email_unverified")}</Alert> : null}

          <div>
            <Submit>{t(strings, "save")}</Submit>
          </div>
        </Form>

        {must_verify_email && urls["verification.send"] ? (
          <Form action={urls["verification.send"]} className="mt-4">
            <Submit color="secondary" size="sm">
              {t(strings, "resend_verification")}
            </Submit>
          </Form>
        ) : null}
      </Card>

      {can_delete_account && urls["profile.destroy"] ? (
        <Card title={t(strings, "delete_account")} data-delete-account>
          <Form
            method="DELETE"
            action={urls["profile.destroy"]}
            className="flex flex-col gap-4"
            onSubmitCapture={confirmSubmit(t(strings, "delete_account_confirm"))}
          >
            <p className="text-sm">{t(strings, "delete_account_help")}</p>
            <Password name="password" label={t(strings, "password")} required autoComplete="current-password" dir="ltr" />
            <div>
              <Submit color="danger">{t(strings, "delete_account")}</Submit>
            </div>
          </Form>
        </Card>
      ) : null}
    </AccountLayout>
  );
}
