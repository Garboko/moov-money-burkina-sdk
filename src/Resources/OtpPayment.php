<?php

namespace MoovMoney\Resources;

use MoovMoney\Exceptions\ValidationException;

/**
 * Gestion des paiements par OTP
 */
class OtpPayment extends AbstractResource
{
    /**
     * Initie une transaction OTP
     * 
     * @param string $msisdn Numéro de téléphone
     * @param float $amount Montant
     * @param string $remarks Remarques
     * @param array $extendedData Données supplémentaires
     * @param string|null $requestId ID de requête personnalisé
     * 
     * @return array Réponse de l'API
     * @throws ValidationException Si les paramètres sont invalides
     */
    public function create(
        string $msisdn,
        float $amount,
        string $remarks,
        array $extendedData = [],
        ?string $requestId = null
    ): array {
        // Validation
        $this->validator->validatePhoneNumber($msisdn);
        $this->validator->validateAmount($amount);
        
        // Requête
        $data = [
            'request-id' => $requestId ?? $this->generateRequestId('OTPMerchantPayment-'),
            'destination' => $msisdn,
            'amount' => $amount,
            'remarks' => $remarks,
        ];
        
        // Ajoute le module pour l'OTP
        $extendedData['module'] = $extendedData['module'] ?? 'MERCHOTPPAY';
        
        // Ajoute les données étendues
        if (!empty($extendedData)) {
            $data['extended-data'] = $extendedData;
        }
        
        // Envoie de la requête
        return $this->httpClient->post('process-create-mror-otp', $data);
    }
    
    /**
     * Valide un OTP et finalise la transaction
     * 
     * @param string $msisdn Numéro de téléphone
     * @param float $amount Montant
     * @param string $otp Code OTP
     * @param string $transId ID de transaction reçu lors de la création
     * @param string $remarks Remarques
     * @param array $extendedData Données supplémentaires
     * @param string|null $requestId ID de requête personnalisé
     * 
     * @return array Réponse de l'API
     * @throws ValidationException Si les paramètres sont invalides
     */
    public function validate(
        string $msisdn,
        float $amount,
        string $otp,
        string $transId,
        string $remarks,
        array $extendedData = [],
        ?string $requestId = null
    ): array {
        // Validation
        $this->validator->validatePhoneNumber($msisdn);
        $this->validator->validateAmount($amount);
        $this->validator->validateOtp($otp);
        
        // Données de base
        $data = [
            'request-id' => $requestId ?? $this->generateRequestId('Commit-OTP-'),
            'destination' => $msisdn,
            'amount' => (string)$amount,
            'remarks' => $remarks,
        ];
        
        // Données étendues
        $extData = [
            'module' => 'MERCHOTPPAY',
            'otp' => $otp,
            'trans-id' => $transId,
        ];
        
        // Fusion avec les données étendues supplémentaires
        $data['extended-data'] = array_merge($extData, $extendedData);
        
        // Envoie de la requête
        return $this->httpClient->post('process-commit-otppay', $data);
    }
}