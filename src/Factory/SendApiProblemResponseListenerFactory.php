<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem\Factory;

use Laminas\ApiTools\ApiProblem\Listener\SendApiProblemResponseListener;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class SendApiProblemResponseListenerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return SendApiProblemResponseListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config            = $serviceLocator->get('Config');
        $displayExceptions = isset($config['view_manager'])
            && isset($config['view_manager']['display_exceptions'])
            && $config['view_manager']['display_exceptions'];

        $listener = new SendApiProblemResponseListener();
        $listener->setDisplayExceptions($displayExceptions);

        return $listener;
    }
}
