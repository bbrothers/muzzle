<?php

namespace Muzzle\Messages;

use GuzzleHttp\Psr7\Uri;
use Muzzle\HttpMethod;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class AssertableRequestTest extends TestCase
{

    /** @test */
    public function itCanAssertAHeaderMatchesAProvidedValue()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://example.com', ['Test-Header' => 'Foo'])
        );

        $request->assertHeader('Test-Header');
        $request->assertHeader('Test-Header', 'Foo');

        $this->expectException(ExpectationFailedException::class);
        $request->assertHeader('Not-Found');

        $this->expectException(ExpectationFailedException::class);
        $request->assertHeader('Test-Header', 'incorrect value');
    }

    /** @test */
    public function itCanAssertARequestTarget()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://example.com/foo')
        );

        $request->assertRequestTarget('/foo');

        $this->expectException(ExpectationFailedException::class);
        $request->assertRequestTarget('/bar');
    }

    /** @test */
    public function itCanAssertAnHttpMethodMatchesTheProvidedValue()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://example.com/foo')
        );

        $request->assertMethod(HttpMethod::GET);

        $this->expectException(ExpectationFailedException::class);
        $request->assertMethod(HttpMethod::OPTIONS);
    }

    /** @test */
    public function itCanAssertTheRequestSchemeMatchesTheProvidedValue()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://example.com/foo')
        );

        $request->assertUriScheme('https');

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriScheme('ftp');
    }


    /** @test */
    public function itCanAssertThatTheRequestAuthorityMatchesTheProvidedValue()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://user@example.com:80/foo?bar=baz&qux=quux')
        );

        $request->assertUriAuthority('user@example.com:80');

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriAuthority('foo');
    }


    /** @test */
    public function itCanAssertThatTheRequestUriUserInformationMatchesTheProvidedValue()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://user:password@example.com:80/foo?bar=baz&qux=quux')
        );

        $request->assertUriUserInfo('user:password');

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriUserInfo('foo');
    }


    /** @test */
    public function itCanAssertThatTheRequestUriHostMatchesTheProvidedValue()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://user:password@example.com:80/foo?bar=baz&qux=quux')
        );

        $request->assertUriHost('example.com');

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriHost('foo');
    }

    /** @test */
    public function itCanAssertThatTheRequestUriPortMatchesTheProvidedValue()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://user:password@example.com:80/foo?bar=baz&qux=quux')
        );

        $request->assertUriPort(80);

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriPort(8080);
    }

    /** @test */
    public function itCanAssertThatTheRequestUriPathMatchesTheProvidedValue()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://user:password@example.com:80/foo?bar=baz&qux=quux')
        );

        $request->assertUriPath('/foo');

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriPath('/bar');
    }

    /** @test */
    public function itCanAssertThatTheRequestUriPathMatchesTheAProvidedPattern()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://user:password@example.com:80/foo?bar=baz&qux=quux')
        );

        $request->assertUriPathMatches('#.*o+#');

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriPathMatches('#b.*#');
    }

    /** @test */
    public function itCanAssertThatTheRequestUriPathMatchesTheProvidedPattern()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://user:password@example.com:80/foo/123/abc?bar=baz&qux=quux')
        );

        $request->assertUriPath('/foo/*/abc');
        $request->assertUriPath('/foo/*');

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriPath('/foo/*/');
    }

    /** @test */
    public function itCanAssertThatTheRequestUriFragmentMatchesTheProvidedValue()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://user:password@example.com:80/foo#fragment')
        );

        $request->assertUriFragment('fragment');

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriFragment('bar');
    }

    /** @test */
    public function itCanAssertThatTheRequestUriQueryMatchesTheProvidedValue()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://user:password@example.com:80/foo?bar=baz&qux=quux')
        );

        $request->assertUriQuery('bar=baz&qux=quux');

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriQuery('foo');
    }

    /** @test */
    public function itCanAssertThatTheRequestUriQueryHasAKey()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://user:password@example.com:80/foo?bar=baz&qux=quux')
        );

        $request->assertUriQueryHasKey('bar');

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriQueryHasKey('foo');
    }

    /** @test */
    public function itCanAssertThatTheRequestUriQueryDoesNotHaveAKey()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://user:password@example.com:80/foo?bar=baz&qux=quux')
        );

        $request->assertUriQueryNotHasKey('foo');

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriQueryNotHasKey('bar');
    }

    /** @test */
    public function itCanAssertThatTheRequestQueryStringContainsAnArray()
    {

        $request = new AssertableRequest(
            new Request(HttpMethod::GET, 'https://example.com/foo?bar=baz&qux=quux')
        );

        $request->assertUriQueryContains(['bar' => 'baz']);

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriQueryContains(['foo']);
    }

    /** @test */
    public function itCanAssertThatRequestUriMatchesAProvidedUri()
    {

        $request = new AssertableRequest(new Request(HttpMethod::GET, 'https://example.com'));

        $request->assertUriEquals(new Uri('https://example.com'));

        $this->expectException(ExpectationFailedException::class);
        $request->assertUriPath('https://example.org');
    }
}
