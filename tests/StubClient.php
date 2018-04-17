<?php

namespace Muzzle;

use GuzzleHttp\Client;

class StubClient
{

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {

        $this->client = $client;
    }

    public function get(string $uri, array $payload = [])
    {

        return $this->client->get($uri, ['query' => $payload]);
    }
}
