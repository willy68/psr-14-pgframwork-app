<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use PgFramework\Validator\ValidationInterface;

use function filter_var;
use function is_null;
use function is_string;

use const FILTER_VALIDATE_EMAIL;

class EmailValidation implements ValidationInterface
{
    protected string $error = 'Le champ %s doit être une adresse E-mail valide';

    public function __construct(?string $error = null)
    {
        if (!is_null($error)) {
            $this->error = $error;
        }
    }

    public function isValid(mixed $var): bool
    {
        if (is_string($var)) {
            return filter_var($var, FILTER_VALIDATE_EMAIL) !== false;
        }
        return false;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function parseParams(string $param): self
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}
