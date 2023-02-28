<?php

declare(strict_types=1);

namespace PgFramework\Validator\Filter;

abstract class AbstractFilter
{
    /**
     *
     * @Check if POST variable is set
     *
     * @access protected
     *
     * @param string $var variable to check
     * @return bool
     *
     */
    protected function isSet(string $var): bool
    {
        return (isset($var) && strlen($var));
    }
}
