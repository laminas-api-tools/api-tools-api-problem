<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\ApiProblem;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use PHPUnit\Framework\TestCase;

class ApiProblemResponseTest extends TestCase
{
    public function testApiProblemResponseIsAnHttpResponse(): void
    {
        $response = new ApiProblemResponse(new ApiProblem(400, 'Random error'));
        self::assertInstanceOf('Laminas\Http\Response', $response);
    }

    /**
     * @depends testApiProblemResponseIsAnHttpResponse
     */
    public function testApiProblemResponseSetsStatusCodeAndReasonPhrase(): void
    {
        $response = new ApiProblemResponse(new ApiProblem(400, 'Random error'));
        self::assertEquals(400, $response->getStatusCode());
        self::assertIsString($response->getReasonPhrase());
        self::assertNotEmpty($response->getReasonPhrase());
        self::assertEquals('bad request', strtolower($response->getReasonPhrase()));
    }

    public function testApiProblemResponseBodyIsSerializedApiProblem(): void
    {
        $additional = [
            'foo' => fopen('php://memory', 'rb')
        ];

        $expected = [
            'foo' => null,
            'type' => 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html',
            'title' => 'Bad Request',
            'status' => 400,
            'detail' => 'Random error',
        ];

        $apiProblem = new ApiProblem(400, 'Random error', null, null, $additional);
        $response   = new ApiProblemResponse($apiProblem);
        self::assertEquals($expected, \json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR));
    }

    /**
     * @depends testApiProblemResponseIsAnHttpResponse
     */
    public function testApiProblemResponseSetsContentTypeHeader(): void
    {
        $response = new ApiProblemResponse(new ApiProblem(400, 'Random error'));
        $headers  = $response->getHeaders();
        self::assertTrue($headers->has('content-type'));
        $header = $headers->get('content-type');
        self::assertInstanceOf('Laminas\Http\Header\ContentType', $header);
        self::assertEquals(ApiProblem::CONTENT_TYPE, $header->getFieldValue());
    }

    public function testComposeApiProblemIsAccessible(): void
    {
        $apiProblem = new ApiProblem(400, 'Random error');
        $response   = new ApiProblemResponse($apiProblem);
        self::assertSame($apiProblem, $response->getApiProblem());
    }

    /**
     * @group 14
     */
    public function testOverridesReasonPhraseIfStatusCodeIsUnknown(): void
    {
        $response = new ApiProblemResponse(new ApiProblem(7, 'Random error'));
        self::assertStringContainsString('Internal Server Error', $response->getReasonPhrase());
    }
}
