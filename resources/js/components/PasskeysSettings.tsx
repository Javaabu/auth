import { Card, Form, Submit } from "@javaabu/forms";
import { useState } from "react";
import { confirmSubmit } from "../lib/confirm";
import { t } from "../lib/strings";
import type { AuthStrings, AuthUrls, PasskeyData } from "../types";
import { PasskeyButton } from "./PasskeyButton";

export interface PasskeysSettingsProps {
  passkeys: PasskeyData[];
  urls: AuthUrls;
  strings: AuthStrings;
  /** After a passkey is added; defaults to reloading the page */
  onAdded?: () => void;
}

/** The user's passkeys, with adding and removing */
export function PasskeysSettings({ passkeys, urls, strings, onAdded }: PasskeysSettingsProps) {
  const [name, setName] = useState("");

  return (
    <Card title={t(strings, "passkeys")} data-passkeys>
      <div className="flex flex-col gap-4">
        <p className="text-sm">{t(strings, "passkeys_help")}</p>

        {passkeys.length ? (
          <ul className="flex flex-col divide-y divide-gray-200" data-passkey-list>
            {passkeys.map((passkey) => (
              <li key={passkey.id} className="flex flex-wrap items-center justify-between gap-2 py-2" data-passkey={passkey.id}>
                <div className="flex flex-col">
                  <span className="font-medium" dir="auto">
                    {passkey.name}
                  </span>
                  <span className="text-sm text-gray-600">
                    {passkey.authenticator ?? t(strings, "passkey")} · {t(strings, "added_time", { time: passkey.created_at ?? "" })}
                    {passkey.last_used_at ? ` · ${t(strings, "last_used_time", { time: passkey.last_used_at })}` : null}
                  </span>
                </div>
                <Form method="DELETE" action={passkey.destroy_url} onSubmitCapture={confirmSubmit(t(strings, "remove_passkey_confirm"))}>
                  <Submit color="danger" size="sm">
                    {t(strings, "remove")}
                  </Submit>
                </Form>
              </li>
            ))}
          </ul>
        ) : (
          <p className="text-sm text-gray-600">{t(strings, "no_passkeys")}</p>
        )}

        {urls["passkey.registration-options"] && urls["passkey.store"] ? (
          <div className="flex flex-wrap items-end gap-2">
            <label className="flex flex-col gap-1 text-sm">
              <span>{t(strings, "passkey_name")}</span>
              <input
                className="rounded-sm border border-gray-300 px-3 py-2"
                value={name}
                onChange={(event) => setName(event.target.value)}
                placeholder={t(strings, "passkey_name_placeholder")}
                maxLength={255}
                dir="auto"
                data-passkey-name
              />
            </label>
            <PasskeyButton
              mode="register"
              optionsUrl={urls["passkey.registration-options"]}
              url={urls["passkey.store"]}
              label={t(strings, "add_passkey")}
              unsupported={t(strings, "passkeys_unsupported")}
              failed={t(strings, "passkey_add_failed")}
              color="primary"
              data={() => ({ name: name.trim() || t(strings, "passkey") })}
              onSuccess={onAdded ?? (() => window.location.reload())}
            />
          </div>
        ) : null}
      </div>
    </Card>
  );
}
