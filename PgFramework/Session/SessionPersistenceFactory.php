<?php

namespace PgFramework\Session;

use Mezzio\Session\Ext\PhpSessionPersistence;
use Mezzio\Session\SessionPersistenceInterface;
use Psr\Container\ContainerInterface;

class SessionPersistenceFactory
{
    public function __invoke(ContainerInterface $c): SessionPersistenceInterface
    {
        $config = $c->has('session.persistence.ext') ? $c->get('session.persistence.ext') : null;
        return new PhpSessionPersistence(
            $config['non_locking'] ?? false,
            $config['delete_cookie_on_empty_session'] ?? false
        );
    }
}
