<?php

namespace Muzzle\Messages;

trait JsonMessage
{

    protected $json;

    /**
     * Gets the body of the message.
     *
     * @return \Psr\Http\Message\StreamInterface Returns the body as a stream.
     */
    abstract public function getBody();

    /**
     * Check if the body of the response is JSON decodable.
     *
     * @return bool
     */
    public function isJson() : bool
    {

        $this->decode();

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Decodes a JSON body to an array.
     *
     * @return array
     */
    public function decode() : array
    {

        if (! $this->json) {
            $this->json = json_decode($this->getBody(), true);
        }

        return $this->json ?: [];
    }
}
