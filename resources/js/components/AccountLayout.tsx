import { Form, LinkButton, Submit } from "@javaabu/forms";
import type { ReactNode } from "react";
import { t } from "../lib/strings";
import type { AuthPageProps } from "../types";

export interface AccountLayoutProps extends Pick<AuthPageProps, "urls" | "strings"> {
  title: string;
  page: string;
  children: ReactNode;
}

/** The heading and account navigation of the profile and security pages */
export function AccountLayout({ title, page, urls, strings, children }: AccountLayoutProps) {
  return (
    <div className="mx-auto flex w-full max-w-4xl flex-col gap-6 px-4 py-8" data-auth-page={page}>
      <header className="flex flex-wrap items-center justify-between gap-4">
        <h1 className="text-2xl font-semibold">{title}</h1>

        <nav className="flex flex-wrap items-center gap-2" aria-label={t(strings, "account")} data-account-nav>
          {urls["profile.edit"] ? (
            <LinkButton url={urls["profile.edit"]} color="secondary" size="sm">
              {t(strings, "profile")}
            </LinkButton>
          ) : null}
          {urls["security.edit"] ? (
            <LinkButton url={urls["security.edit"]} color="secondary" size="sm">
              {t(strings, "security")}
            </LinkButton>
          ) : null}
          {urls.logout ? (
            <Form action={urls.logout} className="inline-flex">
              <Submit color="secondary" size="sm">
                {t(strings, "log_out")}
              </Submit>
            </Form>
          ) : null}
        </nav>
      </header>

      {children}
    </div>
  );
}
