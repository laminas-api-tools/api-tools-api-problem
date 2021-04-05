<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem\View;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\View\Strategy\JsonStrategy;
use Laminas\View\ViewEvent;

use function is_string;

/**
 * Extension of the JSON strategy to handle the ApiProblemModel and provide
 * a Content-Type header appropriate to the response it describes.
 *
 * This will give the following content types:
 *
 * - application/problem+json for a result that contains a Problem
 *   API-formatted response
 */
class ApiProblemStrategy extends JsonStrategy
{
    public function __construct(ApiProblemRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Detect if we should use the ApiProblemRenderer based on model type.
     *
     * @return null|ApiProblemRenderer
     */
    public function selectRenderer(ViewEvent $e)
    {
        $model = $e->getModel();

        if (! $model instanceof ApiProblemModel) {
            // unrecognized model; do nothing
            return;
        }

        // ApiProblemModel found
        return $this->renderer;
    }

    /**
     * Inject the response.
     *
     * Injects the response with the rendered content, and sets the content
     * type based on the detection that occurred during renderer selection.
     */
    public function injectResponse(ViewEvent $e)
    {
        $result = $e->getResult();
        if (! is_string($result)) {
            // We don't have a string, and thus, no JSON
            return;
        }

        $model = $e->getModel();
        if (! $model instanceof ApiProblemModel) {
            // Model is not an ApiProblemModel; we cannot handle it here
            return;
        }

        $problem     = $model->getApiProblem();
        $statusCode  = $this->getStatusCodeFromApiProblem($problem);
        $contentType = ApiProblem::CONTENT_TYPE;

        // Populate response
        $response = $e->getResponse();
        $response->setStatusCode($statusCode);
        $response->setContent($result);
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', $contentType);
    }

    /**
     * Retrieve the HTTP status from an ApiProblem object.
     *
     * Ensures that the status falls within the acceptable range (100 - 599).
     *
     * @return int
     */
    protected function getStatusCodeFromApiProblem(ApiProblem $problem)
    {
        $status = $problem->status;

        if ($status < 100 || $status >= 600) {
            return 500;
        }

        return $status;
    }
}
