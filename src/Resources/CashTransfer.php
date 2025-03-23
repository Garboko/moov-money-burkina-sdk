<?php

namespace MoovMoney\Resources;

use MoovMoney\Exceptions\ValidationException;

/**
 * Gestion des transferts d'argent
 */
class CashTransfer extends AbstractResource
{
    /**
     * Effectue un transfert d'argent
     * 
     * @param string $msisdn Numéro de téléphone du destinataire
     * @param float $amount Montant
     * @param string $remarks Remarques
     * @param array $extendedData Données supplémentaires
     * @param string|null $requestId ID de requête personnalisé
     * 
     * @return array Réponse de l'API
     * @throws ValidationException Si les paramètres sont invalides
     */
    public function transfer(
        string $msisdn,
        float $amount,
        string $remarks,
        array $extendedData = [],
        ?string $requestId = null
    ): array {
        // Validation
        $this->validator->validatePhoneNumber($msisdn);
        $this->validator->validateAmount($amount);
        
        // Données de la requête
        $data = [
            'request-id' => $requestId ?? $this->generateRequestId('CashTransfer-'),
            'destination' => $msisdn,
            'amount' => $amount,
            'remarks' => $remarks,
        ];
        
        // Ajoute les données étendues
        if (!empty($extendedData)) {
            $data['extended-data'] = $extendedData;
        }
        
        return $this->httpClient->post('transfer-api-transaction', $data);
    }
    
    /**
     * Effectue un transfert transfrontalier
     * 
     * @param string $msisdn Numéro de téléphone du destinataire
     * @param float $amount Montant
     * @param string $remarks Remarques
     * @param array $extendedData Données supplémentaires
     * @param string|null $requestId ID de requête personnalisé
     * 
     * @return array Réponse de l'API
     * @throws ValidationException Si les paramètres sont invalides
     */
    public function crossBorderTransfer(
        string $msisdn,
        float $amount,
        string $remarks,
        array $extendedData = [],
        ?string $requestId = null
    ): array {
        // Validation
        $this->validator->validatePhoneNumber($msisdn);
        $this->validator->validateAmount($amount);
        
        // Données de la requête
        $data = [
            'request-id' => $requestId ?? $this->generateRequestId('XCashTransfer-'),
            'destination' => $msisdn,
            'amount' => $amount,
            'remarks' => $remarks,
        ];
        
        // Ajoute les données étendues
        if (!empty($extendedData)) {
            $data['extended-data'] = $extendedData;
        }
        
        return $this->httpClient->post('xcash-api-transaction', $data);
    }
}