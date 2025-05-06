<?php

namespace MoovMoney\Tests\Resources;

use Mockery;
use PHPUnit\Framework\TestCase;
use MoovMoney\HttpClient\HttpClientInterface;
use MoovMoney\Resources\CashTransfer;
use MoovMoney\Exceptions\ValidationException;

class CashTransferTest extends TestCase
{
    private $httpClient;
    private $cashTransfer;

    protected function setUp(): void
    {
        $this->httpClient = Mockery::mock(HttpClientInterface::class);
        $this->cashTransfer = new CashTransfer($this->httpClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testTransfer(): void
    {
        $expectedResponse = [
            'request-id' => 'CashTransfer-123456789',
            'trans-id' => '125020200525BC3946BA',
            'status' => '0',
            'statusdescription' => 'SUCCESS'
        ];

        $this->httpClient->shouldReceive('post')
            ->once()
            ->andReturnUsing(function($method, $data) use ($expectedResponse) {
                $this->assertEquals('transfer-api-transaction', $method);
                $this->assertEquals('22662356789', $data['destination']);
                $this->assertEquals(1000, $data['amount']);
                $this->assertEquals('Test Transfer', $data['remarks']);
                $this->assertArrayHasKey('request-id', $data);
                
                return $expectedResponse;
            });

        $response = $this->cashTransfer->transfer(
            '22662356789',
            1000,
            'Test Transfer',
            [],
            null,
            '22601234561',
            0000
        );

        $this->assertEquals($expectedResponse, $response);
    }

    public function testCrossBorderTransfer(): void
    {
        $expectedResponse = [
            'request-id' => 'XCashTransfer-123456789',
            'trans-id' => '125020200525BC3946BA',
            'status' => '0',
            'statusdescription' => 'SUCCESS',
            'extended-data' => [
                'code' => 'NNNN'
            ]
        ];

        $this->httpClient->shouldReceive('post')
            ->once()
            ->andReturnUsing(function($method, $data) use ($expectedResponse) {
                $this->assertEquals('xcash-api-transaction', $method);
                $this->assertEquals('22662356789', $data['destination']);
                $this->assertEquals(1000, $data['amount']);
                $this->assertEquals('Test Cross Border Transfer', $data['remarks']);
                $this->assertArrayHasKey('request-id', $data);
                $this->assertEquals('WCASH', $data['command-id']);
                $this->assertEquals('22601234561', $data['sender']);
                $this->assertEquals(0000, $data['auth']);
                
                return $expectedResponse;
            });

        $response = $this->cashTransfer->crossBorderTransfer(
            '22662356789',
            1000,
            'Test Cross Border Transfer',
            [],
            null,
            '22601234561',
            0000
        );

        $this->assertEquals($expectedResponse, $response);
    }

    public function testTransferWithExtendedData(): void
    {
        $extendedData = [
            'ext2' => 'CUSTOM STRING',
            'custommessge' => 'Payment for Item XYZ'
        ];

        $expectedResponse = [
            'request-id' => 'CashTransfer-123456789',
            'trans-id' => '125020200525BC3946BA',
            'status' => '0',
            'statusdescription' => 'SUCCESS'
        ];

        $this->httpClient->shouldReceive('post')
            ->once()
            ->andReturnUsing(function($method, $data) use ($expectedResponse, $extendedData) {
                $this->assertEquals('transfer-api-transaction', $method);
                $this->assertEquals('22662356789', $data['destination']);
                $this->assertEquals(1000, $data['amount']);
                $this->assertEquals('Test Transfer', $data['remarks']);
                $this->assertArrayHasKey('request-id', $data);
                $this->assertArrayHasKey('extended-data', $data);
                $this->assertEquals($extendedData, $data['extended-data']);
                
                return $expectedResponse;
            });

        $response = $this->cashTransfer->transfer(
            '22662356789',
            1000,
            'Test Transfer',
            $extendedData,
            null,
            '22601234561',
            0000
        );

        $this->assertEquals($expectedResponse, $response);
    }

    public function testCrossBorderTransferWithExtendedData(): void
    {
        $extendedData = [
            'ext2' => 'CUSTOM STRING',
            'custommessge' => 'Payment for Item XYZ'
        ];

        $expectedResponse = [
            'request-id' => 'XCashTransfer-123456789',
            'trans-id' => '125020200525BC3946BA',
            'status' => '0',
            'statusdescription' => 'SUCCESS'
        ];

        $this->httpClient->shouldReceive('post')
            ->once()
            ->andReturnUsing(function($method, $data) use ($expectedResponse, $extendedData) {
                $this->assertEquals('xcash-api-transaction', $method);
                $this->assertEquals('22662356789', $data['destination']);
                $this->assertEquals(1000, $data['amount']);
                $this->assertEquals('Test Cross Border Transfer', $data['remarks']);
                $this->assertArrayHasKey('request-id', $data);
                $this->assertArrayHasKey('extended-data', $data);
                $this->assertEquals($extendedData, $data['extended-data']);
                
                return $expectedResponse;
            });

        $response = $this->cashTransfer->crossBorderTransfer(
            '22662356789',
            1000,
            'Test Cross Border Transfer',
            $extendedData,
            null,
            '22601234561',
            0000
        );

        $this->assertEquals($expectedResponse, $response);
    }

    public function testValidationErrorOnInvalidPhoneNumber(): void
    {
        $this->expectException(ValidationException::class);
        $this->cashTransfer->transfer('123', 1000, 'Test Transfer', [], null, '22601234561', 0000);
    }

    public function testValidationErrorOnInvalidAmount(): void
    {
        $this->expectException(ValidationException::class);
        $this->cashTransfer->transfer('22662356789', -100, 'Test Transfer', [], null, '22601234561', 0000);
    }

    public function testValidationErrorOnInvalidPhoneNumberForCrossBorder(): void
    {
        $this->expectException(ValidationException::class);
        $this->cashTransfer->crossBorderTransfer('123', 1000, 'Test Cross Border Transfer', [], null, '22601234561', 0000);
    }

    public function testValidationErrorOnInvalidAmountForCrossBorder(): void
    {
        $this->expectException(ValidationException::class);
        $this->cashTransfer->crossBorderTransfer('22662356789', -100, 'Test Cross Border Transfer', [], null, '22601234561', 0000);
    }
}