<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Client;

use GrinchenkoUniversity\Diia\Dto\Request\OfferRequest;
use GrinchenkoUniversity\Diia\Dto\Response\OfferResponse;
use GrinchenkoUniversity\Diia\Exception\DiiaApiException;
use GrinchenkoUniversity\Diia\Http\ApiTransport;
use GrinchenkoUniversity\Diia\Mapper\Request\OfferRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\OfferResponseMapper;

/**
 * Offer requests (Дія.Підпис, Дія.Шеринг): the deep link a user opens in Diia, and its status.
 */
class OfferRequestClient
{
    private ApiTransport $transport;
    private OfferRequestMapper $offerRequestMapper;
    private OfferResponseMapper $offerResponseMapper;

    public function __construct(
        ApiTransport $transport,
        OfferRequestMapper $offerRequestMapper,
        OfferResponseMapper $offerResponseMapper
    ) {
        $this->transport = $transport;
        $this->offerRequestMapper = $offerRequestMapper;
        $this->offerResponseMapper = $offerResponseMapper;
    }

    /**
     * @throws DiiaApiException
     */
    public function makeDynamic(string $branchId, OfferRequest $offerRequest): OfferResponse
    {
        $data = $this->transport->request(
            'POST',
            sprintf('/api/v2/acquirers/branch/%s/offer-request/dynamic', rawurlencode($branchId)),
            $this->offerRequestMapper->mapToRequest($offerRequest)
        );

        return $this->offerResponseMapper->mapFromResponse($data);
    }

    /**
     * @throws DiiaApiException when the request fails or the response carries no status
     */
    public function status(string $otp, string $requestId): ?string
    {
        $data = $this->transport->request(
            'GET',
            '/api/v1/acquirers/offer-request/status',
            null,
            [
                'otp' => $otp,
                'requestId' => $requestId,
            ]
        );

        return StatusResponse::status($data, '/api/v1/acquirers/offer-request/status');
    }
}
