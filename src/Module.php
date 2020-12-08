<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem;

use Laminas\ApiTools\ApiProblem\Listener\SendApiProblemResponseListener;
use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\Mvc\ApplicationInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\ResponseSender\SendResponseEvent;

class Module implements BootstrapListenerInterface, ConfigProviderInterface
{
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Listener for bootstrap event.
     *
     * Attaches a render event.
     *
     * @param  EventInterface $e
     */
    public function onBootstrap(EventInterface $e): void
    {
        /** @var ApplicationInterface $app */
        $app            = $e->getTarget();
        $serviceManager = $app->getServiceManager();
        $eventManager   = $app->getEventManager();

        $serviceManager->get(Listener\ApiProblemListener::class)->attach($eventManager);
        $eventManager->attach(MvcEvent::EVENT_RENDER, [$this, 'onRender'], 100);

        $sendResponseListener = $serviceManager->get('SendResponseListener');
        $sendResponseListener->getEventManager()->attach(
            SendResponseEvent::EVENT_SEND_RESPONSE,
            $serviceManager->get(SendApiProblemResponseListener::class),
            -500
        );
    }

    /**
     * Listener for the render event.
     *
     * Attaches a rendering/response strategy to the View.
     *
     * @param  MvcEvent $e
     */
    public function onRender(MvcEvent $e): void
    {
        /** @var ApplicationInterface $app */
        $app      = $e->getTarget();
        $services = $app->getServiceManager();

        if ($services->has('View')) {
            $view   = $services->get('View');
            $events = $view->getEventManager();

            // register at high priority, to "beat" normal json strategy registered
            // via view manager, as well as HAL strategy.
            $services->get(View\ApiProblemStrategy::class)->attach($events, 400);
        }
    }
}
