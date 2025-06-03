<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use PgFramework\Validator\ValidationInterface;

class SlugValidation implements ValidationInterface
{
    protected string $pattern = '/^[0-9a-z-]+(-[0-9a-z]*)$/';
    protected string $error = 'Le champ %s n\'est pas un slug valide';

    public function __construct(string $error = null)
    {
        if (!is_null($error)) {
            $this->error = $error;
        }
    }

    /**
     * @param string $param
     * @return self
     */
    public function parseParams(string $param): self
    {
        $this->error = $param;
        return $this;
    }

    public function getParams(): array
    {
        return [];
    }

    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param mixed $var
     * @return bool
     */
    public function isValid(mixed $var): bool
    {
        if (is_null($var) || !preg_match($this->pattern, $var)) {
            return false;
        }
        return true;
    }
}
