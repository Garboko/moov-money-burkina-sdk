<?php

namespace MoovMoney\Resources;

use MoovMoney\Exceptions\ValidationException;

/**
 * Gestion des abonnés
 */
class Subscriber extends AbstractResource
{
    /**
     * Vérifie le statut d'un abonné
     * 
     * @param string $msisdn Numéro de téléphone
     * @param string|null $requestId ID de requête personnalisé
     * 
     * @return array Réponse de l'API avec les détails de l'abonné
     * @throws ValidationException Si le numéro de téléphone est invalide
     */
    public function checkStatus(string $msisdn, ?string $requestId = null): array
    {
        $this->validator->validatePhoneNumber($msisdn);
        
        $data = [
            'destination' => $msisdn,
            'request-id' => $requestId ?? $this->generateRequestId('MobileAccountStatus-')
        ];
        
        return $this->httpClient->post('process-check-subscriber', $data);
    }
    
    /**
     * Enregistre un nouvel abonné
     * 
     * @param array $subscriberData Données de l'abonné
     * @param string|null $requestId ID de requête personnalisé
     * 
     * @return array Réponse de l'API
     * @throws ValidationException Si les données sont invalides
     */
    public function register(array $subscriberData, ?string $requestId = null): array
    {
        // Validation des champs obligatoires
        $requiredFields = ['msisdn', 'lastname', 'firstname', 'idnumber', 'iddescription', 
            'gender', 'dateofbirth', 'placeofbirth', 'city'];
            
        foreach ($requiredFields as $field) {
            if (empty($subscriberData[$field])) {
                throw new ValidationException("Le champ '$field' est obligatoire");
            }
        }
        
        // Validation du numéro de téléphone
        $this->validator->validatePhoneNumber($subscriberData['msisdn']);
        
        // Validation du genre
        if (!in_array(strtoupper($subscriberData['gender']), ['HOMME', 'FEMME'])) {
            throw new ValidationException("Le genre doit être 'HOMME' ou 'FEMME'");
        }
        
        // Validation du format de date
        if (!preg_match('/^\d{2}\d{2}\d{4}$/', $subscriberData['dateofbirth'])) {
            throw new ValidationException("Le format de date de naissance doit être 'ddMMyyyy'");
        }
        
        $data = [
            'request-id' => $requestId ?? $this->generateRequestId('SubscriberReg-'),
            'extended-data' => $subscriberData
        ];
        
        return $this->httpClient->post('subscriber-registration', $data);
    }
}