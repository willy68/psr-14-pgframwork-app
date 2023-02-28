<?php

declare(strict_types=1);

namespace PgFramework\Security\Csrf\TokenGenerator;

use Exception;
use PgFramework\Security\Security;

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * Generate and store a random token.
     *
     * @throws Exception
     *
     * @return string
     */
    public function generateToken(): string
    {
        return Security::saltToken(Security::createToken());
    }

    public function generateId(int $length = 8): string
    {
        return bin2hex(Security::randomBytes($length));
    }
}
