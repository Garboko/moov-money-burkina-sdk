<?php

namespace MoovMoney\Tests\Resources;

use Mockery;
use PHPUnit\Framework\TestCase;
use MoovMoney\HttpClient\HttpClientInterface;
use MoovMoney\Resources\OtpPayment;
use MoovMoney\Exceptions\ValidationException;

class OtpPaymentTest extends TestCase
{
    private $httpClient;
    private $otpPayment;

    protected function setUp(): void
    {
        $this->httpClient = Mockery::mock(HttpClientInterface::class);
        $this->otpPayment = new OtpPayment($this->httpClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateOtpPayment(): void
    {
        $expectedResponse = [
            'request-id' => 'OTPMerchantPayment-123456789',
            'trans-id' => 'OMROR241120.1439.J00022',
            'status' => '0',
            'message' => 'OTP généré avec succès, le SMS sera envoyé au numéro de client'
        ];

        $this->httpClient->shouldReceive('post')
            ->once()
            ->andReturnUsing(function($method, $data) use ($expectedResponse) {
                $this->assertEquals('process-create-mror-otp', $method);
                $this->assertEquals('22662356789', $data['destination']);
                $this->assertEquals(1000, $data['amount']);
                $this->assertEquals('Test OTP', $data['remarks']);
                $this->assertArrayHasKey('request-id', $data);
                $this->assertArrayHasKey('extended-data', $data);
                $this->assertEquals('MERCHOTPPAY', $data['extended-data']['module']);
                
                return $expectedResponse;
            });

        $response = $this->otpPayment->create(
            '22662356789',
            1000,
            'Test OTP'
        );

        $this->assertEquals($expectedResponse, $response);
    }

    public function testValidateOtp(): void
    {
        $expectedResponse = [
            'request-id' => 'Commit-OTP-123456789',
            'trans-id' => 'OMRCH241120.1441.J00020',
            'status' => '0',
            'message' => 'Commit Success'
        ];

        $this->httpClient->shouldReceive('post')
            ->once()
            ->andReturnUsing(function($method, $data) use ($expectedResponse) {
                $this->assertEquals('process-commit-otppay', $method);
                $this->assertEquals('22662356789', $data['destination']);
                $this->assertEquals('1000', $data['amount']);
                $this->assertEquals('Validation OTP', $data['remarks']);
                $this->assertArrayHasKey('request-id', $data);
                $this->assertArrayHasKey('extended-data', $data);
                $this->assertEquals('123456', $data['extended-data']['otp']);
                $this->assertEquals('OMROR241120.1439.J00022', $data['extended-data']['trans-id']);
                
                return $expectedResponse;
            });

        $response = $this->otpPayment->validate(
            '22662356789',
            1000,
            '123456',
            'OMROR241120.1439.J00022',
            'Validation OTP'
        );

        $this->assertEquals($expectedResponse, $response);
    }

    public function testValidationErrorOnInvalidPhoneNumber(): void
    {
        $this->expectException(ValidationException::class);
        $this->otpPayment->create('123', 1000, 'Test OTP');
    }

    public function testValidationErrorOnInvalidAmount(): void
    {
        $this->expectException(ValidationException::class);
        $this->otpPayment->create('22662356789', -100, 'Test OTP');
    }

    public function testValidationErrorOnInvalidOtp(): void
    {
        $this->expectException(ValidationException::class);
        $this->otpPayment->validate(
            '22662356789',
            1000,
            '123',
            'OMROR241120.1439.J00022',
            'Validation OTP'
        );
    }
}