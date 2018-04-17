<?php

namespace Muzzle;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Muzzle\Messages\Transaction;
use Muzzle\Middleware\Decodable;
use PHPUnit\Framework\TestCase;

class MuzzleTest extends TestCase
{

    /** @test */
    public function itCanCreateAClientInstanceWithAMockHandler()
    {

        $client = new Muzzle;
        $client->append(
            Transaction::new()
                       ->setRequest(new Request(HttpMethod::POST, 'https://example.com'))
                       ->setResponse(new Response(HttpStatus::CREATED))
        );
        $client->append(
            Transaction::new()
                       ->setRequest(new Request(HttpMethod::GET, 'https://example.com'))
                       ->setResponse(new Response(HttpStatus::OK))
        );

        $client->addMiddleware(new Decodable);

        $this->assertInstanceOf(Muzzle::class, $client);
        $client->post('https://example.com')->assertStatus(HttpStatus::CREATED);
        $client->get('https://example.com')->assertStatus(HttpStatus::OK);
    }

    /** @test */
    public function itCanBeConstructedFromItsBuilderMethod()
    {

        $client = Muzzle::builder()
                        ->post('https://example.com')
                        ->replyWith(new Response(HttpStatus::CREATED))
                        ->get('https://example.com')
                        ->setQuery(['foo' => 'bar'])
                        ->build();

        $this->assertInstanceOf(Muzzle::class, $client);
        $client->post('https://example.com')->assertStatus(HttpStatus::CREATED);
        $client->get('https://example.com?foo=bar&baz=qux')->assertStatus(HttpStatus::OK);
    }
}
