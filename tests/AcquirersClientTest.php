<?php

use GrinchenkoUniversity\Diia\Client\AcquirersClient;
use GrinchenkoUniversity\Diia\Dependency\DependencyResolver;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Branch;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Offer;
use GrinchenkoUniversity\Diia\Dto\Request\DocumentRequest;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;
use GrinchenkoUniversity\Diia\Dto\Request\OfferRequest;
use GrinchenkoUniversity\Diia\Dto\Scopes;
use GrinchenkoUniversity\Diia\Enum\ScopesDiiaId;
use GrinchenkoUniversity\Diia\Enum\ScopesSharing;
use GrinchenkoUniversity\Diia\Mapper\Acquirers\BranchMapper;
use GrinchenkoUniversity\Diia\Mapper\Acquirers\OfferMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\DocumentRequestMapper;
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
use GrinchenkoUniversity\Diia\Tests\Support\ApiFixtures;
use PHPUnit\Framework\TestCase;

/**
 * Freezes the 1.0 behaviour: every public method against tests/fixtures/requests.
 * The 2.0 clients are tested against the same fixtures.
 */
class AcquirersClientTest extends TestCase
{
    use ApiFixtures;

    private function client(string $fixture): AcquirersClient
    {
        $tokenProvider = $this->createConfiguredMock(
            BearerTokenProvider::class,
            [
                'getToken' => 'eyJ...ePg',
            ]
        );

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
            ->addDependency(new DocumentRequestMapper())
        ;

        return new AcquirersClient(
            $this->httpClientReplaying($fixture),
            new HttpHeadersProvider($tokenProvider),
            new RequestJsonMapper($dependencyResolver),
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
        $resource = $this->client('create_branch')->createBranch(new Branch('Name', 'Location', 'Street', '1'));

        $this->assertRequestMatchesFixture('create_branch');
        $this->assertSame('xLm0g93Ghg329NhQj235hAsg32', $resource->getId());
    }

    public function testUpdateBranch()
    {
        $branch = (new Branch('Назва', 'м. Вишневе', 'вул. Київська', '2л'))
            ->setCustomFullName('Custom fullname')
            ->setCustomFullAddress('Custom fulladdress')
            ->setEmail('acquirer@email.com')
            ->setRegion('Київська обл.')
            ->setDistrict('Києво-Святошинський р-н')
            ->setScopes((new Scopes())->addScopes(ScopesDiiaId::NAME, ScopesDiiaId::SCOPES_ALL))
        ;

        $resource = $this->client('update_branch')->updateBranch('xLm0g93Ghg329NhQj235hAsg32', $branch);

        $this->assertRequestMatchesFixture('update_branch');
        $this->assertSame('xLm0g93Ghg329NhQj235hAsg32', $resource->getId());
    }

    public function testDeleteBranch()
    {
        $this->client('delete_branch')->deleteBranch('xLm0g93Ghg329NhQj235hAsg32');

        $this->assertRequestMatchesFixture('delete_branch');
    }

    public function testGetBranch()
    {
        $branch = $this->client('get_branch')->getBranch('xLm0g93Ghg329NhQj235hAsg32');

        $this->assertRequestMatchesFixture('get_branch');
        $this->assertBranch($branch);
    }

    public function testGetBranches()
    {
        $listResponse = $this->client('list_branches')->getBranches(new ItemsListRequest(2));

        $this->assertRequestMatchesFixture('list_branches');
        $this->assertSame(20, $listResponse->getTotal());
        $this->assertCount(1, $listResponse->getItems());
        $this->assertBranch($listResponse->getItems()[0]);
    }

    public function testCreateOffer()
    {
        $offer = (new Offer('Підписання заяви'))
            ->setReturnLink('https://example.com/return')
            ->setScopes((new Scopes())->addScopes(ScopesDiiaId::NAME, ScopesDiiaId::SCOPES_ALL))
        ;

        $resource = $this->client('create_offer')->createOffer('branch_id', $offer);

        $this->assertRequestMatchesFixture('create_offer');
        $this->assertSame('offer_id', $resource->getId());
    }

    public function testDeleteOffer()
    {
        $this->client('delete_offer')->deleteOffer('branch_id', 'offer_id');

        $this->assertRequestMatchesFixture('delete_offer');
    }

    public function testGetOffers()
    {
        $listResponse = $this->client('list_offers')->getOffers('branch_id', new ItemsListRequest(100));
        $items = $listResponse->getItems();

        $this->assertRequestMatchesFixture('list_offers');
        $this->assertSame(2, $listResponse->getTotal());
        $this->assertCount(2, $items);
        $this->assertInstanceOf(Offer::class, $items[0]);
        $this->assertSame('6de1...a4d7', $items[0]->getId());
        $this->assertSame('Поділитися паспортом', $items[0]->getName());
        $this->assertSame('https://example.com/return', $items[0]->getReturnLink());
        $this->assertSame(['sharing' => ['passport']], $items[0]->getScopes()->getAll());
        $this->assertSame('0dc97...d1cc633a81a', $items[1]->getId());
    }

    public function testMakeOfferRequestForSigning()
    {
        $offerRequest = (new OfferRequest('offer_id', 'request_id'))
            ->setSignAlgo(OfferRequest::SIGN_ALGO_ECDSA)
            ->addFile('test', 'MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTI=')
        ;

        $offerResponse = $this->client('offer_request_signing')->makeOfferRequest('branch_id', $offerRequest);

        $this->assertRequestMatchesFixture('offer_request_signing');
        $this->assertSame(
            'https://diia.app/acquirers/branch/offer/offer-request/uuid4',
            $offerResponse->getDeepLink()
        );
    }

    public function testMakeOfferRequestForSharing()
    {
        $offerRequest = (new OfferRequest('offer_id', 'request_id'))
            ->setUseDiia(true)
            ->setReturnLink('https://example.com/return')
        ;

        $offerResponse = $this->client('offer_request_sharing')->makeOfferRequest('branch_id', $offerRequest);

        $this->assertRequestMatchesFixture('offer_request_sharing');
        $this->assertSame(
            'https://diia.app/acquirers/branch/offer/offer-request/sharing',
            $offerResponse->getDeepLink()
        );
    }

    public function testOfferRequestStatus()
    {
        $status = $this->client('offer_request_status')->offerRequestStatus('otp', 'request_id');

        $this->assertRequestMatchesFixture('offer_request_status');
        $this->assertSame('processing', $status);
    }

    public function testDocumentRequest()
    {
        $this->client('document_request')->documentRequest(
            new DocumentRequest('branch_id', '3535267635434', 'request_id')
        );

        $this->assertRequestMatchesFixture('document_request');
    }

    public function testDocumentRequestStatus()
    {
        $status = $this->client('document_request_status')->documentRequestStatus('3535267635434', 'request_id');

        $this->assertRequestMatchesFixture('document_request_status');
        $this->assertSame('success', $status);
    }

    private function assertBranch($branch): void
    {
        $this->assertInstanceOf(Branch::class, $branch);
        $this->assertSame('xLm0g93Ghg329NhQj235hAsg32', $branch->getId());
        $this->assertSame('Назва', $branch->getName());
        $this->assertSame('acquirer@email.com', $branch->getEmail());
        $this->assertSame('Custom fullname', $branch->getCustomFullName());
        $this->assertSame('Custom fulladdress', $branch->getCustomFullAddress());
        $this->assertSame('Київська обл.', $branch->getRegion());
        $this->assertSame('Києво-Святошинський р-н', $branch->getDistrict());
        $this->assertSame('м. Вишневе', $branch->getLocation());
        $this->assertSame('вул. Київська', $branch->getStreet());
        $this->assertSame('2л', $branch->getHouse());
        $this->assertSame(['diiaId' => ['hashedFilesSigning']], $branch->getScopes()->getAll());
    }
}
