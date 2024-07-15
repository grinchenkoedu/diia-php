<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Client;

use GrinchenkoUniversity\Diia\Dto\Acquirers\Branch;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Offer;
use GrinchenkoUniversity\Diia\Dto\ApiResource;
use GrinchenkoUniversity\Diia\Dto\Request\DocumentRequest;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;
use GrinchenkoUniversity\Diia\Dto\Request\OfferRequest;
use GrinchenkoUniversity\Diia\Dto\Response\ItemsListResponse;
use GrinchenkoUniversity\Diia\Dto\Response\OfferResponse;
use GrinchenkoUniversity\Diia\Mapper\Request\RequestJsonMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ResponseJsonMapper;
use GrinchenkoUniversity\Diia\Provider\HttpHeadersProvider;
use GuzzleHttp\ClientInterface;
use UnexpectedValueException;

class AcquirersClient
{
    private ClientInterface $httpClient;
    private HttpHeadersProvider $httpHeadersProvider;
    private RequestJsonMapper $requestJsonMapper;
    private ResponseJsonMapper $createResourceResponseMapper;
    private ResponseJsonMapper $branchListResponseMapper;
    private ResponseJsonMapper $branchResponseMapper;
    private ResponseJsonMapper $offerListResponseMapper;
    private ResponseJsonMapper $offerResponseMapper;

    public function __construct(
        ClientInterface     $httpClient,
        HttpHeadersProvider $httpHeadersProvider,
        RequestJsonMapper   $requestJsonMapper,
        ResponseJsonMapper  $createResourceResponseMapper,
        ResponseJsonMapper  $branchListResponseMapper,
        ResponseJsonMapper  $branchResponseMapper,
        ResponseJsonMapper  $offerListResponseMapper,
        ResponseJsonMapper  $offerResponseMapper
    ) {
        $this->httpClient = $httpClient;
        $this->httpHeadersProvider = $httpHeadersProvider;
        $this->requestJsonMapper = $requestJsonMapper;
        $this->createResourceResponseMapper = $createResourceResponseMapper;
        $this->branchListResponseMapper = $branchListResponseMapper;
        $this->branchResponseMapper = $branchResponseMapper;
        $this->offerListResponseMapper = $offerListResponseMapper;
        $this->offerResponseMapper = $offerResponseMapper;
    }

    public function createBranch(Branch $branch): ApiResource
    {
        $response = $this->httpClient->request(
            'POST',
            '/api/v2/acquirers/branch',
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                'body' => $this->requestJsonMapper->mapToJson($branch),
            ]
        );

        return $this->createResourceResponseMapper->mapFromResponse($response->getBody()->getContents());
    }

    public function updateBranch(string $branchId, Branch $branch): ApiResource
    {
        $response = $this->httpClient->request(
            'PUT',
            sprintf('/api/v2/acquirers/branch/%s', $branchId),
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                'body' => $this->requestJsonMapper->mapToJson($branch),
            ]
        );

        return $this->createResourceResponseMapper->mapFromResponse($response->getBody()->getContents());
    }

    public function deleteBranch(string $branchId): void
    {
        $this->httpClient->request(
            'DELETE',
            sprintf('/api/v2/acquirers/branch/%s', $branchId),
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
            ]
        );
    }

    public function getBranch(string $branchId): Branch
    {
        $response = $this->httpClient->request(
            'GET',
            sprintf('/api/v2/acquirers/branch/%s', $branchId),
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
            ]
        );

        return $this->branchResponseMapper->mapFromResponse($response->getBody()->getContents());
    }

    public function getBranches(ItemsListRequest $itemsListRequest): ItemsListResponse
    {
        $response = $this->httpClient->request(
            'GET',
            '/api/v2/acquirers/branches',
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                'query' => $this->requestJsonMapper->mapToArray($itemsListRequest),
            ]
        );

        return $this->branchListResponseMapper->mapFromResponse($response->getBody()->getContents());
    }

    public function createOffer(string $branchId, Offer $offer): ApiResource
    {
        $response = $this->httpClient->request(
            'POST',
            sprintf(
                '/api/v1/acquirers/branch/%s/offer',
                $branchId
            ),
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                'body' => $this->requestJsonMapper->mapToJson($offer),
            ]
        );

        return $this->createResourceResponseMapper->mapFromResponse($response->getBody()->getContents());
    }

    public function deleteOffer(string $branchId, string $offerId): void
    {
        $this->httpClient->request(
            'DELETE',
            sprintf('/api/v1/acquirers/branch/%s/offer/%s', $branchId, $offerId),
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
            ]
        );
    }

    public function getOffers(string $branchId, ItemsListRequest $itemsListRequest): ItemsListResponse
    {
        $response = $this->httpClient->request(
            'GET',
            sprintf('/api/v1/acquirers/branch/%s/offers', $branchId),
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                'query' => $this->requestJsonMapper->mapToArray($itemsListRequest),
            ]
        );

        return $this->offerListResponseMapper->mapFromResponse($response->getBody()->getContents());
    }

    public function makeOfferRequest(string $branchId, OfferRequest $offerRequest): OfferResponse
    {
        $response = $this->httpClient->request(
            'POST',
            sprintf(
                '/api/v2/acquirers/branch/%s/offer-request/dynamic',
                $branchId
            ),
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                'body' => $this->requestJsonMapper->mapToJson($offerRequest),
            ]
        );

        return $this->offerResponseMapper->mapFromResponse($response->getBody()->getContents());
    }

    public function documentRequest(DocumentRequest $documentRequest): void
    {
        $this->httpClient->request(
            'POST',
            '/api/v1/acquirers/document-request',
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                'body' => $this->requestJsonMapper->mapToJson($documentRequest),
            ]
        );
    }

    public function documentRequestStatus(string $barcode, string $requestId): ?string
    {
        $response = $this->httpClient->request(
            'GET',
            '/api/v1/acquirers/document-request/status',
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                'query' => [
                    'barcode' => $barcode,
                    'requestId' => $requestId,
                ],
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);

        if (($data['status'] ?? null) === null) {
            throw new UnexpectedValueException('Unexpected status response, failed to fetch status.');
        }

        return $data['status'];
    }

    public function offerRequestStatus(string $otp, string $requestId): ?string
    {
        $response = $this->httpClient->request(
            'GET',
            '/api/v1/acquirers/offer-request/status',
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                'query' => [
                    'otp' => $otp,
                    'requestId' => $requestId,
                ],
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);

        if (($data['status'] ?? null) === null) {
            throw new UnexpectedValueException('Unexpected status response, failed to fetch status.');
        }

        return $data['status'];
    }
}
