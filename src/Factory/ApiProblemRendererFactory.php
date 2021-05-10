<?php

namespace Laminas\ApiTools\ApiProblem\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\ApiProblem\View\ApiProblemRenderer;

class ApiProblemRendererFactory
{
    /**
     * @param ContainerInterface $container
     * @return ApiProblemRenderer
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $displayExceptions = isset($config['view_manager'])
            && isset($config['view_manager']['display_exceptions'])
            && $config['view_manager']['display_exceptions'];

        $renderer = new ApiProblemRenderer();
        $renderer->setDisplayExceptions($displayExceptions);

        return $renderer;
    }
}
