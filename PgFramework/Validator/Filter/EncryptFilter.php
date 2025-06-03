<?php

declare(strict_types=1);

namespace PgFramework\Validator\Filter;

use PgFramework\Validator\FilterInterface;

class EncryptFilter extends AbstractFilter implements FilterInterface
{
    public const MD5 = 'MD5';

    public const CUSTOM = 'CUSTOM';

    protected string $method = self::MD5;

    protected mixed $customMethod;

    public function __construct($method = self::MD5, $customName = null)
    {
        $this->setMethod($method);
        $this->setCustomMethod($customName);
    }

    public function setMethod($method): void
    {
        if (is_string($method)) {
            $this->method = $method;
        }
    }

    public function setCustomMethod($customName): void
    {
        if (is_string($customName)) {
            $this->customMethod = $customName;
        }
    }
    /**
     *
     * filter $var method
     * return $var after filter if is set or just $var without filter
     */
    public function filter(mixed $var): mixed
    {
        if ($this->isSet($var)) {
            if ($this->method == self::MD5) {
                return md5($var);
            }
        }

        return $var;
    }
}
