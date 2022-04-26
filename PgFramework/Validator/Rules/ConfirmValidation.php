<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use PgFramework\Validator\ValidationExtraParamsInterface;
use PgFramework\Validator\ValidationInterface;

class ConfirmValidation implements ValidationInterface, ValidationExtraParamsInterface
{
    protected string $error = 'Le champ %s doit être un E-mail identique avec le champ %s';

    protected string $fieldName;

    protected $params = [];

    /**
     * EmailConfirmValidation constructor.
     * @param ServerRequestInterface $request
     * @param string|null $fieldName
     * @param string|null $error
     */
    public function __construct(
        ?string $fieldName = null,
        ?string $error = null
    ) {
        if ($error !== null) {
            $this->error = $error;
        }
        if ($fieldName !== null) {
            $this->setFieldName($fieldName);
        }
    }

    /**
     * @param mixed $var
     * @return bool
     */
    public function isValid($var): bool
    {
        $confirmValue = $this->getValue($this->fieldName);
        return $confirmValue === $var;
    }

    /**
     * @param string $param
     * @return $this
     */
    public function parseParams($param): self
    {
        if (is_string($param)) {
            list($fieldName, $message) = array_pad(explode(',', $param), 2, '');
            if (!empty($message)) {
                $this->error = $message;
            }
            if (!empty($fieldName)) {
                $this->setFieldName($fieldName);
            }
        }
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getParams(): array
    {
        return [$this->fieldName];
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param string $fieldName
     * @return $this
     */
    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    /**
     * Set Request Parsed Body Params
     *
     * @param array $params
     * @return void
     */
    public function setBodyParams(array $params): void
    {
        if (!empty($params)) {
            $this->params = $params;
        }
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    private function getValue(string $key)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
        return null;
    }
}
