<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Max login attempts
     |--------------------------------------------------------------------------
     |
     | The maximum login attempts allowed before a user gets locked out.
     | This is a custom configuration.
     |
     */

    'default_guard' => 'web_admin',

    /*
     |--------------------------------------------------------------------------
     | Max login attempts
     |--------------------------------------------------------------------------
     |
     | The maximum login attempts allowed before a user gets locked out.
     | This is a custom configuration.
     |
     */

    'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
];
