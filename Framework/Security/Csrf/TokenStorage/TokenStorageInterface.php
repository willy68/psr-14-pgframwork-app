<?php

namespace Framework\Security\Csrf\TokenStorage;

interface TokenStorageInterface
{
    public function hasToken(string $key): bool;

    public function getToken(string $key): string;

    public function setToken(string $token, string $key): void;

    public function removeToken(string$key): void;
}
