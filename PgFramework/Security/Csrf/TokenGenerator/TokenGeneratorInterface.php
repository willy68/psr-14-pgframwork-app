<?php

declare(strict_types=1);

namespace PgFramework\Security\Csrf\TokenGenerator;

interface TokenGeneratorInterface
{
    public function generateToken(): string;
}
