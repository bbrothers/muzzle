<?php

namespace Muzzle\Messages;

use DOMDocument;
use DOMException;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Psr\Http\Message\StreamInterface;
use function GuzzleHttp\Psr7\stream_for;

class HtmlFixture extends AbstractFixture
{

    /**
     * @var DOMDocument
     */
    protected $body;

    public function getBody()
    {

        return stream_for($this->body->saveHTML());
    }

    public function withBody(StreamInterface $body)
    {

        $document = new DOMDocument;
        if ($body->getSize()) {
            $document->loadXML((string) $body);
        }
        $this->body = $document;

        return $this;
    }

    public function asDocument() : DOMDocument
    {

        return $this->body;
    }

    public function getXPath(string $selector) : DOMNodeList
    {

        return (new DOMXPath($this->body))->query($selector);
    }

    public function hasXPath(string $selector) : bool
    {

        return $this->getXPath($selector)->length > 0;
    }

    public function createNode(string $tag, string $body = null) : DOMNode
    {

        return $this->body->createElement($tag, $body);
    }

    public function replace(string $selector, DOMNode $replacement) : HtmlFixture
    {

        if (! $this->hasXPath($selector)) {
            throw new DOMException("Could not find node for XPath [{$selector}].");
        }

        $current = $this->getXPath($selector);

        $current->item(0)->parentNode->replaceChild($replacement, $current->item(0));

        $this->saveBody();

        return $this;
    }
}
