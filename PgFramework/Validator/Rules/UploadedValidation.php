<?php

declare(strict_types=1);

namespace PgFramework\Validator\Rules;

use Psr\Http\Message\UploadedFileInterface;
use PgFramework\Validator\ValidationInterface;

class UploadedValidation implements ValidationInterface
{
    protected string $error = "Le champ %s n'est pas au format valide (%s)";

    public function __construct(string $error = null)
    {
        if (!is_null($error)) {
            $this->error = $error;
        }
    }

    public function parseParams(string $param): self
    {
        $this->error = $param;
        return $this;
    }

    public function getParams(): array
    {
        return [];
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function isValid(mixed $var): bool
    {
        /** @var UploadedFileInterface $var */
        if ($var === null || $var->getError() !== UPLOAD_ERR_OK) {
            return false;
        }
        return true;
    }
}
