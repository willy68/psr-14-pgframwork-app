<?php

declare(strict_types=1);

namespace PgFramework\Validator;

use PgFramework\AbstractApplication;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * New ValidationRules( 'auteur', 'required|max:50|min:3|filter:trim');
 *
 * Valide un champ de formulaire avec plusieurs règles
 *
 * Dépend de App et ContainerInterface
 */
class ValidationRules
{
    /**
     * @var ValidationError[]
     */
    protected array $errors = [];

    /**
     * @var ValidationInterface[]
     */
    protected array $validationRules = [];

    /**
     * Filter rules
     */
    protected array $filterRules = [];

    /**
     * Request Parsed Body
     */
    protected array $params;

    /**
     * FieldName
     */
    protected string $fieldName = '';

    /**
     * ValidationRules constructor.
     *
     * @param string $fieldName
     * @param string $rules
     * @param array $params
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
        if (!empty($fieldName)) {
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
        if (empty($rules)) {
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
     * @param bool $excludeParams
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
     * @param mixed $var
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isValid(mixed $var): bool
    {
        $container = AbstractApplication::getApp()->getContainer();
        $validations = $container->get('form.validations');
        $filters = $container->get('form.filters');

        foreach ($this->filterRules as $filter => $param) {
            if (array_key_exists($filter, $filters)) {
                $filter = $container->get($filters[$filter]);
            } else {
                continue;
            }
            /** @var FilterInterface $filter */
            $var = $filter->filter($var);
        }

        /** @var string $param */
        foreach ($this->validationRules as $rule => $param) {
            if (array_key_exists($rule, $validations)) {
                $validation = $container->get($validations[$rule]);
            } else {
                continue;
            }

            /** @var ValidationInterface $validation*/
            $validation->parseParams($param);

            if ($validation instanceof ValidationExtraParamsInterface) {
                $validation->setBodyParams($this->params);
            }

            if (!$validation->isValid($var)) {
                $this->addError(
                    $this->fieldName,
                    $rule,
                    $validation->getParams(),
                    $validation->getError()
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Get all errors
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
     * Add error
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
