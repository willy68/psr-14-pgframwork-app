<?php

declare(strict_types=1);

namespace PgFramework\Auth\Provider;

use PgFramework\Auth\TokenInterface;

interface TokenProviderInterface
{
    /**
     * Get cookie token from a database or what else
     *
     * @param $series
     * @return TokenInterface|null
     */
    public function getTokenBySeries($series): ?TokenInterface;

    /**
     * Get cookie token from a database or what else
     *
     * @param mixed $credential
     * @return TokenInterface|null
     */
    public function getTokenByCredential(mixed $credential): ?TokenInterface;

    /**
     * Save token (database, cookie, both or other)
     *
     * @param array $token
     * @return TokenInterface|null
     */
    public function saveToken(array $token): ?TokenInterface;

    /**
     * Update token on database
     *
     * @param array $token
     * @param mixed $id
     * @return TokenInterface|null
     */
    public function updateToken(array $token, mixed $id): ?TokenInterface;

    /**
     * Destroy unused token
     *
     * @param int $id
     * @return void
     */
    public function deleteToken(int $id): void;
}
