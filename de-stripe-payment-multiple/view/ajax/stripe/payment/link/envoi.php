<?php
/**
 * basic config external file
 */
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
define( 'BLOCK_LOAD', true );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php' );
//$wpdb = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
if ( !( defined('BLOCK_LOAD') && BLOCK_LOAD ) ) require_once(ABSPATH . 'wp-settings.php');

global $wpdb;
	
/**
 * basic config stripe API
 */
require_once  '../../../../../libs/desi_secret.php';
require_once  '../../../../../libs/stripe-libs/vendor/autoload.php';
require_once(DE_STRIPE_PAY_MULTIPLE_PATH.'/libs/stripe-libs/vendor/stripe/stripe-php/init.php');
$secret = WC_Stripe_API::get_secret_key(); 
$stripe = new \Stripe\StripeClient($secret); 

/**
 * Activer paiement stripe
 * 
 */
 if(
     isset($_POST['pm_id']) && 
     isset($_POST['fact_id']) && 
     isset($_POST['pm_id']) &&
     isset($_POST['cust_mail']) &&
     isset($_POST['paim_descr'])&&
     isset($_POST['prof_mail'])
    ){ 
        $user = get_user_by_email($_POST['cust_mail']) ; 
        $stripe->invoices->pay(
        $_POST['fact_id'],
        [
           'payment_method' => $_POST['pm_id'],
        ]);
        
    $order = wc_get_order( $_POST['cmd_id']);  
    foreach ( $order->get_items() as $item_id => $item ) {
               $product_id = $item->get_product_id(); 
               $product = $item->get_product(); // see link above to get $product info
               $product_name = $item->get_name(); 
               $periode = explode("[", $product_name); 
               $expload = explode(",", $product_name);
               $invoice_id = (int) filter_var($expload[0], FILTER_SANITIZE_NUMBER_INT);
               $booking_id = (int) filter_var($expload[1], FILTER_SANITIZE_NUMBER_INT);
                update_post_meta($invoice_id, 'invoice_status_full', 'confirmed'); 
                update_post_meta($invoice_id, 'balance', 0);
                update_post_meta($booking_id, 'booking_status_full', 'confirmed'); 
                update_post_meta($booking_id, 'balance', 0);
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    $send_email_customer = wp_mail( 
        $_POST['cust_mail'], 
        $_POST['paim_descr'],
        'Bonjour '.$user->display_name.', <br>
         Le frais de cours lié à ['.$_POST['paim_descr'].' ] est bien effectué. <br>
          Détails:  <br>
        - Période ['.$periode[1].' <br>
        - Réservation '.$booking_id.' <br>
        - Facture '.$invoice_id.' <br>
         Nous vous remercions, et espérons pouvoir à nouveau travailler avec vous.<br>
         Veuillez agréer, Madame, Monsieur, nos sincères salutations',
         $headers
        );
    $send_email_prof = wp_mail( 
        $_POST['prof_mail'], 
        $_POST['paim_descr'], 
        'Le frais de cours lié à ['.$_POST['paim_descr'].' ] est bien effectué.<br>
        Voici les coordonnées de l\'élève:  <br>
        - Nom : '.$user->display_name.'  <br>
        - Mail : '. $_POST['cust_mail'].'  <br>
        Détails:  <br>
        - Période ['.$periode[1].' <br>
        - Réservation '.$booking_id.' <br>
        - Facture '.$invoice_id.' <br> '
        );
    } 
    //print(json_encode(array('succes' => $send_email_customer)));
    //print(json_encode(array('succes' => $send_email_prof)));
    //print '<div class="confirmationpay">Vous allez recevoir un mail de confirmation.</div>';
}



