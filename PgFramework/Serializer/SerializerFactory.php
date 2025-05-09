<?php

declare(strict_types=1);

namespace PgFramework\Serializer;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $c): SerializerInterface
    {
        $encoders = [new JsonEncoder()];
        return new Serializer($c->get('serializer.normalizers'), $encoders);
    }
}
