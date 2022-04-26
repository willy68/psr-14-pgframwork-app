<?php

declare(strict_types=1);

namespace PgFramework\Session;

use Mezzio\Session\LazySession;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionPersistenceInterface;
use PgFramework\ApplicationInterface;
use Psr\Container\ContainerInterface;

class SessionFactory
{
    public function __invoke(ContainerInterface $c): SessionInterface
    {
        $persistence = $c->get(SessionPersistenceInterface::class);
        $request = $c->get(ApplicationInterface::class)->getRequest();
        return new LazySession($persistence, $request);
    }
}
