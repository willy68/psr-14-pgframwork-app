<?php

namespace PgFramework\Validator;

interface FilterInterface
{
    /**
     *
     *
     * @param mixed $var
     * @return mixed
     */
    public function filter($var);
}
