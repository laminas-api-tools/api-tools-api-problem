<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem\View;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\View\Model\ViewModel;

class ApiProblemModel extends ViewModel
{
    protected $captureTo = 'errors';
    protected $problem;
    protected $terminate = true;

    public function __construct($problem = null, $options = null)
    {
        if ($problem instanceof ApiProblem) {
            $this->setApiProblem($problem);
        }
    }

    public function setApiProblem(ApiProblem $problem)
    {
        $this->problem = $problem;
        return $this;
    }

    public function getApiProblem()
    {
        return $this->problem;
    }
}
