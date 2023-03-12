<?php

declare(strict_types=1);

namespace PgFramework\Session;

use Mezzio\Session\LazySession;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionPersistenceInterface;
use PgFramework\ApplicationInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class SessionFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $c): SessionInterface
    {
        $persistence = $c->get(SessionPersistenceInterface::class);
        $request = $c->get(ApplicationInterface::class)->getRequest();
        $session = new LazySession($persistence, $request);
        $session->set('SESSION_LIFETIME_KEY', $session->getSessionLifetime());
        return $session;
    }
}
