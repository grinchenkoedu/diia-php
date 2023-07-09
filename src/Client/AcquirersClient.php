<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Client;

use GrinchenkoUniversity\Diia\Dto\Request\Acquirers\CreateBranchRequest;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;
use GrinchenkoUniversity\Diia\Dto\Response\Acquirers\BranchResponse;
use GrinchenkoUniversity\Diia\Dto\Response\CreateResourceResponse;
use GrinchenkoUniversity\Diia\Dto\Response\ItemsListResponse;
use GrinchenkoUniversity\Diia\Dto\Response\ResponseInterface;
use GrinchenkoUniversity\Diia\Mapper\Request\RequestJsonMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ResponseJsonMapper;
use GrinchenkoUniversity\Diia\Provider\HttpHeadersProvider;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

class AcquirersClient
{
    private ClientInterface $httpClient;
    private HttpHeadersProvider $httpHeadersProvider;
    private RequestJsonMapper $requestJsonMapper;
    private ResponseJsonMapper $createResourceResponseMapper;
    private ResponseJsonMapper $branchListResponseMapper;
    private ResponseJsonMapper $branchResponseMapper;

    public function __construct(
        ClientInterface     $httpClient,
        HttpHeadersProvider $httpHeadersProvider,
        RequestJsonMapper   $requestJsonMapper,
        ResponseJsonMapper  $createResourceResponseMapper,
        ResponseJsonMapper  $branchListResponseMapper,
        ResponseJsonMapper  $branchResponseMapper
    ) {
        $this->httpClient = $httpClient;
        $this->httpHeadersProvider = $httpHeadersProvider;
        $this->requestJsonMapper = $requestJsonMapper;
        $this->createResourceResponseMapper = $createResourceResponseMapper;
        $this->branchListResponseMapper = $branchListResponseMapper;
        $this->branchResponseMapper = $branchResponseMapper;
    }

    /**
     * @param CreateBranchRequest $createBranchRequest
     *
     * @return CreateResourceResponse|ResponseInterface
     *
     * @throws GuzzleException
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function createBranch(CreateBranchRequest $createBranchRequest): CreateResourceResponse
    {
        $response = $this->httpClient->request(
            'POST',
            '/api/v2/acquirers/branch',
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                'body' => $this->requestJsonMapper->mapFromRequest($createBranchRequest),
            ]
        );

        return $this->createResourceResponseMapper->mapFromResponse($response->getBody()->getContents());
    }

    /**
     * @param string $branchId
     * @param CreateBranchRequest $createBranchRequest
     *
     * @return CreateResourceResponse|ResponseInterface
     *
     * @throws GuzzleException
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function updateBranch(string $branchId, CreateBranchRequest $createBranchRequest): CreateResourceResponse
    {
        $response = $this->httpClient->request(
            'PUT',
            sprintf('/api/v2/acquirers/branch/%s', $branchId),
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                'body' => $this->requestJsonMapper->mapFromRequest($createBranchRequest),
            ]
        );

        return $this->createResourceResponseMapper->mapFromResponse($response->getBody()->getContents());
    }

    public function deleteBranch(string $branchId, CreateBranchRequest $createBranchRequest): void
    {
        $this->httpClient->request(
            'DELETE',
            sprintf('/api/v2/acquirers/branch/%s', $branchId),
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
            ]
        );
    }

    /**
     * @param string $branchId
     *
     * @return BranchResponse|ResponseInterface
     *
     * @throws GuzzleException
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getBranch(string $branchId): BranchResponse
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

    /**
     * @param ItemsListRequest $itemsListRequest
     *
     * @return ItemsListResponse|ResponseInterface
     *
     * @throws GuzzleException
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getBranches(ItemsListRequest $itemsListRequest): ItemsListResponse
    {
        $response = $this->httpClient->request(
            'GET',
            '/api/v2/acquirers/branches',
            [
                'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                'query' => $this->requestJsonMapper->mapFromRequest($itemsListRequest),
            ]
        );

        return $this->branchListResponseMapper->mapFromResponse($response->getBody()->getContents());
    }
}
