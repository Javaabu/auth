<?php

namespace Javaabu\Auth\Contracts;

interface VerificationContract extends HasUserTypeRedirectContract
{
    public function applyMiddlewares(): void;

    public function getEmailVerificationView();

    public function getVerificationResultView();
}
