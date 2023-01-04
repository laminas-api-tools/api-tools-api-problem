<?php

declare(strict_types=1);

namespace Laminas\ApiTools\ApiProblem\Factory;

use interop\container\containerinterface;
use Laminas\ApiTools\ApiProblem\Listener\RenderErrorListener;

class RenderErrorListenerFactory
{
    /**
     * @return RenderErrorListener
     */
    public function __invoke(containerinterface $container)
    {
        $config            = $container->get('config');
        $displayExceptions = false;

        if (
            isset($config['view_manager'])
            && isset($config['view_manager']['display_exceptions'])
        ) {
            $displayExceptions = (bool) $config['view_manager']['display_exceptions'];
        }

        $listener = new RenderErrorListener();
        $listener->setDisplayExceptions($displayExceptions);

        return $listener;
    }
}
