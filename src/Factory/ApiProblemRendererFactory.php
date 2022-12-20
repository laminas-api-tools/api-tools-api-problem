<?php

declare(strict_types=1);

namespace Laminas\ApiTools\ApiProblem\Factory;

use interop\container\containerinterface;
use Laminas\ApiTools\ApiProblem\View\ApiProblemRenderer;

class ApiProblemRendererFactory
{
    /**
     * @return ApiProblemRenderer
     */
    public function __invoke(containerinterface $container)
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
