<?php

namespace MoovMoney;

/**
 * Configuration pour le SDK Moov Money
 */
class Config
{
    /** @var string URL de base de l'API */
    private string $apiUrl;
    
    /** @var string Nom d'utilisateur pour l'authentification */
    private string $username;
    
    /** @var string Mot de passe pour l'authentification */
    private string $password;
    
    /** @var bool Mode test */
    private bool $testMode;
    
    /** @var int Timeout des requêtes en secondes */
    private int $timeout;
    
    /** @var bool Vérification SSL */
    private bool $verifySsl;

    /**
     * @param string $apiUrl URL de base de l'API
     * @param string $username Nom d'utilisateur pour l'authentification
     * @param string $password Mot de passe pour l'authentification
     * @param bool $testMode Mode test
     * @param int $timeout Timeout des requêtes en secondes
     * @param bool $verifySsl Vérification SSL
     */
    public function __construct(
        string $apiUrl, 
        string $username, 
        string $password, 
        bool $testMode = false,
        int $timeout = 30,
        bool $verifySsl = true
    ) {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->username = $username;
        $this->password = $password;
        $this->testMode = $testMode;
        $this->timeout = $timeout;
        $this->verifySsl = $verifySsl;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return bool
     */
    public function isVerifySsl(): bool
    {
        return $this->verifySsl;
    }
    
    /**
     * Informations d'authentification Basic au format base64
     * 
     * @return string
     */
    public function getBasicAuthToken(): string
    {
        return base64_encode($this->username . ':' . $this->password);
    }
}