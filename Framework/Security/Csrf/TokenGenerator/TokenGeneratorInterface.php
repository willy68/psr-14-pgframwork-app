<?php

namespace Framework\Security\Csrf\TokenGenerator;

interface TokenGeneratorInterface
{
    public function generateToken(): string;
}
