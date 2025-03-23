<?php

namespace MoovMoney\Tests\Resources;

use Mockery;
use PHPUnit\Framework\TestCase;
use MoovMoney\HttpClient\HttpClientInterface;
use MoovMoney\Resources\Transaction;
use MoovMoney\Exceptions\ValidationException;

class TransactionTest extends TestCase
{
    private $httpClient;
    private $transaction;

    protected function setUp(): void
    {
        $this->httpClient = Mockery::mock(HttpClientInterface::class);
        $this->transaction = new Transaction($this->httpClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCheckStatus(): void
    {
        $expectedResponse = [
            'request-id' => 'TESTACCOUNT-620200521000000123',
            'trans-id' => 'CHKTRANS220107.0454.J00001',
            'status' => '0',
            'message' => 'OK',
            'extended-data' => [
                'data' => [
                    'reference-id' => 'WMRCH220107.0448.J00000'
                ]
            ]
        ];

        $this->httpClient->shouldReceive('post')
            ->once()
            ->with('process-check-mror-transaction', ['request-id' => 'TESTACCOUNT-620200521000000123'])
            ->andReturn($expectedResponse);

        $response = $this->transaction->checkStatus('TESTACCOUNT-620200521000000123');

        $this->assertEquals($expectedResponse, $response);
    }

    public function testInitiateUssdPayment(): void
    {
        $expectedResponse = [
            'request-id' => 'USSDPayment-123456789',
            'trans-id' => '125020200525BC3946BA',
            'status' => '0',
            'statusdescription' => 'SUCCESS'
        ];

        $this->httpClient->shouldReceive('post')
            ->once()
            ->andReturnUsing(function($method, $data) use ($expectedResponse) {
                $this->assertEquals('mror-transaction-ussd', $method);
                $this->assertEquals('22662356789', $data['destination']);
                $this->assertEquals(1000, $data['amount']);
                $this->assertEquals('PAYMENT OF 1000 TO TEST MERCHANT', $data['message']);
                $this->assertEquals('Test USSD Payment', $data['remarks']);
                $this->assertArrayHasKey('request-id', $data);
                
                return $expectedResponse;
            });

        $response = $this->transaction->initiateUssdPayment(
            '22662356789',
            1000,
            'PAYMENT OF 1000 TO TEST MERCHANT',
            'Test USSD Payment'
        );

        $this->assertEquals($expectedResponse, $response);
    }

    public function testSetupAutoDebit(): void
    {
        $expectedResponse = [
            'request-id' => 'AutoDebit-123456789',
            'status' => '0',
            'message' => 'SUCCESS'
        ];

        $this->httpClient->shouldReceive('post')
            ->once()
            ->andReturnUsing(function($method, $data) use ($expectedResponse) {
                $this->assertEquals('auto-debit-async', $method);
                $this->assertEquals('22662356789', $data['destination']);
                $this->assertEquals(1000, $data['amount']);
                $this->assertEquals('Test Auto Debit', $data['remarks']);
                $this->assertArrayHasKey('request-id', $data);
                $this->assertArrayHasKey('extended-data', $data);
                $this->assertEquals('DEBIT123', $data['extended-data']['trans-id']);
                $this->assertEquals(1, $data['extended-data']['priority']);
                
                return $expectedResponse;
            });

        $response = $this->transaction->setupAutoDebit(
            '22662356789',
            1000,
            'Test Auto Debit',
            'DEBIT123',
            1
        );

        $this->assertEquals($expectedResponse, $response);
    }

    public function testValidationErrorOnInvalidPhoneNumberForUssdPayment(): void
    {
        $this->expectException(ValidationException::class);
        $this->transaction->initiateUssdPayment('123', 1000, 'Test Message', 'Test Remarks');
    }

    public function testValidationErrorOnInvalidAmountForUssdPayment(): void
    {
        $this->expectException(ValidationException::class);
        $this->transaction->initiateUssdPayment('22662356789', -100, 'Test Message', 'Test Remarks');
    }
}