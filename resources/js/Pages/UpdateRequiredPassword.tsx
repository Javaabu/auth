import { Form, Password, Submit } from "@javaabu/forms";
import { AuthCard } from "../components/AuthCard";
import { t } from "../lib/strings";
import type { AuthPageProps } from "../types";

/** auth::UpdateRequiredPassword */
export default function UpdateRequiredPassword({ urls, strings }: AuthPageProps) {
  return (
    <AuthCard title={t(strings, "choose_new_password")} page="update-required-password">
      <p className="text-sm">{t(strings, "password_update_required")}</p>

      <Form method="PUT" action={urls["password.required.update"]} className="flex flex-col gap-4" summary>
        <Password name="current_password" label={t(strings, "current_password")} required autoComplete="current-password" dir="ltr" />
        <Password name="password" label={t(strings, "new_password")} required autoComplete="new-password" dir="ltr" />
        <Password name="password_confirmation" label={t(strings, "confirm_password")} required autoComplete="new-password" dir="ltr" />
        <Submit className="w-full">{t(strings, "save_password")}</Submit>
      </Form>
    </AuthCard>
  );
}
