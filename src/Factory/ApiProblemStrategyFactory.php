<?php

declare(strict_types=1);

namespace Laminas\ApiTools\ApiProblem\Factory;

use interop\container\containerinterface;
use Laminas\ApiTools\ApiProblem\View\ApiProblemRenderer;
use Laminas\ApiTools\ApiProblem\View\ApiProblemStrategy;

class ApiProblemStrategyFactory
{
    /**
     * @return ApiProblemStrategy
     */
    public function __invoke(containerinterface $container)
    {
        return new ApiProblemStrategy($container->get(ApiProblemRenderer::class));
    }
}
