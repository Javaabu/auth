<?php

namespace Javaabu\Auth\Passkeys;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Passkeys\Passkey as BasePasskey;

/**
 * A passkey of any user type: the `passkeys` table has a `user_type` next to `user_id`
 *
 * @property string $user_type
 */
class Passkey extends BasePasskey
{
    protected $hidden = [
        'credential',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }

    /**
     * What the security page shows for the passkey
     *
     * @return array{
     *     id: int,
     *     name: string,
     *     authenticator: string|null,
     *     created_at: string|null,
     *     last_used_at: string|null
     * }
     */
    public function toDisplayArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'authenticator' => $this->authenticator,
            'created_at' => $this->created_at?->diffForHumans(),
            'last_used_at' => $this->last_used_at?->diffForHumans(),
        ];
    }
}
