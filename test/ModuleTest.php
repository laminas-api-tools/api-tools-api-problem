<?php

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

class ModuleTest extends TestCase
{
    public function marshalEventManager()
    {
        $r = new ReflectionClass(EventManager::class);
        if ($r->hasMethod('setSharedManager')) {
            $eventManager = new EventManager();
            $eventManager->setSharedManager(new SharedEventManager());
            return $eventManager;
        }
        return new EventManager(new SharedEventManager());
    }

    public function testOnBootstrap()
    {
        $module = new Module();

        $application = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $serviceLocator->method('get')->will($this->returnCallback([$this, 'serviceLocator']));

        $eventManager = $this->marshalEventManager();
        $event = $this->getMockBuilder(MvcEvent::class)->getMock();

        $application->method('getServiceManager')->willReturn($serviceLocator);
        $application->method('getEventManager')->willReturn($eventManager);
        $event->expects($this->once())->method('getTarget')->willReturn($application);

        $module->onBootstrap($event);
    }

    public function serviceLocator($service)
    {
        switch ($service) {
            case 'Laminas\ApiTools\ApiProblem\Listener\ApiProblemListener':
                return new ApiProblemListener();
                break;
            case 'SendResponseListener':
                $listener = $this->getMockBuilder(SendResponseListener::class)->getMock();
                $listener->method('getEventManager')->willReturn(new EventManager());

                return $listener;
                break;
            case SendApiProblemResponseListener::class:
                return new SendApiProblemResponseListener();
            default:
                //
        }
    }
}
