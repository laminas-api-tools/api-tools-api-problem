<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-api-problem for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-api-problem/blob/master/LICENSE.md New BSD License
 */

return array(
    'api-tools-api-problem' => array(
        /*
        'accept_filter' => $stringOrArray, // Accept types that should allow ApiProblem responses
        'render_error_controllers' => array(), // Array of controller service names that should
                                               // enable the ApiProblem render.error listener
         */
    ),

    'view_manager' => array(
        // Enable this in your application configuration in order to get full
        // exception stack traces in your API-Problem responses.
        'display_exceptions' => false,
    ),
);
