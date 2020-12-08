<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\ApiProblem\Listener\SendApiProblemResponseListener;
use Laminas\Http\Response as HttpResponse;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SendApiProblemResponseListenerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): SendApiProblemResponseListener {
        $config = $container->get('config');
        $displayExceptions = isset($config['view_manager']['display_exceptions'])
            && $config['view_manager']['display_exceptions'];

        $listener = new SendApiProblemResponseListener();
        $listener->setDisplayExceptions($displayExceptions);

        if ($container->has('Response')) {
            $response = $container->get('Response');
            if ($response instanceof HttpResponse) {
                $listener->setApplicationResponse($response);
            }
        }

        return $listener;
    }
}
