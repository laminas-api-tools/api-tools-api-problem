<?php

declare(strict_types=1);

namespace Laminas\ApiTools\ApiProblem\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\ApiProblem\Listener\ApiProblemListener;

class ApiProblemListenerFactory
{
    /**
     * @return ApiProblemListener
     */
    public function __invoke(ContainerInterface $container)
    {
        $filters = null;
        $config  = [];

        if ($container->has('config')) {
            $config = $container->get('config');
        }

        if (isset($config['api-tools-api-problem']['accept_filters'])) {
            $filters = $config['api-tools-api-problem']['accept_filters'];
        }

        return new ApiProblemListener($filters);
    }
}
