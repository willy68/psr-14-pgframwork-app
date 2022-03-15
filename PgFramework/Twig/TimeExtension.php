<?php

namespace PgFramework\Twig;

use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TimeExtension extends AbstractExtension
{
  /**
   * Undocumented function
   *
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
    public function ago(DateTimeInterface $date, string $format = 'd/m/Y H:i')
    {
        return '<time class="timeago" datetime="'  .
        $date->format(DateTimeInterface::ISO8601) .
        '">' .
        $date->format($format) .
        '</time>';
    }
}
