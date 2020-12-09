<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem;

use Laminas\ApiTools\ApiProblem\Listener\ApiProblemListener;
use Laminas\ApiTools\ApiProblem\Listener\SendApiProblemResponseListener;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\SendResponseListener;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

class ModuleTest extends TestCase
{
    public function marshalEventManager(): EventManager
    {
        $r = new ReflectionClass(EventManager::class);
        if ($r->hasMethod('setSharedManager')) {
            $eventManager = new EventManager();
            $eventManager->setSharedManager(new SharedEventManager());
            return $eventManager;
        }
        return new EventManager(new SharedEventManager());
    }

    public function testOnBootstrap(): void
    {
        $module = new Module();

        $application = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')->willReturnCallback([$this, 'serviceLocator']);

        $eventManager = $this->marshalEventManager();
        $event = $this->getMockBuilder(MvcEvent::class)->getMock();

        $application->method('getServiceManager')->willReturn($serviceLocator);
        $application->method('getEventManager')->willReturn($eventManager);
        $event->expects(self::once())->method('getTarget')->willReturn($application);

        $module->onBootstrap($event);
    }

    public function serviceLocator($service)
    {
        switch ($service) {
            case ApiProblemListener::class:
                return new ApiProblemListener();
            case 'SendResponseListener':
                $listener = $this->getMockBuilder(SendResponseListener::class)->getMock();
                $listener->method('getEventManager')->willReturn(new EventManager());

                return $listener;
            case SendApiProblemResponseListener::class:
                return new SendApiProblemResponseListener();
            default:
                throw new RuntimeException('Could not find requested service');
        }
    }
}
