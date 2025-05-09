<?php

declare(strict_types=1);

namespace PgFramework\Auth;

use DateTime;

interface TokenInterface
{
    /**
     * Get token ID
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Get unique user series
     *
     * @return string
     */
    public function getSeries(): string;

    /**
     * Get user credential (ex. username or email)
     *
     * @return string
     */
    public function getCredential(): string;

    /**
     * Get the random password hash
     *
     * @return string
     */
    public function getRandomPassword(): string;

    /**
     * Get the expiration date
     *
     * @return DateTime
     */
    public function getExpirationDate(): DateTime;
}
