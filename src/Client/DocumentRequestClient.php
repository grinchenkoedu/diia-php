<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia\Client;

use GrinchenkoUniversity\Diia\Dto\Request\DocumentRequest;
use GrinchenkoUniversity\Diia\Exception\DiiaApiException;
use GrinchenkoUniversity\Diia\Http\ApiTransport;
use GrinchenkoUniversity\Diia\Mapper\Request\DocumentRequestMapper;

/**
 * Document requests by barcode: /api/v1/acquirers/document-request*.
 */
class DocumentRequestClient
{
    private ApiTransport $transport;
    private DocumentRequestMapper $documentRequestMapper;

    public function __construct(ApiTransport $transport, DocumentRequestMapper $documentRequestMapper)
    {
        $this->transport = $transport;
        $this->documentRequestMapper = $documentRequestMapper;
    }

    /**
     * @throws DiiaApiException
     */
    public function request(DocumentRequest $documentRequest): void
    {
        $this->transport->request(
            'POST',
            '/api/v1/acquirers/document-request',
            $this->documentRequestMapper->mapToRequest($documentRequest)
        );
    }

    /**
     * @throws DiiaApiException when the request fails or the response carries no status
     */
    public function status(string $barcode, string $requestId): ?string
    {
        $data = $this->transport->request(
            'GET',
            '/api/v1/acquirers/document-request/status',
            null,
            [
                'barcode' => $barcode,
                'requestId' => $requestId,
            ]
        );

        return StatusResponse::status($data, '/api/v1/acquirers/document-request/status');
    }
}
