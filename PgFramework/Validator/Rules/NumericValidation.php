<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use PgFramework\Validator\ValidationInterface;

class NumericValidation implements ValidationInterface
{
    protected string $error = 'Le champ %s doit être entre %d et %d';
    protected int $min;
    protected int $max;

    public function __construct(int $min = 1, int $max = 255, string $errormsg = null)
    {
        if ($errormsg !== null) {
            $this->error = $errormsg;
        }
        $this->setMin($min);
        $this->setMax($max);
    }

    public function isValid(mixed $var): bool
    {
        return $this->checkNumeric($var);
    }

    public function parseParams(string $param): self
    {
        list($min, $max, $message) = array_pad(explode(',', $param), 3, '');
        if (!empty($message)) {
            $this->error = $message;
        }
        if (!empty($min)) {
            $this->setMin($min);
        }
        if (!empty($max)) {
            $this->setMax($max);
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

    protected function checkNumeric($var): bool
    {
        if (($val = $this->getNumeric($var)) !== null) {
            $options = array();
            if (!empty($this->min)) {
                $options['options']['min_range'] = $this->min;
            }
            if (!empty($this->max)) {
                $options['options']['max_range'] = $this->max;
            }

            if ((filter_var($val, FILTER_VALIDATE_INT, $options)) !== false) {
                return true;
            }
        }
        return false;
    }

    public function setMin($min = 1): self
    {
        $this->min = $this->getNumeric($min);
        return $this;
    }

    public function setMax($max = 255): self
    {
        $this->max = $this->getNumeric($max);
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
