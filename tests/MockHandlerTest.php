<?php

namespace Muzzle;

use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\TransferStats;
use PHPUnit\Framework\TestCase;

class MockHandlerTest extends TestCase
{

    public function testReturnsMockResponse()
    {

        $res = new Response;
        $mock = new MockHandler([$res]);
        $request = new Request('GET', 'http://example.com');
        $promise = $mock($request, []);
        $this->assertSame($res, $promise->wait());
    }

    public function testIsCountable()
    {

        $res = new Response();
        $mock = new MockHandler([$res, $res]);
        $this->assertCount(2, $mock);
    }

    public function testEmptyHandlerIsCountable()
    {

        $this->assertCount(0, new MockHandler());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEnsuresEachAppendIsValid()
    {

        $mock = new MockHandler(['a']);
        $request = new Request('GET', 'http://example.com');
        $mock($request, []);
    }

    public function testCanQueueExceptions()
    {

        $exception = new Exception('a');
        $mock = new MockHandler([$exception]);
        $request = new Request('GET', 'http://example.com');
        $promise = $mock($request, []);
        try {
            $promise->wait();
            $this->fail();
        } catch (Exception $e2) {
            $this->assertSame($exception, $e2);
        }
    }

    public function testSinkFilename()
    {

        $filename = sys_get_temp_dir() . '/mock_test_' . uniqid();
        $res = new Response(200, [], 'TEST CONTENT');
        $mock = new MockHandler([$res]);
        $request = new Request('GET', '/');
        $promise = $mock($request, ['sink' => $filename]);
        $promise->wait();

        $this->assertFileExists($filename);
        $this->assertStringEqualsFile($filename, 'TEST CONTENT');

        unlink($filename);
    }

    public function testSinkResource()
    {

        $file = tmpfile();
        $meta = stream_get_meta_data($file);
        $res = new Response(200, [], 'TEST CONTENT');
        $mock = new MockHandler([$res]);
        $request = new Request('GET', '/');
        $promise = $mock($request, ['sink' => $file]);
        $promise->wait();

        $this->assertFileExists($meta['uri']);
        $this->assertStringEqualsFile($meta['uri'], 'TEST CONTENT');
    }

    public function testSinkStream()
    {

        $stream = new Stream(tmpfile());
        $res = new Response(200, [], 'TEST CONTENT');
        $mock = new MockHandler([$res]);
        $request = new Request('GET', '/');
        $promise = $mock($request, ['sink' => $stream]);
        $promise->wait();

        $this->assertFileExists($stream->getMetadata('uri'));
        $this->assertStringEqualsFile($stream->getMetadata('uri'), 'TEST CONTENT');
    }

    public function testCanEnqueueCallables()
    {

        $response = new Response;
        $mock = new MockHandler([
            function () use ($response) {

                return $response;
            }
        ]);
        $request = new Request('GET', 'http://example.com');
        $promise = $mock($request, ['foo' => 'bar']);
        $this->assertSame($response, $promise->wait());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEnsuresOnHeadersIsCallable()
    {

        $res = new Response;
        $mock = new MockHandler([$res]);
        $request = new Request('GET', 'http://example.com');
        $mock($request, ['on_headers' => 'error!']);
    }

    /**
     * @expectedException \GuzzleHttp\Exception\RequestException
     * @expectedExceptionMessage An error was encountered during the on_headers event
     * @expectedExceptionMessage test
     */
    public function testRejectsPromiseWhenOnHeadersFails()
    {

        $res = new Response();
        $mock = new MockHandler([$res]);
        $request = new Request('GET', 'http://example.com');
        $promise = $mock($request, [
            'on_headers' => function () {

                throw new Exception('test');
            }
        ]);

        $promise->wait();
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testThrowsWhenNoMoreResponses()
    {

        $mock = new MockHandler();
        $request = new Request('GET', 'http://example.com');
        $mock($request, []);
    }

    public function testInvokesOnStatsFunctionForResponse()
    {

        $res = new Response();
        $mock = new MockHandler([$res]);
        $request = new Request('GET', 'http://example.com');
        $stats = null;
        $onStats = function (TransferStats $transferStats) use (&$stats) {

            $stats = $transferStats;
        };
        $promise = $mock($request, ['on_stats' => $onStats]);
        $promise->wait();
        $this->assertSame($res, $stats->getResponse());
        $this->assertSame($request, $stats->getRequest());
    }

    public function testInvokesOnStatsFunctionForError()
    {

        $exception = new Exception('a');
        $mock = new MockHandler([$exception]);
        $request = new Request('GET', 'http://example.com');
        $stats = null;
        $onStats = function (TransferStats $transferStats) use (&$stats) {

            $stats = $transferStats;
        };
        $mock($request, ['on_stats' => $onStats])->wait(false);
        $this->assertSame($exception, $stats->getHandlerErrorData());
        $this->assertNull($stats->getResponse());
        $this->assertSame($request, $stats->getRequest());
    }
}
