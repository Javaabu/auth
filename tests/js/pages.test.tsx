import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import Login from "../../resources/js/Pages/Login";
import Security from "../../resources/js/Pages/Settings/Security";
import TwoFactorChallenge from "../../resources/js/Pages/TwoFactorChallenge";
import { page, strings } from "./fixtures";

describe("Login", () => {
  it("renders the form, the passkey button and the links in Dhivehi", () => {
    render(<Login {...page} can_reset_password can_register can_use_passkeys />);

    expect(screen.getByText(strings.log_in_title)).toBeTruthy();
    expect(document.querySelector('form[action="/login"] input[name="email"]')).toBeTruthy();
    expect(document.querySelector('input[name="email"]')?.getAttribute("dir")).toBe("ltr");
    expect(screen.getByText(strings.log_in_with_passkey)).toBeTruthy();
    expect(screen.getByText(strings.sign_up).getAttribute("href")).toBe("/register");
  });

  it("shows an error when the browser has no passkeys", () => {
    render(<Login {...page} can_reset_password={false} can_register={false} can_use_passkeys />);

    fireEvent.click(screen.getByText(strings.log_in_with_passkey));

    expect(screen.getByRole("alert").textContent).toBe(strings.passkeys_unsupported);
    expect(screen.queryByText(strings.sign_up)).toBeNull();
  });

  it("links to alternative login methods", () => {
    render(
      <Login
        {...page}
        auth_type={{ ...page.auth_type, login_methods: [{ key: "mobile", label: "މޮބައިލް ނަންބަރުން", url: "/login/mobile" }] }}
        can_reset_password={false}
        can_register={false}
        can_use_passkeys={false}
      />,
    );

    expect(document.querySelector('[data-login-method="mobile"]')?.getAttribute("href")).toBe("/login/mobile");
  });
});

describe("TwoFactorChallenge", () => {
  it("switches between the code and a recovery code", () => {
    render(<TwoFactorChallenge {...page} />);

    expect(document.querySelector('input[name="code"]')).toBeTruthy();

    fireEvent.click(screen.getByText(strings.use_recovery_code));

    expect(document.querySelector('input[name="recovery_code"]')).toBeTruthy();
    expect(document.querySelector('input[name="code"]')).toBeNull();
  });
});

describe("Security", () => {
  it("shows the QR code while two-factor authentication is pending, and the passkeys", () => {
    render(
      <Security
        {...page}
        two_factor={{ enabled: false, pending: true, required: false, qr_code: "<svg data-qr></svg>", secret_key: "JBSWY3DP", recovery_codes: ["aaa-bbb"] }}
        passkeys={[{ id: 1, name: "ލެޕްޓޮޕް", authenticator: "iCloud Keychain", created_at: "1 day ago", last_used_at: null, destroy_url: "/settings/passkeys/1" }]}
        sessions={null}
        api_tokens={null}
      />,
    );

    expect(document.querySelector("[data-qr]")).toBeTruthy();
    expect(screen.getByText("JBSWY3DP")).toBeTruthy();
    expect(screen.getByText("aaa-bbb")).toBeTruthy();
    expect(screen.getByText("ލެޕްޓޮޕް")).toBeTruthy();
    expect(document.querySelector('form[action="/settings/passkeys/1"]')).toBeTruthy();
    expect(document.querySelector("[data-sessions]")).toBeNull();
  });

  it("hides turning off two-factor authentication when it is required", () => {
    render(
      <Security
        {...page}
        two_factor={{ enabled: true, pending: false, required: true, qr_code: null, secret_key: null, recovery_codes: null }}
        passkeys={null}
        sessions={[{ id: "s1", ip_address: "127.0.0.1", platform: "OS X", browser: "Safari", is_desktop: true, is_current_device: true, last_active: "now" }]}
        api_tokens={[]}
        plain_text_token="1|secret"
      />,
    );

    expect(screen.queryByText(strings.two_factor_turn_off)).toBeNull();
    expect(screen.getByText(strings.regenerate_recovery_codes)).toBeTruthy();
    expect(screen.getByText(strings.this_device, { exact: false })).toBeTruthy();
    expect(screen.getByText("1|secret")).toBeTruthy();
  });
});
