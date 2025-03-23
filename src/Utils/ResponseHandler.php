<?php

namespace MoovMoney\Utils;

use MoovMoney\Exceptions\ApiException;

/**
 * Utilitaire de traitement des réponses API
 */
class ResponseHandler
{
    /**
     * Traite une réponse API pour extraire les données pertinentes
     * 
     * @param array $response Réponse brute de l'API
     * @return array Données extraites
     * @throws ApiException Si la réponse indique une erreur
     */
    public static function processResponse(array $response): array
    {
        // Vérifie si la réponse contient une erreur
        if (isset($response['status']) && $response['status'] !== '0' && $response['status'] !== 0) {
            $message = $response['message'] ?? $response['statusdescription'] ?? 'Erreur inconnue';
            $code = $response['status'] ?? 500;
            
            throw new ApiException("Erreur API (code $code): $message", (int) $code);
        }
        
        return $response;
    }
    
    /**
     * Extrait les détails de l'abonné d'une réponse subscriber-details
     * 
     * @param array $response Réponse de l'API pour un appel subscriber
     * @return array Données de l'abonné
     * @throws ApiException Si les données ne peuvent pas être extraites
     */
    public static function extractSubscriberDetails(array $response): array
    {
        if (!isset($response['extended-data']['data']['subscriber-details'])) {
            throw new ApiException('Données d\'abonné introuvables dans la réponse');
        }
        
        $subscriberJson = $response['extended-data']['data']['subscriber-details'];
        $subscriberData = json_decode($subscriberJson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException('Impossible de décoder les données d\'abonné: ' . json_last_error_msg());
        }
        
        return $subscriberData;
    }
    
    /**
     * Extrait les détails d'une transaction depuis une réponse de statut
     * 
     * @param array $response Réponse de l'API pour un appel process-check-transaction
     * @return array Données de la transaction
     * @throws ApiException Si les données ne peuvent pas être extraites
     */
    public static function extractTransactionDetails(array $response): array
    {
        if (!isset($response['extended-data']['data'])) {
            throw new ApiException('Données de transaction introuvables dans la réponse');
        }
        
        return $response['extended-data']['data'];
    }
    
    /**
     * Détermine si une transaction a réussi
     * 
     * @param array $response Réponse de l'API
     * @return bool
     */
    public static function isSuccessful(array $response): bool
    {
        return (isset($response['status']) && ($response['status'] === '0' || $response['status'] === 0));
    }
    
    /**
     * Récuperation du message de réponse formaté
     * 
     * @param array $response Réponse de l'API
     * @return string
     */
    public static function getMessage(array $response): string
    {
        return $response['message'] ?? $response['statusdescription'] ?? 'Aucun message disponible';
    }
    
    /**
     * ID de transaction depuis la réponse
     * 
     * @param array $response Réponse de l'API
     * @return string|null
     */
    public static function getTransactionId(array $response): ?string
    {
        return $response['trans-id'] ?? null;
    }
}