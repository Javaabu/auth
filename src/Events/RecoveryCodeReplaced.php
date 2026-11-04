<?php

namespace Javaabu\Auth\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RecoveryCodeReplaced
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Model $user,
        public string $code,
    ) {}
}
