<?php

declare(strict_types=1);

namespace PgFramework\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Extension pour les textes
 */
class TextExtension extends AbstractExtension
{
  /**
   * @return array
   */
    public function getFilters(): array
    {
        return [
        new TwigFilter('excerpt', [$this, 'excerpt'])
        ];
    }

    /**
     * @param string $content
     * @param int $maxlength
     * @return string
     */
    public function excerpt(?string $content, int $maxlength = 100): string
    {
        if (is_null($content)) {
            return '';
        }
        if (mb_strlen($content) > $maxlength) {
            $excerpt = mb_substr($content, 0, $maxlength);
            $lastspace = mb_strrpos($excerpt, ' ');
            return mb_substr($excerpt, 0, $lastspace) . '...';
        }
        return $content;
    }
}
