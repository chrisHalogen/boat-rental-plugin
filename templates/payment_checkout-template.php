<?php

/**
 * Template Name: Payment Checkout Page
 * Description: A payment checkout page template.
 */

error_log("In Page");

$stripe_secret_key = get_option('wp_rental_stripe_test_key', '');

$stripe_secret_key = "eoivoier"

error_log($stripe_secret_key);

\Stripe\Stripe::setApiKey($stripe_secret_key);

// $checkout_session = \Stripe\Checkout\Session::create([
//     "mode" => "payment",
//     "success_url" => home_url('/payment-success'),
//     "cancel_url" => home_url('/payment-demo'),
//     "locale" => "auto",
//     // "payment_method_types" => ["card"],
//     "payment_method_types" => ["card", "alipay", "paypal", "giropay", "applepay"],
//     "line_items" => [
//         [
//             "quantity" => 1,
//             "price_data" => [
//                 "currency" => "usd",
//                 "unit_amount" => 2000,
//                 "product_data" => [
//                     "name" => "T-shirt"
//                 ]
//             ]
//         ],

//     ]
// ]);

$checkout_session = \Stripe\Checkout\Session::create([
    "mode" => "payment",
    "success_url" => home_url('/payment-success'),
    "cancel_url" => home_url('/payment-demo'),
    "locale" => "auto",
    "customer_email" => "eruiieuiuu@gmail.com", // Pre-fill and lock the email
    // "customer_details" => [
    //     "name" => "John Doe", // Pre-fill and lock the name
    // ],
    "payment_method_types" => [
        "card",          // Credit/Debit cards
        "affirm",        // Buy now, pay later (US)
        "afterpay_clearpay", // Buy now, pay later (US, UK, AU, etc.)
        "alipay",        // Alipay (cross-border payments)
        "cashapp",       // Cash App Pay (US)
        "klarna",        // Klarna (US and other regions)
        "link",          // Link by Stripe (US)
        // "paypal",        // PayPal (global, including US)
        "us_bank_account", // US bank account (ACH)
        "amazon_pay",    // Amazon Pay (US and other regions)

    ],
    "line_items" => [
        [
            "quantity" => 1,
            "price_data" => [
                "currency" => "usd",
                "unit_amount" => 7000,
                "product_data" => [
                    "name" => "T-shirt"
                ]
            ]
        ],
    ]
]);

error_log($checkout_session);

http_response_code(303);
error_log("Redirecting");
safe_redirect($checkout_session->url);
// header("Location: " . $checkout_session->url);
