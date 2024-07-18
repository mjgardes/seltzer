<?php

require_once '../../vendor/autoload.php';

\Stripe\Stripe::setApiKey(getenv('PAYMENT_SECRET_KEY'));

header('Content-Type: application/json');

$YOUR_DOMAIN = getenv('PAYMENT_DOMAIN') . '/crm/modules/stripe_payment/success.html';
// $YOUR_DOMAIN = 'google.com'

try {
  $checkout_session = \Stripe\Checkout\Session::retrieve($_POST['session_id']);
  $return_url = $YOUR_DOMAIN;

  // Authenticate your user.
  $session = \Stripe\BillingPortal\Session::create([
    'customer' => $checkout_session->customer,
    'return_url' => $return_url,
  ]);
  header("HTTP/1.1 303 See Other");
  header("Location: " . $session->url);
} catch (Error $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}