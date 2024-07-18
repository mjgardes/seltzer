<?php

require_once '../../vendor/autoload.php';
require_once 'secret.php';

\Stripe\Stripe::setApiKey($stripeApiKey);

header('Content-Type: application/json');

$YOUR_DOMAIN = getenv('PAYMENT_DOMAIN_REDIRECT_URL_BASE') . '/crm/modules/stripe_payment';
// $YOUR_DOMAIN = 'google.com';

try {
  $prices = \Stripe\Price::all([
    // retrieve lookup_key from form data POST body
    'lookup_keys' => [$_POST['lookup_key']],
    'expand' => ['data.product']
  ]);

  $checkout_session = \Stripe\Checkout\Session::create([
    'line_items' => [[
      'price' => $prices->data[0]->id,
      'quantity' => 1,
    ]],
    'mode' => 'subscription',
    'success_url' => $YOUR_DOMAIN . '/crm/modules/stripe_payment/success.html?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => $YOUR_DOMAIN . '/crm/modules/stripe_payment/cancel.html',
  ]);

  header("HTTP/1.1 303 See Other");
  header("Location: " . $checkout_session->url);
} catch (Error $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}