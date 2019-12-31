<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem\Factory;

use Laminas\ApiTools\ApiProblem\Listener\ApiProblemListener;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ApiProblemListenerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return ApiProblemListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config  = $serviceLocator->has('Config');
        $filters = null;

        if (isset($config['api-tools-api-problem'])
            && isset($config['api-tools-api-problem']['accept_filters'])
        ) {
            $filters = $config['api-tools-api-problem']['accept_filters'];
        }

        return new ApiProblemListener($filters);
    }
}
