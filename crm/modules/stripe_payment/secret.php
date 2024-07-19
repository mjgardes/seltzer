<?php
// Save path of directory containing index.php
$crm_root = realpath(__DIR__ . '/../..');

require_once('../variable/variable.inc.php');

// This allows the database to connect
require_once($crm_root . '/include/crm.inc.php');

$stripeApiKey = variable_get("paymentProcessorApiKey", "NO_KEY_PROVIDED-UPDATE__SERVER_CONFIGURATION");

?>
