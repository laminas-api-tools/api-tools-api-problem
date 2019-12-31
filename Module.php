<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem;

use Laminas\Mvc\ResponseSender\SendResponseEvent;

/**
 * Laminas module
 */
class Module
{
    /**
     * Retrieve autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Laminas\Loader\StandardAutoloader' => array('namespaces' => array(
                __NAMESPACE__ => __DIR__ . '/src/Laminas/ApiProblem/',
            ))
        );
    }

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Listener for bootstrap event
     *
     * Attaches a render event.
     *
     * @param  \Laminas\Mvc\MvcEvent $e
     */
    public function onBootstrap($e)
    {
        $app            = $e->getTarget();
        $serviceManager = $app->getServiceManager();
        $eventManager   = $app->getEventManager();

        $eventManager->attach($serviceManager->get('Laminas\ApiTools\ApiProblem\ApiProblemListener'));
        $eventManager->attach('render', array($this, 'onRender'), 100);

        $sendResponseListener = $serviceManager->get('SendResponseListener');
        $sendResponseListener->getEventManager()->attach(
            SendResponseEvent::EVENT_SEND_RESPONSE,
            $serviceManager->get('Laminas\ApiTools\ApiProblem\Listener\SendApiProblemResponseListener'),
            -500
        );
    }

    /**
     * Listener for the render event
     *
     * Attaches a rendering/response strategy to the View.
     *
     * @param  \Laminas\Mvc\MvcEvent $e
     */
    public function onRender($e)
    {
        $app      = $e->getTarget();
        $services = $app->getServiceManager();

        if ($services->has('View')) {
            $view   = $services->get('View');
            $events = $view->getEventManager();

            // register at high priority, to "beat" normal json strategy registered
            // via view manager, as well as HAL strategy.
            $events->attach($services->get('Laminas\ApiTools\ApiProblem\ApiProblemStrategy'), 400);
        }
    }
}
