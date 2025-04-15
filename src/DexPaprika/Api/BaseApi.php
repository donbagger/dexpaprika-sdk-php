<?php

namespace DexPaprika\Api;

use DexPaprika\Exceptions\DexPaprikaApiException;
use DexPaprika\Utils\ResponseTransformer;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

abstract class BaseApi
{
    /**
     * @var ClientInterface HTTP client
     */
    protected ClientInterface $httpClient;
    
    /**
     * @var bool Whether to transform API responses
     */
    protected bool $transformResponses;

    /**
     * BaseApi constructor.
     *
     * @param ClientInterface $httpClient
     * @param bool $transformResponses Whether to transform API responses
     */
    public function __construct(ClientInterface $httpClient, bool $transformResponses = false)
    {
        $this->httpClient = $httpClient;
        $this->transformResponses = $transformResponses;
    }

    /**
     * Make a GET request to the API
     *
     * @param string $endpoint The API endpoint
     * @param array<string, mixed> $queryParams Query parameters
     * @return array<string, mixed>|object The response data
     * @throws DexPaprikaApiException If the API request fails
     */
    protected function get(string $endpoint, array $queryParams = [])
    {
        try {
            $response = $this->httpClient->request('GET', $endpoint, [
                'query' => $queryParams,
            ]);
            
            return $this->processResponse($response);
        } catch (GuzzleException $e) {
            throw new DexPaprikaApiException(
                'API request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process an API response
     *
     * @param ResponseInterface $response The HTTP response
     * @return array<string, mixed>|object The response data
     * @throws DexPaprikaApiException If the response cannot be processed
     */
    protected function processResponse(ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new DexPaprikaApiException(
                'Failed to decode JSON response: ' . json_last_error_msg()
            );
        }
        
        // Apply transformation if enabled
        if ($this->transformResponses) {
            return $this->transformResponse($data);
        }
        
        return $data;
    }
    
    /**
     * Transform an API response based on its content
     *
     * @param array<string, mixed> $data The response data
     * @param bool $asObject Whether to return the data as an object
     * @return array|object The transformed data
     */
    protected function transformResponse(array $data, bool $asObject = false)
    {
        if (!$asObject) {
            return $data;
        }
        
        // When asObject is true, transform the data to an object
        // Try to determine the response type based on the data structure
        if (isset($data['dexes'])) {
            return \DexPaprika\Utils\ResponseTransformer::transformDexes($data);
        } elseif (isset($data['pools'])) {
            return \DexPaprika\Utils\ResponseTransformer::transformPools($data);
        } elseif (isset($data['transactions'])) {
            return \DexPaprika\Utils\ResponseTransformer::transformTransactions($data);
        } elseif (isset($data['tokens'])) {
            return \DexPaprika\Utils\ResponseTransformer::transformTokens($data);
        } elseif (isset($data['token'])) {
            return \DexPaprika\Utils\ResponseTransformer::transformToken($data);
        } elseif (isset($data['ohlcv'])) {
            return \DexPaprika\Utils\ResponseTransformer::transformOHLCV($data);
        } elseif (isset($data['stats'])) {
            return \DexPaprika\Utils\ResponseTransformer::transformStats($data);
        }
        
        // Default transformation
        return \DexPaprika\Utils\ResponseTransformer::transform($data);
    }

    /**
     * Build query parameters from an options array
     *
     * @param array<string, mixed> $options The options array
     * @param array<string, string> $mapping Mapping from option keys to query parameter keys
     * @return array<string, mixed> The query parameters
     */
    protected function buildQueryParams(array $options, array $mapping = []): array
    {
        $queryParams = [];
        
        foreach ($options as $key => $value) {
            if ($value === null) {
                continue;
            }
            
            $paramKey = $mapping[$key] ?? $key;
            $queryParams[$paramKey] = $value;
        }
        
        return $queryParams;
    }

    /**
     * Validate the required parameters
     *
     * @param array<string, mixed> $params The parameters to validate
     * @param array<string> $required The required parameter keys
     * @throws DexPaprikaApiException If any required parameter is missing
     */
    protected function validateRequired(array $params, array $required): void
    {
        foreach ($required as $key) {
            if (!isset($params[$key]) || $params[$key] === '') {
                throw new DexPaprikaApiException("Required parameter '$key' is missing or empty");
            }
        }
    }
    
    /**
     * Validate that a value is one of the allowed values
     *
     * @param mixed $value The value to validate
     * @param array<mixed> $allowed The allowed values
     * @param string $paramName The parameter name for error messages
     * @throws DexPaprikaApiException If the value is not allowed
     */
    protected function validateAllowed($value, array $allowed, string $paramName): void
    {
        if (!in_array($value, $allowed)) {
            throw new DexPaprikaApiException(
                "Invalid $paramName. Must be one of: " . implode(', ', $allowed)
            );
        }
    }
    
    /**
     * Check if response transformation is enabled
     * 
     * @return bool
     */
    public function isTransformingResponses(): bool
    {
        return $this->transformResponses;
    }
    
    /**
     * Enable or disable response transformation
     * 
     * @param bool $enable Whether to enable transformation
     * @return self
     */
    public function setTransformResponses(bool $enable): self
    {
        $this->transformResponses = $enable;
        return $this;
    }
}