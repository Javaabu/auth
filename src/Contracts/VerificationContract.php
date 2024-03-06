<?php

namespace Javaabu\Auth\Contracts;

interface VerificationContract
{
    public function getEmailVerificationView();

    public function getVerificationResultView();
}
