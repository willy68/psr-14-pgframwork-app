<?php

declare(strict_types=1);

namespace PgFramework\Validator\Filter;

use PgFramework\Validator\FilterInterface;

class TrimFilter extends AbstractFilter implements FilterInterface
{
    /**
     * Return $var after filter if is set or just $var without filter.
     *
     * @param mixed $var
     * @return mixed
     */
    public function filter(mixed $var): mixed
    {
        if ($this->isSet($var)) {
            return trim($var);
        }

        return $var;
    }
}
