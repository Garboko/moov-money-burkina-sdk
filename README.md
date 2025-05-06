# Moov Money SDK PHP

SDK PHP officiel pour l'intégration des API de paiement mobile Moov Money.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/garboko/moov-money-sdk.svg?style=flat-square)](https://packagist.org/packages/garboko/moov-money-sdk)
[![Tests](https://img.shields.io/github/actions/workflow/status/garboko/moov-money-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/garboko/moov-money-sdk/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/garboko/moov-money-sdk.svg?style=flat-square)](https://packagist.org/packages/garboko/moov-money-sdk)

Ce SDK facilite l'intégration avec l'API de paiement mobile Moov Money dans vos applications PHP et gère automatiquement:
- L'authentification et la sécurité des requêtes
- La validation des données
- La gestion des erreurs et exceptions
- Les opérations de paiement par OTP, transferts d'argent, et plus

## Installation

### Composer

```bash
composer require garboko/moov-money-sdk
```

### Git

```bash
git clone https://github.com/Garboko/moov-money-sdk.git
```

## Configuration

```php
use MoovMoney\Config;
use MoovMoney\Client;

// Créer la configuration
$config = new Config(
    'api_url', // URL de l'API
    'votre_nom_utilisateur',  // Nom d'utilisateur fourni par Moov
    'votre_mot_de_passe',     // Mot de passe fourni par Moov
    false,  // Mode test (true pour l'environnement de test)
    30,     // Timeout en secondes
    true    // Vérification SSL
);

// Initialiser le client
$client = new Client($config);
```

## Fonctionnalités

### Vérifier le statut d'un abonné

```php
try {
    $response = $client->subscribers()->checkStatus('22662356789');
    
    // Accéder aux informations de l'abonné
    if ($response['status'] === '0') {
        $subscriberData = json_decode($response['extended-data']['data']['subscriber-details'], true);
        echo "Statut: " . $subscriberData['status'] . "\n";
        echo "Nom: " . $subscriberData['firstname'] . ' ' . $subscriberData['lastname'] . "\n";
    }
} catch (\MoovMoney\Exceptions\ApiException $e) {
    echo "Erreur API: " . $e->getMessage();
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
```

### Paiement avec validation OTP (flux complet)

```php
try {
    // Étape 1: Générer l'OTP (envoyé par SMS au client)
    $createResponse = $client->otpPayments()->create(
        '22662356789',     // Numéro du client (qui sera débité)
        1000,              // Montant (dans la devise locale)
        'Achat en ligne'   // Description
    );
    
    if ($createResponse['status'] === '0') {
        $transId = $createResponse['trans-id'];
        echo "OTP envoyé avec succès! Référence: $transId\n";
        
        // Dans un cas réel, l'utilisateur saisit le code OTP ici
        $otp = '123456'; // Code reçu par SMS
        
        // Étape 2: Valider l'OTP pour finaliser le paiement
        $validateResponse = $client->otpPayments()->validate(
            '22662356789',     // Même numéro qu'à l'étape 1
            1000,              // Même montant qu'à l'étape 1
            $otp,              // Code OTP
            $transId,          // ID de transaction reçu à l'étape 1
            'Validation paiement'
        );
        
        if ($validateResponse['status'] === '0') {
            echo "Paiement réussi! Référence: " . $validateResponse['trans-id'] . "\n";
        } else {
            echo "Échec de validation: " . $validateResponse['message'] . "\n";
        }
    } else {
        echo "Échec de création OTP: " . $createResponse['message'] . "\n";
    }
} catch (\MoovMoney\Exceptions\ApiException $e) {
    echo "Erreur API: " . $e->getMessage();
}
```

### Transfert d'argent

```php
try {
    // Transfert simple vers un autre compte
    $response = $client->cashTransfers()->transfer(
        '22662356789',     // Numéro du destinataire (qui sera crédité)
        500,               // Montant
        'Remboursement'    // Description
    );
    
    if ($response['status'] === '0') {
        echo "Transfert réussi! Référence: " . $response['trans-id'] . "\n";
    } else {
        echo "Échec du transfert: " . $response['message'] . "\n";
    }
} catch (\MoovMoney\Exceptions\ApiException $e) {
    echo "Erreur API: " . $e->getMessage();
}
```

### Transfert transfrontalier

```php
try {
    $response = $client->cashTransfers()->crossBorderTransfer(
        '22662356789',        // Numéro du destinataire international
        1000,                 // Montant
        'Transfert international'
    );
    
    if ($response['status'] === '0') {
        echo "Transfert international réussi! Référence: " . $response['trans-id'] . "\n";
    } else {
        echo "Échec du transfert: " . $response['message'] . "\n";
    }
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
```

### Vérifier le statut d'une transaction

```php
try {
    $response = $client->transactions()->checkStatus('OTPMerchantPayment-000000201120240001');
    
    if ($response['status'] === '0') {
        $data = $response['extended-data']['data'];
        echo "Référence: " . $data['reference-id'] . "\n";
        echo "Statut: " . $data['status-description'] . "\n";
    } else {
        echo "Échec de la vérification: " . $response['message'] . "\n";
    }
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
```

### Enregistrement d'un nouvel abonné

```php
try {
    $subscriberData = [
        'msisdn' => '22662356789',       // Numéro de téléphone
        'lastname' => 'Doe',             // Nom
        'firstname' => 'John',           // Prénom
        'idnumber' => '123456789',       // Numéro d'identification
        'iddescription' => 'CARTE NATIONALE D\'IDENTITÉ',  // Type d'ID
        'gender' => 'HOMME',             // Genre (HOMME/FEMME)
        'dateofbirth' => '01011990',     // Format: ddMMyyyy
        'placeofbirth' => 'Abidjan',     // Lieu de naissance
        'city' => 'Abidjan',             // Ville
        'region' => 'Sud',               // Région (optionnel)
        'country' => 'Côte d\'Ivoire'    // Pays (optionnel)
    ];
    
    $response = $client->subscribers()->register($subscriberData);
    
    if ($response['status'] === '0') {
        echo "Enregistrement réussi! Référence: " . $response['trans-id'] . "\n";
    } else {
        echo "Échec de l'enregistrement: " . $response['message'] . "\n";
    }
} catch (\MoovMoney\Exceptions\ValidationException $e) {
    echo "Erreur de validation: " . $e->getMessage();
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
```

## Gestion des erreurs

Le SDK gère les exceptions suivantes:

- `ApiException` - Erreur générale de l'API
- `AuthenticationException` - Erreur d'authentification
- `ValidationException` - Erreur de validation des données

```php
try {
    // Votre code utilisant le SDK
} catch (\MoovMoney\Exceptions\AuthenticationException $e) {
    // Problème d'authentification (identifiants invalides)
    echo "Erreur d'authentification: " . $e->getMessage();
} catch (\MoovMoney\Exceptions\ValidationException $e) {
    // Problème avec les données fournies
    echo "Erreur de validation: " . $e->getMessage();
} catch (\MoovMoney\Exceptions\ApiException $e) {
    // Erreur API générale
    echo "Erreur API: " . $e->getMessage();
    echo "Code: " . $e->getCode();
} catch (\Exception $e) {
    // Autre erreur
    echo "Erreur: " . $e->getMessage();
}
```

## Codes de statut

Les réponses de l'API utilisent généralement ces codes de statut:

| Code | Description |
|------|-------------|
| 0    | Succès      |
| 11   | N'existe pas |
| 12   | Échec       |
| 15   | En attente  |
| 401  | Non autorisé |
| 404  | Lien introuvable |
| 500  | Erreur interne |

## Personnalisation

### Client HTTP personnalisé

Vous pouvez fournir votre propre implémentation du client HTTP:

```php
use MoovMoney\Config;
use MoovMoney\Client;
use MoovMoney\HttpClient\HttpClientInterface;

class MyCustomHttpClient implements HttpClientInterface
{
    // Implémentation personnalisée
}

$config = new Config(/* ... */);
$httpClient = new MyCustomHttpClient();
$client = new Client($config, $httpClient);
```

## Tests

```bash
composer test
```

## Développement et contribution

1. Fork le dépôt
2. Créer une branche pour votre fonctionnalité (`git checkout -b feature/amazing-feature`)
3. Committez vos changements (`git commit -m 'feat: ajout d'une fonctionnalité'`)
4. Poussez la branche (`git push origin feature/amazing-feature`)
5. Ouvrez une Pull Request

## Support

Pour toute question ou problème concernant ce SDK, contactez [aimezongo2@gmail.com](mailto:aimezongo2@gmail.com) ou ouvrez une issue sur GitHub.

## Licence

[MIT](./LICENSE)

## À propos de Garboko

Ce SDK est maintenu par Garboko. Il vise à faciliter l'intégration des API de paiement mobile Moov Money pour les développeurs PHP au Burkina Faso et dans la région.