<?php

namespace PgFramework\Security\Csrf\TokenGenerator;

interface CsrfGeneratorInterface
{
    public function generateToken(): string;
}
