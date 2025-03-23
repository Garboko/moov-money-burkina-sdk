<?php

namespace MoovMoney\Tests\Resources;

use Mockery;
use PHPUnit\Framework\TestCase;
use MoovMoney\HttpClient\HttpClientInterface;
use MoovMoney\Resources\Subscriber;
use MoovMoney\Exceptions\ValidationException;

class SubscriberTest extends TestCase
{
    private $httpClient;
    private $subscriber;

    protected function setUp(): void
    {
        $this->httpClient = Mockery::mock(HttpClientInterface::class);
        $this->subscriber = new Subscriber($this->httpClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCheckStatus(): void
    {
        $expectedResponse = [
            'request-id' => 'MobileAccountStatus-123456789',
            'trans-id' => 'CHKSUB220107.0453.J00004',
            'status' => '0',
            'message' => 'OK',
            'extended-data' => [
                'data' => [
                    'subscriber-details' => '{"msisdn":"22662356789","status":"ACTIVE","firstname":"TEST"}'
                ]
            ]
        ];

        $this->httpClient->shouldReceive('post')
            ->once()
            ->andReturnUsing(function($method, $data) use ($expectedResponse) {
                $this->assertEquals('process-check-subscriber', $method);
                $this->assertEquals('22662356789', $data['destination']);
                $this->assertArrayHasKey('request-id', $data);
                
                return $expectedResponse;
            });

        $response = $this->subscriber->checkStatus('22662356789');

        $this->assertEquals($expectedResponse, $response);
    }

    public function testRegister(): void
    {
        $subscriberData = [
            'msisdn' => '22662356789',
            'lastname' => 'Doe',
            'firstname' => 'John',
            'idnumber' => '123456789',
            'iddescription' => 'CARTE DE SEJOUR',
            'gender' => 'HOMME',
            'dateofbirth' => '01011990',
            'placeofbirth' => 'Paris',
            'city' => 'Paris'
        ];

        $expectedResponse = [
            'request-id' => 'SubscriberReg-123456789',
            'trans-id' => 'WREG220107.0459.J00096',
            'status' => '0',
            'message' => 'OK',
            'extended-data' => [
                'trans-id' => 'WREG220107.0459.J00096',
                'request-id' => 'SubscriberReg-123456789'
            ]
        ];

        $this->httpClient->shouldReceive('post')
            ->once()
            ->andReturnUsing(function($method, $data) use ($expectedResponse, $subscriberData) {
                $this->assertEquals('subscriber-registration', $method);
                $this->assertArrayHasKey('request-id', $data);
                $this->assertArrayHasKey('extended-data', $data);
                $this->assertEquals($subscriberData, $data['extended-data']);
                
                return $expectedResponse;
            });

        $response = $this->subscriber->register($subscriberData);

        $this->assertEquals($expectedResponse, $response);
    }

    public function testValidationErrorOnInvalidPhoneNumber(): void
    {
        $this->expectException(ValidationException::class);
        $this->subscriber->checkStatus('123');
    }

    public function testValidationErrorOnMissingRequiredFields(): void
    {
        $this->expectException(ValidationException::class);
        
        $incompleteData = [
            'msisdn' => '22662356789',
            'lastname' => 'Doe',
        ];
        
        $this->subscriber->register($incompleteData);
    }

    public function testValidationErrorOnInvalidGender(): void
    {
        $this->expectException(ValidationException::class);
        
        $data = [
            'msisdn' => '22662356789',
            'lastname' => 'Doe',
            'firstname' => 'John',
            'idnumber' => '123456789',
            'iddescription' => 'CARTE DE SEJOUR',
            'gender' => 'INVALID', // Invalid gender
            'dateofbirth' => '01011990',
            'placeofbirth' => 'Paris',
            'city' => 'Paris'
        ];
        
        $this->subscriber->register($data);
    }

    public function testValidationErrorOnInvalidDateFormat(): void
    {
        $this->expectException(ValidationException::class);
        
        $data = [
            'msisdn' => '22662356789',
            'lastname' => 'Doe',
            'firstname' => 'John',
            'idnumber' => '123456789',
            'iddescription' => 'CARTE DE SEJOUR',
            'gender' => 'HOMME',
            'dateofbirth' => '1990-01-01', // Invalid format
            'placeofbirth' => 'Paris',
            'city' => 'Paris'
        ];
        
        $this->subscriber->register($data);
    }
}