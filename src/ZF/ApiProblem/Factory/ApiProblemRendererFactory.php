<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem\Factory;

use Laminas\ApiTools\ApiProblem\View\ApiProblemRenderer;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ApiProblemRendererFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return ApiProblemRenderer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config            = $serviceLocator->get('Config');
        $displayExceptions = isset($config['view_manager'])
            && isset($config['view_manager']['display_exceptions'])
            && $config['view_manager']['display_exceptions'];

        $renderer = new ApiProblemRenderer();
        $renderer->setDisplayExceptions($displayExceptions);

        return $renderer;
    }
}
