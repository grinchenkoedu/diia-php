# Diia (Дія) API client for PHP

> [!NOTE]
> This is a community library. It is not developed, endorsed or supported by Diia or the Ministry of Digital Transformation of Ukraine.

[![Tests](https://github.com/grinchenkoedu/diia-php/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/grinchenkoedu/diia-php/actions/workflows/tests.yml?query=branch%3Amain)
[![PHP](https://img.shields.io/packagist/dependency-v/grinchenkoedu/diia-php/php)](https://packagist.org/packages/grinchenkoedu/diia-php)
[![Packagist](https://img.shields.io/packagist/v/grinchenkoedu/diia-php)](https://packagist.org/packages/grinchenkoedu/diia-php)

A client for the Diia acquirer API: branches, offers, and offer requests for
Дія.Підпис (signing) and Дія.Шеринг (document sharing). PHP 7.4 or newer.

## Installation
```shell
composer require grinchenkoedu/diia-php
```

## Recommended libraries
- [ramsey/uuid](https://github.com/ramsey/uuid) - for request IDs (UUID4)
- [matasarei/euspe](https://github.com/matasarei/euspe) `^2.0` - hashing documents, verifying signatures, opening sharing envelopes

## Examples
The examples run in order: each one uses variables from the ones before it.
`tests/ReadmeExamplesTest.php` runs every PHP block of this file, so they stay in step with the code.

### Client initialization
Build the factory once, in a service provider or your container, and take the clients from it.
Clients from one factory share one bearer token, kept in the PSR-16 cache you pass; it is refreshed
before it expires, and once more if Diia rejects it with a 401.
```php
use GrinchenkoUniversity\Diia\ClientFactory;
use GrinchenkoUniversity\Diia\Dto\Credentials;
use GrinchenkoUniversity\Diia\Dto\Scopes;
use GrinchenkoUniversity\Diia\Enum\ScopesDiiaId;
use GrinchenkoUniversity\Diia\Enum\ScopesSharing;
use GuzzleHttp\Client;

$factory = new ClientFactory(
    new Client([
        'base_uri' => $config['diia_api_url'],
        'timeout' => $config['diia_timeout'],                 // seconds
        'connect_timeout' => $config['diia_connect_timeout'], // seconds
    ]),
    new Credentials($config['diia_acquirer_token'], $config['diia_auth_acquirer_token']),
    $cache, // any Psr\SimpleCache\CacheInterface
    $logger // optional Psr\Log\LoggerInterface
);

// Scopes a branch or an offer gets when you do not set its own.
$defaultScopes = (new Scopes())
    ->addScopes(ScopesDiiaId::NAME, ScopesDiiaId::SCOPES_ALL)
    ->addScopes(ScopesSharing::NAME, [ScopesSharing::SCOPE_INTERNAL_PASSPORT]);

$branchClient = $factory->branchClient($defaultScopes);
$offerClient = $factory->offerClient($defaultScopes);
$offerRequestClient = $factory->offerRequestClient();
```

### Branches
A branch is the part of your organization users see in Diia.
```php
use GrinchenkoUniversity\Diia\Dto\Acquirers\Branch;
use GrinchenkoUniversity\Diia\Dto\Request\ItemsListRequest;

$branch = new Branch(
    'Head office', // name
    'Kyiv',        // location
    'Main street', // street
    '1'            // house
);

$branchId = $branchClient->create($branch)->getId();

$branchClient->update($branchId, $branch->setEmail('office@example.com'));
$branch = $branchClient->get($branchId);
$branches = $branchClient->list(new ItemsListRequest(10, 0)); // limit, skip
```
Changing a branch's scopes: in our experience (observed in 2024, with 1.x) Diia did not apply new
scopes to an existing branch, and the branch had to be deleted and created again. This is not confirmed
by Diia's current documentation; check it on the partner portal before relying on either behaviour.

### Errors
Failed calls throw `DiiaApiException`: an HTTP error, a response the client cannot read, or no
response at all.
```php
use GrinchenkoUniversity\Diia\Exception\DiiaApiException;

try {
    $branchClient->get($branchId);
} catch (DiiaApiException $exception) {
    $exception->getStatusCode();   // HTTP status; 0 when no response arrived
    $exception->getErrorCode();    // the "code" of Diia's error body, if it has one
    $exception->getResponseBody(); // the raw body, for your logs
}
```

### Дія.Підпис
Create the offer once and keep its ID. Then, for every document, hash it, send the hash, and give the
user the deep link.
```php
use GrinchenkoUniversity\Diia\Dto\Acquirers\Offer;
use GrinchenkoUniversity\Diia\Dto\Request\OfferRequest;
use GrinchenkoUniversity\Diia\Dto\Scopes;
use GrinchenkoUniversity\Diia\Enum\ScopesDiiaId;
use Matasar\Euspe\EusignSession;
use Matasar\Euspe\Hasher;

$offer = (new Offer('Signing an application'))
    ->setScopes((new Scopes())->addScopes(ScopesDiiaId::NAME, [ScopesDiiaId::SCOPE_HASHED_FILES_SIGNING]));
$offerId = $offerClient->create($branchId, $offer)->getId();

// euspe returns the hash as raw binary; Diia takes it base64-encoded.
$session = new EusignSession();
$session->open();
$documentHash = base64_encode((new Hasher($session))->hashFile('/path/to/application.pdf'));

// Keep the hashes with the request ID: the callback is checked against them.
$hashes = ['application.pdf' => $documentHash];

$offerRequest = (new OfferRequest(
    $offerId,
    $requestId // a UUID4 you generate and store for this request
))
    ->addFile('application.pdf', $documentHash)
    ->setSignAlgo(OfferRequest::SIGN_ALGO_DSTU)
    ->setReturnLink('https://example.com/return'); // optional

// Send the user here, or show it as a QR code.
$deepLink = $offerRequestClient->makeDynamic($branchId, $offerRequest)->getDeepLink();
```

### Дія.Шеринг
```php
use GrinchenkoUniversity\Diia\Dto\Acquirers\Offer;
use GrinchenkoUniversity\Diia\Dto\Request\OfferRequest;
use GrinchenkoUniversity\Diia\Dto\Scopes;
use GrinchenkoUniversity\Diia\Enum\ScopesSharing;

$offer = (new Offer('Share your ID card to finish enrolment'))
    ->setScopes((new Scopes())->addScopes(ScopesSharing::NAME, [ScopesSharing::SCOPE_INTERNAL_PASSPORT]));
$sharingOfferId = $offerClient->create($branchId, $offer)->getId();

$offerRequest = (new OfferRequest($sharingOfferId, $requestId))
    ->setUseDiia(true)
    ->setReturnLink('https://example.com/return'); // optional

$deepLink = $offerRequestClient->makeDynamic($branchId, $offerRequest)->getDeepLink();
```

### Callbacks
This library covers the calls you make to Diia. Diia's calls back to you — the signed hashes and the
shared documents — are yours to receive. What they carry:

- an `X-Document-Request-Trace-Id` header that identifies the request. Validate it before you use it
  in a key or a file name;
- a body with `encodeData`:
  - **signing**: base64 of a JSON object whose `signedItems` list holds `name` (the file name you sent)
    and `signature` (base64);
  - **sharing**: an encrypted envelope, plus the documents themselves as multipart file uploads.

What the receiver must do:

- **Verify every signature** against the hash you stored for that file, with euspe. A callback is just
  an HTTP request; without the check, anyone who can reach the URL can "sign" your documents.
- **Open the sharing envelope** with your acquirer key (euspe's `EnvelopeDeveloper`).
- **Never store an upload under the name the client sent.** Generate the name, and keep the files in a
  directory your web server does not serve.
- **Answer fast and process asynchronously.** Store the request, return 200, and do the rest in a
  background job.

The check for a signing callback, in that background job:
```php
use Matasar\Euspe\SignatureVerifier;

// $requestBody: the raw body Diia sent; $hashes: what you stored for this request ID.
$payload = json_decode($requestBody, true);
$signed = json_decode(base64_decode($payload['encodeData']), true);

$verifier = new SignatureVerifier($session); // an opened EusignSession, as above

foreach ($signed['signedItems'] as $item) {
    if (!isset($hashes[$item['name']])) {
        throw new UnexpectedValueException('The callback names a file this request did not send.');
    }

    // Both arguments are raw binary. Throws Matasar\Euspe\Exception\VerificationException
    // when the signature is not over this hash: reject the callback then.
    $signInfo = $verifier->verifyHash(base64_decode($item['signature']), base64_decode($hashes[$item['name']]));

    // Verified: store $item['signature'] with the document.
}
```

### Cleaning up
```php
$offerClient->delete($branchId, $offerId);
$branchClient->delete($branchId);
```

## Upgrading from 1.x
2.0 replaces `AcquirersClient` with one client per API resource, built by `ClientFactory`.
Replace the manual wiring (`DependencyResolver`, `RequestJsonMapper`, the five `ResponseJsonMapper`s,
`HttpHeadersProvider`) with the [client initialization](#client-initialization) above, then rename
the calls:

| 1.x `AcquirersClient`                        | 2.0                                             |
|----------------------------------------------|-------------------------------------------------|
| `createBranch(Branch)`                       | `BranchClient::create(Branch)`                  |
| `updateBranch($branchId, Branch)`            | `BranchClient::update($branchId, Branch)`       |
| `deleteBranch($branchId)`                    | `BranchClient::delete($branchId)`               |
| `getBranch($branchId)`                       | `BranchClient::get($branchId)`                  |
| `getBranches(ItemsListRequest)`              | `BranchClient::list(ItemsListRequest)`          |
| `createOffer($branchId, Offer)`              | `OfferClient::create($branchId, Offer)`         |
| `deleteOffer($branchId, $offerId)`           | `OfferClient::delete($branchId, $offerId)`      |
| `getOffers($branchId, ItemsListRequest)`     | `OfferClient::list($branchId, ItemsListRequest)`|
| `makeOfferRequest($branchId, OfferRequest)`  | `OfferRequestClient::makeDynamic($branchId, OfferRequest)` |
| `offerRequestStatus($otp, $requestId)`       | `OfferRequestClient::status($otp, $requestId)`  |
| `documentRequest(DocumentRequest)`           | `DocumentRequestClient::request(DocumentRequest)` |
| `documentRequestStatus($barcode, $requestId)`| `DocumentRequestClient::status($barcode, $requestId)` |

HTTP errors, transport errors and unreadable responses are now `DiiaApiException` instead of Guzzle
exceptions or `UnexpectedValueException`.
The DTOs, enums, endpoints and request bodies are unchanged. See [CHANGELOG.md](CHANGELOG.md).

## Tests and development
```bash
docker run --rm -v "$PWD":/app -w /app composer:lts sh -c 'composer install && vendor/bin/phpunit'
```
