<?php
/**
 * basic config stripe API
 */
 

require_once DE_STRIPE_PAY_MULTIPLE_URI . 'libs/desi_secret.php';
require_once DE_STRIPE_PAY_MULTIPLE_URI . 'libs/stripe-libs/vendor/autoload.php';

if(!function_exists('de_str_product_add')){
    function de_str_product_add($name, $activation, $description, $default_price_data__currency, $default_price_data__unit_amount_decimal){
       require_once(DE_STRIPE_PAY_MULTIPLE_PATH.'/libs/stripe-libs/vendor/stripe/stripe-php/init.php');
        $secret = WC_Stripe_API::get_secret_key(); 
		$stripe = new \Stripe\StripeClient($secret); 
        $prod = $stripe->products->create([
        	'name'            => $description,
        ]);
        
        $stripe->prices->create([
          'unit_amount' => $default_price_data__unit_amount_decimal,
          'currency' => 'eur',
          'product' => $prod->id,
        ]);
    }
}



