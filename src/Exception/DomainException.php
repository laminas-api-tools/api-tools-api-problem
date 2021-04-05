<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem\Exception;

class DomainException extends \DomainException implements
    ExceptionInterface,
    ProblemExceptionInterface
{
    /** @var string */
    protected $type;

    /** @var array */
    protected $details = [];

    /** @var string */
    protected $title;

    /**
     * @param array $details
     * @return self
     */
    public function setAdditionalDetails(array $details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param string $uri
     * @return self
     */
    public function setType($uri)
    {
        $this->type = (string) $uri;
        return $this;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;
        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalDetails()
    {
        return $this->details;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
