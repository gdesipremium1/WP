<?php
require_once DE_STRIPE_PAY_MULTIPLE_URI . 'libs/desi_secret.php';
require_once DE_STRIPE_PAY_MULTIPLE_URI . 'libs/stripe-libs/vendor/autoload.php';
/**
 * Account connect refresh link
 */
 add_action(
	'rest_api_init', //Hook wp pour initialisation de l'api rest 
	function () {
		/**
		 * Gestion de account connect
		 * @path:/function/WP_REST/account/link/refresh.php
		 */
		register_rest_route(
		    'stripe/v1/account', 
		    '/link/refresh/(?P<id>[\d]+)', array(
    			'methods'  => WP_REST_Server::READABLE,
    			'callback' => 'prefix_get_endpoint_account_link_refresh',
		    )
		);
	}
);

function prefix_get_endpoint_account_link_refresh($request) {
     $secret = WC_Stripe_API::get_secret_key(); 
	$de_stripe = new \Stripe\StripeClient($secret); 
    $user_id = (int) $request['id'];
    
    $connect_account_link = $de_stripe->accountLinks->create([
              'account' => get_user_meta( $user_id, 'de_stripe_id', true ),
              'refresh_url' => "https://coursdecuisinealouer.com/wp-json/stripe/v1/account/link/refresh/".$user_id,
              'return_url' => "https://coursdecuisinealouer.com/my-profile/?de_str_confirm_id=".$user_id,
              'type' => 'account_onboarding',
            ]);
    header('Location: '.$connect_account_link->url);
    exit;
}