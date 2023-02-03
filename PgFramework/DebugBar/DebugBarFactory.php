<?php

declare(strict_types=1);

namespace PgFramework\DebugBar;

use DebugBar\DebugBar;
use PgFramework\DebugBar\PgDebugBar;
use Psr\Container\ContainerInterface;
use DebugBar\DataCollector\ExceptionsCollector;

class DebugBarFactory
{
    public function __invoke(ContainerInterface $c): DebugBar
    {
        $debugBar = new PgDebugBar();
        $exceptionCollector = $c->get(ExceptionsCollector::class);

        try {
            // Peut-être placer tous ça dans un listener d'exception
            $exceptionCollector = (new ExceptionsCollector())->useHtmlVarDumper(false);
            $exceptionCollector->setChainExceptions(true);
            /*$exceptionCollector->addThrowable(new Exception());
            $exceptionCollector->addThrowable(new InvalidCsrfException());
            $exceptionCollector->addThrowable(new RecordNotFound());
            $exceptionCollector->addThrowable(new PageNotFoundException());*/

            $debugBar->addCollector($exceptionCollector);
        } catch (\Exception $e) {
        }
        return $debugBar;
    }
}
