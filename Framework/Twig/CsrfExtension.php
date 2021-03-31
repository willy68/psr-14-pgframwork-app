<?php

namespace Framework\Twig;

use Exception;
use Framework\EventListener\CsrfCookieListener;
use Twig\TwigFunction;

class CsrfExtension extends \Twig\Extension\AbstractExtension
{

    /**
     * 
     *
     * @var CsrfCookieListener
     */
    private $csrfListener;

    /**
     *
     * @param CsrfCookieListener $csrfListener
     */
    public function __construct(CsrfCookieListener $csrfListener)
    {
        $this->csrfListener = $csrfListener;
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
        "name=\"{$this->csrfListener->getFormKey()}\" " .
        "value=\"{$this->csrfListener->getToken()}\"/>";
    }
}
