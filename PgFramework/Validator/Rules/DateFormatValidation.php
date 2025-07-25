<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use DateTime;
use PgFramework\Validator\ValidationInterface;

class DateFormatValidation implements ValidationInterface
{
    protected string $format;
    protected string $error = "Le champ %s doit être une date valide %s";

    /**
     * DateFormatValidation constructor.
     * @param string $format
     * @param string|null $error
     */
    public function __construct(string $format = 'Y-m-d H:i:s', string $error = null)
    {
        $this->setFormat($format);
        if (!is_null($error)) {
            $this->error = $error;
        }
    }

    /**
     * @param mixed $var
     * @return bool
     */
    public function isValid(mixed $var): bool
    {
        return $this->checkDate($var);
    }

    /**
     * @param string $param
     * @return $this
     */
    public function parseParams(string $param): self
    {
        list($format, $message) = array_pad(explode(',', $param), 2, '');
        if (!empty($message)) {
            $this->error = $message;
        }
        if (!empty($format)) {
            $this->setFormat($format);
        }
        return $this;
    }

    public function getParams(): array
    {
        return [$this->format];
    }

    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param $format
     * @return $this
     */
    public function setFormat($format): self
    {
        if (is_string($format)) {
            $this->format = $format;
        }
        return $this;
    }

    /**
     * @param $var
     * @return bool
     */
    protected function checkDate($var): bool
    {
        $datetime = DateTime::createFromFormat($this->format, $var);
        $errors = DateTime::getLastErrors();
        if (
			is_array($errors) &&
			($errors['error_count'] > 0 || $errors['warning_count'] > 0) ||
			$datetime === false
        ) {
            return false;
        }
        return true;
    }
}
