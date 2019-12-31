<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\ApiProblem\Listener;

use Laminas\ApiTools\ApiProblem\Exception\DomainException;
use Laminas\ApiTools\ApiProblem\Listener\ApiProblemListener;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Console\Response as ConsoleResponse;
use Laminas\Http\Request;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use PHPUnit_Framework_TestCase as TestCase;

class ApiProblemListenerTest extends TestCase
{
    public function setUp()
    {
        $this->event    = new MvcEvent();
        $this->event->setError('this is an error event');
        $this->listener = new ApiProblemListener();
    }

    public function testOnRenderReturnsEarlyWhenConsoleRequestDetected()
    {
        $this->event->setRequest(new ConsoleRequest());

        $this->assertNull($this->listener->onRender($this->event));
    }

    public function testOnDispatchErrorReturnsAnApiProblemResponseBasedOnCurrentEventException()
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', new DomainException('triggering exception', 400));
        $event->setRequest($request);
        $return = $this->listener->onDispatchError($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertInstanceOf('Laminas\ApiTools\ApiProblem\ApiProblemResponse', $return);
        $response = $event->getResponse();
        $this->assertSame($return, $response);
        $problem = $response->getApiProblem();
        $this->assertInstanceOf('Laminas\ApiTools\ApiProblem\ApiProblem', $problem);
        $this->assertEquals(400, $problem->status);
        $this->assertSame($event->getParam('exception'), $problem->detail);
    }
}
