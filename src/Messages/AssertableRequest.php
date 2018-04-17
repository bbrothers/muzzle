<?php

namespace Muzzle\Messages;

use GuzzleHttp\Psr7;
use PHPUnit\Framework\Assert as PHPUnit;
use Psr\Http\Message\RequestInterface;

class AssertableRequest implements RequestInterface
{

    use ContentAssertions;
    use RequestDecorator;
    use JsonMessage;

    /**
     * Asserts that the request contains the given header and equals the optional value.
     *
     * @param  string $headerName
     * @param  mixed $value
     * @return $this
     */
    public function assertHeader($headerName, $value = null)
    {

        PHPUnit::assertTrue(
            $this->hasHeader($headerName),
            "Header [{$headerName}] not present on request."
        );

        $actual = $this->getHeader($headerName);

        if (! is_null($value)) {
            $expected = (array) $value;
            sort($expected);
            sort($actual);

            $message = sprintf(
                "Header [%s] was found, but value(s) [%s] does not match [%s].",
                $headerName,
                implode(', ', $actual),
                implode(', ', $expected)
            );

            PHPUnit::assertArraySubset($expected, $actual, $message);
        }

        return $this;
    }

    /**
     * Assert that the given string matches the request target.
     *
     * @param  string $target
     * @return $this
     */
    public function assertRequestTarget($target)
    {

        PHPUnit::assertEquals($target, $this->getRequestTarget());

        return $this;
    }

    /**
     * Assert that the given string matches the HTTP request method.
     *
     * @param  string $method
     * @return $this
     */
    public function assertMethod(string $method)
    {

        PHPUnit::assertEquals(strtoupper($method), $this->getMethod());

        return $this;
    }

    /**
     * Assert that the given string matches the scheme component of the URI.
     *
     * @param  string $scheme
     * @return $this
     */
    public function assertUriScheme($scheme)
    {

        PHPUnit::assertEquals($scheme, $this->getUri()->getScheme());

        return $this;
    }

    /**
     * Assert that the given string matches the authority component of the URI.
     *
     * @param  string $authority
     * @return $this
     */
    public function assertUriAuthority($authority)
    {

        PHPUnit::assertEquals($authority, $this->getUri()->getAuthority());

        return $this;
    }

    /**
     * Assert that the given string matches the user information component of the URI.
     *
     * @param  string $userInfo
     * @return $this
     */
    public function assertUriUserInfo($userInfo)
    {

        PHPUnit::assertEquals($userInfo, $this->getUri()->getUserInfo());

        return $this;
    }

    /**
     * Assert that the given string matches the host component of the URI.
     *
     * @param  string $host
     * @return $this
     */
    public function assertUriHost($host)
    {

        PHPUnit::assertEquals($host, $this->getUri()->getHost());

        return $this;
    }

    /**
     * Assert that the given string matches the port component of the URI.
     *
     * @param  int|null $port
     * @return $this
     */
    public function assertUriPort($port = null)
    {

        PHPUnit::assertEquals($port, $this->getUri()->getPort());

        return $this;
    }


    /**
     * Assert that the given string matches the path component of the URI.
     *
     * @param  string $path
     * @return $this
     */
    public function assertUriPath($path)
    {

        PHPUnit::assertEquals($path, $this->getUri()->getPath());

        return $this;
    }


    /**
     * Assert that the given string matches the fragment component of the URI.
     *
     * @param  string $fragment
     * @return $this
     */
    public function assertUriFragment($fragment)
    {

        PHPUnit::assertEquals($fragment, $this->getUri()->getFragment());

        return $this;
    }


    /**
     * Assert that the given string matches the query component of the URI.
     *
     * @param  string $query
     * @return $this
     */
    public function assertUriQuery($query)
    {

        PHPUnit::assertEquals($query, $this->getUri()->getQuery());

        return $this;
    }

    /**
     * Assert that the given key exists in the query.
     *
     * @param  string $key
     * @return $this
     */
    public function assertUriQueryHasKey($key)
    {

        $query = Psr7\parse_query($this->getUri()->getQuery());
        PHPUnit::assertArrayHasKey($key, $query);

        return $this;
    }

    /**
     * Assert that the given key does not exist in the query.
     *
     * @param  string $key
     * @return $this
     */
    public function assertUriQueryNotHasKey($key)
    {

        $query = Psr7\parse_query($this->getUri()->getQuery());
        PHPUnit::assertArrayNotHasKey($key, $query);

        return $this;
    }

    /**
     * Assert that the given key exists in the query.
     *
     * @param  array $values
     * @return $this
     */
    public function assertUriQueryContains(array $values)
    {

        $query = Psr7\parse_query($this->getUri()->getQuery());
        PHPUnit::assertArraySubset($values, $query, false, (function ($expected, $actual) {

            return 'Could not find ' . PHP_EOL
                   . var_export($expected, true) . PHP_EOL
                   . 'within response' . PHP_EOL
                   . var_export($actual, true) . PHP_EOL;
        })($values, $query));

        return $this;
    }

    public function assertUriEquals(Psr7\Uri $uri)
    {

        PHPUnit::assertEquals($this->getUri(), $uri, sprintf('Failed asserting %s equals %s', $this->getUri(), $uri));

        return $this;
    }
}
