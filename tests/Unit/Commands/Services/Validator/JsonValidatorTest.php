<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit\Commands\Services\Validator;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Forecast\Commands\Services\Validator\JsonValidator;

class JsonValidatorTest extends TestCase
{
    // ─── valid inputs ─────────────────────────────────────────────────

    public function testValidateReturnsTrueForValidJsonObject(): void
    {
        self::assertTrue(JsonValidator::validate('{"key":"value"}'));
    }

    public function testValidateReturnsTrueForValidJsonArray(): void
    {
        self::assertTrue(JsonValidator::validate('[{"ds":"2026-01-01","y":10}]'));
    }

    public function testValidateReturnsTrueForEmptyArray(): void
    {
        self::assertTrue(JsonValidator::validate('[]'));
    }

    // ─── invalid inputs ───────────────────────────────────────────────

    public function testValidateReturnsFalseForInvalidJson(): void
    {
        self::assertFalse(JsonValidator::validate('{invalid json}'));
    }

    public function testValidateReturnsFalseForEmptyString(): void
    {
        self::assertFalse(JsonValidator::validate(''));
    }

    // ─── error message output ─────────────────────────────────────────

    public function testValidatePopulatesErrorMessage(): void
    {
        $errorMessage = null;
        JsonValidator::validate('{bad', $errorMessage);

        self::assertNotNull($errorMessage);
        self::assertIsString($errorMessage);
        self::assertNotEmpty($errorMessage);
    }

    public function testValidateDoesNotPopulateErrorMessageOnSuccess(): void
    {
        $errorMessage = 'pre-existing value';
        JsonValidator::validate('{"valid":true}', $errorMessage);

        self::assertSame('pre-existing value', $errorMessage, 'Error message must not be modified on successful validation.');
    }
}
