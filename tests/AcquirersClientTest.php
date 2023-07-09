<?php

use GrinchenkoUniversity\Diia\Client\AcquirersClient;
use GrinchenkoUniversity\Diia\Dto\Request\Acquirers\CreateBranchRequest;
use GrinchenkoUniversity\Diia\Factory\RequestJsonMapperFactory;
use GrinchenkoUniversity\Diia\Mapper\Request\RequestJsonMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\Acquirers\BranchResponseMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\CreateResourceResponseMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ItemsListResponseMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ResponseJsonMapper;
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
        $this->requestJsonMapper = (new RequestJsonMapperFactory())->createDefaultMapper();

        $this->acquirersClient = new AcquirersClient(
            $this->httpClient,
            $this->httpHeadersProvider,
            $this->requestJsonMapper,
            new ResponseJsonMapper(new CreateResourceResponseMapper()),
            new ResponseJsonMapper(
                new ItemsListResponseMapper('branches', new BranchResponseMapper())
            ),
            new ResponseJsonMapper(new BranchResponseMapper())
        );
    }

    public function testCreateBranch()
    {
        $request = new CreateBranchRequest('Name', 'Location', 'Street', '1');
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
                    'body' => $this->requestJsonMapper->mapFromRequest($request),
                ]
            )
            ->willReturn($response)
        ;

        $resource = $this->acquirersClient->createBranch($request);

        $this->assertEquals('xLm0g93Ghg329NhQj235hAsg32', $resource->getId());
    }
}