import { afterEach, describe, expect, it, vi } from "vitest";
import { base64UrlToBuffer, bufferToBase64Url, credentialToJson, errorMessage, passkeysSupported, verifyPasskey } from "../../resources/js/lib/webauthn";

afterEach(() => {
  vi.unstubAllGlobals();
  vi.restoreAllMocks();
});

describe("base64url", () => {
  it("round-trips bytes", () => {
    const bytes = new Uint8Array([0, 1, 250, 251, 252, 253, 254, 255]);
    const encoded = bufferToBase64Url(bytes);

    expect(encoded).not.toMatch(/[+/=]/);
    expect(new Uint8Array(base64UrlToBuffer(encoded))).toEqual(bytes);
  });
});

describe("credentialToJson", () => {
  it("encodes an assertion the way laravel/passkeys reads it", () => {
    const buffer = (values: number[]) => new Uint8Array(values).buffer;
    const credential = {
      id: "abc",
      rawId: buffer([1, 2, 3]),
      type: "public-key",
      authenticatorAttachment: "platform",
      getClientExtensionResults: () => ({}),
      response: {
        clientDataJSON: buffer([4]),
        authenticatorData: buffer([5]),
        signature: buffer([6]),
        userHandle: buffer([7]),
      },
    } as unknown as PublicKeyCredential;

    expect(credentialToJson(credential)).toEqual({
      id: "abc",
      rawId: "AQID",
      type: "public-key",
      authenticatorAttachment: "platform",
      clientExtensionResults: {},
      response: { clientDataJSON: "BA", authenticatorData: "BQ", signature: "Bg", userHandle: "Bw" },
    });
  });
});

describe("verifyPasskey", () => {
  it("gets options, asks the browser and posts the credential with the CSRF token", async () => {
    document.head.innerHTML = '<meta name="csrf-token" content="token-123">';

    const fetch = vi
      .fn()
      .mockResolvedValueOnce(new Response(JSON.stringify({ options: { challenge: "AQID", allowCredentials: [] } })))
      .mockResolvedValueOnce(new Response(JSON.stringify({ redirect: "/dashboard" })));
    vi.stubGlobal("fetch", fetch);

    const get = vi.fn().mockResolvedValue({
      id: "abc",
      rawId: new Uint8Array([1]).buffer,
      type: "public-key",
      getClientExtensionResults: () => ({}),
      response: { clientDataJSON: new Uint8Array([2]).buffer, authenticatorData: new Uint8Array([3]).buffer, signature: new Uint8Array([4]).buffer, userHandle: null },
    });
    vi.stubGlobal("navigator", { credentials: { get } });

    const result = await verifyPasskey({ optionsUrl: "/passkeys/login/options", url: "/passkeys/login", data: { remember: true } });

    expect(result).toEqual({ ok: true, status: 200, body: { redirect: "/dashboard" } });
    expect(new Uint8Array(get.mock.calls[0]![0].publicKey.challenge)).toEqual(new Uint8Array([1, 2, 3]));

    const [url, init] = fetch.mock.calls[1]!;
    expect(url).toBe("/passkeys/login");
    expect(init.method).toBe("POST");
    expect(init.headers["X-CSRF-TOKEN"]).toBe("token-123");
    expect(JSON.parse(init.body)).toMatchObject({ remember: true, credential: { id: "abc", rawId: "AQ" } });
  });
});

describe("errorMessage", () => {
  it("prefers the first validation error", () => {
    expect(errorMessage({ ok: false, status: 422, body: { message: "x", errors: { credential: ["ޕާސްކީ ނުވަޒަނެއް."] } } }, "fallback")).toBe("ޕާސްކީ ނުވަޒަނެއް.");
    expect(errorMessage({ ok: false, status: 500, body: null }, "fallback")).toBe("fallback");
  });
});

describe("passkeysSupported", () => {
  it("is false without PublicKeyCredential", () => {
    expect(passkeysSupported()).toBe(false);
  });
});
