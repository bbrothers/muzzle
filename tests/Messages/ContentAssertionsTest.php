<?php

namespace Muzzle\Messages;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class ContentAssertionsTest extends TestCase
{

    /** @test */
    public function itCanAssertThatAValueIsSeenInTheContent()
    {

        $this->assertable('<p>Some <strong>Text</strong></p>')
             ->assertSee('Some <strong>Text<\/strong>');

        $this->expectException(ExpectationFailedException::class);
        $this->assertable()
             ->assertSee('Some Text');
    }

    /** @test */
    public function itCanAssertThatTextIsSeenInTheContent()
    {

        $this->assertable('<p>Some <strong>Text</strong></p>')
             ->assertSeeText('Some Text');

        $this->expectException(ExpectationFailedException::class);
        $this->assertable()
             ->assertSeeText('Some <strong>Text<\/strong>');
    }

    /** @test */
    public function itCanAssertThatAValueIsNotSeenInTheContent()
    {

        $this->assertable()
             ->assertDoNotSee('cat');

        $this->expectException(ExpectationFailedException::class);
        $this->assertable()
             ->assertDoNotSee('dog');
    }

    /** @test */
    public function itCanAssertThatTextIsNotSeeInTheContent()
    {

        $this->assertable('<p>Some <strong>Text</strong></p>')
             ->assertDoNotSeeText('Not seen value');

        $this->expectException(ExpectationFailedException::class);
        $this->assertable('<p>Some <strong>Text</strong></p>')
             ->assertDoNotSeeText('Some Text');
    }

    /** @test */
    public function itCanAssertThatAJsonValueIsFoundInTheContent()
    {

        $this->assertable()
             ->assertJson(['message' => 'The quick brown fox jumped over the lazy dog.']);

        $this->expectException(ExpectationFailedException::class);
        $this->assertable()
             ->assertJson(['message' => 'foo']);
    }

    /** @test */
    public function itCanAssertThatAnExactArrayIsFoundInTheJsonContent()
    {

        $data = ['foo' => ['bar' => 'baz'], 'qux' => 'quxx'];
        $this->assertable($data)
             ->assertExactJson(['message' => $data]);

        $this->expectException(ExpectationFailedException::class);
        $this->assertable($data)
             ->assertExactJson(['message' => ['foo' => ['bar' => 'baz']]]);
    }

    /** @test */
    public function itCanAssertThatAnArrayIsFoundWithinTheJsonContent()
    {

        $this->assertable(['foo' => ['bar' => 'baz'], 'qux' => 'quxx'])
             ->assertJsonFragment(['bar' => 'baz']);

        $this->expectException(ExpectationFailedException::class);
        $this->assertable(['foo' => ['bar' => 'baz'], 'qux' => 'quxx'])
             ->assertJsonFragment(['a' => ['b' => 'c']]);
    }

    /** @test */
    public function itCanAssertThatAnArrayIsNotFoundWithinTheJsonContent()
    {

        $this->assertable(['foo' => ['bar' => 'baz'], 'qux' => 'quxx'])
             ->assertJsonMissing(['a' => ['b' => 'c']]);

        $this->expectException(ExpectationFailedException::class);
        $this->assertable(['foo' => ['bar' => 'baz'], 'qux' => 'quxx'])
             ->assertJsonMissing(['bar' => 'baz']);
    }

    /** @test */
    public function itCanAssertThatTheJsonContentMatchesTheProvidedArrayKeyStructure()
    {

        // Empty structure
        $this->assertable()->assertJsonStructure();

        $this->assertable(['foo' => [['bar' => 'baz'], ['bar' => 'qux']]])
             ->assertJsonStructure(['message' => ['foo' => ['*' => ['bar']]]]);

        $this->expectException(ExpectationFailedException::class);
        $this->assertable(['foo' => [['bar' => 'baz'], ['bar' => 'qux']]])
             ->assertJsonStructure(['message' => ['foo' => ['*' => ['baz']]]]);
    }

    private function assertable($message = '')
    {

        return new class($message)
        {

            use ContentAssertions;
            use JsonMessage;

            protected $message;

            public function __construct($message)
            {

                $this->message = $message;
            }

            public function getBody()
            {

                return stream_for(json_encode([
                    'message' => $this->message ?: 'The quick brown fox jumped over the lazy dog.',
                ]));
            }
        };
    }
}
