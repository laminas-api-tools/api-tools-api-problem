<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem\Factory;

use Laminas\ApiTools\ApiProblem\View\ApiProblemStrategy;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ApiProblemStrategyFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return ApiProblemStrategy
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ApiProblemStrategy($serviceLocator->get('Laminas\ApiTools\ApiProblem\ApiProblemRenderer'));
    }
}
