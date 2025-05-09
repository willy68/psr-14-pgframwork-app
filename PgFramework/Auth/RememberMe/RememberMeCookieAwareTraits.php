<?php

declare(strict_types=1);

namespace PgFramework\Auth\RememberMe;

trait RememberMeCookieAwareTraits
{
    // Can't use const!
    private string $delimiter = ':';

    /**
     * Get the cookie parts
     *
     * @param string $cookie
     * @return array
     */
    protected function decodeCookie(string $cookie): array
    {
        return explode($this->delimiter, base64_decode($cookie));
    }

    /**
     * Encode the cookie parts
     *
     * @param array $cookieParts
     * @return string
     */
    protected function encodeCookie(array $cookieParts): string
    {
        return base64_encode(implode($this->delimiter, $cookieParts));
    }
}
