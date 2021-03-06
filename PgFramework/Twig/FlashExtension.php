<?php

declare(strict_types=1);

namespace PgFramework\Twig;

use PgFramework\Session\FlashService;
use Twig\TwigFunction;

class FlashExtension extends \Twig\Extension\AbstractExtension
{
  /**
   * @var FlashService
   */
    private $flashservice;

    /**
     * FlashExtension constructor.
     * @param FlashService $flashservice
     */
    public function __construct(FlashService $flashservice)
    {
        $this->flashservice = $flashservice;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('flash', [$this, 'getFlash'])
        ];
    }

  /**
   * @param string $type
   * @return string|null
   */
    public function getFlash(string $type): ?string
    {
        return $this->flashservice->get($type);
    }
}
