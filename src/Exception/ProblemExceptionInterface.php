<?php

namespace Laminas\ApiTools\ApiProblem\Exception;

/**
 * Interface for exceptions that can provide additional API Problem details.
 */
interface ProblemExceptionInterface
{
    /**
     * @return null|array|\Traversable
     */
    public function getAdditionalDetails();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getTitle();
}
