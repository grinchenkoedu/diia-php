<?php

declare(strict_types=1);

namespace GrinchenkoUniversity\Diia;

use GrinchenkoUniversity\Diia\Client\AuthClient;
use GrinchenkoUniversity\Diia\Client\BranchClient;
use GrinchenkoUniversity\Diia\Client\DocumentRequestClient;
use GrinchenkoUniversity\Diia\Client\OfferClient;
use GrinchenkoUniversity\Diia\Client\OfferRequestClient;
use GrinchenkoUniversity\Diia\Dto\Credentials;
use GrinchenkoUniversity\Diia\Dto\Scopes;
use GrinchenkoUniversity\Diia\Http\ApiTransport;
use GrinchenkoUniversity\Diia\Mapper\Acquirers\BranchMapper;
use GrinchenkoUniversity\Diia\Mapper\Acquirers\OfferMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\DocumentRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\ItemsListRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\OfferRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ApiResourceMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ItemsListResponseMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\OfferResponseMapper;
use GrinchenkoUniversity\Diia\Mapper\ScopesMapper;
use GrinchenkoUniversity\Diia\Provider\BearerTokenProvider;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Builds the API clients. All clients made by one factory share one bearer token.
 *
 * The HTTP client carries the Diia API base URI and the timeouts.
 */
class ClientFactory
{
    private ClientInterface $httpClient;
    private Credentials $credentials;
    private CacheInterface $cache;
    private ?LoggerInterface $logger;
    private ?ApiTransport $transport = null;

    public function __construct(
        ClientInterface $httpClient,
        Credentials $credentials,
        CacheInterface $cache,
        ?LoggerInterface $logger = null
    ) {
        $this->httpClient = $httpClient;
        $this->credentials = $credentials;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param Scopes $defaultScopes sent for branches created or updated without their own scopes
     */
    public function branchClient(Scopes $defaultScopes): BranchClient
    {
        $branchMapper = new BranchMapper(new ScopesMapper($defaultScopes));

        return new BranchClient(
            $this->transport(),
            $branchMapper,
            new ItemsListRequestMapper(),
            new ItemsListResponseMapper('branches', $branchMapper),
            new ApiResourceMapper()
        );
    }

    /**
     * @param Scopes $defaultScopes sent for offers created without their own scopes
     */
    public function offerClient(Scopes $defaultScopes): OfferClient
    {
        $offerMapper = new OfferMapper(new ScopesMapper($defaultScopes));

        return new OfferClient(
            $this->transport(),
            $offerMapper,
            new ItemsListRequestMapper(),
            new ItemsListResponseMapper('offers', $offerMapper),
            new ApiResourceMapper()
        );
    }

    public function offerRequestClient(): OfferRequestClient
    {
        return new OfferRequestClient($this->transport(), new OfferRequestMapper(), new OfferResponseMapper());
    }

    public function documentRequestClient(): DocumentRequestClient
    {
        return new DocumentRequestClient($this->transport(), new DocumentRequestMapper());
    }

    private function transport(): ApiTransport
    {
        if ($this->transport === null) {
            $this->transport = new ApiTransport(
                $this->httpClient,
                new BearerTokenProvider(new AuthClient($this->httpClient), $this->credentials, $this->cache),
                $this->logger
            );
        }

        return $this->transport;
    }
}
