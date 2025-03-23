<?php

namespace MoovMoney\Resources;

/**
 * Gestion des transactions
 */
class Transaction extends AbstractResource
{
    /**
     * Vérifie le statut d'une transaction
     * 
     * @param string $requestId ID de la requête originale
     * @return array Réponse de l'API
     */
    public function checkStatus(string $requestId): array
    {
        $data = [
            'request-id' => $requestId
        ];
        
        return $this->httpClient->post('process-check-mror-transaction', $data);
    }
    
    /**
     * Initie un paiement par USSD
     * 
     * @param string $msisdn Numéro de téléphone
     * @param float $amount Montant
     * @param string $message Message de notification push
     * @param string $remarks Remarques
     * @param array $extendedData Données supplémentaires
     * @param string|null $requestId ID de requête personnalisé
     * 
     * @return array Réponse de l'API
     */
    public function initiateUssdPayment(
        string $msisdn,
        float $amount,
        string $message,
        string $remarks,
        array $extendedData = [],
        ?string $requestId = null
    ): array {
        // Validation
        $this->validator->validatePhoneNumber($msisdn);
        $this->validator->validateAmount($amount);
        
        // Données de la requête
        $data = [
            'request-id' => $requestId ?? $this->generateRequestId('USSDPayment-'),
            'destination' => $msisdn,
            'amount' => $amount,
            'message' => $message,
            'remarks' => $remarks,
        ];
        
        // Données étendues
        if (!empty($extendedData)) {
            $data['extended-data'] = $extendedData;
        }
        
        return $this->httpClient->post('mror-transaction-ussd', $data);
    }
    
    /**
     * Configure un débit automatique (asynchrone)
     * 
     * @param string $msisdn Numéro de téléphone
     * @param float $amount Montant
     * @param string $remarks Remarques
     * @param string $transId ID de transaction personnalisé
     * @param int $priority Priorité (0 ou 1)
     * @param array $extendedData Données supplémentaires
     * @param string|null $requestId ID de requête personnalisé
     * 
     * @return array Réponse de l'API
     */
    public function setupAutoDebit(
        string $msisdn,
        float $amount,
        string $remarks,
        string $transId,
        int $priority = 0,
        array $extendedData = [],
        ?string $requestId = null
    ): array {
        // Validation
        $this->validator->validatePhoneNumber($msisdn);
        $this->validator->validateAmount($amount);
        
        // Données de base
        $data = [
            'request-id' => $requestId ?? $this->generateRequestId('AutoDebit-'),
            'destination' => $msisdn,
            'amount' => $amount,
            'remarks' => $remarks,
        ];
        
        // Données étendues obligatoires
        $extData = [
            'trans-id' => $transId,
            'priority' => $priority
        ];
        
        // Fusion avec les données étendues supplémentaires
        $data['extended-data'] = array_merge($extData, $extendedData);
        
        return $this->httpClient->post('auto-debit-async', $data);
    }
}