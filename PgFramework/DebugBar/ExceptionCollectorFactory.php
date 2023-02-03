<?php

namespace PgFramework\DebugBar;

use DebugBar\DataCollector\ExceptionsCollector;

class ExceptionCollectorFactory
{
    public function __invoke(): ExceptionsCollector
    {
        $collector = (new ExceptionsCollector())->useHtmlVarDumper(true);
        $collector->setChainExceptions(true);
        return $collector;
    }
}
