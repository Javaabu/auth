import { Alert, Card, Form, Submit, Text } from "@javaabu/forms";
import { confirmSubmit } from "../lib/confirm";
import { t } from "../lib/strings";
import type { ApiTokenData, AuthStrings, AuthUrls } from "../types";

export interface ApiTokensSettingsProps {
  tokens: ApiTokenData[];
  plainTextToken?: string | null;
  urls: AuthUrls;
  strings: AuthStrings;
}

export function ApiTokensSettings({ tokens, plainTextToken, urls, strings }: ApiTokensSettingsProps) {
  return (
    <Card title={t(strings, "api_tokens")} data-api-tokens>
      <div className="flex flex-col gap-4">
        {plainTextToken ? (
          <Alert type="success">
            {t(strings, "api_token_copy")}
            <code className="block break-all font-mono" dir="ltr" data-plain-text-token>
              {plainTextToken}
            </code>
          </Alert>
        ) : null}

        <Form action={urls["api-tokens.store"]} className="flex flex-wrap items-end gap-2">
          <Text name="token_name" label={t(strings, "token_name")} required maxLength={255} dir="auto" />
          <Submit>{t(strings, "create_token")}</Submit>
        </Form>

        {tokens.length ? (
          <ul className="flex flex-col divide-y divide-gray-200">
            {tokens.map((token) => (
              <li key={token.id} className="flex flex-wrap items-center justify-between gap-2 py-2" data-api-token={token.id}>
                <div className="flex flex-col">
                  <span className="font-medium" dir="auto">
                    {token.name}
                  </span>
                  <span className="text-sm text-gray-600">
                    {token.last_used_at ? t(strings, "last_used_time", { time: token.last_used_at }) : t(strings, "never_used")}
                  </span>
                </div>
                <Form method="DELETE" action={token.destroy_url} onSubmitCapture={confirmSubmit(t(strings, "delete_token_confirm"))}>
                  <Submit color="danger" size="sm">
                    {t(strings, "delete")}
                  </Submit>
                </Form>
              </li>
            ))}
          </ul>
        ) : null}
      </div>
    </Card>
  );
}
