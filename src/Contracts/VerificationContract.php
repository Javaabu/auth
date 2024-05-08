<?php

namespace Javaabu\Auth\Contracts;

interface VerificationContract
{
    public function applyMiddlewares(): void;

    public function getEmailVerificationView();

    public function getVerificationResultView();

    public function determinePathForRedirectUsing(): \Javaabu\Auth\User;
}
