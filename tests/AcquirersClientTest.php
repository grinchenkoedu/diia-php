<?php

use GrinchenkoUniversity\Diia\Client\AcquirersClient;
use GrinchenkoUniversity\Diia\Dependency\DependencyResolver;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Branch;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Offer;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;
use GrinchenkoUniversity\Diia\Dto\Request\OfferRequest;
use GrinchenkoUniversity\Diia\Mapper\Acquirers\BranchMapper;
use GrinchenkoUniversity\Diia\Mapper\Acquirers\OfferMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\ItemsListRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\OfferRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\RequestJsonMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ApiResourceMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ItemsListResponseMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\OfferResponseMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ResponseJsonMapper;
use GrinchenkoUniversity\Diia\Mapper\ScopesMapper;
use GrinchenkoUniversity\Diia\Provider\BearerTokenProvider;
use GrinchenkoUniversity\Diia\Provider\HttpHeadersProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use GrinchenkoUniversity\Diia\Dto\Scopes;
use GrinchenkoUniversity\Diia\Enum\ScopesDiiaId;
use GrinchenkoUniversity\Diia\Enum\ScopesSharing;

class AcquirersClientTest extends TestCase
{
    private Client $httpClient;
    private HttpHeadersProvider $httpHeadersProvider;
    private RequestJsonMapper $requestJsonMapper;
    private AcquirersClient $acquirersClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(Client::class);
        $tokenProvider = $this->createConfiguredMock(
            BearerTokenProvider::class,
            [
                'getToken' => 'eyJ...ePg',
            ]
        );
        $this->httpHeadersProvider = new HttpHeadersProvider($tokenProvider);

        $defaultScopes = new Scopes();
        $defaultScopes
            ->addScopes(ScopesDiiaId::NAME, ScopesDiiaId::SCOPES_ALL)
            ->addScopes(ScopesSharing::NAME, ScopesSharing::SCOPES_ALL)
        ;
        $scopesMapper = new ScopesMapper($defaultScopes);

        $branchMapper = new BranchMapper($scopesMapper);
        $offerMapper = new OfferMapper($scopesMapper);

        $dependencyResolver = (new DependencyResolver())
            ->addDependency(new ItemsListRequestMapper())
            ->addDependency($branchMapper)
            ->addDependency($offerMapper)
            ->addDependency(new OfferRequestMapper())
        ;
        $this->requestJsonMapper = new RequestJsonMapper($dependencyResolver);

