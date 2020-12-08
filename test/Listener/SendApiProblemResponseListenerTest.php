<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\ApiProblem\Listener;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ApiProblem\Exception\DomainException;
use Laminas\ApiTools\ApiProblem\Listener\SendApiProblemResponseListener;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\ResponseSender\SendResponseEvent;
use PHPUnit\Framework\TestCase;
use Laminas\Mvc\ResponseSender\ResponseSenderInterface;

class SendApiProblemResponseListenerTest extends TestCase
{
    /**
     * @var DomainException
     */
    private $exception;

    /**
     * @var ApiProblemResponse
     */
    private $response;

    /**
     * @var SendResponseEvent
     */
    private $event;

    /**
     * @var SendApiProblemResponseListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->exception = new DomainException('Random error', 400);
        $this->response  = new ApiProblemResponse(new ApiProblem(400, $this->exception));
        $this->event     = new SendResponseEvent();
        $this->event->setResponse($this->response);
        $this->listener = new SendApiProblemResponseListener();
    }

    public function testListenerImplementsResponseSenderInterface(): void
    {
        self::assertInstanceOf(ResponseSenderInterface::class, $this->listener);
    }

    public function testDisplayExceptionsFlagIsFalseByDefault(): void
    {
        self::assertFalse($this->listener->displayExceptions());
    }

    /**
     * @depends testDisplayExceptionsFlagIsFalseByDefault
     */
    public function testDisplayExceptionsFlagIsMutable(): void
    {
        $this->listener->setDisplayExceptions(true);
        self::assertTrue($this->listener->displayExceptions());
    }

    /**
     * @depends testDisplayExceptionsFlagIsFalseByDefault
     */
    public function testSendContentDoesNotRenderExceptionsByDefault(): void
    {
        \ob_start();
        $this->listener->sendContent($this->event);
        $contents = \ob_get_clean();
        self::assertIsString($contents);
        $data = \json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString("\n", $data['detail']);
        self::assertStringNotContainsString($this->exception->getTraceAsString(), $data['detail']);
    }

    public function testEnablingDisplayExceptionFlagRendersExceptionStackTrace(): void
    {
        $this->listener->setDisplayExceptions(true);
        \ob_start();
        $this->listener->sendContent($this->event);
        $contents = \ob_get_clean();
        self::assertIsString($contents);
        $data = \json_decode($contents, true, 512, \JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('trace', $data);
        self::assertIsArray($data['trace']);
        self::assertGreaterThanOrEqual(1, count($data['trace']));
    }

    public function testSendContentDoesNothingIfEventDoesNotContainApiProblemResponse(): void
    {
        $this->event->setResponse(new HttpResponse());
        \ob_start();
        $this->listener->sendContent($this->event);
        $contents = \ob_get_clean();
        self::assertIsString($contents);
        self::assertEmpty($contents);
    }

    public function testSendHeadersMergesApplicationAndProblemHttpHeaders(): void
    {
        $appResponse = new HttpResponse();
        $appResponse->getHeaders()->addHeaderLine('Access-Control-Allow-Origin', '*');

        $listener = new SendApiProblemResponseListener();
        $listener->setApplicationResponse($appResponse);

        \ob_start();
        $listener->sendHeaders($this->event);
        \ob_get_clean();

        $headers = $this->response->getHeaders();
        self::assertTrue($headers->has('Access-Control-Allow-Origin'));
    }
}
