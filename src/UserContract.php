<?php
/**
 * The contract for user
 */

namespace Javaabu\Auth;

interface UserContract
{
    /**
     * Name of the user guard
     */
    public function guardName(): string;

    /**
     * Get home url attribute
     *
     * @return string
     */
    public function homeUrl();

    /**
     * Get login url attribute
     *
     * @return string
     */
    public function loginUrl();

    /**
     * Get the password reset route name
     *
     * @return string
     */
    public function getRouteForPasswordReset();

    /**
     * Get the email verification route name
     *
     * @return string
     */
    public function getRouteForEmailVerification();

    /**
     * Get email verification redirect url
     *
     * @return string
     */
    public function emailVerificationRedirectUrl();

    /**
     * Get the inactive notice url
     *
     * @return string
     */
    public function inactiveNoticeUrl();
}
