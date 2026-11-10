import { Form, LinkButton, Submit } from "@javaabu/forms";
import type { ReactNode } from "react";
import { t } from "../lib/strings";
import type { AccountLink, AuthPageProps } from "../types";

export interface AccountLayoutProps extends Pick<AuthPageProps, "urls" | "strings"> {
  title: string;
  page: string;
  /** The `account_links` prop: Profile, Security and the links packages added. Without it, Profile and Security from `urls` */
  links?: AccountLink[];
  children: ReactNode;
}

/** The links from `urls` when a page has no `account_links` prop */
function fallbackLinks(urls: AuthPageProps["urls"], strings: AuthPageProps["strings"]): AccountLink[] {
  const links: AccountLink[] = [];

  if (urls["profile.edit"]) {
    links.push({ key: "profile", label: t(strings, "profile"), url: urls["profile.edit"], active: false });
  }

  if (urls["security.edit"]) {
    links.push({ key: "security", label: t(strings, "security"), url: urls["security.edit"], active: false });
  }

  return links;
}

/** The heading and account navigation of the profile, security and package account pages */
export function AccountLayout({ title, page, urls, strings, links, children }: AccountLayoutProps) {
  const navigation = links ?? fallbackLinks(urls, strings);

  return (
    <div className="mx-auto flex w-full max-w-4xl flex-col gap-6 px-4 py-8" data-auth-page={page}>
      <header className="flex flex-wrap items-center justify-between gap-4">
        <h1 className="text-2xl font-semibold">{title}</h1>

        <nav className="flex flex-wrap items-center gap-2" aria-label={t(strings, "account")} data-account-nav>
          {navigation.map((link) => (
            <LinkButton
              key={link.key}
              url={link.url}
              color={link.active ? "primary" : "secondary"}
              size="sm"
              data-account-link={link.key}
              aria-current={link.active ? "page" : undefined}
            >
              {link.label}
            </LinkButton>
          ))}
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
