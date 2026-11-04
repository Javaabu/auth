import { Card, Form, Submit } from "@javaabu/forms";
import { t } from "../lib/strings";
import type { AuthStrings, AuthUrls, SessionData } from "../types";

export function SessionsSettings({ sessions, urls, strings }: { sessions: SessionData[]; urls: AuthUrls; strings: AuthStrings }) {
  return (
    <Card title={t(strings, "browser_sessions")} data-sessions>
      <div className="flex flex-col gap-4">
        <p className="text-sm">{t(strings, "browser_sessions_help")}</p>
        <ul className="flex flex-col divide-y divide-gray-200">
          {sessions.map((session) => (
            <li key={session.id} className="flex flex-col py-2" data-session={session.id}>
              <span className="font-medium">
                {session.platform ?? t(strings, "unknown")} · {session.browser ?? t(strings, "unknown")}
              </span>
              <span className="text-sm text-gray-600">
                <span dir="ltr">{session.ip_address}</span> ·{" "}
                {session.is_current_device ? t(strings, "this_device") : t(strings, "last_active_time", { time: session.last_active })}
              </span>
            </li>
          ))}
        </ul>
        <Form method="DELETE" action={urls["sessions.destroy"]}>
          <Submit color="secondary">{t(strings, "log_out_other_sessions")}</Submit>
        </Form>
      </div>
    </Card>
  );
}
