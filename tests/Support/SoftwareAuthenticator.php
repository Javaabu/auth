<?php

namespace Javaabu\Auth\Tests\Support;

use OpenSSLAsymmetricKey;
use RuntimeException;
use stdClass;

/**
 * A passkey authenticator in PHP, for tests: makes real P-256 credentials and signs
 * real WebAuthn responses, so the server-side checks run for real.
 */
class SoftwareAuthenticator
{
    protected OpenSSLAsymmetricKey $key;

    protected string $credential_id;

    protected ?string $user_handle = null;

    protected int $sign_count = 0;

    public function __construct(
        protected string $origin = 'http://localhost',
        protected string $rp_id = 'localhost',
    ) {
        $key = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);

        if (! $key) {
            throw new RuntimeException('Could not make a P-256 key.');
        }

        $this->key = $key;
        $this->credential_id = random_bytes(32);
    }

    public function credentialId(): string
    {
        return static::base64Url($this->credential_id);
    }

    /**
     * The credential for `navigator.credentials.create()` with the server's options
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function register(array $options): array
    {
        $this->user_handle = $options['user']['id'];

        $client_data = $this->clientData('webauthn.create', $options['challenge']);

        $details = openssl_pkey_get_details($this->key)['ec'];
        $cose_key = static::cbor([
            1 => 2,
            3 => -7,
            -1 => 1,
            -2 => new CborBytes(str_pad($details['x'], 32, "\0", STR_PAD_LEFT)),
            -3 => new CborBytes(str_pad($details['y'], 32, "\0", STR_PAD_LEFT)),
        ]);

        $auth_data = hash('sha256', $this->rp_id, true)
            . chr(0x45)
            . pack('N', $this->sign_count)
            . str_repeat("\0", 16)
            . pack('n', strlen($this->credential_id))
            . $this->credential_id
            . $cose_key;

        $attestation = static::cbor([
            'fmt' => 'none',
            'attStmt' => [],
            'authData' => new CborBytes($auth_data),
        ]);

        return [
            'id' => $this->credentialId(),
            'rawId' => $this->credentialId(),
            'type' => 'public-key',
            'response' => [
                'clientDataJSON' => static::base64Url($client_data),
                'attestationObject' => static::base64Url($attestation),
                'transports' => ['internal'],
            ],
            'clientExtensionResults' => new stdClass,
        ];
    }

    /**
     * The credential for `navigator.credentials.get()` with the server's options
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function assert(array $options): array
    {
        $this->sign_count++;

        $client_data = $this->clientData('webauthn.get', $options['challenge']);
        $auth_data = hash('sha256', $this->rp_id, true) . chr(0x05) . pack('N', $this->sign_count);

        openssl_sign($auth_data . hash('sha256', $client_data, true), $signature, $this->key, OPENSSL_ALGO_SHA256);

        return [
            'id' => $this->credentialId(),
            'rawId' => $this->credentialId(),
            'type' => 'public-key',
            'response' => [
                'clientDataJSON' => static::base64Url($client_data),
                'authenticatorData' => static::base64Url($auth_data),
                'signature' => static::base64Url($signature),
                'userHandle' => $this->user_handle,
            ],
            'clientExtensionResults' => new stdClass,
        ];
    }

    protected function clientData(string $type, string $challenge): string
    {
        return json_encode([
            'type' => $type,
            'challenge' => $challenge,
            'origin' => $this->origin,
            'crossOrigin' => false,
        ], JSON_UNESCAPED_SLASHES);
    }

    public static function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * A small CBOR encoder: maps, integers, text and byte strings
     */
    public static function cbor(mixed $value): string
    {
        return match (true) {
            $value instanceof CborBytes => static::cborHead(2, strlen($value->bytes)) . $value->bytes,
            is_int($value) && $value >= 0 => static::cborHead(0, $value),
            is_int($value) => static::cborHead(1, -1 - $value),
            is_string($value) => static::cborHead(3, strlen($value)) . $value,
            is_array($value) => static::cborHead(5, count($value)) . implode('', array_map(
                fn ($key, $item) => static::cbor($key) . static::cbor($item),
                array_keys($value),
                $value,
            )),
            default => throw new RuntimeException('Unsupported CBOR value.'),
        };
    }

    protected static function cborHead(int $major, int $length): string
    {
        $type = $major << 5;

        return match (true) {
            $length < 24 => chr($type | $length),
            $length < 256 => chr($type | 24) . chr($length),
            $length < 65536 => chr($type | 25) . pack('n', $length),
            default => chr($type | 26) . pack('N', $length),
        };
    }
}
