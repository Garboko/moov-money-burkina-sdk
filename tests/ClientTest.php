<?php

namespace MoovMoney\Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use MoovMoney\Client;
use MoovMoney\Config;
use MoovMoney\HttpClient\HttpClientInterface;
use MoovMoney\Resources\OtpPayment;
use MoovMoney\Resources\Transaction;
use MoovMoney\Resources\Subscriber;
use MoovMoney\Resources\CashTransfer;

class ClientTest extends TestCase
{
    private $config;
    private $httpClient;
    private $client;

    protected function setUp(): void
    {
        $this->config = new Config(
            'https://test-api.com',
            'test-username',
            'test-password'
        );
        
        $this->httpClient = Mockery::mock(HttpClientInterface::class);
        $this->client = new Client($this->config, $this->httpClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetConfig(): void
    {
        $this->assertSame($this->config, $this->client->getConfig());
    }

    public function testGetHttpClient(): void
    {
        $this->assertSame($this->httpClient, $this->client->getHttpClient());
    }

    public function testSubscribersReturnsSameInstance(): void
    {
        $subscribers1 = $this->client->subscribers();
        $subscribers2 = $this->client->subscribers();
        
        $this->assertInstanceOf(Subscriber::class, $subscribers1);
        $this->assertSame($subscribers1, $subscribers2);
    }

    public function testTransactionsReturnsSameInstance(): void
    {
        $transactions1 = $this->client->transactions();
        $transactions2 = $this->client->transactions();
        
        $this->assertInstanceOf(Transaction::class, $transactions1);
        $this->assertSame($transactions1, $transactions2);
    }

    public function testOtpPaymentsReturnsSameInstance(): void
    {
        $otpPayments1 = $this->client->otpPayments();
        $otpPayments2 = $this->client->otpPayments();
        
        $this->assertInstanceOf(OtpPayment::class, $otpPayments1);
        $this->assertSame($otpPayments1, $otpPayments2);
    }

    public function testCashTransfersReturnsSameInstance(): void
    {
        $cashTransfers1 = $this->client->cashTransfers();
        $cashTransfers2 = $this->client->cashTransfers();
        
        $this->assertInstanceOf(CashTransfer::class, $cashTransfers1);
        $this->assertSame($cashTransfers1, $cashTransfers2);
    }
}