<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

return array(
    'service_manager' => array(
        'aliases'   => array(
            'Laminas\ApiTools\ApiProblem\ApiProblemListener'  => 'Laminas\ApiTools\ApiProblem\Listener\ApiProblemListener',
            'Laminas\ApiTools\ApiProblem\RenderErrorListener' => 'Laminas\ApiTools\ApiProblem\Listener\RenderErrorListener',
            'Laminas\ApiTools\ApiProblem\ApiProblemRenderer'  => 'Laminas\ApiTools\ApiProblem\View\ApiProblemRenderer',
            'Laminas\ApiTools\ApiProblem\ApiProblemStrategy'  => 'Laminas\ApiTools\ApiProblem\View\ApiProblemStrategy',
        ),
        'factories' => array(
            'Laminas\ApiTools\ApiProblem\Listener\ApiProblemListener'             => 'Laminas\ApiTools\ApiProblem\Factory\ApiProblemListenerFactory',
            'Laminas\ApiTools\ApiProblem\Listener\RenderErrorListener'            => 'Laminas\ApiTools\ApiProblem\Factory\RenderErrorListenerFactory',
            'Laminas\ApiTools\ApiProblem\Listener\SendApiProblemResponseListener' => 'Laminas\ApiTools\ApiProblem\Factory\SendApiProblemResponseListenerFactory',
            'Laminas\ApiTools\ApiProblem\View\ApiProblemRenderer'                 => 'Laminas\ApiTools\ApiProblem\Factory\ApiProblemRendererFactory',
            'Laminas\ApiTools\ApiProblem\View\ApiProblemStrategy'                 => 'Laminas\ApiTools\ApiProblem\Factory\ApiProblemStrategyFactory',
        )
    ),

    'view_manager' => array(
        // Enable this in your application configuration in order to get full
        // exception stack traces in your API-Problem responses.
        'display_exceptions' => false,
    ),

    'api-tools-api-problem' => array(
        // Accept types that should allow ApiProblem responses
        // 'accept_filter' => $stringOrArray,

        // Array of controller service names that should enable the ApiProblem render.error listener
        //'render_error_controllers' => array(),
    )
);
