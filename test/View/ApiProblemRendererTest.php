<?php

namespace LaminasTest\ApiTools\ApiProblem\View;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\View\ApiProblemModel;
use Laminas\ApiTools\ApiProblem\View\ApiProblemRenderer;
use PHPUnit\Framework\TestCase;

class ApiProblemRendererTest extends TestCase
{
    protected function setUp()
    {
        $this->renderer = new ApiProblemRenderer();
    }

    public function testRendersApiProblemCorrectly()
    {
        $apiProblem = new ApiProblem(401, 'login error', 'http://status.dev/errors.md', 'Unauthorized');
        $model = new ApiProblemModel();
        $model->setApiProblem($apiProblem);
        $test = $this->renderer->render($model);
        $expected = [
            'status' => 401,
            'type' => 'http://status.dev/errors.md',
            'title' => 'Unauthorized',
            'detail' => 'login error',
        ];
        $this->assertEquals($expected, json_decode($test, true));
    }

    public function testCanHintToApiProblemToRenderStackTrace()
    {
        $exception = new \Exception('exception message', 500);
        $apiProblem = new ApiProblem(500, $exception);
        $model = new ApiProblemModel();
        $model->setApiProblem($apiProblem);
        $this->renderer->setDisplayExceptions(true);
        $test = $this->renderer->render($model);
        $test = json_decode($test, true);
        $this->assertArrayHasKey('trace', $test);
        $this->assertInternalType('array', $test['trace']);
        $this->assertGreaterThanOrEqual(1, count($test['trace']));
    }
}
