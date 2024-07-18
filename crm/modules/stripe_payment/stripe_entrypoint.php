<?php
function connectToStripe() {
    ob_start(); // ensures anything dumped out will be caught

    // do stuff here
    $url = $crm_root . '/crm/modules/stripe_payment/checkout.html'; // this can be set based on whatever

    // clear out the output buffer
    while (ob_get_status()) 
    {
        ob_end_clean();
    }

    // no redirect
    header( "Location: $url" );
}
?>