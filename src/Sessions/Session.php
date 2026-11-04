<?php

namespace Javaabu\Auth\Sessions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A row of the `multi_auth_database` session driver's table
 *
 * @property string $id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $last_activity
 */
class Session extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    public function __construct(array $attributes = [])
    {
        $this->table = config('session.table', 'sessions');
        $this->connection = config('session.connection');

        parent::__construct($attributes);
    }

    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }

    public function agent(): Agent
    {
        return tap(new Agent, fn (Agent $agent) => $agent->setUserAgent((string) $this->user_agent));
    }

    public function isCurrentDevice(): bool
    {
        return request()->hasSession()
            && $this->id === request()->session()->getId();
    }

    /**
     * What the security page shows for the session
     *
     * @return array{
     *     id: string,
     *     ip_address: string|null,
     *     platform: string|null,
     *     browser: string|null,
     *     is_desktop: bool,
     *     is_current_device: bool,
     *     last_active: string
     * }
     */
    public function toDisplayArray(): array
    {
        $agent = $this->agent();

        return [
            'id' => $this->id,
            'ip_address' => $this->ip_address,
            'platform' => $agent->platform() ?: null,
            'browser' => $agent->browser() ?: null,
            'is_desktop' => $agent->isDesktop(),
            'is_current_device' => $this->isCurrentDevice(),
            'last_active' => now()->setTimestamp((int) $this->last_activity)->diffForHumans(),
        ];
    }
}
