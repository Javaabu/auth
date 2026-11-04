import { Alert, Card, Form, Submit, Text } from "@javaabu/forms";
import { t } from "../lib/strings";
import type { AuthStrings, AuthUrls, TwoFactorState } from "../types";

export interface TwoFactorSettingsProps {
  state: TwoFactorState;
  urls: AuthUrls;
  strings: AuthStrings;
}

/** Turning two-factor authentication on (QR code, confirm), recovery codes, and off */
export function TwoFactorSettings({ state, urls, strings }: TwoFactorSettingsProps) {
  return (
    <Card title={t(strings, "two_factor_title")} data-two-factor>
      <div className="flex flex-col gap-4">
        {state.enabled ? <Alert type="success">{t(strings, "two_factor_on")}</Alert> : null}

        {state.pending ? (
          <>
            <p className="text-sm">{t(strings, "two_factor_scan")}</p>
            <div className="flex flex-col items-start gap-2" data-two-factor-qr-code>
              {/* The server renders the QR code as SVG */}
              <div className="rounded-sm bg-white p-2" dangerouslySetInnerHTML={{ __html: state.qr_code ?? "" }} />
              <p className="text-sm">
                {t(strings, "setup_key")}:{" "}
                <code dir="ltr" className="font-mono">
                  {state.secret_key}
                </code>
              </p>
            </div>
            <Form action={urls["two-factor.confirm"]} className="flex flex-col gap-4" data-two-factor-confirm>
              <Text name="code" label={t(strings, "code")} inputMode="numeric" autoComplete="one-time-code" maxLength={6} required dir="ltr" />
              <div>
                <Submit>{t(strings, "confirm")}</Submit>
              </div>
            </Form>
          </>
        ) : null}

        {!state.enabled && !state.pending ? (
          <>
            <p className="text-sm">{state.required ? t(strings, "two_factor_required") : t(strings, "two_factor_offer")}</p>
            <Form action={urls["two-factor.enable"]}>
              <Submit>{t(strings, "two_factor_turn_on")}</Submit>
            </Form>
          </>
        ) : null}

        {state.recovery_codes ? (
          <div className="flex flex-col gap-2" data-recovery-codes>
            <p className="text-sm">{t(strings, "recovery_codes_help")}</p>
            <ul className="grid gap-1 rounded-sm bg-gray-100 p-4 font-mono text-sm sm:grid-cols-2" dir="ltr">
              {state.recovery_codes.map((code) => (
                <li key={code}>{code}</li>
              ))}
            </ul>
          </div>
        ) : null}

        {state.enabled ? (
          <div className="flex flex-wrap gap-2">
            <Form action={urls["two-factor.regenerate-recovery-codes"]}>
              <Submit color="secondary">{t(strings, "regenerate_recovery_codes")}</Submit>
            </Form>
            {!state.required ? (
              <Form method="DELETE" action={urls["two-factor.disable"]}>
                <Submit color="danger">{t(strings, "two_factor_turn_off")}</Submit>
              </Form>
            ) : null}
          </div>
        ) : null}
      </div>
    </Card>
  );
}
