<?php

declare(strict_types=1);

namespace PgFramework\Validator;

use PgFramework\Validator\Filter\TrimFilter;
use PgFramework\Validator\Rules\ConfirmValidation;
use PgFramework\Validator\Rules\MaxValidation;
use PgFramework\Validator\Rules\MinValidation;
use PgFramework\Validator\Rules\SlugValidation;
use PgFramework\Validator\Rules\EmailValidation;
use PgFramework\Validator\Rules\RangeValidation;
use PgFramework\Validator\Filter\StriptagsFilter;
use PgFramework\Validator\Rules\ExistsValidation;
use PgFramework\Validator\Rules\UniqueValidation;
use PgFramework\Validator\Rules\NotEmptyValidation;
use PgFramework\Validator\Rules\RequiredValidation;
use PgFramework\Validator\Rules\UploadedValidation;
use PgFramework\Validator\Rules\ExtensionValidation;
use PgFramework\Validator\Rules\DateFormatValidation;

class ValidationsRules
{
    public static array $validations = [
        'required' => RequiredValidation::class,
        'min' => MinValidation::class,
        'max' => MaxValidation::class,
        'date' => DateFormatValidation::class,
        'email' => EmailValidation::class,
        'confirm' => ConfirmValidation::class,
        'notEmpty' => NotEmptyValidation::class,
        'range' => RangeValidation::class,
        'filetype' => ExtensionValidation::class,
        'uploaded' => UploadedValidation::class,
        'slug' => SlugValidation::class,
        'exists' => ExistsValidation::class,
        'unique' => UniqueValidation::class
    ];

    public static array $filters = [
        'trim' => TrimFilter::class,
        'striptags' => StriptagsFilter::class
    ];

    public static function getValidations(): array
    {
        return static::$validations;
    }

    public static function getFilters(): array
    {
        return static::$filters;
    }
}
