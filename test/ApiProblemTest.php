<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\ApiProblem;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

class ApiProblemTest extends TestCase
{
    public function statusCodes(): array
    {
        return [
            '200' => [200],
            '201' => [201],
            '300' => [300],
            '301' => [301],
            '302' => [302],
            '400' => [400],
            '401' => [401],
            '404' => [404],
            '500' => [500],
        ];
    }

    /**
     * @dataProvider statusCodes
     */
    public function testStatusIsUsedVerbatim($status): void
    {
        $apiProblem = new ApiProblem($status, 'foo');
        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('status', $payload);
        self::assertEquals($status, $payload['status']);
    }

    /**
     * @requires PHP 7.0
     */
    public function testErrorAsDetails(): void
    {
        $error = new \TypeError('error message', 705);
        $apiProblem = new ApiProblem(500, $error);
        $payload = $apiProblem->toArray();

        self::assertArrayHasKey('title', $payload);
        self::assertEquals('TypeError', $payload['title']);
        self::assertArrayHasKey('status', $payload);
        self::assertEquals(705, $payload['status']);
        self::assertArrayHasKey('detail', $payload);
        self::assertEquals('error message', $payload['detail']);
    }

    public function testExceptionCodeIsUsedForStatus(): void
    {
        $exception = new \Exception('exception message', 401);
        $apiProblem = new ApiProblem('500', $exception);
        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('status', $payload);
        self::assertEquals($exception->getCode(), $payload['status']);
    }

    public function testDetailStringIsUsedVerbatim(): void
    {
        $apiProblem = new ApiProblem('500', 'foo');
        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('detail', $payload);
        self::assertEquals('foo', $payload['detail']);
    }

    public function testExceptionMessageIsUsedForDetail(): void
    {
        $exception = new \Exception('exception message');
        $apiProblem = new ApiProblem('500', $exception);
        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('detail', $payload);
        self::assertEquals($exception->getMessage(), $payload['detail']);
    }

    public function testExceptionsCanTriggerInclusionOfStackTraceInDetails(): void
    {
        $exception = new \Exception('exception message');
        $apiProblem = new ApiProblem('500', $exception);
        $apiProblem->setDetailIncludesStackTrace(true);
        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('trace', $payload);
        self::assertIsArray($payload['trace']);
        self::assertEquals($exception->getTrace(), $payload['trace']);
    }

    public function testExceptionsCanTriggerInclusionOfNestedExceptions(): void
    {
        $exceptionChild = new \Exception('child exception');
        $exceptionParent = new \Exception('parent exception', null, $exceptionChild);

        $apiProblem = new ApiProblem('500', $exceptionParent);
        $apiProblem->setDetailIncludesStackTrace(true);
        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('exception_stack', $payload);
        self::assertIsArray($payload['exception_stack']);
        $expected = [
            [
                'code' => $exceptionChild->getCode(),
                'message' => $exceptionChild->getMessage(),
                'trace' => $exceptionChild->getTrace(),
            ],
        ];
        self::assertEquals($expected, $payload['exception_stack']);
    }

    public function testTypeUrlIsUsedVerbatim(): void
    {
        $apiProblem = new ApiProblem('500', 'foo', 'http://status.dev:8080/details.md');
        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('type', $payload);
        self::assertEquals('http://status.dev:8080/details.md', $payload['type']);
    }

    public function knownStatusCodes(): array
    {
        return [
            '404' => [404],
            '409' => [409],
            '422' => [422],
            '500' => [500],
        ];
    }

    /**
     * @dataProvider knownStatusCodes
     */
    public function testKnownStatusResultsInKnownTitle($status): void
    {
        $apiProblem = new ApiProblem($status, 'foo');
        $r = new ReflectionObject($apiProblem);
        $p = $r->getProperty('problemStatusTitles');
        $p->setAccessible(true);
        $titles = $p->getValue($apiProblem);

        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('title', $payload);
        self::assertEquals($titles[$status], $payload['title']);
    }

    public function testUnknownStatusResultsInUnknownTitle(): void
    {
        $apiProblem = new ApiProblem(420, 'foo');
        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('title', $payload);
        self::assertEquals('Unknown', $payload['title']);
    }

    public function testProvidedTitleIsUsedVerbatim(): void
    {
        $apiProblem = new ApiProblem('500', 'foo', 'http://status.dev:8080/details.md', 'some title');
        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('title', $payload);
        self::assertEquals('some title', $payload['title']);
    }

    public function testCanPassArbitraryDetailsToConstructor(): void
    {
        $problem = new ApiProblem(
            400,
            'Invalid input',
            'http://example.com/api/problem/400',
            'Invalid entity',
            ['foo' => 'bar']
        );
        self::assertEquals('bar', $problem->foo);
    }

    public function testArraySerializationIncludesArbitraryDetails(): void
    {
        $problem = new ApiProblem(
            400,
            'Invalid input',
            'http://example.com/api/problem/400',
            'Invalid entity',
            ['foo' => 'bar']
        );
        $array = $problem->toArray();
        self::assertArrayHasKey('foo', $array);
        self::assertEquals('bar', $array['foo']);
    }

    public function testArbitraryDetailsShouldNotOverwriteRequiredFieldsInArraySerialization(): void
    {
        $problem = new ApiProblem(
            400,
            'Invalid input',
            'http://example.com/api/problem/400',
            'Invalid entity',
            ['title' => 'SHOULD NOT GET THIS']
        );
        $array = $problem->toArray();
        self::assertArrayHasKey('title', $array);
        self::assertEquals('Invalid entity', $array['title']);
    }

    public function testUsesTitleFromExceptionWhenProvided(): void
    {
        $exception = new Exception\DomainException('exception message', 401);
        $exception->setTitle('problem title');
        $apiProblem = new ApiProblem('401', $exception);
        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('title', $payload);
        self::assertEquals($exception->getTitle(), $payload['title']);
    }

    public function testUsesTypeFromExceptionWhenProvided(): void
    {
        $exception = new Exception\DomainException('exception message', 401);
        $exception->setType('http://example.com/api/help/401');
        $apiProblem = new ApiProblem('401', $exception);
        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('type', $payload);
        self::assertEquals($exception->getType(), $payload['type']);
    }

    public function testUsesAdditionalDetailsFromExceptionWhenProvided(): void
    {
        $exception = new Exception\DomainException('exception message', 401);
        $exception->setAdditionalDetails(['foo' => 'bar']);
        $apiProblem = new ApiProblem('401', $exception);
        $payload = $apiProblem->toArray();
        self::assertArrayHasKey('foo', $payload);
        self::assertEquals('bar', $payload['foo']);
    }

    public function invalidStatusCodes(): array
    {
        return [
            '-1'  => [-1],
            '0'   => [0],
            '7'   => [7],  // reported
            '14'  => [14], // observed
            '600' => [600],
        ];
    }

    /**
     * @dataProvider invalidStatusCodes
     * @group api-tools-118
     */
    public function testInvalidHttpStatusCodesAreCastTo500($code): void
    {
        $e = new \Exception('Testing', $code);
        $problem = new ApiProblem($code, $e);
        self::assertEquals(500, $problem->status);
    }
}
