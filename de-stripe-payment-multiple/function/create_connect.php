<?php
/**
 * basic config stripe API
 */
require_once DE_STRIPE_PAY_MULTIPLE_URI.'libs/desi_secret.php';
require_once DE_STRIPE_PAY_MULTIPLE_URI.'libs/stripe-libs/vendor/autoload.php';

/**
 * Creation d'un compte connect
 */
if (!function_exists('de_create_register_connect')) {
	function de_create_register_connect($_email, $refresh_url, $return_url)
	{
		/**
		 * Load library functions
		 */
		 $secret = WC_Stripe_API::get_secret_key();  
		$de_stripe = new \Stripe\StripeClient($secret);
		/**
		 * Creation d'un compte connect stripe
		 */
		 return $de_stripe->accounts->create([
            'country' => 'FR',
            'type' => 'express',
            'email' => $_email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
              ],
            'business_type' => 'individual',
            'business_profile' => ['product_description' => 'Profésseur de cuisine'],
		]);
	}
}

/**
 * Création d'un account link
 */
if (!function_exists('de_create_register_connect_link')) {
    function de_create_register_connect_link($wp_id_user, $strp_id_user){
        /**
		 * Load library functions
		 */
		$secret = WC_Stripe_API::get_secret_key(); 
		$de_stripe = new \Stripe\StripeClient($secret);
		/**
		 * Creation d'un compte connect stripe
		 */
		return $de_stripe->accountLinks->create([
          'account' => $strp_id_user,
          'refresh_url' => "https://coursdecuisinealouer.com/wp-json/stripe/v1/account/link/refresh/".$wp_id_user,
          'return_url' => "https://coursdecuisinealouer.com/my-profile/?de_str_confirm_id=$wp_id_user",
          'type' => 'account_onboarding',
        ]);
    }
}

if (!function_exists('clean_connect')) {
    function clean_connect(){
        $secret = WC_Stripe_API::get_secret_key(); 
		$stripe = new \Stripe\StripeClient($secret); 
		
        $liste_connect = $stripe->accounts->all();
        foreach($liste_connect->data as $connect){
            $stripe->accounts->delete(
              $connect->id,
              []
            );
        }
    }
}
if (!function_exists('clean_connect_by_id')) {
    function clean_connect_by_id($id_conn){
        $secret = WC_Stripe_API::get_secret_key(); 
		$stripe = new \Stripe\StripeClient($secret); 
		
        //$liste_connect = $stripe->accounts->all();
        
            $stripe->accounts->delete(
              $id_conn,
              []
            );
       
    }
}
