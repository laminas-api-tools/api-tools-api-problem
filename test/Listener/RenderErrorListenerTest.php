<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\ApiProblem\Listener;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\Listener\RenderErrorListener;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypeError;

class RenderErrorListenerTest extends TestCase
{
    /**
     * @var RenderErrorListener
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->listener = new RenderErrorListener();
    }

    public function testOnRenderErrorCreatesAnApiProblemResponse(): void
    {
        $response = new Response();
        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setRequest($request);
        $event->setResponse($response);
        $this->listener->onRenderError($event);

        self::assertTrue($event->propagationIsStopped());
        self::assertSame($response, $event->getResponse());

        self::assertEquals(406, $response->getStatusCode());
        $headers = $response->getHeaders();
        self::assertTrue($headers->has('Content-Type'));
        self::assertEquals(ApiProblem::CONTENT_TYPE, $headers->get('content-type')->getFieldValue());
        $content = \json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('status', $content);
        self::assertArrayHasKey('title', $content);
        self::assertArrayHasKey('describedBy', $content);
        self::assertArrayHasKey('detail', $content);

        self::assertEquals(406, $content['status']);
        self::assertEquals('Not Acceptable', $content['title']);
        self::assertStringContainsString('www.w3.org', $content['describedBy']);
        self::assertStringContainsString('accept', $content['detail']);
    }

    public function testOnRenderErrorCreatesAnApiProblemResponseFromException(): void
    {
        $response = new Response();
        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', new RuntimeException('exception', 400));
        $event->setRequest($request);
        $event->setResponse($response);

        $this->listener->setDisplayExceptions(true);
        $this->listener->onRenderError($event);

        self::assertTrue($event->propagationIsStopped());
        self::assertSame($response, $event->getResponse());

        self::assertEquals(400, $response->getStatusCode());
        $headers = $response->getHeaders();
        self::assertTrue($headers->has('Content-Type'));
        self::assertEquals(ApiProblem::CONTENT_TYPE, $headers->get('content-type')->getFieldValue());
        $content = \json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('status', $content);
        self::assertArrayHasKey('title', $content);
        self::assertArrayHasKey('describedBy', $content);
        self::assertArrayHasKey('detail', $content);
        self::assertArrayHasKey('details', $content);

        self::assertEquals(400, $content['status']);
        self::assertEquals('Unexpected error', $content['title']);
        self::assertStringContainsString('www.w3.org', $content['describedBy']);
        self::assertEquals('exception', $content['detail']);

        self::assertIsArray($content['details']);
        $details = $content['details'];
        self::assertArrayHasKey('code', $details);
        self::assertArrayHasKey('message', $details);
        self::assertArrayHasKey('trace', $details);
        self::assertEquals(400, $details['code']);
        self::assertEquals('exception', $details['message']);
    }

    /**
     * @requires PHP 7.0
     */
    public function testOnRenderErrorCreatesAnApiProblemResponseFromThrowable(): void
    {
        $response = new Response();
        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', new TypeError('throwable', 400));
        $event->setRequest($request);
        $event->setResponse($response);

        $this->listener->setDisplayExceptions(true);
        $this->listener->onRenderError($event);

        self::assertTrue($event->propagationIsStopped());
        self::assertSame($response, $event->getResponse());

        self::assertEquals(400, $response->getStatusCode());
        $headers = $response->getHeaders();
        self::assertTrue($headers->has('Content-Type'));
        self::assertEquals(ApiProblem::CONTENT_TYPE, $headers->get('content-type')->getFieldValue());
        $content = \json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('status', $content);
        self::assertArrayHasKey('title', $content);
        self::assertArrayHasKey('describedBy', $content);
        self::assertArrayHasKey('detail', $content);
        self::assertArrayHasKey('details', $content);

        self::assertEquals(400, $content['status']);
        self::assertEquals('Unexpected error', $content['title']);
        self::assertStringContainsString('www.w3.org', $content['describedBy']);
        self::assertEquals('throwable', $content['detail']);

        self::assertIsArray($content['details']);
        $details = $content['details'];
        self::assertArrayHasKey('code', $details);
        self::assertArrayHasKey('message', $details);
        self::assertArrayHasKey('trace', $details);
        self::assertEquals(400, $details['code']);
        self::assertEquals('throwable', $details['message']);
    }
}
