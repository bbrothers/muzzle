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
If not specified responses will default to an empty `200`.

The `expect` method can be used to pass pre-built `Exception` instances:
```php
$createUser = (new Expectation)
    ->post('users')
    ->json(['name' => 'Jane', 'email' => 'j.doe@example.com'])
    ->replyWith((new ResponseBuilder)->setJson(User::make([
        'name' => 'Jane', 
        'email' => 'j.doe@example.com'
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

By default `Muzzle` will expect that a request was made and return an empty `200` response.

There are several pre-defined expectations available on the builder or `Expectation` class directly:
- `method`: accepts a variadic list of HTTP methods and asserts the actual request method is in the provided list.
- `uri`: accepts a URI, path or regex pattern to match the actual request against.
- `headers`: accepts an array of headers. They can be either the header name or a key/value pair of header name/expected value and will assert that all headers match the provided values.
- `query`: accepts an array of query parameters expected to be contained in the request. Parameters should be passed as an associative array of `[$name => $value]`, with the value optionally being a regex pattern.
- `queryShouldEqual`: like `query` this method accepts an associative array of parameters, however these must match exactly (with the exception of the order) with the actual request.
- `body`: accepts a string, array, `StreamInterface` instance or a regex pattern. If an array is given and the actual request is not json, it will `json_encode` the array and look for an exact match. If the actual request is JSON, it will decode it and use the same matching strategy as the `query` method, allowing for regex patterns as values. When a JSON string is provided, it will be decoded and treated the same as an array.
- `json`: accepts an array and delegates to the `body` method.
- `bodyShouldEqual`: accepts a string or string castable object, such as a `StreamInterface` instance, and asserts that it is an exact match to the actual request body.
- `should`: accepts a `callable` and provides the actual request as an `AssertableRequest` instance and the `Muzzle` instance as parameters when invoking the `callable`. The `callable` is expected be a `void` return type, so any return value will be ignored. See below for details.
 

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
