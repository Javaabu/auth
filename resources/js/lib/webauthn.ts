/**
 * The browser side of passkeys: fetches options from the server, asks the browser for a
 * credential and posts it back. Works with the JSON endpoints of laravel/passkeys.
 */

export interface PasskeyRequestOptions {
  /** GET: returns `{ options }` */
  optionsUrl: string;
  /** POST: receives `{ credential, … }` */
  url: string;
  /** Extra fields for the POST, e.g. `{ name }` or `{ remember: true }` */
  data?: Record<string, unknown>;
}

export interface PasskeyResult<T = unknown> {
  ok: boolean;
  status: number;
  body: T;
}

type Json = Record<string, unknown>;

export function passkeysSupported(): boolean {
  return typeof window !== "undefined" && typeof window.PublicKeyCredential === "function" && !!navigator.credentials;
}

export function base64UrlToBuffer(value: string): ArrayBuffer {
  const base64 = value.replace(/-/g, "+").replace(/_/g, "/").padEnd(Math.ceil(value.length / 4) * 4, "=");
  const binary = atob(base64);
  const bytes = new Uint8Array(binary.length);

  for (let i = 0; i < binary.length; i++) {
    bytes[i] = binary.charCodeAt(i);
  }

  return bytes.buffer;
}

export function bufferToBase64Url(buffer: ArrayBuffer | ArrayBufferView): string {
  const bytes = buffer instanceof ArrayBuffer ? new Uint8Array(buffer) : new Uint8Array(buffer.buffer, buffer.byteOffset, buffer.byteLength);
  let binary = "";

  for (const byte of bytes) {
    binary += String.fromCharCode(byte);
  }

  return btoa(binary).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/, "");
}

function csrfHeaders(): Record<string, string> {
  const headers: Record<string, string> = {};
  const meta = typeof document !== "undefined" ? document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]') : null;

  if (meta?.content) {
    headers["X-CSRF-TOKEN"] = meta.content;

    return headers;
  }

  const cookie = typeof document !== "undefined" ? document.cookie.split("; ").find((row) => row.startsWith("XSRF-TOKEN=")) : undefined;

  if (cookie) {
    headers["X-XSRF-TOKEN"] = decodeURIComponent(cookie.split("=")[1] ?? "");
  }

  return headers;
}

async function request<T>(url: string, init: RequestInit = {}): Promise<PasskeyResult<T>> {
  const response = await fetch(url, {
    credentials: "same-origin",
    ...init,
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-Requested-With": "XMLHttpRequest",
      ...csrfHeaders(),
      ...(init.headers ?? {}),
    },
  });

  const text = await response.text();
  let body: unknown = null;

  try {
    body = text ? JSON.parse(text) : null;
  } catch {
    body = text;
  }

  return { ok: response.ok, status: response.status, body: body as T };
}

function creationOptions(options: Json): CredentialCreationOptions {
  const publicKey = { ...options } as Json;

  publicKey.challenge = base64UrlToBuffer(String(options.challenge));
  publicKey.user = { ...(options.user as Json), id: base64UrlToBuffer(String((options.user as Json).id)) };
  publicKey.excludeCredentials = ((options.excludeCredentials as Json[] | undefined) ?? []).map((credential) => ({
    ...credential,
    id: base64UrlToBuffer(String(credential.id)),
  }));

  return { publicKey: publicKey as unknown as PublicKeyCredentialCreationOptions };
}

function requestOptions(options: Json): CredentialRequestOptions {
  const publicKey = { ...options } as Json;

  publicKey.challenge = base64UrlToBuffer(String(options.challenge));
  publicKey.allowCredentials = ((options.allowCredentials as Json[] | undefined) ?? []).map((credential) => ({
    ...credential,
    id: base64UrlToBuffer(String(credential.id)),
  }));

  return { publicKey: publicKey as unknown as PublicKeyCredentialRequestOptions };
}

/** The JSON form of a credential, as the server expects it */
export function credentialToJson(credential: PublicKeyCredential): Json {
  const response = credential.response as AuthenticatorAttestationResponse & AuthenticatorAssertionResponse;
  const json: Json = {
    clientDataJSON: bufferToBase64Url(response.clientDataJSON),
  };

  if ("attestationObject" in response && response.attestationObject) {
    json.attestationObject = bufferToBase64Url(response.attestationObject);
    json.transports = typeof response.getTransports === "function" ? response.getTransports() : [];
  }

  if ("authenticatorData" in response && response.authenticatorData) {
    json.authenticatorData = bufferToBase64Url(response.authenticatorData);
    json.signature = bufferToBase64Url(response.signature);
    json.userHandle = response.userHandle ? bufferToBase64Url(response.userHandle) : null;
  }

  return {
    id: credential.id,
    rawId: bufferToBase64Url(credential.rawId),
    type: credential.type,
    authenticatorAttachment: credential.authenticatorAttachment ?? null,
    clientExtensionResults: credential.getClientExtensionResults(),
    response: json,
  };
}

async function ceremony<T>(kind: "create" | "get", { optionsUrl, url, data = {} }: PasskeyRequestOptions): Promise<PasskeyResult<T>> {
  const options = await request<{ options: Json }>(optionsUrl);

  if (!options.ok || !options.body?.options) {
    return options as unknown as PasskeyResult<T>;
  }

  const credential =
    kind === "create"
      ? await navigator.credentials.create(creationOptions(options.body.options))
      : await navigator.credentials.get(requestOptions(options.body.options));

  if (!credential) {
    throw new Error("No passkey was chosen.");
  }

  return request<T>(url, {
    method: "POST",
    body: JSON.stringify({ ...data, credential: credentialToJson(credential as PublicKeyCredential) }),
  });
}

/** Add a passkey to the signed-in user's account */
export function registerPasskey<T = unknown>(options: PasskeyRequestOptions): Promise<PasskeyResult<T>> {
  return ceremony<T>("create", options);
}

/** Log in, or confirm the password, with a passkey */
export function verifyPasskey<T = unknown>(options: PasskeyRequestOptions): Promise<PasskeyResult<T>> {
  return ceremony<T>("get", options);
}

/** The first validation message in a Laravel error response */
export function errorMessage(result: PasskeyResult, fallback: string): string {
  const body = result.body as { message?: string; errors?: Record<string, string[]> } | null;
  const first = body?.errors ? Object.values(body.errors)[0]?.[0] : undefined;

  return first ?? body?.message ?? fallback;
}
