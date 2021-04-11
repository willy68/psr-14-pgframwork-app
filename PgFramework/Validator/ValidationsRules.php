<?php

namespace PgFramework\Validator;

use PgFramework\Validator\Filter\TrimFilter;
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
use PgFramework\Validator\Rules\EmailConfirmValidation;

class ValidationsRules
{
    static public $validations = [
        'required' => RequiredValidation::class,
        'min' => MinValidation::class,
        'max' => MaxValidation::class,
        'date' => DateFormatValidation::class,
        'email' => EmailValidation::class,
        'emailConfirm' => EmailConfirmValidation::class,
        'notEmpty' => NotEmptyValidation::class,
        'range' => RangeValidation::class,
        'filetype' => ExtensionValidation::class,
        'uploaded' => UploadedValidation::class,
        'slug' => SlugValidation::class,
        'exists' => ExistsValidation::class,
        'unique' => UniqueValidation::class
    ];

    static public $filters = [
        'trim' => TrimFilter::class,
        'striptags' => StriptagsFilter::class
    ];

    static public function getValidations()
    {
        return static::$validations;
    }

    static public function getFilters()
    {
        return static::$filters;
    }
}
