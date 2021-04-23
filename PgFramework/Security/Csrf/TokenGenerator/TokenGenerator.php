<?php

namespace PgFramework\Security\Csrf\TokenGenerator;

use PgFramework\Security\Security;

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
        $token = Security::saltToken(Security::createToken());

        return $token;
    }
}
