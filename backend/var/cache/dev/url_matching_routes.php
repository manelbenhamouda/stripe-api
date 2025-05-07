<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/api/checkout' => [[['_route' => 'create_checkout_session', '_controller' => 'App\\Controller\\PaymentController::createCheckoutSession'], null, ['POST' => 0], null, false, false, null]],
        '/api/webhook' => [[['_route' => 'stripe_webhook', '_controller' => 'App\\Controller\\PaymentController::stripeWebhook'], null, ['POST' => 0], null, false, false, null]],
        '/api/products' => [
            [['_route' => 'product_list', '_controller' => 'App\\Controller\\ProductController::index'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'product_create', '_controller' => 'App\\Controller\\ProductController::create'], null, ['POST' => 0], null, false, false, null],
        ],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_error/(\\d+)(?:\\.([^/]++))?(*:35)'
                .'|/api/p(?'
                    .'|ayment/(?'
                        .'|success/([^/]++)(*:77)'
                        .'|cancel/([^/]++)(*:99)'
                    .')'
                    .'|roducts/([^/]++)(?'
                        .'|(*:126)'
                    .')'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        35 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        77 => [[['_route' => 'payment_success', '_controller' => 'App\\Controller\\PaymentController::paymentSuccess'], ['orderId'], ['GET' => 0], null, false, true, null]],
        99 => [[['_route' => 'payment_cancel', '_controller' => 'App\\Controller\\PaymentController::paymentCancel'], ['orderId'], ['GET' => 0], null, false, true, null]],
        126 => [
            [['_route' => 'product_show', '_controller' => 'App\\Controller\\ProductController::show'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => 'product_update', '_controller' => 'App\\Controller\\ProductController::update'], ['id'], ['PUT' => 0], null, false, true, null],
            [['_route' => 'product_delete', '_controller' => 'App\\Controller\\ProductController::delete'], ['id'], ['DELETE' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
