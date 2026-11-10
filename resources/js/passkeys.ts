/**
 * For the Blade pages: wires up [data-passkey-login], [data-passkey-confirm] and [data-passkeys]
 * (adding a passkey on the security page) to the passkey endpoints in their data attributes.
 */
import { errorMessage, passkeysSupported, registerPasskey, verifyPasskey } from "./lib/webauthn";

function showError(root: HTMLElement, message: string): void {
  const element = root.querySelector<HTMLElement>("[data-passkey-error]");

  if (!element) {
    return;
  }

  element.textContent = message;
  element.hidden = false;
}

function redirectFrom(body: unknown, fallback: string): string {
  const redirect = (body as { redirect?: string } | null)?.redirect;

  return redirect ?? fallback;
}

async function run(root: HTMLElement, action: () => Promise<{ ok: boolean; body: unknown; status: number }>, onSuccess: (body: unknown) => void): Promise<void> {
  const failed = root.dataset.failed ?? "Something went wrong.";

  if (!passkeysSupported()) {
    showError(root, root.dataset.unsupported ?? failed);

    return;
  }

  try {
    const result = await action();

    if (!result.ok) {
      showError(root, errorMessage(result, failed));

      return;
    }

    onSuccess(result.body);
  } catch {
    showError(root, failed);
  }
}

function onClick(event: Event): void {
  const target = event.target as Element | null;
  const login = target?.closest?.<HTMLElement>("[data-passkey-login-button]")?.closest<HTMLElement>("[data-passkey-login]");
  const confirm = target?.closest?.<HTMLElement>("[data-passkey-confirm-button]")?.closest<HTMLElement>("[data-passkey-confirm]");
  const add = target?.closest?.<HTMLElement>("[data-passkey-add-button]")?.closest<HTMLElement>("[data-passkeys]");

  if (login) {
    event.preventDefault();
    const remember = document.querySelector<HTMLInputElement>('[data-login-form] input[name="remember"]')?.checked ?? false;

    void run(
      login,
      () => verifyPasskey({ optionsUrl: login.dataset.optionsUrl!, url: login.dataset.url!, data: { remember } }),
      (body) => window.location.assign(redirectFrom(body, window.location.href)),
    );
  }

  if (confirm) {
    event.preventDefault();

    void run(
      confirm,
      () => verifyPasskey({ optionsUrl: confirm.dataset.optionsUrl!, url: confirm.dataset.url! }),
      (body) => window.location.assign(redirectFrom(body, window.location.href)),
    );
  }

  if (add) {
    event.preventDefault();
    const name = add.querySelector<HTMLInputElement>("[data-passkey-name]")?.value.trim() || navigator.platform || "Passkey";

    void run(
      add,
      () => registerPasskey({ optionsUrl: add.dataset.optionsUrl!, url: add.dataset.url!, data: { name } }),
      () => window.location.reload(),
    );
  }
}

export function init(root: Document = document): void {
  root.addEventListener("click", onClick);
}

if (typeof document !== "undefined" && !(window as { __javaabuAuthPasskeys?: boolean }).__javaabuAuthPasskeys) {
  (window as { __javaabuAuthPasskeys?: boolean }).__javaabuAuthPasskeys = true;
  init();
}
