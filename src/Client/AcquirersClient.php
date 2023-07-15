<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Client;

use GrinchenkoUniversity\Diia\Dto\Acquirers\Branch;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Offer;
use GrinchenkoUniversity\Diia\Dto\ApiResource;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;
use GrinchenkoUniversity\Diia\Dto\Response\CreateResourceResponse;
use GrinchenkoUniversity\Diia\Dto\Response\ItemsListResponse;
use GrinchenkoUniversity\Diia\Mapper\Request\RequestJsonMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ResponseJsonMapper;
use GrinchenkoUniversity\Diia\Provider\HttpHeadersProvider;
use GuzzleHttp\ClientInterface;

class AcquirersClient
{
    private ClientInterface $httpClient;
    private HttpHeadersProvider $httpHeadersProvider;
    private RequestJsonMapper $requestJsonMapper;
    private ResponseJsonMapper $createResourceResponseMapper;
    private ResponseJsonMapper $branchListResponseMapper;
    private ResponseJsonMapper $branchResponseMapper;
    private ResponseJsonMapper $offerResponseMapper;

    public function __construct(
        ClientInterface     $httpClient,
        HttpHeadersProvider $httpHeadersProvider,
        RequestJsonMapper   $requestJsonMapper,
        ResponseJsonMapper  $createResourceResponseMapper,
        ResponseJsonMapper  $branchListResponseMapper,
        ResponseJsonMapper  $branchResponseMapper,
        ResponseJsonMapper  $offerResponseMapper
    ) {
        $this->httpClient = $httpClient;
        $this->httpHeadersProvider = $httpHeadersProvider;
        $this->requestJsonMapper = $requestJsonMapper;
        $this->createResourceResponseMapper = $createResourceResponseMapper;
        $this->branchListResponseMapper = $branchListResponseMapper;
        $this->branchResponseMapper = $branchResponseMapper;
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
}
