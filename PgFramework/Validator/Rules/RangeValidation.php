<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use InvalidArgumentException;
use PgFramework\Validator\ValidationInterface;

class RangeValidation implements ValidationInterface
{
    protected string $error = 'Le champ %s doit être entre %d et %d';

    protected int $min;

    protected int $max;

    public function __construct(int $min = 1, int $max = 255, string $error = null)
    {
        if ($error !== null) {
            $this->error = $error;
        }
        $this->setMin($min);
        $this->setMax($max);
    }

    public function parseParams(string $param): self
    {
        list($min, $max, $message) = array_pad(explode(',', $param), 3, '');
        if (!empty($min)) {
            $this->setMin($min);
        }
        if (!empty($max)) {
            $this->setMax($max);
        }
        if (!empty($message)) {
            $this->error = $message;
        }
        return $this;
    }

    public function getParams(): array
    {
        return [$this->min, $this->max];
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function isValid(mixed $var): bool
    {
        if (!isset($var)) {
            return false;
        }
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

    protected function checkString($var): bool
    {
        $len = strlen($var);
        if ($len < $this->min || $len > $this->max) {
            return false;
        }
        return true;
    }

    protected function checkInt($var): bool
    {
        if ($var < $this->min || $var > $this->max) {
            return false;
        }
        return true;
    }

    protected function checkFloat($var): bool
    {
        if ($var < $this->min || $var > $this->max) {
            return false;
        }
        return true;
    }

    protected function checkNumeric($var): bool
    {
        if (($val = $this->getNumeric($var)) !== null) {
            if ($val < $this->min || $val > $this->max) {
                return false;
            }
        }
        return true;
    }

    public function setMax($max = null): self
    {
        $this->max = $this->getNumeric($max);
        //lancer une exception si max === null;
        if ($this->max === null || $this->max <= 0) {
            throw new InvalidArgumentException(
                'Argument invalide, $max doit être de type numeric plus grand que 0 ex: 256 ou \'256\''
            );
        }
        return $this;
    }

    public function setMin($min): self
    {
        $this->min = $this->getNumeric($min);
        //lancer une exception si min === null;
        if ($this->min === null || $this->min < 0) {
            throw new InvalidArgumentException(
                'Argument invalide, $min doit être de type numeric plus grand ou égal a 0 ex: 2 ou \'2\''
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
