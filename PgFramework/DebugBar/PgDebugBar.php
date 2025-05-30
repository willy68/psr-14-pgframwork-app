<?php

declare(strict_types=1);

namespace PgFramework\DebugBar;

use DebugBar\DebugBar;
use DebugBar\DebugBarException;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DataCollector\ExceptionsCollector;

/**
 * Based on https://github.com/barryvdh/laravel-debugbar/blob/master/src/LaravelDebugbar.php
 */
class PgDebugBar extends DebugBar
{
    /**
     * @throws DebugBarException
     */
    public function __construct()
    {
        $this->addCollector(new PhpInfoCollector())
            ->addCollector(new MessagesCollector())
            ->addCollector(new TimeDataCollector())
            ->addCollector(new MemoryCollector());

        $exceptionCollector = (new ExceptionsCollector())->useHtmlVarDumper(false);
        $exceptionCollector->setChainExceptions();

        $this->addCollector($exceptionCollector);
    }

    /**
     * Injects the web debug toolbar into the given Response.
     *
     * @param ResponseInterface $response A Response instance
     * Based on https://github.com/symfony/WebProfilerBundle/blob/master/EventListener/WebDebugToolbarListener.php
     */
    public function injectDebugbar(ResponseInterface $response): ResponseInterface
    {
        $content = $response->getBody()->getContents();

        $renderer = $this->getJavascriptRenderer()
            ->setBaseUrl('/assets/Resources');
        $head = $renderer->renderHead();
        $widget = $renderer->render();

        // Try to put the js/css directly before the </head>
        $pos = strripos($content, '</head>');
        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $head . substr($content, $pos);
        } else {
            // Append the head before the widget
            $widget = $head . $widget;
        }

        // Try to put the widget at the end, directly before the </body>
        $pos = strripos($content, '</body>');
        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $widget . substr($content, $pos);
        } else {
            $content = $content . $widget;
        }

        // Update the new content and reset the content length
        return $response->withBody(Utils::streamFor($content))
            ->withoutHeader('Content-Length');
    }
}
