<?php
/**
 * Account connect refresh link
 * rest_ensure_response()
 */
function prefix_get_endpoint_product_create($request) {
    $secret = WC_Stripe_API::get_secret_key(); 
	$de_stripe = new \Stripe\StripeClient($secret); 
    rest_ensure_response(json_encode($_POST));
}
