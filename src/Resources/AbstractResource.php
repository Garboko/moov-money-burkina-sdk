<?php

namespace MoovMoney\Resources;

use MoovMoney\HttpClient\HttpClientInterface;
use MoovMoney\Utils\Validator;

/**
 * Classe abstraite de base pour les ressources API
 */
abstract class AbstractResource
{
    /** @var HttpClientInterface Client HTTP */
    protected HttpClientInterface $httpClient;
    
    /** @var Validator Validateur */
    protected Validator $validator;

    /**
     * @param HttpClientInterface $httpClient Client HTTP
     */
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->validator = new Validator();
    }
    
    /**
     * Génére un ID de requête unique
     * 
     * @param string $prefix Préfixe pour l'ID
     * @return string
     */
    protected function generateRequestId(string $prefix = ''): string
    {
        $timestamp = time();
        $random = mt_rand(10000, 99999);
        
        return $prefix . $timestamp . $random;
    }
    
    /**
     * Formate les données étendues
     * 
     * @param array $data Données étendues
     * @return array
     */
    protected function formatExtendedData(array $data): array
    {
        if (empty($data)) {
            return [];
        }
        
        return ['extended-data' => $data];
    }
}