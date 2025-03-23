<?php

namespace MoovMoney\Exceptions;

/**
 * Exception liée aux erreurs de validation
 */
class ValidationException extends ApiException
{
    /** @var string|null Nom du champ qui a échoué à la validation */
    protected ?string $field;
    
    /** @var mixed Valeur qui a échoué à la validation */
    protected $value;
    
    /** @var string|null Règle de validation qui a échoué */
    protected ?string $rule;

    /**
     * @param string $message Message d'erreur
     * @param string|null $field Nom du champ qui a échoué à la validation
     * @param mixed $value Valeur qui a échoué à la validation
     * @param string|null $rule Règle de validation qui a échoué
     * @param int $code Code d'erreur
     * @param \Throwable|null $previous Exception précédente
     */
    public function __construct(
        string $message = "", 
        ?string $field = null,
        $value = null,
        ?string $rule = null,
        int $code = 400, 
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
        $this->field = $field;
        $this->value = $value;
        $this->rule = $rule;
    }

    /**
     * Récupére le nom du champ qui a échoué à la validation
     * 
     * @return string|null
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * Récupére la valeur qui a échoué à la validation
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Récupére la règle de validation qui a échoué
     * 
     * @return string|null
     */
    public function getRule(): ?string
    {
        return $this->rule;
    }
    
    /**
     * Crée une exception pour un numéro de téléphone invalide
     * 
     * @param string $phoneNumber Numéro de téléphone invalide
     * @return self
     */
    public static function invalidPhoneNumber(string $phoneNumber): self
    {
        return new self(
            "Numéro de téléphone invalide: $phoneNumber. Format attendu: 8-12 chiffres, peut commencer par +",
            'phoneNumber',
            $phoneNumber,
            'phoneFormat'
        );
    }
    
    /**
     * Crée une exception pour un montant invalide
     * 
     * @param float $amount Montant invalide
     * @return self
     */
    public static function invalidAmount(float $amount): self
    {
        return new self(
            "Le montant doit être supérieur à 0, reçu: $amount",
            'amount',
            $amount,
            'positive'
        );
    }
    
    /**
     * Crée une exception pour un OTP invalide
     * 
     * @param string $otp Code OTP invalide
     * @return self
     */
    public static function invalidOtp(string $otp): self
    {
        return new self(
            "Code OTP invalide. Format attendu: 6 chiffres",
            'otp',
            $otp,
            'otpFormat'
        );
    }
    
    /**
     * Crée une exception pour un champ requis manquant
     * 
     * @param string $field Nom du champ requis
     * @return self
     */
    public static function requiredField(string $field): self
    {
        return new self(
            "Le champ '$field' est obligatoire",
            $field,
            null,
            'required'
        );
    }
}