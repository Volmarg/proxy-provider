<?php

namespace App\Service\Request\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * Service for handling standard request via POST / GET etc.
 */
class GuzzleService implements GuzzleInterface
{
    /**
     * @var Client $client
     */
    private Client $client;

    /**
     * @var array $jsonBody
     */
    private array $jsonBody = [];

    /**
     * @var array $headers
     */
    private array $headers = [];

    /**
     * @var array|int[] $defaultOptions
     */
    private array $defaultOptions = [
        RequestOptions::TIMEOUT         => 15,
        RequestOptions::CONNECT_TIMEOUT => 15,
    ];

    /**
     * Headers to be used with every connection
     *
     * @var array
     */
    readonly private array $defaultHeaders;

    /**
     * Will set headers that are to be merged into original client configured headers
     *
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * Will set the body to be attached to the client request
     *
     * @param array $content
     */
    public function setJsonBody(array $content): void
    {
        $this->jsonBody = $content;
    }

    public function __construct()
    {
        $this->defaultHeaders = [
            GuzzleInterface::HEADER_CONNECTION => GuzzleInterface::CONNECTION_TYPE_CLOSE,
        ];
    }

    /**
     * Will perform get request toward provided url
     *
     * @param string $url - url to be called
     * @param array  $options
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $url, array $options = []): ResponseInterface
    {
        $this->buildClient();

        $optionsMerges = [
            ...$this->defaultOptions,
            ...$options
        ];

        $response = $this->client->get($url, $optionsMerges);

        return $response;
    }

    /**
     * Will perform post request toward provided url
     *
     * @param string $url - url to be called
     * @param array  $options
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function post(string $url, array $options = []): ResponseInterface
    {
        $this->buildClient();

        $optionsMerges = [
            ...$this->defaultOptions,
            ...$options
        ];

        $response = $this->client->post($url, $optionsMerges);

        return $response;
    }

    /**
     * Will build guzzle configuration to be used with all calls when using instance of service
     *
     * @return array
     */
    private function buildConfiguration(): array
    {
        $newConfiguration                               = (new Client())->getConfig();
        $newConfiguration[GuzzleInterface::KEY_HEADERS] = [
            ...$this->defaultHeaders,
            ...$this->headers,
        ];

        $newConfiguration[GuzzleInterface::KEY_JSON_REQUEST_BODY] = $this->jsonBody;

        return $newConfiguration;
    }

    /**
     * This must be separated method due to:
     * - proxy connection being re-fetched on each call
     *
     * @throws GuzzleException
     */
    private function buildClient(): void
    {
        $newConfig    = $this->buildConfiguration();
        $this->client = new Client($newConfig);
    }

}