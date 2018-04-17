<?php

namespace Muzzle\Messages;

use Muzzle\HttpStatus;

trait Statusable
{

    /**
     * Gets the response status code.
     *
     * @return int Status code.
     */
    abstract public function getStatusCode();

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given header.
     */
    abstract public function getHeader($name);

    /**
     * Is response invalid?
     *
     * @return bool
     *
     * @see   http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     *
     * @final since version 3.2
     */
    public function isInvalid()
    {

        return $this->getStatusCode() < HttpStatus::CONTINUE || $this->getStatusCode() >= 600;
    }

    /**
     * Is response informative?
     *
     * @return bool
     *
     * @final since version 3.3
     */
    public function isInformational()
    {

        return $this->getStatusCode() >= HttpStatus::CONTINUE && $this->getStatusCode() < HttpStatus::OK;
    }

    /**
     * Is response successful?
     *
     * @return bool
     *
     * @final since version 3.2
     */
    public function isSuccessful()
    {

        return $this->getStatusCode() >= HttpStatus::OK && $this->getStatusCode() < HttpStatus::MULTIPLE_CHOICES;
    }

    /**
     * Is the response a redirect?
     *
     * @return bool
     *
     * @final since version 3.2
     */
    public function isRedirection()
    {

        return
            $this->getStatusCode() >= HttpStatus::MULTIPLE_CHOICES
            && $this->getStatusCode() < HttpStatus::BAD_REQUEST;
    }

    /**
     * Is there a client error?
     *
     * @return bool
     *
     * @final since version 3.2
     */
    public function isClientError()
    {

        return
            $this->getStatusCode() >= HttpStatus::BAD_REQUEST
            && $this->getStatusCode() < HttpStatus::INTERNAL_SERVER_ERROR;
    }

    /**
     * Was there a server side error?
     *
     * @return bool
     *
     * @final since version 3.3
     */
    public function isServerError()
    {

        return $this->getStatusCode() >= HttpStatus::INTERNAL_SERVER_ERROR && $this->getStatusCode() < 600;
    }

    /**
     * Is the response OK?
     *
     * @return bool
     *
     * @final since version 3.2
     */
    public function isOk()
    {

        return HttpStatus::OK === $this->getStatusCode();
    }

    /**
     * Is the response forbidden?
     *
     * @return bool
     *
     * @final since version 3.2
     */
    public function isForbidden()
    {

        return HttpStatus::FORBIDDEN === $this->getStatusCode();
    }

    /**
     * Is the response a not found error?
     *
     * @return bool
     *
     * @final since version 3.2
     */
    public function isNotFound()
    {

        return HttpStatus::NOT_FOUND === $this->getStatusCode();
    }

    /**
     * Is the response a redirect of some form?
     *
     * @param string $location
     *
     * @return bool
     *
     * @final since version 3.2
     */
    public function isRedirect($location = null)
    {

        $redirectStatuses = [
            HttpStatus::CREATED,
            HttpStatus::MOVED_PERMANENTLY,
            HttpStatus::FOUND,
            HttpStatus::SEE_OTHER,
            HttpStatus::TEMPORARY_REDIRECT,
            HttpStatus::PERMANENTLY_REDIRECT,
        ];
        return
            in_array($this->getStatusCode(), $redirectStatuses)
            && (null === $location ?: in_array($location, $this->getHeader('Location')));
    }

    /**
     * Is the response empty?
     *
     * @return bool
     *
     * @final since version 3.2
     */
    public function isEmpty()
    {

        return in_array($this->getStatusCode(), [HttpStatus::NO_CONTENT, HttpStatus::NOT_MODIFIED]);
    }
}