        $this->acquirersClient = new AcquirersClient(
            $this->httpClient,
            $this->httpHeadersProvider,
            $this->requestJsonMapper,
            new ResponseJsonMapper(new ApiResourceMapper()),
            new ResponseJsonMapper(
                new ItemsListResponseMapper('branches', $branchMapper)
            ),
            new ResponseJsonMapper($branchMapper),
            new ResponseJsonMapper(
                new ItemsListResponseMapper('offers', $offerMapper)
            ),
            new ResponseJsonMapper(new OfferResponseMapper())
        );
    }

    public function testCreateBranch()
    {
        $request = new Branch('Name', 'Location', 'Street', '1');
        $response = new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            '{"_id": "xLm0g93Ghg329NhQj235hAsg32"}'
        );

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/api/v2/acquirers/branch',
                [
                    'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                    'body' => $this->requestJsonMapper->mapToJson($request),
                ]
            )
            ->willReturn($response)
        ;

        $resource = $this->acquirersClient->createBranch($request);

        $this->assertEquals('xLm0g93Ghg329NhQj235hAsg32', $resource->getId());
    }

    public function testGetBranches()
    {
        $response = new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            <<<EOF
            {
              "branches": [
                {
                  "_id": "xLm0g93Ghg329NhQj235hAsg32",
                  "name": "Назва",
                  "email": "acquirer@email.com",
                  "customFullName": "Custom fullname",
                  "customFullAddress": "Custom fulladdress",
                  "region": "Київська обл.",
                  "district": "Києво-Святошинський р-н",
                  "location": "м. Вишневе",
                  "street": "вул. Київська",
                  "house": "2л",
                  "scopes": {
                    "diiaId": [
                      "hashedFilesSigning"
                    ]
                  },
                  "offerRequestType": "dynamic",
                  "deliveryTypes": [
                    "api"
                  ]
                }
              ],
              "total": 20
            }
            EOF
        );

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/api/v2/acquirers/branches',
                [
                    'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                    'query' => [
                        'skip' => 0,
                        'limit' => 2,
                    ],
                ]
            )
            ->willReturn($response)
        ;

        $listResponse = $this->acquirersClient->getBranches(new ItemsListRequest(2));
        $items = $listResponse->getItems();

        $this->assertCount(1, $items);
        $this->assertInstanceOf(Branch::class, $items[0]);
    }

    public function testGetBranch()
    {
        $response = new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            <<<EOF
            {
              "_id": "xLm0g93Ghg329NhQj235hAsg32",
              "name": "Назва",
              "email": "acquirer@email.com",
              "customFullName": "Custom fullname",
              "customFullAddress": "Custom fulladdress",
              "region": "Київська обл.",
              "district": "Києво-Святошинський р-н",
              "location": "м. Вишневе",
              "street": "вул. Київська",
              "house": "2л",
              "scopes": {
                "diiaId": [
                  "hashedFilesSigning"
                ]
              },
              "offerRequestType": "dynamic",
              "deliveryTypes": [
                "api"
              ]
            }
            EOF
        );

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/api/v2/acquirers/branch/xLm0g93Ghg329NhQj235hAsg32',
                [
                    'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                ]
            )
            ->willReturn($response)
        ;

        $branchResponse = $this->acquirersClient->getBranch('xLm0g93Ghg329NhQj235hAsg32');
        $this->assertInstanceOf(Branch::class, $branchResponse);
    }

    public function testUpdateBranch()
    {
        $request = new Branch('Name', 'Location', 'Street', '1');
        $response = new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            '{"_id": "xLm0g93Ghg329NhQj235hAsg32"}'
        );

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'PUT',
                '/api/v2/acquirers/branch/xLm0g93Ghg329NhQj235hAsg32',
                [
                    'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                    'body' => $this->requestJsonMapper->mapToJson($request),
                ]
            )
            ->willReturn($response)
        ;

        $resource = $this->acquirersClient->updateBranch(
            'xLm0g93Ghg329NhQj235hAsg32',
            new Branch('Name', 'Location', 'Street', '1')
        );

        $this->assertEquals('xLm0g93Ghg329NhQj235hAsg32', $resource->getId());
    }

    public function testDeleteBranch()
    {
        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'DELETE',
                '/api/v2/acquirers/branch/xLm0g93Ghg329NhQj235hAsg32',
                [
                    'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                ]
            )
            ->willReturn(new Response(204))
        ;

        $this->acquirersClient->deleteBranch('xLm0g93Ghg329NhQj235hAsg32');
    }

    public function testCreateOffer()
    {
        $offer = new Offer('Name');
        $response = new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            '{"_id": "offer_id"}'
        );

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/api/v1/acquirers/branch/branch_id/offer',
                [
                    'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                    'body' => $this->requestJsonMapper->mapToJson($offer),
                ]
            )
            ->willReturn($response)
        ;

        $resource = $this->acquirersClient->createOffer('branch_id', $offer);

        $this->assertEquals('offer_id', $resource->getId());
    }

    public function testDeleteOffer()
    {
        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'DELETE',
                '/api/v1/acquirers/branch/branch_id/offer/offer_id',
                [
                    'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                ]
            )
            ->willReturn(new Response(204))
        ;

        $this->acquirersClient->deleteOffer('branch_id', 'offer_id');
    }

    public function testGetOffers()
    {
        $response = new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            <<<EOF
            {
                "total": 2,
                "offers": [
                   {
                       "_id": "27924...ecb42f44bec9",
                       "name": "Підписання документа",
                       "scopes": { "diiaId": ["hashedFilesSigning"] },
                       "returnLink": "1"
                   },
                   {
                       "_id": "0dc97...d1cc633a81a",
                       "name": "Підписання заяви",
                       "scopes": { "diiaId": ["hashedFilesSigning"] },
                       "returnLink": "1"
                    }
                ]
            }
            EOF
        );

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/api/v1/acquirers/branch/branch_id/offers',
                [
                    'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                    'query' => [
                        'skip' => 0,
                        'limit' => 100,
                    ],
                ]
            )
            ->willReturn($response)
        ;

        $listResponse = $this->acquirersClient->getOffers('branch_id', new ItemsListRequest(100));
        $items = $listResponse->getItems();

        $this->assertCount(2, $items);
        $this->assertInstanceOf(Offer::class, $items[0]);
    }

    public function testOfferRequest()
    {
        $offerRequest = new OfferRequest('offer_id', 'request_id');
        $offerRequest
            ->setSignAlgo(OfferRequest::SIGN_ALGO_ECDSA)
            ->addFile('test', 'MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTI=')
        ;

        $response = new Response(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            '{"deeplink": "https://diia.app/acquirers/branch/offer/offer-request/uuid4"}'
        );

        $this
            ->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                '/api/v2/acquirers/branch/branch_id/offer-request/dynamic',
                [
                    'headers' => $this->httpHeadersProvider->getDefaultHeaders(),
                    'body' => $this->requestJsonMapper->mapToJson($offerRequest),
                ]
            )
            ->willReturn($response)
        ;

        $offerResponse = $this->acquirersClient->makeOfferRequest('branch_id', $offerRequest);

        $this->assertEquals(
            'https://diia.app/acquirers/branch/offer/offer-request/uuid4',
            $offerResponse->getDeepLink()
        );
    }
}