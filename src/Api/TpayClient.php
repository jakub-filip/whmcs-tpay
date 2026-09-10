<?php

namespace JakubFilip\Tpay\Api;

use InvalidArgumentException;
use JakubFilip\Tpay\Loggers\TpayLogger;
use JsonException;
use RuntimeException;
use stdClass;

class TpayClient
{
    private const PRODUCTION_URL = 'https://api.tpay.com/';
    private const SANDBOX_URL = 'https://openapi.sandbox.tpay.com/';

    private string $clientId;
    private string $clientSecret;
    private bool $sandbox;

    private ?TpayLogger $logger = null;

    private ?string $token = null;

    public function __construct(
        string $clientId,
        string $clientSecret,
        bool $sandbox = false,
        ?TpayLogger $logger = null
    ) {
        $normalizedClientId = trim($clientId);
        $normalizedClientSecret = trim($clientSecret);

        if ($normalizedClientId === '') {
            throw new InvalidArgumentException('Client ID cannot be empty.');
        }

        if ($normalizedClientSecret === '') {
            throw new InvalidArgumentException('Client secret cannot be empty.');
        }

        $this->clientId = $normalizedClientId;
        $this->clientSecret = $normalizedClientSecret;
        $this->sandbox = $sandbox;
        $this->logger = $logger;
    }

    private function executeRequest(string $method, string $endpoint, array $headers = [], array $data = []): stdClass
    {
        $jsonData = null;
        $prettyJsonData = null;

        if ($method === 'POST' && !empty($data)) {
            try {
                $jsonData = json_encode($data, JSON_THROW_ON_ERROR);
                $prettyJsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new RuntimeException('Failed to encode data to JSON: ' . $e->getMessage(), 0, $e);
            }
        }

        $curl = curl_init();

        if ($curl === false) {
            throw new RuntimeException('Failed to initialize cURL session');
        }

        $curlOptions = [
            CURLOPT_URL => ($this->sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL) . $endpoint,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array_merge([
                'Content-Type: application/json',
                'Accept: application/json',
            ], $headers),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLINFO_HEADER_OUT => true,
        ];

        if (curl_setopt_array($curl, $curlOptions) === false) {
            throw new RuntimeException('Failed to set cURL options');
        }

        if ($jsonData !== null) {
            if (curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData) === false) {
                throw new RuntimeException('Failed to set cURL POST fields');
            }
        }

        $response = curl_exec($curl);
        $curlErrno = curl_errno($curl);
        $curlError = curl_error($curl);

        if ($response === false || $curlErrno !== 0 || $curlError !== '') {
            curl_close($curl);
            throw new RuntimeException($curlError !== '' ? 'cURL error' : 'cURL error: ' . $curlError);
        }

        $requestHeaders = curl_getinfo($curl, CURLINFO_HEADER_OUT);

        if ($requestHeaders === false) {
            throw new RuntimeException('Failed to get request headers');
        }

        $request = $requestHeaders . ($prettyJsonData !== null ? $prettyJsonData : '');

        $responseHeaderSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

        if ($responseHeaderSize === false) {
            throw new RuntimeException('Failed to get response headers size');
        }

        $responseBody = substr($response, $responseHeaderSize);

        curl_close($curl);

        $this->logRequest($request, $response);

        try {
            $decodedResponse = json_decode($responseBody, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to decode response body: ' . $e->getMessage(), 0, $e);
        }

        if (!$decodedResponse instanceof stdClass) {
            throw new RuntimeException('Invalid response format');
        }

        return $decodedResponse;
    }

    private function getToken(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        $response = $this->executeRequest('POST', 'oauth/auth', [], [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (empty($response->access_token)) {
            throw new RuntimeException('Missing access token in response');
        }

        $this->token = $response->access_token;

        return $this->token;
    }

    private function request(string $method, string $endpoint, array $data = []): stdClass
    {
        $token = $this->getToken();

        return $this->executeRequest($method, $endpoint, [
            'Authorization: Bearer ' . $token,
        ], $data);
    }

    private function logRequest(string $request, string $response): void
    {
        $this?->logger->request($request, $response);
    }

    public function get(string $endpoint): stdClass
    {
        return $this->request('GET', $endpoint);
    }

    public function post(string $endpoint, array $data = []): stdClass
    {
        return $this->request('POST', $endpoint, $data);
    }
}
