<?php

use GrinchenkoUniversity\Diia\Client\AcquirersClient;
use GrinchenkoUniversity\Diia\Dependency\DependencyResolver;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Branch;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Offer;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;
use GrinchenkoUniversity\Diia\Mapper\Acquirers\BranchMapper;
use GrinchenkoUniversity\Diia\Mapper\Acquirers\OfferMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\ItemsListRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\RequestJsonMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ApiResourceMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ItemsListResponseMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ResponseJsonMapper;
use GrinchenkoUniversity\Diia\Mapper\ScopesMapper;
use GrinchenkoUniversity\Diia\Provider\BearerTokenProvider;
use GrinchenkoUniversity\Diia\Provider\HttpHeadersProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

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

        $scopesMapper = new ScopesMapper([
            'diiaId' => ['hashedFilesSigning'],
        ]);

        $branchMapper = new BranchMapper($scopesMapper);
        $offerMapper = new OfferMapper($scopesMapper);

        $dependencyResolver = (new DependencyResolver())
            ->addDependency(new ItemsListRequestMapper())
            ->addDependency($branchMapper)
            ->addDependency($offerMapper)
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
            new ResponseJsonMapper($offerMapper)
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
}