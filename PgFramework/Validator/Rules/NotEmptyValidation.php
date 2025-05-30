<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use PgFramework\Validator\ValidationInterface;

class NotEmptyValidation implements ValidationInterface
{
    protected string $error = "Le champ %s ne peut-être vide";

    public function __construct(string $error = null)
    {
        if ($error !== null) {
            $this->error = $error;
        }
    }

    public function isValid(mixed $var): bool
    {
        return $this->isNotEmpty($var);
    }

    /**
     *
     * Check if POST variable is set
     *
     * @access protected
     *
     * @param mixed $var The POST variable to check
     *
     */
    protected function isNotEmpty(mixed $var): bool
    {
        if (!isset($var)) {
            $check = false;
        } elseif (is_array($var)) {
            $check = !empty($var);
        } elseif (is_string($var)) { // une chaine constituée que d’espaces est considérée comme vide
            $check = (bool) strlen(trim($var));
        } else {
            $check = !empty($var); // autre type de variable
        }

        return $check;
    }

    public function parseParams($param): self
    {
        if (is_string($param)) {
            list(, $message) = array_pad(explode(',', $param), 2, '');
            if (!empty($message)) {
                $this->error = $message;
            }
        }
        return $this;
    }

    public function getParams(): array
    {
        return [];
    }

    /**
     *
     *
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}
