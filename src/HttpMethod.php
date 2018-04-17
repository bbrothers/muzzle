<?php

namespace Muzzle;

use MyCLabs\Enum\Enum;

/**
 * @method static HttpMethod CONNECT
 * @method static HttpMethod DELETE
 * @method static HttpMethod GET
 * @method static HttpMethod HEAD
 * @method static HttpMethod OPTIONS
 * @method static HttpMethod PATCH
 * @method static HttpMethod POST
 * @method static HttpMethod PUT
 * @method static HttpMethod TRACE
 */
class HttpMethod extends Enum
{

    const CONNECT = 'CONNECT';
    const DELETE = 'DELETE';
    const GET = 'GET';
    const HEAD = 'HEAD';
    const OPTIONS = 'OPTIONS';
    const PATCH = 'PATCH';
    const POST = 'POST';
    const PUT = 'PUT';
    const TRACE = 'TRACE';

    public function __construct(string $value)
    {

        parent::__construct(strtoupper($value));
    }

    public static function isValid($value)
    {

        return in_array(strtoupper($value), static::toArray(), true);
    }
}
