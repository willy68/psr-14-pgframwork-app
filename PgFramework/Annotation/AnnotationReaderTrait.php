<?php

declare(strict_types=1);

namespace PgFramework\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;

trait AnnotationReaderTrait
{
    protected mixed $reader = null;

    protected function getReader():Reader
    {
        if ($this->reader === null) {
            if (PHP_VERSION_ID >= 80000) {
                $this->reader = new AttributeReader();
            } else {
                $this->reader = new AnnotationReader();
            }
        }
        return $this->reader;
    }
}
