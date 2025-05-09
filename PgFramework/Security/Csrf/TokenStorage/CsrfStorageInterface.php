<?php

namespace PgFramework\Security\Csrf\TokenStorage;

interface CsrfStorageInterface
{
    public function hasToken(string $token): bool;

    public function setToken(string $token): void;

    public function removeToken(string $token): string;

    public function getSessionKey(): string;
}
