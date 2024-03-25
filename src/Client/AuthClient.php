<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Client;

use GrinchenkoUniversity\Diia\Dto\Credentials;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use UnexpectedValueException;

class AuthClient
{
    private ClientInterface $httpClient;

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Returns a session token.
     *
     * @param Credentials $credentials
     *
     * @return string
     *
     * @throws GuzzleException
     */
    public function acquireToken(Credentials $credentials): string
    {
        $headers = [
            'accept' => 'application/json',
        ];

        if ($credentials->getAuthAcquirerToken() !== null) {
            $headers['Authorization'] = sprintf(
                'Basic %s',
                $credentials->getAuthAcquirerToken()
            );
        }

        $response = $this->httpClient->request(
            'GET',
            sprintf(
                '/api/v1/auth/acquirer/%s',
                $credentials->getAcquirerToken()
            ),
            [
                'headers' => $headers,
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);

        if (($data['token'] ?? null) === null) {
            throw new UnexpectedValueException('Unexpected acquirer endpoint response, bad data provided.');
        }

        return $data['token'];
    }
}
