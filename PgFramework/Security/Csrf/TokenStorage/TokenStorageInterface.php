<?php

declare(strict_types=1);

namespace PgFramework\Security\Csrf\TokenStorage;

interface TokenStorageInterface
{
    public function hasToken(string $tokenId): bool;

    public function getToken(?string $tokenId = null): ?string;

    public function setToken(string $tokenId, string $token): void;

    public function removeToken(string $tokenId): ?string;
}
