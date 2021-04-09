<?php

namespace Framework\Twig;

use InvalidArgumentException;
use Twig\TwigFunction;
use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;

class WebpackExtension extends AbstractExtension
{

    /**
     * Path du fichier entrypoints.json
     *
     * @var string
     */
    private $entryPoints;

    public function __construct(string $entryPoints)
    {
            $this->entryPoints =  $entryPoints;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('add_script_tag_entrypoints', [$this, 'scriptTag'], ['is_safe' => ['html']]),
            new TwigFunction('add_link_tag_entrypoints', [$this, 'linkTag'], ['is_safe' => ['html']])
        ];
    }

    public function scriptTag(string $entryName): string
    {
        $entryPoints = $this->readJsonFile($this->entryPoints, $entryName);
        $tags = '';
        if (!empty($entryPoints) && array_key_exists('js', $entryPoints)) {
            foreach($entryPoints['js'] as $tag) {
            $tags .= <<<HTML
        <script src="{$tag}" defer></script>\n
HTML;
            }
        }
        return $tags;
    }

    public function linkTag(string $entryName)
    {
        $entryPoints = $this->readJsonFile($this->entryPoints, $entryName);
        $tags = '';
        if (!empty($entryPoints) && array_key_exists('css', $entryPoints)) {
            foreach($entryPoints['css'] as $tag) {
                $tags .= <<<HTML
        <link href="{$tag}" rel="stylesheet">\n
HTML;
            }
        }
        return $tags;

    }

    protected function readJsonFile(string $file, string $entryName): array
    {
        $scriptTag = [];

        if (!file_exists($file)) {
            return $scriptTag;
        }

        $entry = json_decode(file_get_contents($file), true);

        if (is_array($entry) && array_key_exists('entrypoints', $entry) && is_array($entry['entrypoints'])) {
            if (array_key_exists($entryName, $entry['entrypoints'])) {
                $scriptTag = $entry['entrypoints'][$entryName];
            }
        }

        return $scriptTag;
    }
}
