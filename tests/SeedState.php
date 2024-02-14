<?php

namespace Javaabu\Auth\Tests;

class SeedState
{
    /**
     * Indicates if the test seeders has been run.
     *
     * @var bool
     */
    public static $seeded = false;

    /**
     * Indicates if a lazy refresh hook has been invoked.
     *
     * @var bool
     */
    public static $lazilyRefreshed = false;
}
