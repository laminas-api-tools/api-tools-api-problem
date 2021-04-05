<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem\View;

use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\JsonRenderer;

class ApiProblemRenderer extends JsonRenderer
{
    /**
     * Whether or not to render exception stack traces in API-Problem payloads.
     *
     * @var bool
     */
    protected $displayExceptions = false;

    /**
     * Set display_exceptions flag.
     *
     * @param bool $flag
     * @return self
     */
    public function setDisplayExceptions($flag)
    {
        $this->displayExceptions = (bool) $flag;

        return $this;
    }

    /**
     * @param string|ModelInterface $nameOrModel
     * @param array|null                             $values
     * @return string
     */
    public function render($nameOrModel, $values = null)
    {
        if (! $nameOrModel instanceof ApiProblemModel) {
            return '';
        }

        $apiProblem = $nameOrModel->getApiProblem();

        if ($this->displayExceptions) {
            $apiProblem->setDetailIncludesStackTrace(true);
        }

        return parent::render($apiProblem->toArray());
    }
}
