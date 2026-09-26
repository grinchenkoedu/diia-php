<?php

use GrinchenkoUniversity\Diia\Client\BranchClient;
use GrinchenkoUniversity\Diia\Client\DocumentRequestClient;
use GrinchenkoUniversity\Diia\Client\OfferClient;
use GrinchenkoUniversity\Diia\Client\OfferRequestClient;
use GrinchenkoUniversity\Diia\ClientFactory;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Branch;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Offer;
use GrinchenkoUniversity\Diia\Dto\Credentials;
use GrinchenkoUniversity\Diia\Dto\Request\DocumentRequest;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;
use GrinchenkoUniversity\Diia\Dto\Request\OfferRequest;
use GrinchenkoUniversity\Diia\Dto\Scopes;
use GrinchenkoUniversity\Diia\Enum\ScopesDiiaId;
use GrinchenkoUniversity\Diia\Enum\ScopesSharing;
use GrinchenkoUniversity\Diia\Tests\Support\ApiFixtures;
use GrinchenkoUniversity\Diia\Tests\Support\InMemoryCache;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientFactoryTest extends TestCase
{
    use ApiFixtures;
    use InMemoryCache;

    public function testEveryClientIsWiredAndSharesOneToken(): void
    {
        $factory = new ClientFactory(
            $this->httpClientReplaying(
                new Response(200, [], '{"token": "eyJ...ePg"}'),
                'create_branch',
                'list_branches',
                'list_offers',
                'offer_request_sharing',
                'document_request_status'
            ),
            new Credentials('acquirerToken', 'authAcquirerToken'),
            $this->inMemoryCache()
        );
        $allScopes = (new Scopes())
            ->addScopes(ScopesDiiaId::NAME, ScopesDiiaId::SCOPES_ALL)
            ->addScopes(ScopesSharing::NAME, ScopesSharing::SCOPES_ALL)
        ;

        $factory->branchClient($allScopes)->create(new Branch('Name', 'Location', 'Street', '1'));
        $branches = $factory->branchClient($allScopes)->list(new ItemsListRequest(2));
        $offers = $factory->offerClient(new Scopes())->list('branch_id', new ItemsListRequest(100));
        $factory->offerRequestClient()->makeDynamic(
            'branch_id',
            (new OfferRequest('offer_id', 'request_id'))
                ->setUseDiia(true)
                ->setReturnLink('https://example.com/return')
        );
        $status = $factory->documentRequestClient()->status('3535267635434', 'request_id');

        $this->assertSame('/api/v1/auth/acquirer/acquirerToken', $this->recordedRequest(0)->getUri()->getPath());
        $this->assertSame('Basic authAcquirerToken', $this->recordedRequest(0)->getHeaderLine('Authorization'));
        $this->assertRequestMatchesFixture('create_branch', 1);
        $this->assertRequestMatchesFixture('list_branches', 2);
        $this->assertRequestMatchesFixture('list_offers', 3);
        $this->assertRequestMatchesFixture('offer_request_sharing', 4);
        $this->assertRequestMatchesFixture('document_request_status', 5);
        $this->assertSame(6, $this->recordedRequestCount());

        BranchClientTest::assertBranch($branches->getItems()[0]);
        $this->assertInstanceOf(Offer::class, $offers->getItems()[0]);
        $this->assertSame('success', $status);
    }

    public function testDefaultScopesArePerClient(): void
    {
        $factory = new ClientFactory(
            $this->httpClientReplaying('create_offer'),
            new Credentials('acquirerToken', null),
            $this->inMemoryCache(['token' => 'eyJ...ePg'])
        );
        $signingOnly = (new Scopes())->addScopes(ScopesDiiaId::NAME, ScopesDiiaId::SCOPES_ALL);

        $factory->offerClient($signingOnly)->create(
            'branch_id',
            (new Offer('Підписання заяви'))->setReturnLink('https://example.com/return')
        );

        $this->assertRequestMatchesFixture('create_offer');
    }

    /**
     * @dataProvider clientClasses
     */
    public function testNoConstructorTakesTwoParametersOfTheSameClass(string $class): void
    {
        $types = [];

        foreach ((new ReflectionClass($class))->getConstructor()->getParameters() as $parameter) {
            // getName(), not a string cast: casting ReflectionType is deprecated on PHP 7.4.
            $types[] = $parameter->getType()->getName();
        }

        $this->assertSame(array_unique($types), $types, $class);
    }

    public function clientClasses(): Generator
    {
        yield [BranchClient::class];
        yield [OfferClient::class];
        yield [OfferRequestClient::class];
        yield [DocumentRequestClient::class];
        yield [ClientFactory::class];
    }
}
