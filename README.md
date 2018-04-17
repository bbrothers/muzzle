# Muzzle

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

An experiment into the usefulness of assertions on Guzzle Requests and Responses. This code is mostly pulled from hack sessions and is not fully tested or ready for production use.

## Install

Via Composer

``` bash
$ composer require bbrothers/muzzle
```

## Usage

``` php
$client = Muzzle::builder()
                ->post('https://example.com')
                ->replyWith(new Response(201))
                ->get('https://example.com')
                ->build();

$this->assertInstanceOf(HttPhake::class, $client);
$client->post('https://example.com')->assertStatus(HttpStatus::CREATED);
$client->get('https://example.com')->assertStatus(HttpStatus::OK);
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email brad@bradbrothers.ca instead of using the issue tracker.

## Credits

- [Brad Brothers][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/bbrothers/muzzle.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/bbrothers/muzzle/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/bbrothers/muzzle.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/bbrothers/muzzle.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/bbrothers/muzzle.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/bbrothers/muzzle
[link-travis]: https://travis-ci.org/bbrothers/muzzle
[link-scrutinizer]: https://scrutinizer-ci.com/g/bbrothers/muzzle/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/bbrothers/muzzle
[link-downloads]: https://packagist.org/packages/bbrothers/muzzle
[link-author]: https://github.com/bbrothers
[link-contributors]: ../../contributors
