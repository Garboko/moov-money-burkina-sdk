<?php

namespace MoovMoney\Exceptions;

use Exception;

/**
 * Exception liée aux erreurs de l'API
 */
class ApiException extends Exception
{
    /** @var array Données supplémentaires sur l'erreur */
    protected array $errorData;
    
    /** @var string|null ID de transaction associé à l'erreur */
    protected ?string $transactionId;

    /**
     * @param string $message Message d'erreur
     * @param int $code Code d'erreur
     * @param \Throwable|null $previous Exception précédente
     * @param array $errorData Données supplémentaires sur l'erreur
     * @param string|null $transactionId ID de transaction associé
     */
    public function __construct(
        string $message = "", 
        int $code = 0, 
        ?\Throwable $previous = null,
        array $errorData = [],
        ?string $transactionId = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorData = $errorData;
        $this->transactionId = $transactionId;
    }

    /**
     * Récupére les données d'erreur supplémentaires
     * 
     * @return array
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }

    /**
     * Récupére l'ID de transaction associé à l'erreur
     * 
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
    
    /**
     * Crée une exception à partir d'une réponse d'API
     * 
     * @param array $response Réponse de l'API
     * @return self
     */
    public static function fromApiResponse(array $response): self
    {
        $code = isset($response['status']) ? (int)$response['status'] : 0;
        $message = $response['message'] ?? $response['statusdescription'] ?? 'Erreur API inconnue';
        $transId = $response['trans-id'] ?? null;
        
        // Extraire les données supplémentaires
        $errorData = [];
        if (isset($response['extended-data'])) {
            $errorData = $response['extended-data'];
        }
        
        return new self($message, $code, null, $errorData, $transId);
    }
}