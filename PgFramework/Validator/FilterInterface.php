<?php

declare(strict_types=1);

namespace PgFramework\Validator;

interface FilterInterface
{
    /**
     *
     *
     * @param mixed $var
     * @return mixed
     */
    public function filter(mixed $var): mixed;
}
