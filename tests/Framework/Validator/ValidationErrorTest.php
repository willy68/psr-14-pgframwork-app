<?php

namespace Tests\Framework\Validator;

use PgFramework\Validator\ValidationError;
use PHPUnit\Framework\TestCase;

class ValidationErrorTest extends TestCase
{
    public function testString()
    {
        $error = new ValidationError('demo', 'fake', ['a1', 'a2']);
        $error->addErrorMsg('fake', 'problem %2$s %3$s');
        $this->assertEquals('problem a1 a2', (string)$error);
    }

    public function testUnknownError()
    {
        $rule = 'fake';
        $field = 'demo';
        $error = new ValidationError($field, $rule, ['a1', 'a2']);
        $this->assertStringContainsString($field, (string)$error);
        $this->assertStringContainsString($rule, (string)$error);
    }
}
