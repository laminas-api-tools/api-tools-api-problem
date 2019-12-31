<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem;

use Laminas\Http\Response;

/**
 * Represents an ApiProblem response payload
 */
class ApiProblemResponse extends Response
{
    protected $apiProblem;

    /**
     * @param ApiProblem $apiProblem
     */
    public function __construct(ApiProblem $apiProblem)
    {
        $this->apiProblem = $apiProblem;
        $this->setStatusCode($apiProblem->httpStatus);
        $this->setReasonPhrase($apiProblem->title);
    }

    /**
     * @return ApiProblem
     */
    public function getApiProblem()
    {
        return $this->apiProblem;
    }

    /**
     * Retrieve the content
     *
     * Serializes the composed ApiProblem instance to JSON.
     *
     * @return string
     */
    public function getContent()
    {
        return json_encode($this->apiProblem->toArray(), JSON_UNESCAPED_SLASHES);
    }

    /**
     * Retrieve headers
     *
     * Proxies to parent class, but then checks if we have an content-type 
     * header; if not, sets it, with a value of "application/api-problem+json".
     *
     * @return \Laminas\Http\Headers
     */
    public function getHeaders()
    {
        $headers = parent::getHeaders();
        if (!$headers->has('content-type')) {
            $headers->addHeaderLine('content-type', 'application/api-problem+json');
        }
        return $headers;
    }
}
