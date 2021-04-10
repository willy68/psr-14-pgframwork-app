<?php

namespace PgFramework\Security\Csrf\TokenGenerator;

class TokenGenerator implements TokenGeneratorInterface
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
        $token = bin2hex(random_bytes(16));

        return $token;
    }
}
