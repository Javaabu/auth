import { Form, Password, Submit } from "@javaabu/forms";
import { AuthCard } from "../components/AuthCard";
import { PasskeyButton } from "../components/PasskeyButton";
import { t } from "../lib/strings";
import type { ConfirmPasswordPageProps } from "../types";

/** auth::ConfirmPassword */
export default function ConfirmPassword({ urls, strings, can_use_passkeys }: ConfirmPasswordPageProps) {
  return (
    <AuthCard title={t(strings, "confirm_password_title")} page="confirm-password">
      <p className="text-sm">{t(strings, "confirm_password_help")}</p>

      <Form action={urls["password.confirm.store"]} className="flex flex-col gap-4">
        <Password name="password" label={t(strings, "password")} required autoFocus autoComplete="current-password" dir="ltr" />
        <Submit className="w-full">{t(strings, "confirm")}</Submit>
      </Form>

      {can_use_passkeys && urls["passkey.confirm-options"] && urls["passkey.confirm"] ? (
        <PasskeyButton
          mode="verify"
          optionsUrl={urls["passkey.confirm-options"]}
          url={urls["passkey.confirm"]}
          label={t(strings, "confirm_with_passkey")}
          unsupported={t(strings, "passkeys_unsupported")}
          failed={t(strings, "passkey_confirm_failed")}
          className="w-full"
        />
      ) : null}
    </AuthCard>
  );
}
