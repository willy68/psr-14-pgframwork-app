<?php

namespace Framework\Security\Csrf;

interface CsrfTokenManagerInterface
{
    public function getToken(): string;
}
