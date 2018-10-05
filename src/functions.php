<?php

namespace Muzzle;

use GuzzleHttp\Psr7\Response;
use Muzzle\Messages\JsonFixture;
use Psr\Http\Message\ResponseInterface;

/**
 * @param string $fixture
 * @param int $status
 * @param array $headers
 * @return Response|JsonFixture
 */
function fixture(string $fixture, int $status = HttpStatus::OK, array $headers = []) : ResponseInterface
{

    return ResponseBuilder::fromFixture($fixture, $status, $headers);
}


/**
 * @param string $value
 * @return bool
 * @see https://github.com/symfony/finder/blob/master/Iterator/MultiplePcreFilterIterator.php
 */
function is_regex($value) : bool
{

    try {
        $value = (string) $value;
    } catch (\Throwable $_) {
        return false;
    }

    if (preg_match('/^(.{3,}?)[imsxuADU]*$/', $value, $matches)) {
        $start = substr($matches[1], 0, 1);
        $end = substr($matches[1], -1);
        if ($start === $end) {
            return ! preg_match('/[*?[:alnum:] \\\\]/', $start);
        }
        foreach ([['{', '}'], ['(', ')'], ['[', ']'], ['<', '>']] as $delimiters) {
            if ($start === $delimiters[0] && $end === $delimiters[1]) {
                return true;
            }
        }
    }

    return false;
}

function is_json($value) : bool
{

    try {
        $value = (string) $value;
    } catch (\Throwable $_) {
        return false;
    }

    json_decode($value);
    return json_last_error() === JSON_ERROR_NONE;
}
