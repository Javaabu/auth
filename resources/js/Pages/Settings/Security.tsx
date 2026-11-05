import { Card, Form, Password, Submit } from "@javaabu/forms";
import { AccountLayout } from "../../components/AccountLayout";
import { ApiTokensSettings } from "../../components/ApiTokensSettings";
import { PasskeysSettings } from "../../components/PasskeysSettings";
import { SessionsSettings } from "../../components/SessionsSettings";
import { StatusAlert } from "../../components/StatusAlert";
import { TwoFactorSettings } from "../../components/TwoFactorSettings";
import { t } from "../../lib/strings";
import type { SecurityPageProps } from "../../types";

/** auth::Settings/Security */
export default function Security({ urls, strings, account_links, status, two_factor, passkeys, sessions, api_tokens, plain_text_token }: SecurityPageProps) {
  return (
    <AccountLayout title={t(strings, "security")} page="settings.security" urls={urls} strings={strings} links={account_links}>
      <StatusAlert status={status} />

      <Card title={t(strings, "update_password")} data-password-form>
        <Form method="PUT" action={urls["user-password.update"]} className="flex flex-col gap-4">
          <Password name="current_password" label={t(strings, "current_password")} required autoComplete="current-password" dir="ltr" />
          <Password name="password" label={t(strings, "new_password")} required autoComplete="new-password" dir="ltr" />
          <Password name="password_confirmation" label={t(strings, "confirm_password")} required autoComplete="new-password" dir="ltr" />
          <div>
            <Submit>{t(strings, "save_password")}</Submit>
          </div>
        </Form>
      </Card>

      {two_factor ? <TwoFactorSettings state={two_factor} urls={urls} strings={strings} /> : null}
      {passkeys ? <PasskeysSettings passkeys={passkeys} urls={urls} strings={strings} /> : null}
      {sessions ? <SessionsSettings sessions={sessions} urls={urls} strings={strings} /> : null}
      {api_tokens ? <ApiTokensSettings tokens={api_tokens} plainTextToken={plain_text_token} urls={urls} strings={strings} /> : null}
    </AccountLayout>
  );
}
