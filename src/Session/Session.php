<?php

namespace Javaabu\Auth\Session;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Javaabu\Auth\Agent;

class Session extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    /**
     * The attributes that are cast to native types
     *
     * @var array
     */
    protected $casts = [
        'last_activity' => 'datetime',
    ];


    public function __construct(array $attributes = [])
    {
        $this->table = config('session.table');
        $this->connection = config('session.connection');

        parent::__construct($attributes);
    }

    public function getAgentAttribute(): Agent
    {
        return $this->createAgent();
    }

    public function getIsCurrentDeviceAttribute(): bool
    {
        return $this->id === request()->session()->getId();
    }

    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }

    /**
     * Create a new agent instance from the given session.
     *
     * @return Agent
     */
    protected function createAgent(): Agent
    {
        return tap(new Agent(), fn ($agent) => $agent->setUserAgent($this->user_agent));
    }
}
