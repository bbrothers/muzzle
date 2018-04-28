<?php

namespace Muzzle;

use Muzzle\Messages\JsonFixture;
use PHPUnit\Framework\TestCase;
use VirtualFileSystem\FileSystem;

class FunctionsTest extends TestCase
{

    /** @test */
    public function itCanCreateAJsonFixture()
    {

        $data = ['data' => ['message' => 'done']];
        $vfs = new FileSystem;
        $vfs->createDirectory('/fixtures');

        file_put_contents($vfs->path('fixtures/response.json'), json_encode($data));
        ResponseBuilder::setFixtureDirectory($vfs->path('fixtures'));

        $fixture = fixture(
            'response.json',
            HttpStatus::BAD_REQUEST,
            ['content-type' => 'application/json']
        );

        $this->assertInstanceOf(JsonFixture::class, $fixture);
        $this->assertEquals(HttpStatus::BAD_REQUEST, $fixture->getStatusCode());
        $this->assertEquals(['application/json'], $fixture->getHeader('Content-Type'));
    }
}
