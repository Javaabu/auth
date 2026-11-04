import { Button, Form, Submit, Text } from "@javaabu/forms";
import { useState } from "react";
import { AuthCard } from "../components/AuthCard";
import { t } from "../lib/strings";
import type { AuthPageProps } from "../types";

/** auth::TwoFactorChallenge */
export default function TwoFactorChallenge({ urls, strings }: AuthPageProps) {
  const [recovery, setRecovery] = useState(false);

  return (
    <AuthCard title={t(strings, "two_factor_title")} page="two-factor-challenge">
      <div className="flex flex-col gap-4" data-two-factor-challenge>
        {recovery ? (
          <Form action={urls["two-factor.login.store"]} className="flex flex-col gap-4" data-two-factor-recovery>
            <Text name="recovery_code" label={t(strings, "recovery_code")} autoFocus autoComplete="one-time-code" dir="ltr" />
            <Submit className="w-full">{t(strings, "continue")}</Submit>
          </Form>
        ) : (
          <Form action={urls["two-factor.login.store"]} className="flex flex-col gap-4" data-two-factor-code>
            <p className="text-sm">{t(strings, "two_factor_help")}</p>
            <Text name="code" label={t(strings, "code")} inputMode="numeric" autoFocus autoComplete="one-time-code" maxLength={6} dir="ltr" />
            <Submit className="w-full">{t(strings, "continue")}</Submit>
          </Form>
        )}

        <Button type="button" color="secondary" onClick={() => setRecovery(!recovery)}>
          {recovery ? t(strings, "use_authenticator_code") : t(strings, "use_recovery_code")}
        </Button>
      </div>
    </AuthCard>
  );
}
