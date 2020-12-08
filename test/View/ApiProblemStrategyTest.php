<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\ApiProblem\View;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\View\ApiProblemModel;
use Laminas\ApiTools\ApiProblem\View\ApiProblemRenderer;
use Laminas\ApiTools\ApiProblem\View\ApiProblemStrategy;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\ViewEvent;
use PHPUnit\Framework\TestCase;

class ApiProblemStrategyTest extends TestCase
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var ViewEvent
     */
    private $event;

    /**
     * @var ApiProblemRenderer
     */
    private $renderer;

    /**
     * @var ApiProblemStrategy
     */
    private $strategy;

    protected function setUp(): void
    {
        $this->response = new Response();
        $this->event = new ViewEvent();
        $this->event->setResponse($this->response);

        $this->renderer = new ApiProblemRenderer();
        $this->strategy = new ApiProblemStrategy($this->renderer);
    }

    public function invalidViewModels(): array
    {
        return [
            'null'    => [null],
            'generic' => [new ViewModel()],
            'json'    => [new JsonModel()],
        ];
    }

    /**
     * @dataProvider invalidViewModels
     */
    public function testSelectRendererReturnsNullIfModelIsNotAnApiProblemModel($model): void
    {
        if (null !== $model) {
            $this->event->setModel($model);
        }
        self::assertNull($this->strategy->selectRenderer($this->event));
    }

    public function testSelectRendererReturnsRendererIfModelIsAnApiProblemModel(): void
    {
        $model = new ApiProblemModel();
        $this->event->setModel($model);
        self::assertSame($this->renderer, $this->strategy->selectRenderer($this->event));
    }

    public function testInjectResponseDoesNotSetContentTypeHeaderIfResultIsNotString(): void
    {
        $this->event->setRenderer($this->renderer);
        $this->event->setResult(['foo']);
        $this->strategy->injectResponse($this->event);
        $headers = $this->response->getHeaders();
        self::assertFalse($headers->has('Content-Type'));
    }

    public function testInjectResponseSetsContentTypeHeaderToApiProblemForApiProblemModel(): void
    {
        $problem = new ApiProblem(500, 'whatever', 'foo', 'bar');
        $model = new ApiProblemModel($problem);
        $this->event->setModel($model);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult('{"foo":"bar"}');
        $this->strategy->injectResponse($this->event);
        $headers = $this->response->getHeaders();
        self::assertTrue($headers->has('Content-Type'));
        $header = $headers->get('Content-Type');
        self::assertEquals(ApiProblem::CONTENT_TYPE, $header->getFieldValue());
    }

    public function invalidStatusCodes(): array
    {
        return [
            [0],
            [1],
            [99],
            [600],
            [10081],
        ];
    }

    /**
     * @dataProvider invalidStatusCodes
     */
    public function testUsesStatusCode500ForAnyStatusCodesAbove599OrBelow100($status): void
    {
        $problem = new ApiProblem($status, 'whatever');
        $model = new ApiProblemModel($problem);
        $this->event->setModel($model);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult('{"foo":"bar"}');
        $this->strategy->injectResponse($this->event);

        self::assertEquals(500, $this->response->getStatusCode());
    }
}
