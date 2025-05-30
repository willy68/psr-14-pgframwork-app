<?php

namespace PgFramework\Mailer;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;

class MailerFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $c): MailerInterface
    {
        if ($c->get('env') === 'prod') {
            $transport = Transport::fromDsn($c->get('mailer.dsn'));
        } else {
            $transport = Transport::fromDsn('smtp://localhost:1025');
        }
        return new Mailer($transport, null, $c->get(EventDispatcherInterface::class));
    }
}
