<?php

namespace Javaabu\Auth\Contracts;

interface RegisterContract extends HasUserTypeRedirectContract
{
    public function applyMiddlewares(): void;

    public function showRegistrationForm();

    public function userClass(): string;
}
