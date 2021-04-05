<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\ApiProblem;

return [
    'service_manager'       => [
        'aliases'   => [
            ApiProblemListener::class  => Listener\ApiProblemListener::class,
            RenderErrorListener::class => Listener\RenderErrorListener::class,
            ApiProblemRenderer::class  => View\ApiProblemRenderer::class,
            ApiProblemStrategy::class  => View\ApiProblemStrategy::class,

            // Legacy Zend Framework aliases
            // @codingStandardsIgnoreStart
            \ZF\ApiProblem\ApiProblemListener::class                      => ApiProblemListener::class,
            \ZF\ApiProblem\RenderErrorListener::class                     => RenderErrorListener::class,
            \ZF\ApiProblem\ApiProblemRenderer::class                      => ApiProblemRenderer::class,
            \ZF\ApiProblem\ApiProblemStrategy::class                      => ApiProblemStrategy::class,
            \ZF\ApiProblem\Listener\ApiProblemListener::class             => Listener\ApiProblemListener::class,
            \ZF\ApiProblem\Listener\RenderErrorListener::class            => Listener\RenderErrorListener::class,
            \ZF\ApiProblem\Listener\SendApiProblemResponseListener::class => Listener\SendApiProblemResponseListener::class,
            \ZF\ApiProblem\View\ApiProblemRenderer::class                 => View\ApiProblemRenderer::class,
            \ZF\ApiProblem\View\ApiProblemStrategy::class                 => View\ApiProblemStrategy::class,
            // @codingStandardsIgnoreEnd
        ],
        'factories' => [
            Listener\ApiProblemListener::class             => Factory\ApiProblemListenerFactory::class,
            Listener\RenderErrorListener::class            => Factory\RenderErrorListenerFactory::class,
            Listener\SendApiProblemResponseListener::class => Factory\SendApiProblemResponseListenerFactory::class,
            View\ApiProblemRenderer::class                 => Factory\ApiProblemRendererFactory::class,
            View\ApiProblemStrategy::class                 => Factory\ApiProblemStrategyFactory::class,
        ],
    ],
    'view_manager'          => [
        // Enable this in your application configuration in order to get full
        // exception stack traces in your API-Problem responses.
        'display_exceptions' => false,
    ],
    'api-tools-api-problem' => [
        // Accept types that should allow ApiProblem responses
        // 'accept_filters' => $stringOrArray,

        // Array of controller service names that should enable the ApiProblem render.error listener
        // 'render_error_controllers' => [],
    ],
];
