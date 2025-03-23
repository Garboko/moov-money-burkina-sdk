<?php

namespace MoovMoney;

use MoovMoney\HttpClient\GuzzleHttpClient;
use MoovMoney\HttpClient\HttpClientInterface;
use MoovMoney\Resources\OtpPayment;
use MoovMoney\Resources\Subscriber;
use MoovMoney\Resources\Transaction;
use MoovMoney\Resources\CashTransfer;

/**
 * Client principal pour l'API Moov Money
 */
class Client
{
    /** @var Config Configuration du client */
    private Config $config;
    
    /** @var HttpClientInterface Client HTTP */
    private HttpClientInterface $httpClient;
    
    /** @var array Classes de ressources */
    private array $resources = [];

    /**
     * @param Config $config Configuration du client
     * @param HttpClientInterface|null $httpClient Client HTTP (optionnel)
     */
    public function __construct(Config $config, ?HttpClientInterface $httpClient = null)
    {
        $this->config = $config;
        $this->httpClient = $httpClient ?? new GuzzleHttpClient($config);
    }

    /**
     * Accès aux ressources d'abonnés
     * 
     * @return Subscriber
     */
    public function subscribers(): Subscriber
    {
        if (!isset($this->resources['subscriber'])) {
            $this->resources['subscriber'] = new Subscriber($this->httpClient);
        }
        
        return $this->resources['subscriber'];
    }

    /**
     * Accès aux ressources de transactions
     * 
     * @return Transaction
     */
    public function transactions(): Transaction
    {
        if (!isset($this->resources['transaction'])) {
            $this->resources['transaction'] = new Transaction($this->httpClient);
        }
        
        return $this->resources['transaction'];
    }

    /**
     * Accès aux ressources de paiements OTP
     * 
     * @return OtpPayment
     */
    public function otpPayments(): OtpPayment
    {
        if (!isset($this->resources['otpPayment'])) {
            $this->resources['otpPayment'] = new OtpPayment($this->httpClient);
        }
        
        return $this->resources['otpPayment'];
    }

    /**
     * Accès aux ressources de transferts d'argent
     * 
     * @return CashTransfer
     */
    public function cashTransfers(): CashTransfer
    {
        if (!isset($this->resources['cashTransfer'])) {
            $this->resources['cashTransfer'] = new CashTransfer($this->httpClient);
        }
        
        return $this->resources['cashTransfer'];
    }

    /**
     * Configuration
     * 
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Obtenir le client HTTP
     * 
     * @return HttpClientInterface
     */
    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }
}