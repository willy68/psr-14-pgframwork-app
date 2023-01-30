<?php

namespace PgFramework\Security\Csrf\TokenGenerator;

class CsrfGenerator implements TokenGeneratorInterface
{
    /**
     * Generate and store a random token.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }
}
