<?php

namespace Muzzle\Messages;

use Muzzle\HttpStatus;
use PHPUnit\Framework\TestCase;

class StatusableTest extends TestCase
{

    /** @test */
    public function itTestsIfAStatusCodeIsInvalid()
    {

        $this->assertTrue($this->statusable(999)->isInvalid());
        $this->assertFalse($this->statusable()->isInvalid());
    }

    /**
     * @test
     * @dataProvider informationalStatuses
     *
     * @param int $status
     */
    public function itTestsIfAResponseIsInformational($status)
    {

        $this->assertTrue($this->statusable($status)->isInformational());
        $this->assertFalse($this->statusable($status + 999)->isInformational());
    }

    public function informationalStatuses()
    {

        yield from array_map(function ($status) {

            return [$status];
        }, range(HttpStatus::CONTINUE, HttpStatus::OK - 1));
    }

    /**
     * @test
     * @dataProvider successfulStatuses
     *
     * @param int $status
     */
    public function itTestsIfAResponseIsSuccessful($status)
    {

        $this->assertTrue($this->statusable($status)->isSuccessful());
        $this->assertFalse($this->statusable($status + 100)->isSuccessful());
    }

    public function successfulStatuses()
    {

        yield from array_map(function ($status) {

            return [$status];
        }, range(HttpStatus::OK, HttpStatus::MULTIPLE_CHOICES - 1));
    }

    /**
     * @test
     * @dataProvider redirectionStatuses
     *
     * @param int $status
     */
    public function itTestsIfAResponseIsARedirection($status)
    {

        $this->assertTrue($this->statusable($status)->isRedirection());
        $this->assertFalse($this->statusable($status + 100)->isRedirection());
    }

    public function redirectionStatuses()
    {

        yield from array_map(function ($status) {

            return [$status];
        }, range(HttpStatus::MULTIPLE_CHOICES, HttpStatus::BAD_REQUEST - 1));
    }

    /**
     * @test
     * @dataProvider clientErrorStatuses
     *
     * @param int $status
     */
    public function itTestsIfAResponseIsAClientError($status)
    {

        $this->assertTrue($this->statusable($status)->isClientError());
        $this->assertFalse($this->statusable($status + 100)->isClientError());
    }

    public function clientErrorStatuses()
    {

        yield from array_map(function ($status) {

            return [$status];
        }, range(HttpStatus::BAD_REQUEST, HttpStatus::INTERNAL_SERVER_ERROR - 1));
    }

    /**
     * @test
     * @dataProvider serverErrorStatuses
     *
     * @param int $status
     */
    public function itTestsIfAResponseIsAServerError($status)
    {

        $this->assertTrue($this->statusable($status)->isServerError());
        $this->assertFalse($this->statusable($status + 100)->isServerError());
    }

    public function serverErrorStatuses()
    {

        yield from array_map(function ($status) {

            return [$status];
        }, range(HttpStatus::INTERNAL_SERVER_ERROR, 599));
    }

    /** @test */
    public function itTestsThatAResponseReturnsOk()
    {

        $this->assertTrue($this->statusable()->isOk());
        $this->assertFalse($this->statusable(HttpStatus::BAD_REQUEST)->isOk());
    }

    /** @test */
    public function itTestsIfAResponseWasForbidden()
    {

        $this->assertTrue($this->statusable(HttpStatus::FORBIDDEN)->isForbidden());
        $this->assertFalse($this->statusable(HttpStatus::BAD_REQUEST)->isForbidden());
    }

    /** @test */
    public function itTestsIfAResponseWasNotFound()
    {

        $this->assertTrue($this->statusable(HttpStatus::NOT_FOUND)->isNotFound());
        $this->assertFalse($this->statusable(HttpStatus::BAD_REQUEST)->isNotFound());
    }

    /**
     * @test
     * @dataProvider redirectStatuses
     *
     * @param int $status
     */
    public function itTestsIfAResponseIsARedirect($status)
    {

        $this->assertTrue($this->statusable($status)->isRedirect());
        $this->assertFalse($this->statusable(HttpStatus::INTERNAL_SERVER_ERROR)->isRedirect());
    }

    public function redirectStatuses()
    {

        yield from [
            [HttpStatus::CREATED],
            [HttpStatus::MOVED_PERMANENTLY],
            [HttpStatus::FOUND],
            [HttpStatus::SEE_OTHER],
            [HttpStatus::TEMPORARY_REDIRECT],
            [HttpStatus::PERMANENTLY_REDIRECT],
        ];
    }

    /** @test */
    public function itTestsIfAResponseIsARedirectToAProvidedLocation()
    {

        $statusable = $this->statusable(HttpStatus::MOVED_PERMANENTLY, ['Location' => '/foo']);
        $this->assertTrue($statusable->isRedirect('/foo'));
        $this->assertFalse($statusable->isRedirect('/bar'));
    }

    /** @test */
    public function itTestsIfAResponseIsEmpty()
    {

        $this->assertTrue($this->statusable(HttpStatus::NO_CONTENT)->isEmpty());
        $this->assertTrue($this->statusable(HttpStatus::NOT_MODIFIED)->isEmpty());
        $this->assertFalse($this->statusable(HttpStatus::OK)->isEmpty());
    }

    private function statusable($status = HttpStatus::OK, $headers = [])
    {

        return new class($status, $headers)
        {

            use Statusable;

            private $status;
            private $headers;

            public function __construct($status, $headers = [])
            {

                $this->status = $status;
                $this->headers = $headers;
            }

            public function getStatusCode()
            {

                return $this->status;
            }

            public function getHeader($value)
            {

                return (array) $this->headers[$value] ?? [];
            }

        };
    }
}
