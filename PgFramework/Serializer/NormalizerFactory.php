<?php

declare(strict_types=1);

namespace PgFramework\Serializer;

use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class NormalizerFactory
{
    public function __invoke(): array
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader());
        return [
            new DateTimeNormalizer(),
            new ObjectNormalizer($classMetadataFactory),
        ];
    }

}