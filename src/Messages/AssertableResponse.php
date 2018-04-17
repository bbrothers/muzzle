<?php

namespace Muzzle\Messages;

use PHPUnit\Framework\Assert as PHPUnit;

class AssertableResponse extends DecodableResponse
{

    use ContentAssertions;
    use Statusable;

    /**
     * Assert that the response has a successful status code.
     *
     * @return $this
     */
    public function assertSuccessful()
    {

        PHPUnit::assertTrue(
            $this->isSuccessful(),
            "Response status code [{$this->getStatusCode()}] is not a successful status code."
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code.
     *
     * @param  int $status
     * @return $this
     */
    public function assertStatus($status)
    {

        $actual = $this->getStatusCode();

        PHPUnit::assertTrue(
            $actual === $status,
            "Expected status code {$status} but received {$actual}."
        );

        return $this;
    }

    /**
     * Assert whether the response is redirecting to a given URI.
     *
     * @param  string $uri
     * @return $this
     */
    public function assertRedirect($uri = null)
    {

        PHPUnit::assertTrue(
            $this->isRedirect(),
            "Response status code [{$this->getStatusCode()}] is not a redirect status code."
        );

        if (! is_null($uri)) {
            PHPUnit::assertContains(app('url')->to($uri), $this->getHeader('Location'), '', true);
        }

        return $this;
    }

    /**
     * Asserts that the response contains the given header and equals the optional value.
     *
     * @param  string $headerName
     * @param  mixed $value
     * @return $this
     */
    public function assertHeader($headerName, $value = null)
    {

        PHPUnit::assertTrue(
            $this->hasHeader($headerName),
            "Header [{$headerName}] not present on response."
        );

        $actual = $this->getHeader($headerName);

        if (! is_null($value)) {
            $message = sprintf(
                "Header [%s] was found, but value(s) [%s] does not match [%s].",
                $headerName,
                implode(', ', $actual),
                $value
            );
            PHPUnit::assertContains($value, $actual, $message);
        }

        return $this;
    }
}
