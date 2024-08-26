# [Unofficial] Diia (Дія) API Client for PHP
![CI workflow](https://github.com/grinchenkoedu/diia-php/actions/workflows/tests.yml/badge.svg)

This library is not official, no parts are provided or approved by Diia developers.

## Installation
```shell
composer require grinchenkoedu/diia-php
````

## Recommended libraries
- [ramsey/uuid](https://github.com/ramsey/uuid) - for UUID generation
- [matasarei/euspe](https://github.com/matasarei/euspe) - cryptographic library

## Examples
The client library does not implement callback handlers, only API calls.

### Client initialization
The next code should be encapsulated in a service provider or a factory.
```php
/** @var \GuzzleHttp\Client $httpClient */
$httpClient = new Client([
    'base_uri' => 'http://api.dev.lan', // replace with actual API URL
    'timeout'  => 2.0,
]);

$tokenProvider = new BearerTokenProvider(
    new AuthClient($httpClient),
    new Credentials('token', 'auth_token'),
    $cacheInterface // PSR cache interface
);

$globalScopes = (new Scopes())
    ->addScopes(ScopesDiiaId::NAME, ScopesDiiaId::SCOPES_ALL)
    ->addScopes(ScopesSharing::NAME, [
        // any sharing scopes if needed
    ])
;

$scopesMapper = new ScopesMapper($globalScopes);
$branchMapper = new BranchMapper($scopesMapper);
$offerMapper = new OfferMapper($scopesMapper);

$dependencyResolver = (new DependencyResolver())
    ->addDependency(new ItemsListRequestMapper())
    ->addDependency($branchMapper)
    ->addDependency($offerMapper)
    ->addDependency(new OfferRequestMapper())
;
$requestJsonMapper = new RequestJsonMapper($dependencyResolver);

$client = new AcquirersClient(
    $httpClient,
    new HttpHeadersProvider($tokenProvider),
    $requestJsonMapper,
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
```
### Branches
```php
// Branch of the organization
$branch = new Branch(
    'Branch name', // like "Head Office"
    'Branch location', // like "City"
    'Branch street', // like "Street"
    'Branch building' // like "1"
);

$resource = $client->createBranch($branch);
$branchId = $resource->getId();

// The branches can be also updated or deleted.
// To update scopes branches must be removed and created again (see $globalScopes).
$client->updateBranch($branchId, $branch);
$client->deleteBranch($branchId);
```

### Дія.Підпис
```php
// Define scopes.
$scopes = new Scopes();
$scopes->addScopes(
    ScopesDiiaId::NAME, [
        ScopesDiiaId::SCOPE_HASHED_FILES_SIGNING,
    ]
);

// Add a new offer.
$offer = new Offer('Document description');
$offer->setScopes($scopes);

/** @var \GrinchenkoUniversity\Diia\Dto\ApiResource $apiResource */
$apiResource = $client->createOffer($branchId, $offer);
// You need to save the ID of the offer for future use.
$offerId = $apiResource->getId();

$offerRequest = new OfferRequest(
    $offerId,
    $requestId // UUID4, must be previously generated and saved
);

$crypto = new Matasar\Euspe\Crypto(); // from matasarei/euspe
$documentHash = $crypto->hashFile('/path/to/file.pdf'); // not only PDF

$offerRequest
    ->addFile('filename', $documentHash)
    ->setSignAlgo(OfferRequest::SIGN_ALGO_DSTU)
    ->setReturnLink('https://example.com/return') // users return link, optional
;

$offerResponse = $client->makeOfferRequest($branchId, $offerId);

// Users deep link to sign the document.
var_dump($offerResponse->getDeepLink());
```

### Дія.Шеринг
```php
// Define scopes
$scopes = new Scopes();
$scopes->addScopes(ScopesSharing::NAME, [
    // list of scopes (documents) to share
])

$offer = new Offer('Reason to get document');
$offer->setScopes($scopes);

/** @var \GrinchenkoUniversity\Diia\Dto\ApiResource $apiResource */
$apiResource = $client->createOffer($brancId, $offer);

$offerRequest = new OfferRequest(
    $apiResource->getId(), // you need to save the ID of the offer for future use
    $requestId // UUID4, must be previously generated and saved
);
$offerRequest
    ->setUseDiia(true)
    ->setReturnLink('https://example.com/return') // users return link, optional
;

/** @var \GrinchenkoUniversity\Diia\Dto\Response\OfferResponse $offerResponse */
$offerResponse = $client->makeOfferRequest($brancId, $offerRequest);

// Users deep link to share document(s).
var_dump($offerResponse->getDeepLink());
```

## Tests and development
1. Install vendors
```bash
docker run --rm -v $(pwd):/app -w /app composer:lts composer install
```
2. Run tests
```bash
docker run --rm -v $(pwd):/app -w /app composer:lts vendor/bin/phpunit
```