<?php

namespace MoovMoney\HttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use MoovMoney\Config;
use MoovMoney\Exceptions\ApiException;
use MoovMoney\Exceptions\AuthenticationException;
use MoovMoney\Exceptions\NetworkException;

/**
 * Implémentation Guzzle du client HTTP
 */
class GuzzleHttpClient implements HttpClientInterface
{
    /** @var Config Configuration */
    private Config $config;
    
    /** @var Client Client Guzzle */
    private Client $client;

    /**
     * @param Config $config Configuration
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => $config->getApiUrl(),
            'timeout' => $config->getTimeout(),
            'verify' => $config->isVerifySsl(),
        ]);
    }

    /**
     * {@inheritdoc}
     * @throws ApiException
     * @throws AuthenticationException
     * @throws NetworkException
     */
    public function post(string $commandId, array $data): array
    {
        try {
            $formattedData = $this->formatRequestData($data);
            
            $response = $this->client->post('', [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->config->getBasicAuthToken(),
                    'command-id' => $commandId,
                    'Content-Type' => 'application/json',
                ],
                'json' => $formattedData,
            ]);
            
            $body = (string) $response->getBody();
            $decodedResponse = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ApiException('Réponse JSON invalide: ' . json_last_error_msg());
            }
            
            if ($this->isError($decodedResponse)) {
                throw ApiException::fromApiResponse($decodedResponse);
            }
            
            return $decodedResponse;
        } catch (ConnectException $e) {
            $request = $e->getRequest();
            $url = (string) $request->getUri();
            $method = $request->getMethod();
            
            if (strpos($e->getMessage(), 'timeout') !== false) {
                throw NetworkException::connectionTimeout($url, $method, $this->config->getTimeout());
            }
            
            if (strpos($e->getMessage(), 'ssl') !== false || strpos($e->getMessage(), 'SSL') !== false) {
                throw NetworkException::sslError($url, $e->getMessage());
            }
            
            if (strpos($e->getMessage(), 'Could not resolve host') !== false) {
                throw NetworkException::dnsError($url);
            }
            
            throw NetworkException::connectionError($url, $method, $e->getMessage());
        } catch (RequestException $e) {
            $response = $e->getResponse();
            
            if ($response && $response->getStatusCode() === 401) {
                throw AuthenticationException::invalidCredentials(
                    $this->config->getUsername(),
                    $response->getReasonPhrase()
                );
            }
            
            if ($response) {
                $body = (string) $response->getBody();
                try {
                    $decodedResponse = json_decode($body, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        throw ApiException::fromApiResponse($decodedResponse);
                    }
                } catch (\Exception $jsonEx) {
                    // Ignore JSON parsing errors
                }
                
                throw new ApiException(
                    'Erreur HTTP: ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase(),
                    $response->getStatusCode()
                );
            }
            
            throw new ApiException('Erreur HTTP: ' . $e->getMessage(), $e->getCode(), $e);
        } catch (GuzzleException $e) {
            throw new ApiException('Erreur HTTP: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isError(array $response): bool
    {
        // Les codes de statut autre que 0 indiquent une erreur
        if (isset($response['status']) && $response['status'] !== '0' && $response['status'] !== 0) {
            return true;
        }
        
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function formatRequestData(array $data): array
    {
        // Format attendu par l'API
        return $data;
    }
}