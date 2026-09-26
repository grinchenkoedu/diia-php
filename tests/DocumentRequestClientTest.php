<?php

use GrinchenkoUniversity\Diia\Client\DocumentRequestClient;
use GrinchenkoUniversity\Diia\Dto\Request\DocumentRequest;
use GrinchenkoUniversity\Diia\Exception\DiiaApiException;
use GrinchenkoUniversity\Diia\Mapper\Request\DocumentRequestMapper;
use GrinchenkoUniversity\Diia\Tests\Support\ApiFixtures;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class DocumentRequestClientTest extends TestCase
{
    use ApiFixtures;

    /**
     * @param string|Response $response
     */
    private function client($response): DocumentRequestClient
    {
        return new DocumentRequestClient($this->transportReplaying($response), new DocumentRequestMapper());
    }

    public function testRequest(): void
    {
        $this->client('document_request')->request(
            new DocumentRequest('branch_id', '3535267635434', 'request_id')
        );

        $this->assertRequestMatchesFixture('document_request');
    }

    public function testStatus(): void
    {
        $status = $this->client('document_request_status')->status('3535267635434', 'request_id');

        $this->assertRequestMatchesFixture('document_request_status');
        $this->assertSame('success', $status);
    }

    public function testStatusMissingFromResponse(): void
    {
        $this->expectException(DiiaApiException::class);

        $this->client(new Response(200, [], '{"status": null}'))->status('3535267635434', 'request_id');
    }
}
