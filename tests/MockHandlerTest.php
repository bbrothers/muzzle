<?php

namespace Muzzle;

use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\TransferStats;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TypeError;

class MockHandlerTest extends TestCase
{

    /** @test */
    public function itReturnsMockResponse()
    {

        $expectation = (new Expectation)->replyWith(new Response);
        $mock = new MockHandler([$expectation]);
        $request = new Request('GET', 'http://example.com');
        $promise = $mock($request, []);
        $this->assertSame($expectation->reply(), $promise->wait());
    }

    /** @test */
    public function itIsCountable()
    {

        $res = (new Expectation)->replyWith(new Response);
        $mock = new MockHandler([$res, $res]);
        $this->assertCount(2, $mock);
    }

    /** @test */
    public function itEmptyHandlerIsCountable()
    {

        $this->assertCount(0, new MockHandler());
    }

    /** @test */
    public function itEnsuresEachAppendIsValid()
    {

        $this->expectException(TypeError::class);
        new MockHandler(['a']);
    }

    /** @test */
    public function itCanQueueExceptions()
    {

        $expectation = (new Expectation)->replyWith(new Exception('a'));
        $mock = new MockHandler([$expectation]);
        $request = new Request('GET', 'http://example.com');
        $promise = $mock($request, []);
        try {
            $promise->wait();
            $this->fail();
        } catch (Exception $e2) {
            $this->assertSame($expectation->reply(), $e2);
        }
    }

    /** @test */
    public function itSinkFilename()
    {

        $filename = sys_get_temp_dir() . '/mock_test_' . uniqid();
        $res = (new Expectation)->replyWith(new Response(200, [], 'TEST CONTENT'));
        $mock = new MockHandler([$res]);
        $request = new Request('GET', '/');
        $promise = $mock($request, ['sink' => $filename]);
        $promise->wait();

        $this->assertFileExists($filename);
        $this->assertStringEqualsFile($filename, 'TEST CONTENT');

        unlink($filename);
    }

    /** @test */
    public function itSinkResource()
    {

        $file = tmpfile();
        $meta = stream_get_meta_data($file);
        $res = (new Expectation)->replyWith(new Response(200, [], 'TEST CONTENT'));
        $mock = new MockHandler([$res]);
        $request = new Request('GET', '/');
        $promise = $mock($request, ['sink' => $file]);
        $promise->wait();

        $this->assertFileExists($meta['uri']);
        $this->assertStringEqualsFile($meta['uri'], 'TEST CONTENT');
    }

    /** @test */
    public function itSinkStream()
    {

        $stream = new Stream(tmpfile());
        $res = (new Expectation)->replyWith(new Response(200, [], 'TEST CONTENT'));
        $mock = new MockHandler([$res]);
        $request = new Request('GET', '/');
        $promise = $mock($request, ['sink' => $stream]);
        $promise->wait();

        $this->assertFileExists($stream->getMetadata('uri'));
        $this->assertStringEqualsFile($stream->getMetadata('uri'), 'TEST CONTENT');
    }

    /** @test */
    public function itCanEnqueueCallables()
    {

        $response = new Response;
        $expectation = (new Expectation)->replyWith(function () use ($response) {

            return $response;
        });
        $mock = new MockHandler([$expectation]);
        $request = new Request('GET', 'http://example.com');
        $promise = $mock($request, ['foo' => 'bar']);
        $this->assertSame($response, $promise->wait());
    }

    /** @test */
    public function itEnsuresOnHeadersIsCallable()
    {

        $res = (new Expectation)->replyWith(new Response);
        $mock = new MockHandler([$res]);
        $request = new Request('GET', 'http://example.com');
        $this->expectException(InvalidArgumentException::class);
        $mock($request, ['on_headers' => 'error!']);
    }

    /** @test */
    public function itRejectsPromiseWhenOnHeadersFails()
    {

        $res = (new Expectation)->replyWith(new Response);
        $mock = new MockHandler([$res]);
        $request = new Request('GET', 'http://example.com');
        $promise = $mock($request, [
            'on_headers' => function () {

                throw new Exception('test');
            },
        ]);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('An error was encountered during the on_headers event');
        $promise->wait();
    }

    /** @test */
    public function itThrowsWhenNoMoreResponses()
    {

        $mock = new MockHandler();
        $request = new Request('GET', 'http://example.com');
        $this->expectException(UnexpectedRequestWasMade::class);
        $mock($request, []);
    }

    /** @test */
    public function itInvokesOnStatsFunctionForResponse()
    {

        $expectation = (new Expectation)->replyWith(new Response);
        $mock = new MockHandler([$expectation]);
        $request = new Request('GET', 'http://example.com');
        $stats = null;
        $onStats = function (TransferStats $transferStats) use (&$stats) {

            $stats = $transferStats;
        };
        $promise = $mock($request, ['on_stats' => $onStats]);
        $promise->wait();
        $this->assertSame($expectation->reply(), $stats->getResponse());
        $this->assertSame($request, $stats->getRequest());
    }

    /** @test */
    public function itInvokesOnStatsFunctionForError()
    {

        $expectation = (new Expectation)->replyWith(new Exception('a'));
        $mock = new MockHandler([$expectation]);
        $request = new Request('GET', 'http://example.com');
        $stats = null;
        $onStats = function (TransferStats $transferStats) use (&$stats) {

            $stats = $transferStats;
        };
        $mock($request, ['on_stats' => $onStats])->wait(false);
        $this->assertSame($expectation->reply(), $stats->getHandlerErrorData());
        $this->assertNull($stats->getResponse());
        $this->assertSame($request, $stats->getRequest());
    }

    /** @test */
    public function itAppliesADelayToTheResponse() : void
    {

        global $delay;
        function usleep($milliseconds)
        {

            global $delay;
            $delay = $milliseconds;
        }

        $handler = new MockHandler([(new Expectation)->replyWith(new Response)]);
        $request = new Request('GET', 'http://example.com');

        $handler($request, ['delay' => 5]);

        $this->assertEquals(5000, $delay);
        unset($delay);
    }

    /** @test */
    public function itThrowsAnExceptionIfTheOnHeadersOptionIsNotCallable() : void
    {

        $handler = new MockHandler([(new Expectation)->replyWith(new Response)]);
        $request = new Request('GET', 'http://example.com');

        $this->expectException(ValueNotCallable::class);
        $handler($request, ['on_headers' => 'not callable']);
    }
}
