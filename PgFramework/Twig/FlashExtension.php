<?php

declare(strict_types=1);

namespace PgFramework\Twig;

use PgFramework\Session\FlashService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlashExtension extends AbstractExtension
{
    private FlashService $flashService;

    /**
     * FlashExtension constructor.
     * @param FlashService $falshService
     */
    public function __construct(FlashService $falshService)
    {
        $this->flashService = $falshService;
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
        return $this->flashService->get($type);
    }
}
