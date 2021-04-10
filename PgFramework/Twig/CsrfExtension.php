<?php

namespace PgFramework\Twig;

use Exception;
use PgFramework\EventListener\CsrfListener;
use Twig\TwigFunction;

class CsrfExtension extends \Twig\Extension\AbstractExtension
{

    /**
     * 
     *
     * @var CsrfListener
     */
    private $csrfListener;

    /**
     *
     * @param CsrfCookieListener $csrfListener
     */
    public function __construct(CsrfListener $csrfListener)
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
