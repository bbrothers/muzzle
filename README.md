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

```bash
$ composer require bbrothers/muzzle
```

## Usage

Use the fluent builder to define a set of expected requests and mock responses:
```php
$client = Muzzle::builder()
                ->post('https://example.com/contact')
                ->json(['name' => 'Jane Doe'])
                ->replyWith(new Response(HttpStatus::CREATED))
                ->get('https://example.com/contact')
                ->query(['name' => 'Jane Doe'])
                ->build();

$this->assertInstanceOf(Muzzle::class, $client);
$client->post('https://example.com');
$client->get('https://example.com');
```
If not specified, requests will default to a `GET` with an empty URI and responses will default to an empty `200`.

The `expect` method can be used to pass pre-built `Exception` instances:
```php
$createUser = (new Expectation)
    ->post('users')
    ->json(['name' => 'Jane', 'email' => "j.doe@example.com"])
    ->replyWith((new ResponseBuilder)->setJson(User::make([
        'name' => 'Jane', 
        'email' => "j.doe@example.com"
    ])->toArray());

$client = Muzzle::builder()->expect($createUser)->build();
```

Expectations can also be added directly to the `Muzzle` instance by using the `append` method:
```php
$client = new Muzzle;
$expectations = [];
for ($i = 0; $i < 10; $i++) {
    $expectations[] = (new Expectation)
        ->get("users/{$i}")
        ->replyWith((new ResponseBuilder)->setJson(['number' => $i]));
}
$client->append(...$expectations);
```

`Muzzle` assertions should be run at the end of a test by calling the `makeAssertions` method.
This can be automated by including the `MuzzleIntegration` trait in your test or base `TestCase` file. `Muzzle` stores a reference to all instances in the `Container` class, which allows us to call `Muzzle::close` to execute any outstanding assertions:
```php
class TestCase extends PHPUnit\Framework\TestCase
{
    use Muzzle\PHPUnit\MuzzleIntegrations;
}
``` 
The container can also be cleared without running assertions using the `Muzzle::flush` method.

By default `Muzzle` will run assertions that:
- assert all expected requests were made
- assert the expected request URI matches the actual request URI (including a configured `base_uri`)
- assert the expected request method matches the actual request method
- assert the expected request query (if provided) is contained in the actual request query
- assert the expected request body (if provided) is contained in the actual request body
- assert the expected request headers (if provided) are contained in the actual request headers

Custom assertion rules can be added to an `Expectation` by calling the `should` method with a `callable` that implements the `Assertion` interface. When the `Assertion` is run, the recorded request will be passed to the `__invkoke` method as an `AssertableRequest` instance. The `Muzzle` instance is also passed as an optional second parameter.
```php
class ContainJson implements Assertion {
   public function __consturct(array $content) 
   {
       $this->expected = $expected;
   }
   public function __invoke(AssertableRequest $actual) : void
   {
        $actual->assertJson($this->expected);
   }
}
// then

(new Expectation)->should(new ContainJson(['name' => 'Jane Doe']));
``` 
Or as just a callback:
```php
$expected = ['name' => 'Jane Doe'];
(new Expectation)->should(function (AssertableRequest $actual) use ($expected) : void {
    $actual->assertJson($expected);
});
``` 

Additional assertions can also be run on any responses from `Muzzle` or on requests/responses from the transaction history:
```php
$client = Muzzle::builder()
                ->post('https://example.com/contact')
                ->json(['name' => 'Jane Doe'])
                ->replyWith(new Response(HttpStatus::CREATED))
                ->get('http://example.com/contact')
                ->query(['name' => 'Jane Doe'])
                ->replyWith(new Response(HttpStatus::MOVED_PERMANENTLY))
                ->build();

$this->assertInstanceOf(Muzzle::class, $client);
$client->post('https://example.com/contact')->assertSuccessful();
$client->get('http://example.com/contact')->assertRedirect('https://example.com/contact');

$client->lastRequest()->assertUriQueryNotHasKey('age');
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

```bash
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
