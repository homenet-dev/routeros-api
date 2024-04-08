# RouterOS API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/homenet/routeros-api.svg?style=flat-square)](https://packagist.org/packages/homenet/routeros-api)
[![Tests](https://img.shields.io/github/actions/workflow/status/homenet/routeros-api/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/homenet/routeros-api/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/homenet/routeros-api.svg?style=flat-square)](https://packagist.org/packages/homenet/routeros-api)

This is an Service for connecting to RouterOS API.

## Installation

You can install the package via composer:

```bash
composer require homenet.dev/routeros-api
```

## Usage

```php
use HomeNet\RouterosApi\RouterAPI;

$routerApi = RouterAPI::make('host', 'username', 'password');

$routerApi->comm('/interface/print');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Rupadana](https://github.com/homenet.dev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
