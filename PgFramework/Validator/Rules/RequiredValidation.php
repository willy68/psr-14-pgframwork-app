<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use PgFramework\Validator\ValidationInterface;

class RequiredValidation implements ValidationInterface
{
    protected string $error = 'Le champ %s est obligatoire';

    public function __construct($error = '')
    {
        if (!empty($error)) {
            $this->error = $error;
        }
    }

    public function isValid(mixed $var): bool
    {
        return $this->isSet($var);
    }

    public function parseParams($param): self
    {
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

    /**
     *
     * @check if POST variable is set
     *
     * @access protected
     *
     * @param mixed $var The POST variable to check
     * @return bool
     */
    protected function isSet(mixed $var): bool
    {
        if (!isset($var)) {
            $check = false;
        } elseif (is_array($var)) {
            $check = !empty($var);
        } elseif (is_string($var)) { // une chaine constituée que d'espaces est considérée comme vide!
            $check = (bool) strlen(trim($var));
        } else {
            $check = !empty($var); // autre type de variable
        }
        return $check;
    }
}
