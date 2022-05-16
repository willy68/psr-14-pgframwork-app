<?php

namespace PgFramework\Mailer;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;

class MailerFactory
{
    public function __invoke(ContainerInterface $c): MailerInterface
    {
        if ($c->get('env') === 'prod') {
            $transport = Transport::fromDsn($c->get('dsn'));
        } else {
            $transport = Transport::fromDsn('smtp://localhost:1025');
        }
        $mailer = new Mailer($transport, null, $c->get(EventDispatcherInterface::class));
        return $mailer;
    }
}
