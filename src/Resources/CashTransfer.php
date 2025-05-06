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
        ?string $requestId = null,
        string $sender,
        int $auth 
    ): array {
        $this->validator->validatePhoneNumber($msisdn);
        $this->validator->validateAmount($amount);
        
        $data = [
            'request-id' => $requestId ?? $this->generateRequestId('CashTransfer-'),
            "command-id" => "WCASH",
            "sender" => $sender,
            'destination' => $msisdn,
            'auth' => $auth,
            'amount' => $amount,
            'remarks' => $remarks,
        ];
        
        if (!empty($extendedData)) {
            $data['extended-data'] = $extendedData;
        }else{
            $data['extended-data'] = [
                'custommessge' => 'Transfert d\'argent',
                'ext2' => 'Transfert d\'argent',
            ];
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
        ?string $requestId = null,
        string $sender,
        int $auth 
    ): array {
        $this->validator->validatePhoneNumber($msisdn);
        $this->validator->validateAmount($amount);
        
        $data = [
            'request-id' => $requestId ?? $this->generateRequestId('XCashTransfer-'),
            "command-id" => "WCASH",
            "sender" => $sender,
            'destination' => $msisdn,
            'auth' => $auth,
            'amount' => $amount,
            'remarks' => $remarks,
        ];
        
        if (!empty($extendedData)) {
            $data['extended-data'] = $extendedData;
        }else{
            $data['extended-data'] = [
                'custommessge' => 'Transfert d\'argent',
                'ext2' => 'Transfert d\'argent',
            ];
        }
        
        return $this->httpClient->post('xcash-api-transaction', $data);
    }
}