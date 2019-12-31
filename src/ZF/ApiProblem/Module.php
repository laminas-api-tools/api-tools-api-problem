<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem;

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
        return array('Laminas\Loader\StandardAutoloader' => array('namespaces' => array(
            __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
        )));
    }

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../../config/module.config.php';
    }

    /**
     * Retrieve Service Manager configuration
     *
     * Defines the following service factories:
     * - Laminas\ApiTools\ApiProblem\ApiProblemListener
     * - Laminas\ApiTools\ApiProblem\ApiProblemRenderer
     * - Laminas\ApiTools\ApiProblem\ApiProblemStrategy
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return array('factories' => array(
            'Laminas\ApiTools\ApiProblem\ApiProblemListener' => function ($services) {
                $config = array();
                if ($services->has('config')) {
                    $config = $services->get('config');
                }

                $filter = null;
                if (isset($config['api-tools-api-problem'])
                    && isset($config['api-tools-api-problem']['accept_filter'])
                ) {
                    $filter = $config['api-tools-api-problem']['accept_filter'];
                }

                return new Listener\ApiProblemListener($filter);
            },
            'Laminas\ApiTools\ApiProblem\ApiProblemRenderer' => function ($services) {
                $config   = $services->get('Config');

                $displayExceptions = false;
                if (isset($config['view_manager'])
                    && isset($config['view_manager']['display_exceptions'])
                ) {
                    $displayExceptions = (bool) $config['view_manager']['display_exceptions'];
                }

                $renderer = new View\ApiProblemRenderer();
                $renderer->setDisplayExceptions($displayExceptions);

                return $renderer;
            },
            'Laminas\ApiTools\ApiProblem\ApiProblemStrategy' => function ($services) {
                $renderer = $services->get('Laminas\ApiTools\ApiProblem\ApiProblemRenderer');
                return new View\ApiProblemStrategy($renderer);
            },
            'Laminas\ApiTools\ApiProblem\RenderErrorListener' => function ($services) {
                $config   = $services->get('Config');

                $displayExceptions = false;
                if (isset($config['view_manager'])
                    && isset($config['view_manager']['display_exceptions'])
                ) {
                    $displayExceptions = (bool) $config['view_manager']['display_exceptions'];
                }

                $listener = new Listener\RenderErrorListener();
                $listener->setDisplayExceptions($displayExceptions);

                return $listener;
            },
        ));
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
        $app      = $e->getTarget();
        $services = $app->getServiceManager();
        $events   = $app->getEventManager();
        $events->attach($services->get('Laminas\ApiTools\ApiProblem\ApiProblemListener'));
        $events->attach('render', array($this, 'onRender'), 100);

        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach('Laminas\Stdlib\DispatchableInterface', $e::EVENT_DISPATCH, array($this, 'onDispatch'), 100);
    }

    public function onDispatch($e)
    {
        $app      = $e->getApplication();
        $services = $app->getServiceManager();
        $config   = $services->get('Config');
        if (!isset($config['api-tools-api-problem'])) {
            return;
        }
        if (!isset($config['api-tools-api-problem']['render_error_controllers'])) {
            return;
        }

        $controller  = $e->getRouteMatch()->getParam('controller');
        $controllers = $config['api-tools-api-problem']['render_error_controllers'];
        if (!in_array($controller, $controllers)) {
            // The current controller is not in our list of controllers to handle
            return;
        }

        // Attach the ApiProblem render.error listener
        $events = $app->getEventManager();
        $events->attach($services->get('Laminas\ApiTools\ApiProblem\RenderErrorListener'));
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
        $view     = $services->get('View');
        $events   = $view->getEventManager();

        // register at high priority, to "beat" normal json strategy registered
        // via view manager, as well as HAL strategy.
        $events->attach($services->get('Laminas\ApiTools\ApiProblem\ApiProblemStrategy'), 400);
    }
}
