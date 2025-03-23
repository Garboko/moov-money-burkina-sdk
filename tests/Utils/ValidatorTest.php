<?php

namespace MoovMoney\Tests\Utils;

use PHPUnit\Framework\TestCase;
use MoovMoney\Utils\Validator;
use MoovMoney\Exceptions\ValidationException;

class ValidatorTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testValidatePhoneNumberWithValidNumbers(): void
    {
        $this->validator->validatePhoneNumber('22662356789'); // Standard
        $this->validator->validatePhoneNumber('12345678'); // Minimum
        $this->validator->validatePhoneNumber('123456789012'); // Maximum
        $this->validator->validatePhoneNumber('+22662356789'); // Avec préfixe +
        
        $this->assertTrue(true);
    }

    public function testValidatePhoneNumberWithInvalidNumbers(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validatePhoneNumber('123'); 
    }

    public function testValidatePhoneNumberWithNonNumeric(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validatePhoneNumber('abcdefghij');
    }

    public function testValidatePhoneNumberWithInvalidPrefix(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validatePhoneNumber('*22662356789');
    }

    public function testValidatePhoneNumberWithTooLong(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validatePhoneNumber('1234567890123');
    }

    public function testValidateAmountWithValidAmount(): void
    {
        // Ces tests ne devraient pas lever d'exception
        $this->validator->validateAmount(100);
        $this->validator->validateAmount(0.5);
        $this->validator->validateAmount(1000000);
        
        $this->assertTrue(true);
    }

    public function testValidateAmountWithZero(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validateAmount(0);
    }

    public function testValidateAmountWithNegative(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validateAmount(-100);
    }

    public function testValidateOtpWithValidOtp(): void
    {
        // Ces tests ne devraient pas lever d'exception
        $this->validator->validateOtp('123456');
        
        $this->assertTrue(true);
    }

    public function testValidateOtpWithTooShort(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validateOtp('12345');
    }

    public function testValidateOtpWithTooLong(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validateOtp('1234567');
    }

    public function testValidateOtpWithNonNumeric(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validateOtp('abcdef');
    }

    public function testValidateRequestIdWithValidId(): void
    {
        $this->validator->validateRequestId('abcde');
        $this->validator->validateRequestId('MobileAccountStatus-0000001120202401');
        $this->validator->validateRequestId(str_repeat('a', 50));
        
        $this->assertTrue(true);
    }

    public function testValidateRequestIdWithTooShort(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validateRequestId('abcd');
    }

    public function testValidateRequestIdWithTooLong(): void
    {
        $this->expectException(ValidationException::class);
        $this->validator->validateRequestId(str_repeat('a', 51));
    }
}