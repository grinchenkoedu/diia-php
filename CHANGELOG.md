# Changelog

## 2.0.0 — unreleased

### Changed
- `AcquirersClient` is split into one client per API resource, each built by `ClientFactory`
  and each taking one typed mapper per job, so two mappers can no longer be swapped by mistake:

  | 1.x `AcquirersClient`                         | 2.0                                                      |
  |-----------------------------------------------|----------------------------------------------------------|
  | `createBranch(Branch)`                        | `BranchClient::create(Branch)`                           |
  | `updateBranch($branchId, Branch)`             | `BranchClient::update($branchId, Branch)`                |
  | `deleteBranch($branchId)`                     | `BranchClient::delete($branchId)`                        |
  | `getBranch($branchId)`                        | `BranchClient::get($branchId)`                           |
  | `getBranches(ItemsListRequest)`               | `BranchClient::list(ItemsListRequest)`                   |
  | `createOffer($branchId, Offer)`               | `OfferClient::create($branchId, Offer)`                  |
  | `deleteOffer($branchId, $offerId)`            | `OfferClient::delete($branchId, $offerId)`               |
  | `getOffers($branchId, ItemsListRequest)`      | `OfferClient::list($branchId, ItemsListRequest)`         |
  | `makeOfferRequest($branchId, OfferRequest)`   | `OfferRequestClient::makeDynamic($branchId, OfferRequest)` |
  | `offerRequestStatus($otp, $requestId)`        | `OfferRequestClient::status($otp, $requestId)`           |
  | `documentRequest(DocumentRequest)`            | `DocumentRequestClient::request(DocumentRequest)`        |
  | `documentRequestStatus($barcode, $requestId)` | `DocumentRequestClient::status($barcode, $requestId)`    |

- Build the clients with `new ClientFactory($guzzle, $credentials, $cache, $logger)` and its
  `branchClient($defaultScopes)`, `offerClient($defaultScopes)`, `offerRequestClient()` and
  `documentRequestClient()`. Clients from one factory share one bearer token.
- Every failed API call throws `Exception\DiiaApiException` (`getStatusCode()`, `getErrorCode()`,
  `getResponseBody()`): HTTP errors and transport errors, which were Guzzle exceptions, and
  unreadable responses or a status response without a status, which were `UnexpectedValueException`.
  A request that got no response has status code 0. Still `UnexpectedValueException`: a list
  response without its items key, and a token response without a token. A DTO that cannot be
  encoded as JSON (a string that is not valid UTF-8) throws `JsonException` before any request
  is sent; 1.x sent an empty body.
- `BearerTokenProvider` caches the token for 7200 − 300 seconds instead of its full lifetime,
  and has `invalidate()`. A 401 from the API drops the token, fetches a new one and retries the
  call once.
- Requires `guzzlehttp/guzzle ^7.8`; accepts `psr/simple-cache` 1–3 and `psr/log` 1–3;
  `"php": "^7.4 || ^8.0"`. CI runs the tests on PHP 7.4 and 8.0–8.5.

### Removed
- `Client\AcquirersClient`
- `Dependency\DependencyResolver`, `Dependency\SupportedDependencyInterface`,
  `Dependency\UnresolvedDependencyException`, and `isSupported()` on every mapper
- `Mapper\Request\RequestJsonMapper`, `Mapper\Response\ResponseJsonMapper`: JSON is encoded and
  decoded by the new `Http\ApiTransport`
- `Provider\HttpHeadersProvider`: the headers are set by `Http\ApiTransport`

### Unchanged
- Endpoints, HTTP methods, query strings, headers and JSON bodies: 2.0 sends what 1.x sent,
  checked against the 1.x requests recorded in `tests/fixtures/requests/`.
- The DTOs and enums: `Branch`, `Offer`, `OfferRequest`, `DocumentRequest`, `ItemsListRequest`,
  `ApiResource`, `ItemsListResponse`, `OfferResponse`, `Scopes`, `ScopesDiiaId`, `ScopesSharing`.

## 1.0.0 — 2024-08-26
- First release.
