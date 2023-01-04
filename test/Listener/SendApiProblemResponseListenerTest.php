<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\ApiProblem\Listener;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ApiProblem\Exception\DomainException;
use Laminas\ApiTools\ApiProblem\Listener\SendApiProblemResponseListener;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\ResponseSender\ResponseSenderInterface;
use Laminas\Mvc\ResponseSender\SendResponseEvent;
use PHPUnit\Framework\TestCase;

use function count;
use function json_decode;
use function ob_get_clean;
use function ob_start;

class SendApiProblemResponseListenerTest extends TestCase
{
    /** @var DomainException */
    protected $exception;

    /** @var ApiProblem */
    protected $apiProblem;

    /** @var ApiProblemResponse */
    protected $response;

    /** @var SendResponseEvent */
    protected $event;

    /** @var SendApiProblemResponseListener */
    protected $listener;

    protected function setUp(): void
    {
        $this->exception  = new DomainException('Random error', 400);
        $this->apiProblem = new ApiProblem(400, $this->exception);
        $this->response   = new ApiProblemResponse($this->apiProblem);
        $this->event      = new SendResponseEvent();
        $this->event->setResponse($this->response);
        $this->listener = new SendApiProblemResponseListener();
    }

    public function testListenerImplementsResponseSenderInterface(): void
    {
        $this->assertInstanceOf(ResponseSenderInterface::class, $this->listener);
    }

    public function testDisplayExceptionsFlagIsFalseByDefault(): void
    {
        $this->assertFalse($this->listener->displayExceptions());
    }

    /**
     * @depends testDisplayExceptionsFlagIsFalseByDefault
     */
    public function testDisplayExceptionsFlagIsMutable(): void
    {
        $this->listener->setDisplayExceptions(true);
        $this->assertTrue($this->listener->displayExceptions());
    }

    /**
     * @depends testDisplayExceptionsFlagIsFalseByDefault
     */
    public function testSendContentDoesNotRenderExceptionsByDefault(): void
    {
        ob_start();
        $this->listener->sendContent($this->event);
        $contents = ob_get_clean();
        $this->assertIsString($contents);
        $data = json_decode($contents, true);
        $this->assertStringNotContainsString("\n", $data['detail']);
        $this->assertStringNotContainsString($this->exception->getTraceAsString(), $data['detail']);
    }

    public function testEnablingDisplayExceptionFlagRendersExceptionStackTrace(): void
    {
        $this->listener->setDisplayExceptions(true);
        ob_start();
        $this->listener->sendContent($this->event);
        $contents = ob_get_clean();
        $this->assertIsString($contents);
        $data = json_decode($contents, true);
        $this->assertArrayHasKey('trace', $data);
        $this->assertIsArray($data['trace']);
        $this->assertGreaterThanOrEqual(1, count($data['trace']));
    }

    public function testSendContentDoesNothingIfEventDoesNotContainApiProblemResponse(): void
    {
        $this->event->setResponse(new HttpResponse());
        ob_start();
        $this->listener->sendContent($this->event);
        $contents = ob_get_clean();
        $this->assertIsString($contents);
        $this->assertEmpty($contents);
    }

    public function testSendHeadersMergesApplicationAndProblemHttpHeaders(): void
    {
        $appResponse = new HttpResponse();
        $appResponse->getHeaders()->addHeaderLine('Access-Control-Allow-Origin', '*');

        $listener = new SendApiProblemResponseListener();
        $listener->setApplicationResponse($appResponse);

        ob_start();
        $listener->sendHeaders($this->event);
        ob_get_clean();

        $headers = $this->response->getHeaders();
        $this->assertTrue($headers->has('Access-Control-Allow-Origin'));
    }
}
