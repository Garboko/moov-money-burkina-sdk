<?php

namespace MoovMoney\Exceptions;

/**
 * Exception liée aux erreurs d'authentification
 */
class AuthenticationException extends ApiException
{
    /** @var string|null Identifiant utilisé lors de l'authentification */
    protected ?string $username;
    
    /**
     * @param string $message Message d'erreur
     * @param int $code Code d'erreur
     * @param \Throwable|null $previous Exception précédente
     * @param string|null $username Identifiant utilisé
     */
    public function __construct(
        string $message = "Échec d'authentification", 
        int $code = 401, 
        ?\Throwable $previous = null,
        ?string $username = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->username = $username;
    }

    /**
     * Récupére l'identifiant utilisé lors de l'authentification
     * 
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }
    
    /**
     * Crée une exception pour des identifiants invalides
     * 
     * @param string|null $username Identifiant utilisé
     * @param string|null $errorMessage Message d'erreur spécifique
     * @return self
     */
    public static function invalidCredentials(?string $username = null, ?string $errorMessage = null): self
    {
        $message = $errorMessage ?? "Identifiants invalides. Veuillez vérifier vos informations d'authentification.";
        return new self($message, 401, null, $username);
    }
    
    /**
     * Crée une exception pour une session expirée
     * 
     * @param string|null $username Identifiant utilisé
     * @return self
     */
    public static function sessionExpired(?string $username = null): self
    {
        return new self("Session expirée. Veuillez vous reconnecter.", 401, null, $username);
    }
    
    /**
     * Crée une exception pour des permissions insuffisantes
     * 
     * @param string|null $username Identifiant utilisé
     * @param string|null $resource Ressource pour laquelle les permissions sont insuffisantes
     * @return self
     */
    public static function insufficientPermissions(?string $username = null, ?string $resource = null): self
    {
        $message = "Permissions insuffisantes";
        if ($resource) {
            $message .= " pour accéder à $resource";
        }
        
        return new self($message, 403, null, $username);
    }
}