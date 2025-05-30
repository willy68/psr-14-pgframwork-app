<?php

declare(strict_types=1);

namespace PgFramework\Twig;

use Exception;
use PgFramework\Security\Csrf\CsrfTokenManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CsrfExtension extends AbstractExtension
{
    private CsrfTokenManager $tokenManager;

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
    public function csrfInput(): string
    {
        return "<input type=\"hidden\" " .
        "name=\"{$this->tokenManager->getFormKey()}\" " .
        "value=\"{$this->tokenManager->getToken()}\"/>";
    }
}
