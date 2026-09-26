<?php

use GrinchenkoUniversity\Diia\Client\BranchClient;
use GrinchenkoUniversity\Diia\Dto\Acquirers\Branch;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;
use GrinchenkoUniversity\Diia\Dto\Scopes;
use GrinchenkoUniversity\Diia\Enum\ScopesDiiaId;
use GrinchenkoUniversity\Diia\Enum\ScopesSharing;
use GrinchenkoUniversity\Diia\Exception\DiiaApiException;
use GrinchenkoUniversity\Diia\Mapper\Acquirers\BranchMapper;
use GrinchenkoUniversity\Diia\Mapper\Request\ItemsListRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ApiResourceMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\ItemsListResponseMapper;
use GrinchenkoUniversity\Diia\Mapper\ScopesMapper;
use GrinchenkoUniversity\Diia\Tests\Support\ApiFixtures;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class BranchClientTest extends TestCase
{
    use ApiFixtures;

    /**
     * @param string|Response ...$responses
     */
    private function client(...$responses): BranchClient
    {
        $defaultScopes = (new Scopes())
            ->addScopes(ScopesDiiaId::NAME, ScopesDiiaId::SCOPES_ALL)
            ->addScopes(ScopesSharing::NAME, ScopesSharing::SCOPES_ALL)
        ;
        $branchMapper = new BranchMapper(new ScopesMapper($defaultScopes));

        return new BranchClient(
            $this->transportReplaying(...$responses),
            $branchMapper,
            new ItemsListRequestMapper(),
            new ItemsListResponseMapper('branches', $branchMapper),
            new ApiResourceMapper()
        );
    }

    public function testCreate(): void
    {
        $resource = $this->client('create_branch')->create(new Branch('Name', 'Location', 'Street', '1'));

        $this->assertRequestMatchesFixture('create_branch');
        $this->assertSame('xLm0g93Ghg329NhQj235hAsg32', $resource->getId());
    }

    public function testUpdate(): void
    {
        $branch = (new Branch('Назва', 'м. Вишневе', 'вул. Київська', '2л'))
            ->setCustomFullName('Custom fullname')
            ->setCustomFullAddress('Custom fulladdress')
            ->setEmail('acquirer@email.com')
            ->setRegion('Київська обл.')
            ->setDistrict('Києво-Святошинський р-н')
            ->setScopes((new Scopes())->addScopes(ScopesDiiaId::NAME, ScopesDiiaId::SCOPES_ALL))
        ;

        $resource = $this->client('update_branch')->update('xLm0g93Ghg329NhQj235hAsg32', $branch);

        $this->assertRequestMatchesFixture('update_branch');
        $this->assertSame('xLm0g93Ghg329NhQj235hAsg32', $resource->getId());
    }

    public function testDelete(): void
    {
        $this->client('delete_branch')->delete('xLm0g93Ghg329NhQj235hAsg32');

        $this->assertRequestMatchesFixture('delete_branch');
    }

    public function testGet(): void
    {
        $branch = $this->client('get_branch')->get('xLm0g93Ghg329NhQj235hAsg32');

        $this->assertRequestMatchesFixture('get_branch');
        self::assertBranch($branch);
    }

    public function testList(): void
    {
        $listResponse = $this->client('list_branches')->list(new ItemsListRequest(2));

        $this->assertRequestMatchesFixture('list_branches');
        $this->assertSame(20, $listResponse->getTotal());
        $this->assertCount(1, $listResponse->getItems());
        self::assertBranch($listResponse->getItems()[0]);
    }

    public function testListOfNone(): void
    {
        $listResponse = $this->client(new Response(200, [], '{"branches": [], "total": 0}'))
            ->list(new ItemsListRequest())
        ;

        $this->assertSame(0, $listResponse->getTotal());
        $this->assertSame([], $listResponse->getItems());
    }

    public function testNotFound(): void
    {
        $this->expectException(DiiaApiException::class);
        $this->expectExceptionCode(404);

        $this->client(new Response(404, [], '{"message": "Branch not found"}'))->get('missing');
    }

    public function testEscapesBranchIdInPath(): void
    {
        $this->client('get_branch')->get('a/../b?c');

        // Unescaped, this would resolve to /api/v2/acquirers/b with a query string.
        $this->assertSame('/api/v2/acquirers/branch/a%2F..%2Fb%3Fc', $this->recordedRequest(0)->getUri()->getPath());
        $this->assertSame('', $this->recordedRequest(0)->getUri()->getQuery());
    }

    public static function assertBranch($branch): void
    {
        self::assertInstanceOf(Branch::class, $branch);
        self::assertSame('xLm0g93Ghg329NhQj235hAsg32', $branch->getId());
        self::assertSame('Назва', $branch->getName());
        self::assertSame('acquirer@email.com', $branch->getEmail());
        self::assertSame('Custom fullname', $branch->getCustomFullName());
        self::assertSame('Custom fulladdress', $branch->getCustomFullAddress());
        self::assertSame('Київська обл.', $branch->getRegion());
        self::assertSame('Києво-Святошинський р-н', $branch->getDistrict());
        self::assertSame('м. Вишневе', $branch->getLocation());
        self::assertSame('вул. Київська', $branch->getStreet());
        self::assertSame('2л', $branch->getHouse());
        self::assertSame(['diiaId' => ['hashedFilesSigning']], $branch->getScopes()->getAll());
    }
}
