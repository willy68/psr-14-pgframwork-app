<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use InvalidArgumentException;
use PgFramework\Validator\ValidationInterface;

class MaxValidation implements ValidationInterface
{
    protected int $max;
    protected string $error = 'Le champ %s doit avoir maximum %d caractères';

    public function __construct(int $max = 255, string $error = '')
    {
        $this->setMax($max);
        if (!empty($error)) {
            $this->error = $error;
        }
    }

    public function isValid(mixed $var): bool
    {
        //if (empty($var)) return false;
        if (is_numeric($var)) {
            return $this->checkNumeric($var);
        } elseif (is_string($var)) {
            return $this->checkString($var);
        } elseif (is_int($var)) {
            return $this->checkInt($var);
        } elseif (is_float($var)) {
            return $this->checkFloat($var);
        }
        return true;
    }

    public function parseParams($param): self
    {
        if (is_string($param)) {
            list($max, $message) = array_pad(explode(',', $param), 2, '');
            if (!empty($max)) {
                $this->setMax($max);
            }
            if (!empty($message)) {
                $this->error = $message;
            }
        }
        return $this;
    }

    public function getParams(): array
    {
        return [$this->max];
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    protected function checkString($var): bool
    {
        $len = strlen($var);
        if ($len > $this->max) {
            return false;
        }

        return true;
    }

    protected function checkInt($var): bool
    {
        if ($var > $this->max) {
            return false;
        }

        return true;
    }

    protected function checkFloat($var): bool
    {
        if ($var > $this->max) {
            return false;
        }

        return true;
    }

    protected function checkNumeric($var): bool
    {
        if (($val = $this->getNumeric($var)) !== null) {
            if ($val > $this->max) {
                return false;
            }
        }
        return true;
    }

    public function setMax($max = 255): static
    {
        $this->max = $this->getNumeric($max);
        //lancer une exception si max === null;
        if ($this->max === null || $this->max <= 0) {
            throw new InvalidArgumentException(
                'Argument invalide, $max doit être de type numeric  plus grand que 0 ex: 256 ou \'256\''
            );
        }
        return $this;
    }

    protected function getNumeric($val): float|int|string|null
    {
        if (is_numeric($val)) {
            return $val + 0;
        }
        return null;
    }
}
