<?php

namespace DexPaprika;

use DexPaprika\Api\DexesApi;
use DexPaprika\Api\NetworksApi;
use DexPaprika\Api\PoolsApi;
use DexPaprika\Api\SearchApi;
use DexPaprika\Api\TokensApi;
use DexPaprika\Api\UtilsApi;
use DexPaprika\Utils\Paginator;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;

class Client
{
    /**
     * @var string API base URL
     */
    private string $baseUrl;

    /**
     * @var ClientInterface HTTP client
     */
    private ClientInterface $httpClient;
    
    /**
     * @var bool Whether to transform API responses
     */
    private bool $transformResponses;

    /**
     * @var NetworksApi
     */
    public NetworksApi $networks;

    /**
     * @var DexesApi
     */
    public DexesApi $dexes;

    /**
     * @var PoolsApi
     */
    public PoolsApi $pools;

    /**
     * @var TokensApi
     */
    public TokensApi $tokens;

    /**
     * @var SearchApi
     */
    public SearchApi $search;

    /**
     * @var UtilsApi
     */
    public UtilsApi $utils;
    
    /**
     * SDK version
     */
    public const VERSION = '1.0.0';

    /**
     * Create a new DexPaprika client
     *
     * @param string|null $baseUrl The API base URL
     * @param ClientInterface|null $httpClient The HTTP client to use
     * @param bool $transformResponses Whether to transform API responses into objects
     */
    public function __construct(
        ?string $baseUrl = null,
        ?ClientInterface $httpClient = null,
        bool $transformResponses = false
    ) {
        $this->baseUrl = $baseUrl ?? 'https://api.dexpaprika.com';
        $this->transformResponses = $transformResponses;
        $this->httpClient = $httpClient ?? new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'dexpaprika-sdk-php/' . self::VERSION,
            ],
        ]);

        // Initialize API services
        $this->networks = new NetworksApi($this->httpClient, $this->transformResponses);
        $this->dexes = new DexesApi($this->httpClient, $this->transformResponses);
        $this->pools = new PoolsApi($this->httpClient, $this->transformResponses);
        $this->tokens = new TokensApi($this->httpClient, $this->transformResponses);
        $this->search = new SearchApi($this->httpClient, $this->transformResponses);
        $this->utils = new UtilsApi($this->httpClient, $this->transformResponses);
    }

    /**
     * Get the HTTP client
     *
     * @return ClientInterface
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Get the base URL
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
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
     * Create a paginator for a specific API method
     *
     * @param object $api The API instance (e.g., $client->pools)
     * @param string $method The method name (e.g., 'getNetworkPools')
     * @param array<string, mixed> $params The method parameters
     * @return Paginator The paginator instance
     */
    public function createPaginator(object $api, string $method, array $params = []): Paginator
    {
        return new Paginator($api, $method, $params);
    }
} 