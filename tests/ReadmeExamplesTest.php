<?php

use GrinchenkoUniversity\Diia\Exception\DiiaApiException;
use GrinchenkoUniversity\Diia\Tests\Support\ApiFixtures;
use GrinchenkoUniversity\Diia\Tests\Support\InMemoryCache;
use GuzzleHttp\Psr7\Response;
use Matasar\Euspe\Hasher;
use Matasar\Euspe\SignatureVerifier;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Runs every ```php block of README.md, in order, against a mocked Diia API,
 * so the examples cannot drift from the library again.
 */
class ReadmeExamplesTest extends TestCase
{
    use ApiFixtures;
    use InMemoryCache;

    private const README = __DIR__ . '/../README.md';

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/Support/euspe-stubs.php';
    }

    /**
     * @return string[]
     */
    private static function phpBlocks(): array
    {
        preg_match_all('/^```php\r?\n(.*?)^```\r?$/ms', (string) file_get_contents(self::README), $matches);

        return $matches[1];
    }

    public function testStartsWithTitleAndNotice(): void
    {
        $lines = preg_split('/\r?\n/', (string) file_get_contents(self::README));

        $this->assertSame('# Diia (Дія) API client for PHP', $lines[0]);
        $this->assertSame('', $lines[1]);
        $this->assertSame('> [!NOTE]', $lines[2]);
        $this->assertSame(
            '> This is a community library. It is not developed, endorsed or supported by Diia or the Ministry of Digital Transformation of Ukraine.',
            $lines[3]
        );
    }

    public function testCodeFencesAreBalanced(): void
    {
        $readme = (string) file_get_contents(self::README);

        $this->assertDoesNotMatchRegularExpression('/^````/m', $readme, 'a four-backtick fence');
        $this->assertSame(0, preg_match_all('/^```/m', $readme) % 2, 'an unclosed fence');
    }

    public function testExamplesRun(): void
    {
        $blocks = self::phpBlocks();
        $this->assertCount(7, $blocks);

        $handler = $this->handlerReplaying(
            new Response(200, [], '{"token": "eyJ...ePg"}'),
            // Branches
            new Response(200, [], '{"_id": "branch_id"}'),
            new Response(200, [], '{"_id": "branch_id"}'),
            'get_branch',
            'list_branches',
            // Errors
            new Response(404, [], '{"message": "Branch not found", "code": 404}'),
            // Дія.Підпис
            new Response(200, [], '{"_id": "signing_offer_id"}'),
            'offer_request_signing',
            // Дія.Шеринг
            new Response(200, [], '{"_id": "sharing_offer_id"}'),
            'offer_request_sharing',
            // Cleaning up
            new Response(204),
            new Response(204)
        );

        // What the README leaves to the application.
        $config = [
            'diia_api_url' => 'https://api.diia.test',
            'diia_timeout' => 10.0,
            'diia_connect_timeout' => 5.0,
            'diia_acquirer_token' => 'acquirerToken',
            'diia_auth_acquirer_token' => null,
        ];
        $cache = $this->inMemoryCache();
        $logger = new NullLogger();
        $requestId = '6f1c1d9e-3d0b-4c43-9b53-2d5b1f0f8a11';
        $signature = "\x30\x82signature";
        $requestBody = json_encode([
            'encodeData' => base64_encode(json_encode([
                'signedItems' => [
                    ['name' => 'application.pdf', 'signature' => base64_encode($signature)],
                ],
            ])),
        ]);

        $blocks[0] = str_replace('new Client([', 'new Client([\'handler\' => $handler,', $blocks[0], $replaced);
        $this->assertSame(1, $replaced, 'the client initialization block builds one Guzzle client');

        foreach ($blocks as $block) {
            eval($block);
        }

        $this->assertSame(
            [
                'GET /api/v1/auth/acquirer/acquirerToken',
                'POST /api/v2/acquirers/branch',
                'PUT /api/v2/acquirers/branch/branch_id',
                'GET /api/v2/acquirers/branch/branch_id',
                'GET /api/v2/acquirers/branches',
                'GET /api/v2/acquirers/branch/branch_id',
                'POST /api/v1/acquirers/branch/branch_id/offer',
                'POST /api/v2/acquirers/branch/branch_id/offer-request/dynamic',
                'POST /api/v1/acquirers/branch/branch_id/offer',
                'POST /api/v2/acquirers/branch/branch_id/offer-request/dynamic',
                'DELETE /api/v1/acquirers/branch/branch_id/offer/signing_offer_id',
                'DELETE /api/v2/acquirers/branch/branch_id',
            ],
            array_map(
                function (int $index): string {
                    $request = $this->recordedRequest($index);

                    return $request->getMethod() . ' ' . $request->getUri()->getPath();
                },
                range(0, $this->recordedRequestCount() - 1)
            )
        );

        // Branches: the list came back.
        $this->assertCount(1, $branches->getItems());

        // Errors: the 404 surfaced as DiiaApiException.
        $this->assertInstanceOf(DiiaApiException::class, $exception);
        $this->assertSame(404, $exception->getStatusCode());

        // Дія.Підпис: the euspe hash went out base64-encoded, with the caller's request ID.
        $signingRequest = json_decode((string) $this->recordedRequest(7)->getBody(), true);
        $this->assertSame('signing_offer_id', $signingRequest['offerId']);
        $this->assertSame($requestId, $signingRequest['requestId']);
        $this->assertSame('DSTU', $signingRequest['signAlgo']);
        $this->assertSame(
            [['fileName' => 'application.pdf', 'fileHash' => base64_encode("\x01\x02raw-hash:/path/to/application.pdf")]],
            $signingRequest['data']['hashedFilesSigning']['hashedFiles']
        );
        $this->assertSame(['/path/to/application.pdf'], Hasher::$hashedFiles);

        // Дія.Шеринг: the deep link of the last offer request.
        $this->assertSame('https://diia.app/acquirers/branch/offer/offer-request/sharing', $deepLink);

        // Callbacks: the signature was checked, raw, against the raw hash that was sent.
        $this->assertSame(
            [[$signature, "\x01\x02raw-hash:/path/to/application.pdf"]],
            SignatureVerifier::$verified
        );
    }
}
