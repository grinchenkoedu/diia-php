<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Client;

use GrinchenkoUniversity\Diia\Dto\Acquirers\Branch;
use GrinchenkoUniversity\Diia\Dto\ApiResource;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;
use GrinchenkoUniversity\Diia\Dto\Response\ItemsListResponse;
use GrinchenkoUniversity\Diia\Exception\DiiaApiException;
use GrinchenkoUniversity\Diia\Http\ApiTransport;
use GrinchenkoUniversity\Diia\Mapper\Acquirers\BranchMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\ItemsListRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ApiResourceMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ItemsListResponseMapper;

/**
 * Branches of the acquirer: /api/v2/acquirers/branch*.
 */
class BranchClient
{
    private ApiTransport $transport;
    private BranchMapper $branchMapper;
    private ItemsListRequestMapper $itemsListRequestMapper;
    private ItemsListResponseMapper $branchListMapper;
    private ApiResourceMapper $apiResourceMapper;

    public function __construct(
        ApiTransport $transport,
        BranchMapper $branchMapper,
        ItemsListRequestMapper $itemsListRequestMapper,
        ItemsListResponseMapper $branchListMapper,
        ApiResourceMapper $apiResourceMapper
    ) {
        $this->transport = $transport;
        $this->branchMapper = $branchMapper;
        $this->itemsListRequestMapper = $itemsListRequestMapper;
        $this->branchListMapper = $branchListMapper;
        $this->apiResourceMapper = $apiResourceMapper;
    }

    /**
     * @throws DiiaApiException
     */
    public function create(Branch $branch): ApiResource
    {
        $data = $this->transport->request(
            'POST',
            '/api/v2/acquirers/branch',
            $this->branchMapper->mapToRequest($branch)
        );

        return $this->apiResourceMapper->mapFromResponse($data);
    }

    /**
     * @throws DiiaApiException
     */
    public function update(string $branchId, Branch $branch): ApiResource
    {
        $data = $this->transport->request(
            'PUT',
            sprintf('/api/v2/acquirers/branch/%s', $branchId),
            $this->branchMapper->mapToRequest($branch)
        );

        return $this->apiResourceMapper->mapFromResponse($data);
    }

    /**
     * @throws DiiaApiException
     */
    public function delete(string $branchId): void
    {
        $this->transport->request('DELETE', sprintf('/api/v2/acquirers/branch/%s', $branchId));
    }

    /**
     * @throws DiiaApiException
     */
    public function get(string $branchId): Branch
    {
        $data = $this->transport->request('GET', sprintf('/api/v2/acquirers/branch/%s', $branchId));

        return $this->branchMapper->mapFromResponse($data);
    }

    /**
     * @throws DiiaApiException
     */
    public function list(ItemsListRequest $itemsListRequest): ItemsListResponse
    {
        $data = $this->transport->request(
            'GET',
            '/api/v2/acquirers/branches',
            null,
            $this->itemsListRequestMapper->mapToRequest($itemsListRequest)
        );

        return $this->branchListMapper->mapFromResponse($data);
    }
}
