# Diia (Дія) API Client for PHP
![CI workflow](https://github.com/grinchenkoedu/diia-php/actions/workflows/tests.yml/badge.svg)

## Tests and development
1. Install vendors
```bash
docker run --rm -v $(pwd):/app -w /app composer:lts composer install
```
2. Run tests
```bash
docker run --rm -v $(pwd):/app -w /app composer:lts vendor/bin/phpunit
```