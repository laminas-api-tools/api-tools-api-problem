<?php

namespace Laminas\ApiTools\ApiProblem\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\ApiProblem\View\ApiProblemRenderer;
use Laminas\ApiTools\ApiProblem\View\ApiProblemStrategy;

class ApiProblemStrategyFactory
{
    /**
     * @param ContainerInterface $container
     * @return ApiProblemStrategy
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ApiProblemStrategy($container->get(ApiProblemRenderer::class));
    }
}
