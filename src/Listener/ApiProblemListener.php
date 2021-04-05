<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem\Listener;

use Exception;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ApiProblem\View\ApiProblemModel;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Header\Accept as AcceptHeader;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\DispatchableInterface;
use Laminas\View\Model\ModelInterface;
use Throwable;

use function in_array;
use function is_array;
use function is_string;

/**
 * ApiProblemListener.
 *
 * Provides a listener on the render event, at high priority.
 *
 * If the MvcEvent represents an error, then its view model and result are
 * replaced with a RestfulJsonModel containing an API-Problem payload.
 */
class ApiProblemListener extends AbstractListenerAggregate
{
    /**
     * Default types to match in Accept header.
     *
     * @var array
     */
    protected $acceptFilters = [
        'application/json',
        'application/*+json',
    ];

    /**
     * Set the accept filter, if one is passed
     *
     * @param string|array $filters
     */
    public function __construct($filters = null)
    {
        if (! empty($filters)) {
            if (is_string($filters)) {
                $this->acceptFilters = [$filters];
            }

            if (is_array($filters)) {
                $this->acceptFilters = $filters;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, [$this, 'onRender'], 1000);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onDispatchError'], 100);

        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach(
            DispatchableInterface::class,
            MvcEvent::EVENT_DISPATCH,
            [$this, 'onDispatch'],
            100
        );
    }

    /**
     * Listen to the render event.
     */
    public function onRender(MvcEvent $e)
    {
        if (! $this->validateErrorEvent($e)) {
            return;
        }

        // Next, do we have a view model in the result?
        // If not, nothing more to do.
        $model = $e->getResult();
        if (! $model instanceof ModelInterface || $model instanceof ApiProblemModel) {
            return;
        }

        // Marshal the information we need for the API-Problem response
        $status    = $e->getResponse()->getStatusCode();
        $exception = $model->getVariable('exception');

        if ($exception instanceof Throwable || $exception instanceof Exception) {
            $apiProblem = new ApiProblem($status, $exception);
        } else {
            $apiProblem = new ApiProblem($status, $model->getVariable('message'));
        }

        // Create a new model with the API-Problem payload, and reset
        // the result and view model in the event using it.
        $model = new ApiProblemModel($apiProblem);
        $e->setResult($model);
        $e->setViewModel($model);
    }

    /**
     * Handle dispatch.
     *
     * It checks if the controller is in our list
     */
    public function onDispatch(MvcEvent $e)
    {
        $app      = $e->getApplication();
        $services = $app->getServiceManager();
        $config   = $services->get('config');

        if (! isset($config['api-tools-api-problem']['render_error_controllers'])) {
            return;
        }

        $controller  = $e->getRouteMatch()->getParam('controller');
        $controllers = $config['api-tools-api-problem']['render_error_controllers'];
        if (! in_array($controller, $controllers)) {
            // The current controller is not in our list of controllers to handle
            return;
        }

        // Attach the ApiProblem render.error listener
        $events = $app->getEventManager();
        $services->get('Laminas\ApiTools\ApiProblem\RenderErrorListener')->attach($events);
    }

    /**
     * Handle render errors.
     *
     * If the event represents an error, and has an exception composed, marshals an ApiProblem
     * based on the exception, stops event propagation, and returns an ApiProblemResponse.
     *
     * @return ApiProblemResponse
     */
    public function onDispatchError(MvcEvent $e)
    {
        if (! $this->validateErrorEvent($e)) {
            return;
        }

        // Marshall an ApiProblem and view model based on the exception
        $exception = $e->getParam('exception');
        if (! ($exception instanceof Throwable || $exception instanceof Exception)) {
            // If it's not an exception, do not know what to do.
            return;
        }

        $e->stopPropagation();
        $response = new ApiProblemResponse(new ApiProblem($exception->getCode(), $exception));
        $e->setResponse($response);

        return $response;
    }

    /**
     * Determine if we have a valid error event.
     *
     * @return bool
     */
    protected function validateErrorEvent(MvcEvent $e)
    {
        // only worried about error pages
        if (! $e->isError()) {
            return false;
        }

        // and then, only if we have an Accept header...
        $request = $e->getRequest();
        if (! $request instanceof HttpRequest) {
            return false;
        }

        $headers = $request->getHeaders();
        if (! $headers->has('Accept')) {
            return false;
        }

        // ... that matches certain criteria
        $accept = $headers->get('Accept');
        if (! $this->matchAcceptCriteria($accept)) {
            return false;
        }

        return true;
    }

    /**
     * Attempt to match the accept criteria.
     *
     * If it matches, but on "*\/*", return false.
     *
     * Otherwise, return based on whether or not one or more criteria match.
     *
     * @return bool
     */
    protected function matchAcceptCriteria(AcceptHeader $accept)
    {
        foreach ($this->acceptFilters as $type) {
            $match = $accept->match($type);
            if ($match && $match->getTypeString() !== '*/*') {
                return true;
            }
        }

        return false;
    }
}
