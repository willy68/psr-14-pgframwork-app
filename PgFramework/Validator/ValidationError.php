<?php

declare(strict_types=1);

namespace PgFramework\Validator;

class ValidationError
{
    private string $key;
    private string $rule;

    private array $messages = [
        'required' => "Le champ %s est requis",
        'empty' => "Le champ %s ne peut-être vide",
        'slug' => "Le champ %s n\'est pas un slug valide",
        'minLength' => "Le champ %s doit contenir plus de %d caractères",
        'maxLength' => "Le champ %s doit contenir moins de %d caractères",
        'betweenLength' => "Le champ %s doit contenir entre %d et %d caractères",
        'datetime' => "Le champ %s doit être une date valide %s",
        'exists' => "Le champ %s n'existe pas dans la table %s",
        'unique' => "Le champ %s doit être unique",
        'filetype' => "Le champ %s n'est pas au format valide (%s)",
        'uploaded' => "Vous devez uploaded un fichier",
        'email' => "Le champ %s doit être une adresse E-mail valide",
        'confirm' => "Le champ %s doit être identique avec le champ %s",
    ];

    private array $attributes;

    /**
     * ValidationError constructor.
     * @param string $key
     * @param string $rule
     * @param array $attributes
     */
    public function __construct(string $key, string $rule, array $attributes = [])
    {
        $this->key = $key;
        $this->rule = $rule;
        $this->attributes = $attributes;
    }

    /**
     * Add error message for rule
     * if rule exists, error message will be replaced
     *
     * @param string $rule
     * @param string $error
     * @return self
     */
    public function addErrorMsg(string $rule, string $error): self
    {
        $this->messages[$rule] = $error;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!array_key_exists($this->rule, $this->messages)) {
            return "Le champs $this->key ne correspond pas à la règle $this->rule";
        } else {
            $params = array_merge([$this->messages[$this->rule], $this->key], $this->attributes);
            return (string)call_user_func_array('sprintf', $params);
        }
    }
}
