# Diia (Дія) API Client for PHP

## Tests and development
1. Prepare image
```bash
docker build -t diia-php .
```
2. Install vendors
```bash
docker run --rm -v $(pwd):/app -w /app diia-php composer install
```
3. Run tests
```bash
docker run --rm -v $(pwd):/app -w /app diia-php vendor/bin/phpunit
```