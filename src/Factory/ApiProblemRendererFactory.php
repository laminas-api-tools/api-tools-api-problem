<?php

declare(strict_types=1);

namespace Laminas\ApiTools\ApiProblem\Factory;

use Laminas\ApiTools\ApiProblem\View\ApiProblemRenderer;
use Psr\Container\ContainerInterface;

class ApiProblemRendererFactory
{
    /**
     * @return ApiProblemRenderer
     */
    public function __invoke(ContainerInterface $container)
    {
        $config            = $container->get('config');
        $displayExceptions = isset($config['view_manager'])
            && isset($config['view_manager']['display_exceptions'])
            && $config['view_manager']['display_exceptions'];

        $renderer = new ApiProblemRenderer();
        $renderer->setDisplayExceptions($displayExceptions);

        return $renderer;
    }
}
