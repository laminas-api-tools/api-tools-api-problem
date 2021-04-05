<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

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
