<?php

namespace LaminasTest\ApiTools\ApiProblem\Listener;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ApiProblem\Exception\DomainException;
use Laminas\ApiTools\ApiProblem\Listener\ApiProblemListener;
use Laminas\Http\Request;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\RequestInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use TypeError;

class ApiProblemListenerTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->event = new MvcEvent();
        $this->event->setError('this is an error event');
        $this->listener = new ApiProblemListener();
    }

    public function testOnRenderReturnsEarlyWhenNonHttpRequestDetected(): void
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();
        $this->event->setRequest($request);

        $this->assertNull($this->listener->onRender($this->event));
    }

    public function testOnDispatchErrorReturnsAnApiProblemResponseBasedOnCurrentEventException(): void
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', new DomainException('triggering exception', 400));
        $event->setRequest($request);
        $return = $this->listener->onDispatchError($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertInstanceOf(ApiProblemResponse::class, $return);
        $response = $event->getResponse();
        $this->assertSame($return, $response);
        $problem = $response->getApiProblem();
        $this->assertInstanceOf(ApiProblem::class, $problem);
        $this->assertEquals(400, $problem->status);
        $this->assertSame($event->getParam('exception'), $problem->detail);
    }

    /**
     * @requires PHP 7.0
     */
    public function testOnDispatchErrorReturnsAnApiProblemResponseBasedOnCurrentEventThrowable(): void
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', new TypeError('triggering throwable', 400));
        $event->setRequest($request);
        $return = $this->listener->onDispatchError($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertInstanceOf(ApiProblemResponse::class, $return);
        $response = $event->getResponse();
        $this->assertSame($return, $response);
        $problem = $response->getApiProblem();
        $this->assertInstanceOf(ApiProblem::class, $problem);
        $this->assertEquals(400, $problem->status);
        $this->assertSame($event->getParam('exception'), $problem->detail);
    }
}
