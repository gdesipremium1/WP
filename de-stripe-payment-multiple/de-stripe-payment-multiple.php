<?php

/**
	Plugin Name: Payement stripe par tranche et par membre
	Version: 1.0
	Author: Désiré Fetraniaina RABEMANANTSOA (gdesi.mada@gmail.com)
    Description: Wordpress WooCommerce plugin and Stripe payment.
 */
define('DE_STRIPE_PAY_MULTIPLE_PATH', plugin_dir_path(__FILE__));
define('DE_STRIPE_PAY_MULTIPLE_URI', get_home_url() . '/' . PLUGINDIR . '/de-stripe-payment-multiple/');

/**
 * Load library plugin function functions
 */
require_once DE_STRIPE_PAY_MULTIPLE_PATH . 'function/WP_REST/account/link/refresh.php';
require_once 'function/create_connect.php';




/**
 * Activate hook
 */
register_activation_hook(__FILE__, function () {
	wp_insert_post(array(
		'post_title'     => 'ai-05-tunnel-de-create-connect',
		'post_content'   => '',
		'post_type'      => 'page',
		'post_status'    => 'publish',
	));
});

/**
 * Deactivate hook
 */
register_deactivation_hook(__FILE__, function () {
	global $wpdb;
	$return_id = $wpdb->get_row("SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_title = 'ai-05-tunnel-de-create-connect' && post_type = 'page' ", 'ARRAY_N');
	if (isset($return_id[0])) {
		$post_id = (int) $return_id[0];
		wp_delete_post($post_id, true);
	}
});


/**
 * Plugin initialisation hook handled 
 */
