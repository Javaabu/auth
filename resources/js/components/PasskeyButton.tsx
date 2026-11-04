import { Button } from "@javaabu/forms";
import { useState } from "react";
import { errorMessage, passkeysSupported, registerPasskey, verifyPasskey, type PasskeyResult } from "../lib/webauthn";

export interface PasskeyButtonProps {
  /** `verify` to log in or confirm, `register` to add a passkey */
  mode: "verify" | "register";
  optionsUrl: string;
  url: string;
  label: string;
  unsupported: string;
  failed: string;
  /** Extra POST fields, e.g. `{ remember }` or `{ name }` */
  data?: () => Record<string, unknown>;
  /** Called with the server's JSON answer; defaults to following its `redirect` */
  onSuccess?: (body: unknown) => void;
  color?: "primary" | "secondary";
  className?: string;
}

/** A button that runs a passkey ceremony against the package's endpoints */
export function PasskeyButton({ mode, optionsUrl, url, label, unsupported, failed, data, onSuccess, color = "secondary", className }: PasskeyButtonProps) {
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  async function run() {
    setError(null);

    if (!passkeysSupported()) {
      setError(unsupported);

      return;
    }

    setBusy(true);

    try {
      const options = { optionsUrl, url, data: data?.() ?? {} };
      const result: PasskeyResult = mode === "register" ? await registerPasskey(options) : await verifyPasskey(options);

      if (!result.ok) {
        setError(errorMessage(result, failed));

        return;
      }

      if (onSuccess) {
        onSuccess(result.body);

        return;
      }

      const redirect = (result.body as { redirect?: string } | null)?.redirect;
      window.location.assign(redirect ?? window.location.href);
    } catch {
      setError(failed);
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="flex flex-col gap-2" data-passkey-button={mode}>
      <Button type="button" color={color} className={className} onClick={run} disabled={busy}>
        {label}
      </Button>
      {error ? (
        <p className="text-sm text-red-700" role="alert" data-passkey-error>
          {error}
        </p>
      ) : null}
    </div>
  );
}
