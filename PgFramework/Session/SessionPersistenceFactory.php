<?php

declare(strict_types=1);

namespace PgFramework\Session;

use Mezzio\Session\Ext\PhpSessionPersistence;
use Mezzio\Session\SessionPersistenceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class SessionPersistenceFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $c): SessionPersistenceInterface
    {
        $config = $c->has('session.persistence.ext') ? $c->get('session.persistence.ext') : null;
        return new PhpSessionPersistence(
            $config['non_locking'] ?? false,
            $config['delete_cookie_on_empty_session'] ?? false
        );
    }
}
