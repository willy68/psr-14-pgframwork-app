<?php

declare(strict_types=1);

namespace PgFramework\Validator;

use PgFramework\AbstractApplication;

/**
 * new ValidationRules( 'auteur', 'required|max:50|min:3|filter:trim');
 *
 * Valide un champs de formulaire avec plusieurs règles
 *
 * Dépend de App et ContainerInterface
 */
class ValidationRules
{
    /**
     *
     *
     * @var ValidationError[]
     */
    protected array $errors = [];

    /**
     *
     *
     * @var ValidationInterface[]
     */
    protected array $validationRules = [];

    /**
     * Filter rules
     *
     * @var array
     */
    protected array $filterRules = [];

    /**
     * Request Parsed Body
     *
     * @var array
     */
    protected array $params;

    /**
     * FieldName
     *
     * @var string
     */
    protected string $fieldName = '';

    /**
     * ValidationRules constructor.
     * @param string $fieldName
     * @param string $rules
     */
    public function __construct(string $fieldName = '', string $rules = '', array $params = [])
    {
        $this->setFieldName($fieldName);
        $this->setRules($rules);
        $this->params = $params;
    }

    /**
     * Set fieldName
     *
     * @param string $fieldName
     * @return self
     */
    public function setFieldName(string $fieldName): self
    {
        if (is_string($fieldName) && !empty($fieldName)) {
            $this->fieldName = $fieldName;
        }
        return $this;
    }

    /**
     * Parse string rules
     *
     * @param string $rules
     * @return self
     */
    public function setRules(string $rules): self
    {
        if (!is_string($rules) || empty($rules)) {
            return $this;
        }

        $options = explode('|', $rules);

        foreach ($options as $option) {
            list($key, $value) = array_pad(explode(':', $option, 2), 2, '');
            if (strtolower($key) === 'filter') {
                $this->filterRules[$value] = '';
            } else {
                $this->validationRules[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Set Request Parsed Body Params
     *
     * @param array $params
     * @return self
     */
    public function setParams(array $params): self
    {
        if (!empty($params)) {
            $this->params = $params;
        }
        return $this;
    }

    /**
     * Clean object
     *
     * @return self
     */
    public function clean(bool $excludeParams = true): self
    {
        $this->fieldName = '';
        $this->validationRules = [];
        $this->filterRules = [];
        $this->errors = [];
        if ($excludeParams === false) {
            $this->params = [];
        }
        return $this;
    }

    /**
     *
     *
     * @param mixed $var
     * @return bool
     * @throws \Exception
     */
    public function isValid($var): bool
    {
        $valid = true;
        $container = AbstractApplication::getApp()->getContainer();
        $validations = $container->get('form.validations');
        $filters = $container->get('form.filters');

        foreach ($this->filterRules as $filter => $param) {
            if (array_key_exists($filter, $filters)) {
                /** @var FilterInterface $filter*/
                $filter = $container->get($filters[$filter]);
            } else {
                continue;
            }
            $var = $filter->filter($var);
        }

        foreach ($this->validationRules as $rule => $param) {
            if (array_key_exists($rule, $validations)) {
                /** @var ValidationInterface $validation*/
                $validation = $container->get($validations[$rule]);
            } else {
                continue;
            }

            $validation->parseParams((string) $param);

            if ($validation instanceof ValidationExtraParamsInterface) {
                $validation->setBodyParams($this->params);
            }

            if (!$validation->isValid($var)) {
                $valid = false;
                $this->addError(
                    $this->fieldName,
                    $rule,
                    $validation->getParams(),
                    $validation->getError()
                );
            }
        }
        return $valid;
    }

    /**
     * Undocumented function
     *
     * @return ValidationError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * Undocumented function
     *
     * @param string $key
     * @param string $rule
     * @param array $attributes
     * @param string $message
     * @return self
     */
    private function addError(string $key, string $rule, array $attributes = [], string $message = ''): self
    {
        $error = new ValidationError($key, $rule, $attributes);
        if (!empty($message)) {
            $error->addErrorMsg($rule, $message);
        }
        $this->errors[$key] = $error;
        return $this;
    }
}
