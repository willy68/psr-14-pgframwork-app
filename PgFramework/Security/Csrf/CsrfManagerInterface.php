<?php

namespace PgFramework\Security\Csrf;

interface CsrfManagerInterface
{
    public function getToken(): string;
    public function removeToken(string $token): string;
    public function generateToken(): string;
    public function isTokenValid(string $token): bool;
    public function getSessionKey(): string;
    public function getFormKey(): string;
}
