<?php

namespace Muzzle\Messages;

use Muzzle\HttpStatus;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

function app()
{

    return new class
    {

        public function to($uri)
        {

            return $uri;
        }
    };
}

class AssertableResponseTest extends TestCase
{

    /** @test */
    public function itCanAssertThatAResponseWasSuccessful()
    {

        $response = AssertableResponse::fromBaseResponse(new Response(HttpStatus::OK));

        $response->assertSuccessful();

        $response = AssertableResponse::fromBaseResponse(new Response(HttpStatus::INTERNAL_SERVER_ERROR));

        $this->expectException(ExpectationFailedException::class);
        $response->assertSuccessful();
    }

    /**
     * @test
     * @dataProvider statusCodes
     * @param int $code
     */
    public function itCanAssertAResponseStatusCodeMatchesTheProvidedValue($code)
    {

        $response = AssertableResponse::fromBaseResponse(new Response($code));

        $response->assertStatus($code);

        $this->expectException(ExpectationFailedException::class);
        $response->assertStatus(999);
    }

    public function statusCodes()
    {

        yield from [(new ReflectionClass(HttpStatus::class))->getConstants()];
    }

    /** @test */
    public function itCanAssertThatAResponseIsARedirect()
    {

        $response = AssertableResponse::fromBaseResponse(new Response(HttpStatus::MOVED_PERMANENTLY));

        $response->assertRedirect();

        $response = AssertableResponse::fromBaseResponse(new Response(HttpStatus::BAD_REQUEST));

        $this->expectException(ExpectationFailedException::class);
        $response->assertRedirect();
    }

    /** @test */
    public function itCanAssertThatAResponseRedirectsToAGivenUri()
    {

        $response = AssertableResponse::fromBaseResponse(
            new Response(HttpStatus::MOVED_PERMANENTLY, ['Location' => 'https://example.com/foo'])
        );

        $response->assertRedirect('https://example.com/foo');

        $this->expectException(ExpectationFailedException::class);
        $response->assertRedirect('https://example.com/bar');
    }

    /** @test */
    public function itCanAssertThatAResponseContainsAHeader()
    {

        $response = AssertableResponse::fromBaseResponse(
            new Response(HttpStatus::OK, ['Content-Type' => 'application/json'])
        );

        $response->assertHeader('content-type');

        $this->expectException(ExpectationFailedException::class);
        $response->assertHeader('missing');
    }

    /** @test */
    public function itCanAssertThatAResponseHasAHeaderMatchingTheGivenValue()
    {

        $response = AssertableResponse::fromBaseResponse(
            new Response(HttpStatus::OK, ['Content-Type' => 'application/json'])
        );

        $response->assertHeader('content-type', 'application/json');

        $this->expectException(ExpectationFailedException::class);
        $response->assertHeader('content-type', 'text/html');
    }
}
