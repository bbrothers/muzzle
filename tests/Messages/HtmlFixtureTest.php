<?php

namespace Muzzle\Messages;

use DOMDocument;
use DOMException;
use GuzzleHttp\Psr7\Response;
use Muzzle\HttpStatus;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class HtmlFixtureTest extends TestCase
{

    /** @test */
    public function itCanBeCreatedFromAResponseInstance() : void
    {

        $fixture = HtmlFixture::fromBaseResponse(new Response);
        $this->assertInstanceOf(HtmlFixture::class, $fixture);
    }

    /** @test */
    public function itReturnsTheBodyAsAStream() : void
    {

        $fixture = new HtmlFixture(HttpStatus::OK, [], '<span>some html</span>');

        $this->assertInstanceOf(StreamInterface::class, $fixture->getBody());
    }

    /** @test */
    public function itCanReplaceANodeByXPath() : void
    {

        $fixture = new HtmlFixture(HttpStatus::OK, [], '<div>Some text <span>with span</span></div>');

        $node = $fixture->createNode('i', 'italicized');
        $fixture->replace("//div//span", $node);

        $this->assertSame('<div>Some text <i>italicized</i></div>', trim((string) $fixture->getBody()));
    }

    /** @test */
    public function itThrowsAnExceptionWhenTryingToReplaceANodeThatIsNotPresent() : void
    {

        $fixture = new HtmlFixture(HttpStatus::OK, [], '<div>Some text <span>with span</span></div>');

        $node = $fixture->createNode('i', 'italicized');
        $selector = '//div//span[contains(text(),"Not Found")]';

        $this->expectException(DOMException::class);
        $this->expectExceptionMessage($selector);
        $fixture->replace($selector, $node);
    }

    /** @test */
    public function itCanReturnTheBodyAsADomDocumentInstance() : void
    {

        $payload = '<span>some html</span>';
        $fixture = new HtmlFixture(HttpStatus::OK, [], $payload);

        $expected = new DOMDocument;
        $expected->loadXML($payload);
        $this->assertEquals($expected, $fixture->asDocument());
    }

    /** @test */
    public function itCanBeQueriedByXPath() : void
    {

        $fixture = new HtmlFixture(
            HttpStatus::OK,
            [],
            '<div>Some text <span>first span</span><span>second span</span></div>'
        );

        $nodeList = $fixture->getXPath('//div//span[2]');

        $this->assertEquals('second span', $nodeList->item(0)->textContent);
    }

    /** @test */
    public function itCanCheckIfTheBodyContainsANodeAtAGivenXPath() : void
    {

        $fixture = new HtmlFixture(
            HttpStatus::OK,
            [],
            '<div>Some text <span>first span</span><span>second span</span></div>'
        );

        $this->assertTrue($fixture->hasXPath('//div//span[contains(text(),"second span")]'));
        $this->assertFalse($fixture->hasXPath('//div//span[contains(text(),"Not Found")]'));
    }

    /** @test */
    public function itCanBeCastToAString() : void
    {

        $payload = '<span>some html</span>';
        $this->assertSame($payload . PHP_EOL, (string) new HtmlFixture(HttpStatus::OK, [], $payload));
    }

    /** @test */
    public function itCanBeInstantiatedFromTheDecoratedWithMethods() : void
    {

        $fixture = new HtmlFixture(HttpStatus::OK, [], '<span>some html</span>');
        $response = $fixture->withoutHeader('foo');
        $response = $response->withStatus(HttpStatus::NOT_MODIFIED);

        $this->assertFalse($response->hasHeader('foo'));
        $this->assertEquals(HttpStatus::NOT_MODIFIED, $response->getStatusCode());
        $this->assertNotSame($fixture, $response);
    }

    /** @test */
    public function itWillRetainChangesWhenCallingWithMethods() : void
    {

        $fixture = new HtmlFixture(HttpStatus::OK, [], '<div>Some text <span>with span</span></div>');
        $node = $fixture->createNode('i', 'italicized');
        $fixture->replace("//div//span", $node);
        $response = $fixture->withoutHeader('foo');

        $this->assertFalse($response->hasHeader('foo'));
        $this->assertSame(
            '<div>Some text <i>italicized</i></div>',
            trim((string) $response->getBody()),
            'The modified value was not retained.'
        );
        $this->assertNotSame($fixture, $response);
    }
}
