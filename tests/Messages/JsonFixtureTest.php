<?php

namespace Muzzle\Messages;

use GuzzleHttp\Psr7\Response;
use Muzzle\HttpStatus;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class JsonFixtureTest extends TestCase
{


    /** @test */
    public function itCanBeCreatedFromAResponseInstance()
    {

        $fixture = JsonFixture::fromBaseResponse(new Response);
        $this->assertInstanceOf(JsonFixture::class, $fixture);
    }

    /** @test */
    public function itMakesTheBodyOfAJsonResponseArrayAccessible()
    {

        $fixture = new JsonFixture(HttpStatus::OK, [], json_encode(['data' => ['foo' => 'bar']]));

        $this->assertSame('bar', $fixture['data.foo']);
    }

    /** @test */
    public function itReturnsTheBodyAsAStream()
    {

        $fixture = new JsonFixture(HttpStatus::OK, [], json_encode(['data' => ['foo' => 'bar']]));

        $this->assertInstanceOf(StreamInterface::class, $fixture->getBody());
    }

    /** @test */
    public function itCanReplaceAValueByArrayKey()
    {

        $fixture = new JsonFixture(HttpStatus::OK, [], json_encode(['data' => ['foo' => 'bar']]));

        $fixture['data.foo'] = 'baz';

        $decoded = json_decode($fixture->getBody(), true);

        $this->assertSame([
            'data' => [
                'foo' => 'baz',
            ],
        ], $decoded);
    }

    /** @test */
    public function itCanReturnTheBodyAsAnArray()
    {

        $payload = ['data' => ['foo' => 'bar']];
        $fixture = new JsonFixture(HttpStatus::OK, [], json_encode($payload));

        $this->assertSame($payload, $fixture->asArray());
    }

    /** @test */
    public function itCanForgetAnArrayKey()
    {

        $fixture = new JsonFixture(HttpStatus::OK, [], json_encode([
            'data' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ]));

        $fixture->forget('data.foo');
        unset($fixture['data.baz']);

        $decoded = json_decode($fixture->getBody(), true);

        $this->assertSame([
            'data' => [],
        ], $decoded);
    }

    /** @test */
    public function itCanGetASetOfValuesFromTheBody()
    {

        $fixture = new JsonFixture(HttpStatus::OK, [], json_encode([
            'foo' => 'bar',
            'baz' => 'qux',
            'a' => ['b' => 'c'],
            'should' => 'exclude',
        ]));

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'qux',
            'a' => ['b' => 'c'],
        ], $fixture->only(['foo', 'baz', 'a.b']));
    }

    /** @test */
    public function itCanCheckIfTheBodyContainsAKey()
    {

        $fixture = new JsonFixture(HttpStatus::OK, [], json_encode(['data' => ['foo' => 'bar']]));

        $this->assertTrue($fixture->has('data.foo'));
        $this->assertFalse(isset($fixture['data.missing']));
    }

    /** @test */
    public function itCanBeCastToAString()
    {

        $body = json_encode(['data' => ['foo' => 'bar']]);
        $this->assertSame($body, (string) new JsonFixture(HttpStatus::OK, [], $body));
    }

    /** @test */
    public function itCanInstantiatedFromTheDecoratedWithMethods() : void
    {

        $fixture = new JsonFixture(HttpStatus::OK, ['foo' => 'bar'], json_encode(['data' => ['foo' => 'bar']]));
        $response = $fixture->withoutHeader('foo');
        $response = $response->withStatus(HttpStatus::NOT_MODIFIED);

        $this->assertFalse($response->hasHeader('foo'));
        $this->assertEquals(HttpStatus::NOT_MODIFIED, $response->getStatusCode());
        $this->assertNotSame($fixture, $response);
    }

    /** @test */
    public function itWillRetainChangesWhenCallingWithMethods() : void
    {

        $fixture = new JsonFixture(HttpStatus::OK, ['foo' => 'bar'], json_encode(['data' => ['foo' => 'bar']]));
        $fixture->set('data.foo', 'baz');
        $response = $fixture->withoutHeader('foo');

        $this->assertFalse($response->hasHeader('foo'));
        $this->assertEquals('baz', $response->get('data.foo'), 'The modified value was not retained.');
        $this->assertNotSame($fixture, $response);
    }
}
