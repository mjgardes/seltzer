<?php

require_once '../variable/variable.inc.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$stripeApiKey = $_ENV['PAYMENT_SECRET_KEY'];

?>
