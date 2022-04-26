<?php

declare(strict_types=1);

namespace PgFramework\Twig;

use Exception;
use PgFramework\Security\Csrf\CsrfTokenManager;
use Twig\TwigFunction;

class CsrfExtension extends \Twig\Extension\AbstractExtension
{
    private $tokenManager;

    /**
     *
     * @param CsrfCookieListener $csrfListener
     */
    public function __construct(CsrfTokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('csrf_input', [$this, 'csrfInput'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @return string
     * @throws Exception
     */
    public function csrfInput()
    {
        return "<input type=\"hidden\" " .
        "name=\"{$this->tokenManager->getFormKey()}\" " .
        "value=\"{$this->tokenManager->getToken()}\"/>";
    }
}
