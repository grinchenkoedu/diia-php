<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Client;

use GrinchenkoUniversity\Diia\Dto\Acquirers\Offer;
use GrinchenkoUniversity\Diia\Dto\ApiResource;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;
use GrinchenkoUniversity\Diia\Dto\Response\ItemsListResponse;
use GrinchenkoUniversity\Diia\Exception\DiiaApiException;
use GrinchenkoUniversity\Diia\Http\ApiTransport;
use GrinchenkoUniversity\Diia\Mapper\Acquirers\OfferMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\ItemsListRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ApiResourceMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ItemsListResponseMapper;

/**
 * Offers of a branch: /api/v1/acquirers/branch/{branchId}/offer*.
 */
class OfferClient
{
    private ApiTransport $transport;
    private OfferMapper $offerMapper;
    private ItemsListRequestMapper $itemsListRequestMapper;
    private ItemsListResponseMapper $offerListMapper;
    private ApiResourceMapper $apiResourceMapper;

    public function __construct(
        ApiTransport $transport,
        OfferMapper $offerMapper,
        ItemsListRequestMapper $itemsListRequestMapper,
        ItemsListResponseMapper $offerListMapper,
        ApiResourceMapper $apiResourceMapper
    ) {
        $this->transport = $transport;
        $this->offerMapper = $offerMapper;
        $this->itemsListRequestMapper = $itemsListRequestMapper;
        $this->offerListMapper = $offerListMapper;
        $this->apiResourceMapper = $apiResourceMapper;
    }

    /**
     * @throws DiiaApiException
     */
    public function create(string $branchId, Offer $offer): ApiResource
    {
        $data = $this->transport->request(
            'POST',
            sprintf('/api/v1/acquirers/branch/%s/offer', $branchId),
            $this->offerMapper->mapToRequest($offer)
        );

        return $this->apiResourceMapper->mapFromResponse($data);
    }

    /**
     * @throws DiiaApiException
     */
    public function delete(string $branchId, string $offerId): void
    {
        $this->transport->request(
            'DELETE',
            sprintf('/api/v1/acquirers/branch/%s/offer/%s', $branchId, $offerId)
        );
    }

    /**
     * @throws DiiaApiException
     */
    public function list(string $branchId, ItemsListRequest $itemsListRequest): ItemsListResponse
    {
        $data = $this->transport->request(
            'GET',
            sprintf('/api/v1/acquirers/branch/%s/offers', $branchId),
            null,
            $this->itemsListRequestMapper->mapToRequest($itemsListRequest)
        );

        return $this->offerListMapper->mapFromResponse($data);
    }
}
