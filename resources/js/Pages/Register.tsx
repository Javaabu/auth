import { Email, Form, Password, Submit, Text } from "@javaabu/forms";
import { AuthCard } from "../components/AuthCard";
import { t } from "../lib/strings";
import type { AuthPageProps } from "../types";

/** auth::Register */
export default function Register({ auth_type, urls, strings }: AuthPageProps) {
  return (
    <AuthCard title={t(strings, "register_title")} page="register">
      <Form action={urls["register.store"]} className="flex flex-col gap-4" summary>
        <Text name="name" label={t(strings, "name")} required autoFocus autoComplete="name" maxLength={255} dir="auto" />
        <Email name={auth_type.username} label={t(strings, "email")} required autoComplete="email" dir="ltr" />
        <Password name="password" label={t(strings, "password")} required autoComplete="new-password" dir="ltr" />
        <Password name="password_confirmation" label={t(strings, "confirm_password")} required autoComplete="new-password" dir="ltr" />

        <Submit className="w-full">{t(strings, "create_account")}</Submit>

        <p className="text-center text-sm">
          {t(strings, "have_account")}{" "}
          <a href={urls.login} className="underline">
            {t(strings, "log_in")}
          </a>
        </p>
      </Form>
    </AuthCard>
  );
}
