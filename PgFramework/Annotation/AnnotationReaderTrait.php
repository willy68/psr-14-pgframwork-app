<?php

declare(strict_types=1);

namespace PgFramework\Annotation;

use Doctrine\ORM\Mapping\Driver\AttributeReader;
use Doctrine\Common\Annotations\AnnotationReader;

trait AnnotationReaderTrait
{
    protected $reader = null;

    protected function getReader()
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
