<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\ApiProblem\View;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\View\ApiProblemModel;
use Laminas\ApiTools\ApiProblem\View\ApiProblemRenderer;
use Laminas\ApiTools\ApiProblem\View\ApiProblemStrategy;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\ViewEvent;
use PHPUnit\Framework\TestCase;

class ApiProblemStrategyTest extends TestCase
{
    /** @var Response */
    protected $response;

    /** @var ViewEvent */
    protected $event;

    /** @var ApiProblemRenderer */
    protected $renderer;

    /** @var ApiProblemStrategy */
    protected $strategy;

    protected function setUp(): void
    {
        $this->response = new Response();
        $this->event    = new ViewEvent();
        $this->event->setResponse($this->response);

        $this->renderer = new ApiProblemRenderer();
        $this->strategy = new ApiProblemStrategy($this->renderer);
    }

    /** @psalm-return array<string, array{0: null|ViewModel}> */
    public static function invalidViewModels()
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
    public function testSelectRendererReturnsNullIfModelIsNotAnApiProblemModel(?ViewModel $model): void
    {
        if (null !== $model) {
            $this->event->setModel($model);
        }
        $this->assertNull($this->strategy->selectRenderer($this->event));
    }

    public function testSelectRendererReturnsRendererIfModelIsAnApiProblemModel(): void
    {
        $model = new ApiProblemModel();
        $this->event->setModel($model);
        $this->assertSame($this->renderer, $this->strategy->selectRenderer($this->event));
    }

    public function testInjectResponseDoesNotSetContentTypeHeaderIfResultIsNotString(): void
    {
        $this->event->setRenderer($this->renderer);
        $this->event->setResult(['foo']);
        $this->strategy->injectResponse($this->event);
        $headers = $this->response->getHeaders();
        $this->assertFalse($headers->has('Content-Type'));
    }

    public function testInjectResponseSetsContentTypeHeaderToApiProblemForApiProblemModel(): void
    {
        $problem = new ApiProblem(500, 'whatever', 'foo', 'bar');
        $model   = new ApiProblemModel($problem);
        $this->event->setModel($model);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult('{"foo":"bar"}');
        $this->strategy->injectResponse($this->event);
        $headers = $this->response->getHeaders();
        $this->assertTrue($headers->has('Content-Type'));
        $this->assertInstanceOf(HeaderInterface::class, $header = $headers->get('Content-Type'));
        $this->assertEquals(ApiProblem::CONTENT_TYPE, $header->getFieldValue());
    }

    /** @psalm-return array<array-key, array{0: int}> */
    public static function invalidStatusCodes(): array
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
    public function testUsesStatusCode500ForAnyStatusCodesAbove599OrBelow100(int $status): void
    {
        $problem = new ApiProblem($status, 'whatever');
        $model   = new ApiProblemModel($problem);
        $this->event->setModel($model);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult('{"foo":"bar"}');
        $this->strategy->injectResponse($this->event);

        $this->assertEquals(500, $this->response->getStatusCode());
    }
}
