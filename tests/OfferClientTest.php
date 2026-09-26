<?php

use GrinchenkoUniversity\Diia\Client\OfferClient;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Offer;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;
use GrinchenkoUniversity\Diia\Dto\Scopes;
use GrinchenkoUniversity\Diia\Enum\ScopesDiiaId;
use GrinchenkoUniversity\Diia\Mapper\Acquirers\OfferMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\ItemsListRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ApiResourceMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ItemsListResponseMapper;
use GrinchenkoUniversity\Diia\Mapper\ScopesMapper;
use GrinchenkoUniversity\Diia\Tests\Support\ApiFixtures;
use PHPUnit\Framework\TestCase;

class OfferClientTest extends TestCase
{
    use ApiFixtures;

    private function client(string $fixture): OfferClient
    {
        $offerMapper = new OfferMapper(new ScopesMapper());

        return new OfferClient(
            $this->transportReplaying($fixture),
            $offerMapper,
            new ItemsListRequestMapper(),
            new ItemsListResponseMapper('offers', $offerMapper),
            new ApiResourceMapper()
        );
    }

    public function testCreate(): void
    {
        $offer = (new Offer('Підписання заяви'))
            ->setReturnLink('https://example.com/return')
            ->setScopes((new Scopes())->addScopes(ScopesDiiaId::NAME, ScopesDiiaId::SCOPES_ALL))
        ;

        $resource = $this->client('create_offer')->create('branch_id', $offer);

        $this->assertRequestMatchesFixture('create_offer');
        $this->assertSame('offer_id', $resource->getId());
    }

    public function testDelete(): void
    {
        $this->client('delete_offer')->delete('branch_id', 'offer_id');

        $this->assertRequestMatchesFixture('delete_offer');
    }

    public function testList(): void
    {
        $listResponse = $this->client('list_offers')->list('branch_id', new ItemsListRequest(100));
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
}
