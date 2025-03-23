<?php

namespace MoovMoney\Utils;

use MoovMoney\Exceptions\ValidationException;

/**
 * Classe utilitaire de validation
 */
class Validator
{
    /**
     * Validation d'un numéro de téléphone
     * 
     * @param string $phoneNumber Numéro de téléphone à valider
     * @throws ValidationException Si le numéro est invalide
     */
    public function validatePhoneNumber(string $phoneNumber): void
    {
        // Format attendu: numéro à 8-12 chiffres, peut commencer par +
        if (!preg_match('/^\+?[0-9]{8,12}$/', $phoneNumber)) {
            throw ValidationException::invalidPhoneNumber($phoneNumber);
        }
    }
    
    /**
     * Validation du montant
     * 
     * @param float $amount Montant à valider
     * @throws ValidationException Si le montant est invalide
     */
    public function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw ValidationException::invalidAmount($amount);
        }
    }
    
    /**
     * Validation du code OTP
     * 
     * @param string $otp Code OTP à valider
     * @throws ValidationException Si le code OTP est invalide
     */
    public function validateOtp(string $otp): void
    {
        // Format attendu: 6 chiffres
        if (!preg_match('/^[0-9]{6}$/', $otp)) {
            throw ValidationException::invalidOtp($otp);
        }
    }
    
    /**
     * Validation de l'ID d'une requête
     * 
     * @param string $requestId ID de requête à valider
     * @throws ValidationException Si l'ID est invalide
     */
    public function validateRequestId(string $requestId): void
    {
        if (strlen($requestId) < 5 || strlen($requestId) > 50) {
            throw new ValidationException(
                "ID de requête invalide. Longueur attendue: entre 5 et 50 caractères",
                'requestId',
                $requestId,
                'length'
            );
        }
    }
    
    /**
     * Valide que tous les champs requis sont présents
     * 
     * @param array $data Données à valider
     * @param array $requiredFields Liste des champs requis
     * @throws ValidationException Si un champ requis est manquant
     */
    public function validateRequiredFields(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw ValidationException::requiredField($field);
            }
        }
    }
    
    /**
     * Validation du format de date
     * 
     * @param string $date Date à valider
     * @param string $format Format attendu (ex: 'ddMMyyyy')
     * @throws ValidationException Si le format de date est invalide
     */
    public function validateDateFormat(string $date, string $format = 'ddMMyyyy'): void
    {
        $pattern = '/^\d{2}\d{2}\d{4}$/';
        
        if ($format === 'dd-MM-yyyy') {
            $pattern = '/^\d{2}-\d{2}-\d{4}$/';
        } elseif ($format === 'yyyy-MM-dd') {
            $pattern = '/^\d{4}-\d{2}-\d{2}$/';
        }
        
        if (!preg_match($pattern, $date)) {
            throw new ValidationException(
                "Format de date invalide. Format attendu: $format",
                'date',
                $date,
                'dateFormat'
            );
        }
    }
    
    /**
     * Valide une énumération
     * 
     * @param string $value Valeur à valider
     * @param array $allowedValues Valeurs autorisées
     * @param string $field Nom du champ
     * @throws ValidationException Si la valeur n'est pas dans la liste des valeurs autorisées
     */
    public function validateEnum(string $value, array $allowedValues, string $field): void
    {
        if (!in_array($value, $allowedValues, true)) {
            $allowedValuesStr = implode(', ', $allowedValues);
            throw new ValidationException(
                "Valeur invalide pour '$field'. Valeurs autorisées: $allowedValuesStr",
                $field,
                $value,
                'enum'
            );
        }
    }
}