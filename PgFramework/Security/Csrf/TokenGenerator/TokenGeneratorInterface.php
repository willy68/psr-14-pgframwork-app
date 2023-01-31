<?php

declare(strict_types=1);

namespace PgFramework\Security\Csrf\TokenGenerator;

interface TokenGeneratorInterface
{
    public function generateToken(): string;
    public function generateId(int $length = 8): string;
}
