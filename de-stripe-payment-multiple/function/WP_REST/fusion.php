<?php
require_once DE_STRIPE_PAY_MULTIPLE_URI . 'libs/desi_secret.php';
require_once DE_STRIPE_PAY_MULTIPLE_URI . 'libs/stripe-libs/vendor/autoload.php';

include DE_STRIPE_PAY_MULTIPLE_URI . '/function/WP_REST/account/link/refresh.php';

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


