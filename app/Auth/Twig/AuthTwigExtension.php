<?php

namespace App\Auth\Twig;

use Framework\Auth;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class AuthTwigExtension extends AbstractExtension
{

    /**
     * auth
     *
     * @var Auth
     */
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('current_user', [$this->auth, 'getUser'])
        ];
    }
}
