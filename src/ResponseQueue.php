<?php

namespace Muzzle;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use SplQueue;

class ResponseQueue extends SplQueue
{

    public function enqueue($value)
    {

        $this->assertQueueableResponse($value);
        parent::enqueue($value);
    }

    /**
     * @param $value
     */
    private function assertQueueableResponse($value) : void
    {

        if (! $this->isQueueableResponse($value)) {
            throw InvalidResponseProvided::fromValue($value);
        }
    }

    private function isQueueableResponse($value) : bool
    {

        return $value instanceof ResponseInterface
               || $value instanceof Exception
               || $value instanceof PromiseInterface
               || is_callable($value);
    }
}
