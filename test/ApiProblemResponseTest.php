<?php

namespace LaminasTest\ApiTools\ApiProblem;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use PHPUnit\Framework\TestCase;

class ApiProblemResponseTest extends TestCase
{
    public function testApiProblemResponseIsAnHttpResponse()
    {
        $response = new ApiProblemResponse(new ApiProblem(400, 'Random error'));
        $this->assertInstanceOf('Laminas\Http\Response', $response);
    }

    /**
     * @depends testApiProblemResponseIsAnHttpResponse
     */
    public function testApiProblemResponseSetsStatusCodeAndReasonPhrase()
    {
        $response = new ApiProblemResponse(new ApiProblem(400, 'Random error'));
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertInternalType('string', $response->getReasonPhrase());
        $this->assertNotEmpty($response->getReasonPhrase());
        $this->assertEquals('bad request', strtolower($response->getReasonPhrase()));
    }

    public function testApiProblemResponseBodyIsSerializedApiProblem()
    {
        $additional = [
            'foo' => fopen('php://memory', 'r')
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
        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }

    /**
     * @depends testApiProblemResponseIsAnHttpResponse
     */
    public function testApiProblemResponseSetsContentTypeHeader()
    {
        $response = new ApiProblemResponse(new ApiProblem(400, 'Random error'));
        $headers  = $response->getHeaders();
        $this->assertTrue($headers->has('content-type'));
        $header = $headers->get('content-type');
        $this->assertInstanceOf('Laminas\Http\Header\ContentType', $header);
        $this->assertEquals(ApiProblem::CONTENT_TYPE, $header->getFieldValue());
    }

    public function testComposeApiProblemIsAccessible()
    {
        $apiProblem = new ApiProblem(400, 'Random error');
        $response   = new ApiProblemResponse($apiProblem);
        $this->assertSame($apiProblem, $response->getApiProblem());
    }

    /**
     * @group 14
     */
    public function testOverridesReasonPhraseIfStatusCodeIsUnknown()
    {
        $response = new ApiProblemResponse(new ApiProblem(7, 'Random error'));
        $this->assertContains('Internal Server Error', $response->getReasonPhrase());
    }
}
