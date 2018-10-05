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

    /**
     * @test
     * @dataProvider regexFixtures
     * @see          https://github.com/symfony/finder/blob/master/Tests/Iterator/MultiplePcreFilterIteratorTest.php
     *
     * @param string $string
     * @param bool $isRegex
     * @param string $message
     */
    public function itCanCheckIfAStringIsRegex(string $string, bool $isRegex, string $message)
    {

        $this->assertEquals($isRegex, is_regex($string), $message);
    }

    public function regexFixtures()
    {

        return [
            ['foo', false, 'string'],
            [' foo ', false, '" " is not a valid delimiter'],
            ['\\foo\\', false, '"\\" is not a valid delimiter'],
            ['afooa', false, '"a" is not a valid delimiter'],
            ['//', false, 'the pattern should contain at least 1 character'],
            ['/a/', true, 'valid regex'],
            ['/foo/', true, 'valid regex'],
            ['/foo/i', true, 'valid regex with a single modifier'],
            ['/foo/imsxu', true, 'valid regex with multiple modifiers'],
            ['#foo#', true, '"#" is a valid delimiter'],
            ['{foo}', true, '"{,}" is a valid delimiter pair'],
            ['[foo]', true, '"[,]" is a valid delimiter pair'],
            ['(foo)', true, '"(,)" is a valid delimiter pair'],
            ['<foo>', true, '"<,>" is a valid delimiter pair'],
            ['*foo.*', false, '"*" is not considered as a valid delimiter'],
            ['?foo.?', false, '"?" is not considered as a valid delimiter'],
        ];
    }

    /** @test */
    public function itCanCheckIfAValueIsValidJson()
    {

        $valid = '{"data" : [{"foo": "bar"}]}';
        $invalid = '{"data" : [{"foo": "bar"}]';

        $this->assertTrue(is_json($valid));
        $this->assertFalse(is_json($invalid));
    }
}
