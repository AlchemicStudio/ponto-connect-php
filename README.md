# Implementation of the Ponto Connect API in PHP.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alchemicstudio/ponto-connect-php.svg?style=flat-square)](https://packagist.org/packages/alchemicstudio/ponto-connect-php)
[![Tests](https://img.shields.io/github/actions/workflow/status/alchemicstudio/ponto-connect-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/alchemicstudio/ponto-connect-php/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/alchemicstudio/ponto-connect-php.svg?style=flat-square)](https://packagist.org/packages/alchemicstudio/ponto-connect-php)

Implementation of the [Ponto Connect API](https://documentation.ibanity.com/ponto-connect/2/api/curl) in PHP.

## /!\ This package is still in development. /!\

You can find the developpement documentation [here](docs/developpement.md)

## Installation

You can install the package via composer:

```bash
composer require alchemicstudio/ponto-connect-php
```

## Usage

```php
$skeleton = new AlchemicStudio\PontoConnect();
echo $skeleton->echoPhrase('Hello, AlchemicStudio!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Sébastien Denooz](https://github.com/AlchemicStudio)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
