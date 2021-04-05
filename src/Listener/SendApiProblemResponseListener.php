<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem\Listener;

use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\ResponseSender\HttpResponseSender;
use Laminas\Mvc\ResponseSender\SendResponseEvent;

/**
 * Send ApiProblem responses.
 */
class SendApiProblemResponseListener extends HttpResponseSender
{
    /** @var HttpResponse; */
    protected $applicationResponse;

    /** @var bool */
    protected $displayExceptions = false;

    /**
     * @return self
     */
    public function setApplicationResponse(HttpResponse $response)
    {
        $this->applicationResponse = $response;

        return $this;
    }

    /**
     * Set the flag determining whether exception stack traces are included.
     *
     * @param bool $flag
     * @return self
     */
    public function setDisplayExceptions($flag)
    {
        $this->displayExceptions = (bool) $flag;

        return $this;
    }

    /**
     * Are exception stack traces included in the response?
     *
     * @return bool
     */
    public function displayExceptions()
    {
        return $this->displayExceptions;
    }

    /**
     * Send the response content.
     *
     * Sets the composed ApiProblem's flag for including the stack trace in the
     * detail based on the display exceptions flag, and then sends content.
     *
     * @return self
     */
    public function sendContent(SendResponseEvent $e)
    {
        $response = $e->getResponse();
        if (! $response instanceof ApiProblemResponse) {
            return $this;
        }
        $response->getApiProblem()->setDetailIncludesStackTrace($this->displayExceptions());

        return parent::sendContent($e);
    }

    /**
     * Send HTTP response headers.
     *
     * If an application response is composed, and is an HTTP response, merges
     * its headers with the ApiProblemResponse headers prior to sending them.
     *
     * @return self
     */
    public function sendHeaders(SendResponseEvent $e)
    {
        $response = $e->getResponse();
        if (! $response instanceof ApiProblemResponse) {
            return $this;
        }

        if ($this->applicationResponse instanceof HttpResponse) {
            $this->mergeHeaders($this->applicationResponse, $response);
        }

        return parent::sendHeaders($e);
    }

    /**
     * Send ApiProblem response.
     *
     * @return self
     */
    public function __invoke(SendResponseEvent $event)
    {
        $response = $event->getResponse();
        if (! $response instanceof ApiProblemResponse) {
            return $this;
        }

        $this->sendHeaders($event)
             ->sendContent($event);
        $event->stopPropagation(true);

        return $this;
    }

    /**
     * Merge headers set on the application response into the API Problem response.
     */
    protected function mergeHeaders(HttpResponse $applicationResponse, ApiProblemResponse $apiProblemResponse)
    {
        $apiProblemHeaders = $apiProblemResponse->getHeaders();
        foreach ($applicationResponse->getHeaders() as $header) {
            if ($apiProblemHeaders->has($header->getFieldName())) {
                continue;
            }
            $apiProblemHeaders->addHeader($header);
        }
    }
}
