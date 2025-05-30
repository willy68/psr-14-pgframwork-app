<?php

declare(strict_types=1);

namespace PgFramework\Security\Csrf;

interface CsrfTokenManagerInterface
{
    public const DELIMITER = '.';

    /**
    * Returns a CSRF token.
    *
    * If previously no token existed, a new token is
    * generated. Otherwise the last token is returned.
    *
    * @return string The CSRF token
    */
    public function getToken(?string $tokenId = null): string;

   /**
    * Generates a new token value.
    *
    * This method will generate a new token for this id, independent
    * of whether a token value previously existed or not. It can be used
    * after a POST etc... request .
    *
    * @return string The CSRF token
    */
    public function refreshToken(string $tokenId): string;

   /**
    * Invalidates the CSRF token, if one exists.
    *
    * @return string|null Returns the removed token value if one existed, NULL
    *                     otherwise
    */
    public function removeToken(string $tokenId): ?string;

   /**
    * Returns whether the given CSRF token is valid.
    *
    * @return bool Returns true if the token is valid, false otherwise
    */
    public function isTokenValid(string $token): bool;

   /**
    * Generates a new token value.
    *
    * This method will generate a new token with new id, independent
    * of whether a token value previously existed or not. It can be used to
    * enforce once-only tokens in environments with high security needs.
    *
    * @return string The CSRF token
    */
    public function generateToken(): string;
}
