<?php

namespace Javaabu\Auth\Events;

use Illuminate\Queue\SerializesModels;
use Javaabu\Auth\PasswordUpdate\PasswordUpdatableContract;
use Javaabu\Auth\User;

class PasswordChanged
{
    use SerializesModels;

    /**
     * The verified user.
     *
     * @var PasswordUpdatableContract
     */
    public $user;

    /**
     * The verified user.
     *
     * @var User
     */
    public $causer;

    /**
     * Create a new event instance.
     *
     * @param  PasswordUpdatableContract  $user
     * @return void
     */
    public function __construct($user, $causer = null)
    {
        $this->user = $user;
        $this->causer = $causer;
    }
}
