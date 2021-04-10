<?php

namespace PgFramework\Security\Csrf\TokenGenerator;

interface TokenGeneratorInterface
{
    public function generateToken(): string;
}
