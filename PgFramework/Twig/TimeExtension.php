<?php

declare(strict_types=1);

namespace PgFramework\Twig;

use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TimeExtension extends AbstractExtension
{
  /**
   * @return array
   */
    public function getFilters(): array
    {
        return [
        new TwigFilter('ago', [$this, 'ago'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @param DateTimeInterface $date
     * @param string $format
     * @return string
     */
    public function ago(DateTimeInterface $date, string $format = 'd/m/Y H:i'): string
    {
        return '<time class="timeago" datetime="'  .
        $date->format(DateTimeInterface::ATOM) .
        '">' .
        $date->format($format) .
        '</time>';
    }
}
