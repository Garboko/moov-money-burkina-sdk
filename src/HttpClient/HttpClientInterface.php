<?php

namespace MoovMoney\HttpClient;

/**
 * Interface pour le client HTTP
 */
interface HttpClientInterface
{
    /**
     * Envoie une requête POST à l'API
     * 
     * @param string $commandId Identifiant de la commande
     * @param array $data Données à envoyer
     * @return array Réponse décodée
     */
    public function post(string $commandId, array $data): array;
    
    /**
     * Vérifie si une réponse indique une erreur
     * 
     * @param array $response Réponse de l'API
     * @return bool
     */
    public function isError(array $response): bool;
    
    /**
     * Transforme les données en structure de requête attendue par l'API
     * 
     * @param array $data Données brutes
     * @return array Données formatées
     */
    public function formatRequestData(array $data): array;
}