add_action('init', function () {
    
	if (!is_admin()) { 
	    add_action( 'woocommerce_payment_complete', 'so_payment_complete' ); 
        function so_payment_complete( $order_id ){  
            $order = wc_get_order( $order_id );  
            $customer_id = $order->get_meta( '_stripe_customer_id', true ); 
            $totalorder = $order->get_total();
            
            $secret = WC_Stripe_API::get_secret_key();  
		    $stripe = new \Stripe\StripeClient($secret); 
		    
            // Get and Loop Over Order Items
            foreach ( $order->get_items() as $item_id => $item ) {
               $product_id = $item->get_product_id();  
               if($product_id!=40290){
                   $product = $item->get_product(); // see link above to get $product info
                   $product_name = $item->get_name(); 
                   $total = $item->get_total();   
                   
                   $booking_id =get_post_meta($product_id,'_booking_id', true );//booking wprental id
                   $inv_id =get_post_meta($product_id,'_invoice_id', true );//facture wprental id
                   $owner_id   =   get_post_meta($booking_id,'owner_id', true );
                   $stripe_prof_connect_id =get_user_meta($owner_id,'de_stripe_id', true );  //acct_1O6AepGfIq1msPYF
                   
                   $date_paiement   =   get_post_meta($booking_id,'booking_to_date_unix', true );
                   if(is_array($date_paiement)){
                       $date_paiement_unix =  $date_paiement[0];
                   }else{
                       $date_paiement_unix =  $date_paiement;
                   }
                    //facture pour chaque prof
                    $nom_product = "Frais professeur lié au commande N° ".$order_id;
                    $prd = $stripe->products->create(
                        ['name' => $nom_product]
                    );
                    $stripe->products->update(
                      $prd->id,
                       ['metadata' => 
                                    ['order_id' => $order_id,
                                    'invoice_id'=>$inv_id,
                                    'booking_id'=>$booking_id, 
                                    'product_name'=>$product_name,
                                    ]
                        ]
                    ); 
                $pric = $stripe->prices->create([
                  'product' => $prd->id,
                  'unit_amount' => $total*100*9,//90% pour le prof
                  'currency' => 'eur',
                  'tax_behavior' => 'exclusive',
                ]); 
                $pm_id =get_post_meta($order_id,'_stripe_source_id', true );
                 
                $stripe->paymentMethods->attach(
                  $pm_id,
                  ['customer' => $customer_id]
                );
                $descr = "COURS de Cuisine à louer - Commande N° ".$order_id."(Frais professeur)";
                $invoic = $stripe->invoices->create([
                  'customer' => $customer_id,
                  'description' => $descr,
                  'auto_advance' => false,//true
                  'collection_method'  => 'send_invoice',//charge_automatically
                  'due_date'  => $date_paiement_unix, 
                  'pending_invoice_items_behavior' => "exclude", 
                  ['metadata' => 
                                ['order_id' => $order_id,
                                'invoice_id'=>$inv_id,
                                'booking_id'=>$booking_id, 
                                'product_name'=>$product_name,
                                ]
                    ],
                  'transfer_data' =>
                    [
                        'destination' => $stripe_prof_connect_id,
                        'amount' => $total*100*9,
                    ],
                  
                ]); 
                $stripe->invoiceItems->create([
                  'customer' => $customer_id,
                  'price' =>$pric->id,
                  'invoice' => $invoic->id,
                ]); 
                
                $stripe->invoices->finalizeInvoice($invoic->id, []);
                $stripe->invoices->sendInvoice(
                  $invoic->id,
                  []
                );
            
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $send_email_prof = wp_mail( 
            $invoic->customer_email, 
            'Votre expérience culinaire débute bientôt', 
            "Votre expérience culinaire débute bientôt  <br> 
            Une demande d'autorisation de prélèvement de $total*100*9 €. à titre de paiement complet du cours de cuisine sera effectué <b> dès le lendemain sur la carte  </b> ayant servi au paiement. <br>
            Pensez à contacter le professeur pour <b> confirmer l'heure et le lieu </b> de votre cours." ,
            $headers
            );
            }else{ 
                var_dump('here');
            }
        }

        }

		//wp_enqueue_script('ajax-str-product-script', DE_STRIPE_PAY_MULTIPLE_URI . 'public/js/ajax-str-product-script.js', array('jquery'));
		$de_current_user =  wp_get_current_user();

		/**
		 * Creation d'un connect stripe
		 */
		if (
		    $_SERVER['REQUEST_URI'] == "/ai-05-tunnel-de-create-connect/" 
		    ) {
		    if (isset($_POST['user_register']) && $_POST['user_register'] == 0 && !isset($_POST['user_register'])) {
		        de_account_connect_creation($_POST['user_register']);
		    }elseif(!isset($_POST['user_register']) && isset($de_current_user->ID)){
		        de_account_connect_creation($de_current_user->ID);
		    }else{
		        die('Error systeme');
		    }
			header('Location: ' . get_user_meta($de_current_user->ID, 'de_stripe_account_link', true));
			exit;
		}elseif ($_SERVER['REQUEST_URI'] == "/ai-05-tunnel-de-create-connect/?remove_connect=1") {
			clean_connect();
		}
		elseif (isset($_GET['de_str_confirm_id']) && $_GET['de_str_confirm_id'] != "") {
			update_user_meta($_GET['de_str_confirm_id'], 'de_stripe_account_link_validate', 1); 
		}
	} else {
		/**
		 * Gestion des page admin
		 * 
		 */
		 add_action( 'admin_menu', 'de_stripe_link_pay_connect_plugin_menu' ); 

		/** Step 1. */
		function de_stripe_link_pay_connect_plugin_menu() {
		    /**
		     * Informations du main menu admin
		     */
		    $page_title = 'Gestion des paiements des professeurs';
		    $menu_title = 'Payer les professeurs';
		    $capability_sub1 = $capability = 'administrator';
		    $menu_slug= $parent_menu_slug = 'de-gestion-link-pay-connect-stripe';
		    $callback_list = $callback ='de_stripe_link_pay_connect_plugin_dashbord';
		    $icon_url = '';
		    $position = 2;
		    
		    add_menu_page( $page_title,  $menu_title,  $capability,  $menu_slug,  $callback_list,  $icon_url, $position );
		    /**
		     * Information du sub menu
		     */
		   /* $page_title_sub1 = 'Gestion des profs à payer';
		    $menu_title_sub1 = 'Liste à payer';
		    $menu_slug_sub1 = 'de-gestion-link-pay-connect-stripe-list';
		    add_submenu_page( $parent_menu_slug, $page_title_sub1, $menu_title_sub1, $capability_sub1, $menu_slug_sub1, $callback_list, $position );*/
		}

		/** Step 3. */
		function de_stripe_link_pay_connect_plugin_dashbord() {
			if ( !current_user_can( 'manage_options' ) )  {
				wp_die( __( 'Vous ne possedez pas assez de droit pour cette page.' ) );
			}
			require_once DE_STRIPE_PAY_MULTIPLE_PATH.'view/html/liste_payement_prof.php';
			echo de_get_liste_peyement_prof();
		}
	}
}, 1);

/**
 * lancer la création d'un account connect aprè registration hook
 */
if (!function_exists('de_account_connect_creation')) {
	add_action('user_register', 'de_account_connect_creation', 10, 1);

	function de_account_connect_creation($user_id)
	{
			$new_registered_user = get_user_by('ID', $user_id); 
			$stripe_account_connect = de_create_register_connect($new_registered_user->data->user_email, '', '');
			$stripe_account_connect_link = de_create_register_connect_link($user_id, $stripe_account_connect->id);
			update_user_meta($user_id, 'de_stripe_id', $stripe_account_connect->id);
			update_user_meta($user_id, 'de_stripe_account_link', $stripe_account_connect_link->url);
			update_user_meta($user_id, 'de_stripe_account_link_validate', 1);
	}
}
