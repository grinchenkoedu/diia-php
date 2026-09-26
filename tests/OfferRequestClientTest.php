<?php

use GrinchenkoUniversity\Diia\Client\OfferRequestClient;
use GrinchenkoUniversity\Diia\Dto\Request\OfferRequest;
use GrinchenkoUniversity\Diia\Exception\DiiaApiException;
use GrinchenkoUniversity\Diia\Mapper\Request\OfferRequestMapper;
use GrinchenkoUniversity\Diia\Mapper\Response\OfferResponseMapper;
use GrinchenkoUniversity\Diia\Tests\Support\ApiFixtures;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class OfferRequestClientTest extends TestCase
{
    use ApiFixtures;

    /**
     * @param string|Response $response
     */
    private function client($response): OfferRequestClient
    {
        return new OfferRequestClient(
            $this->transportReplaying($response),
            new OfferRequestMapper(),
            new OfferResponseMapper()
        );
    }

    public function testMakeDynamicForSigning(): void
    {
        $offerRequest = (new OfferRequest('offer_id', 'request_id'))
            ->setSignAlgo(OfferRequest::SIGN_ALGO_ECDSA)
            ->addFile('test', 'MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTI=')
        ;

        $offerResponse = $this->client('offer_request_signing')->makeDynamic('branch_id', $offerRequest);

        $this->assertRequestMatchesFixture('offer_request_signing');
        $this->assertSame(
            'https://diia.app/acquirers/branch/offer/offer-request/uuid4',
            $offerResponse->getDeepLink()
        );
    }

    public function testMakeDynamicForSharing(): void
    {
        $offerRequest = (new OfferRequest('offer_id', 'request_id'))
            ->setUseDiia(true)
            ->setReturnLink('https://example.com/return')
        ;

        $offerResponse = $this->client('offer_request_sharing')->makeDynamic('branch_id', $offerRequest);

        $this->assertRequestMatchesFixture('offer_request_sharing');
        $this->assertSame(
            'https://diia.app/acquirers/branch/offer/offer-request/sharing',
            $offerResponse->getDeepLink()
        );
    }

    public function testEscapesBranchIdInPath(): void
    {
        $this->client('offer_request_sharing')->makeDynamic('../x', new OfferRequest('offer_id', 'request_id'));

        $this->assertSame(
            '/api/v2/acquirers/branch/..%2Fx/offer-request/dynamic',
            $this->recordedRequest(0)->getUri()->getPath()
        );
    }

    public function testStatus(): void
    {
        $status = $this->client('offer_request_status')->status('otp', 'request_id');

        $this->assertRequestMatchesFixture('offer_request_status');
        $this->assertSame('processing', $status);
    }

    public function testStatusMissingFromResponse(): void
    {
        $this->expectException(DiiaApiException::class);

        $this->client(new Response(200, [], '{}'))->status('otp', 'request_id');
    }
}
