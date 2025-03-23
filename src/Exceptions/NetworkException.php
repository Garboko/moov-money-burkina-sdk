<?php

namespace MoovMoney\Exceptions;

/**
 * Exception liée aux erreurs de réseau
 */
class NetworkException extends ApiException
{
    /** @var string|null URL qui a généré l'erreur */
    protected ?string $url;
    
    /** @var string|null Méthode HTTP utilisée */
    protected ?string $method;
    
    /** @var int|null Timeout en secondes */
    protected ?int $timeout;

    /**
     * @param string $message Message d'erreur
     * @param int $code Code d'erreur
     * @param \Throwable|null $previous Exception précédente
     * @param string|null $url URL qui a généré l'erreur
     * @param string|null $method Méthode HTTP utilisée
     * @param int|null $timeout Timeout en secondes
     */
    public function __construct(
        string $message = "", 
        int $code = 0, 
        ?\Throwable $previous = null,
        ?string $url = null,
        ?string $method = null,
        ?int $timeout = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->url = $url;
        $this->method = $method;
        $this->timeout = $timeout;
    }

    /**
     * Récupére l'URL qui a généré l'erreur
     * 
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Récupére la méthode HTTP utilisée
     * 
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Récupére le timeout en secondes
     * 
     * @return int|null
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }
    
    /**
     * Crée une exception pour un timeout
     * 
     * @param string $url URL qui a généré l'erreur
     * @param string $method Méthode HTTP utilisée
     * @param int $timeout Timeout en secondes
     * @return self
     */
    public static function connectionTimeout(string $url, string $method = 'POST', int $timeout = 30): self
    {
        return new self(
            "Délai d'attente dépassé lors de la connexion à $url (timeout: {$timeout}s)",
            408,
            null,
            $url,
            $method,
            $timeout
        );
    }
    
    /**
     * Crée une exception pour une erreur de connexion
     * 
     * @param string $url URL qui a généré l'erreur
     * @param string $method Méthode HTTP utilisée
     * @param string $errorMessage Message d'erreur spécifique
     * @return self
     */
    public static function connectionError(string $url, string $method = 'POST', string $errorMessage = ""): self
    {
        $message = "Erreur de connexion à $url";
        if ($errorMessage) {
            $message .= ": $errorMessage";
        }
        
        return new self(
            $message,
            500,
            null,
            $url,
            $method
        );
    }
    
    /**
     * Crée une exception pour une erreur DNS
     * 
     * @param string $url URL qui a généré l'erreur
     * @return self
     */
    public static function dnsError(string $url): self
    {
        return new self(
            "Impossible de résoudre le nom d'hôte pour $url",
            500,
            null,
            $url
        );
    }
    
    /**
     * Crée une exception pour une erreur SSL
     * 
     * @param string $url URL qui a généré l'erreur
     * @param string $errorMessage Message d'erreur spécifique
     * @return self
     */
    public static function sslError(string $url, string $errorMessage = ""): self
    {
        $message = "Erreur SSL lors de la connexion à $url";
        if ($errorMessage) {
            $message .= ": $errorMessage";
        }
        
        return new self(
            $message,
            525,
            null,
            $url
        );
    }
}