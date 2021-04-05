<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\ApiProblem\View;

use Exception;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\View\ApiProblemModel;
use Laminas\ApiTools\ApiProblem\View\ApiProblemRenderer;
use PHPUnit\Framework\TestCase;

use function count;
use function json_decode;

class ApiProblemRendererTest extends TestCase
{
    protected function setUp(): void
    {
        $this->renderer = new ApiProblemRenderer();
    }

    public function testRendersApiProblemCorrectly()
    {
        $apiProblem = new ApiProblem(401, 'login error', 'http://status.dev/errors.md', 'Unauthorized');
        $model      = new ApiProblemModel();
        $model->setApiProblem($apiProblem);
        $test     = $this->renderer->render($model);
        $expected = [
            'status' => 401,
            'type'   => 'http://status.dev/errors.md',
            'title'  => 'Unauthorized',
            'detail' => 'login error',
        ];
        $this->assertEquals($expected, json_decode($test, true));
    }

    public function testCanHintToApiProblemToRenderStackTrace()
    {
        $exception  = new Exception('exception message', 500);
        $apiProblem = new ApiProblem(500, $exception);
        $model      = new ApiProblemModel();
        $model->setApiProblem($apiProblem);
        $this->renderer->setDisplayExceptions(true);
        $test = $this->renderer->render($model);
        $test = json_decode($test, true);
        $this->assertArrayHasKey('trace', $test);
        $this->assertIsArray($test['trace']);
        $this->assertGreaterThanOrEqual(1, count($test['trace']));
    }
}
