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
 * Creation du stripe product pour le payment link
 * 
 */
 if(
     isset($_POST['str_price']) && 
     isset($_POST['str_prof_id']) && 
     isset($_POST['str_price_desc']) && 
     isset($_POST['customer_email']) && 
     isset($_POST['str_price_title']) && 
     isset($_POST['str_invoice_id'])
    ){
    $str_product = $stripe->products->create([
      'name' => $_POST['str_price_desc'],
    ]);


    $str_price = $stripe->prices->create([
      'unit_amount' => floatval($_POST['str_price']) * 100,
      'currency' => 'eur',
      'product' => $str_product->id,
    ]);
    
    /*$str_transfer = $stripe->transfers->create([
      'amount' =>  $_POST['str_price'],
      'currency' =>  'eur',
      'destination' => $_POST['str_prof_id'],
      'transfer_group' => 'ORDER_'.$_POST['str_invoice_id'],
    ]);*/
    print(json_encode($str_transfer));
    $payment_link = $stripe->paymentLinks->create([
          'line_items' => [
                [
                  'price' => $str_price->id,
                  'quantity' => 1,
                ],
          ],
          /*'transfer_data' => [
                [
                    'destination' => $str_transfer->id,
                    'amount' => $_POST['str_price'],
                ],
            ],*/
        ]);
    $send_email = wp_mail( 
        $_POST['customer_email'], 
        $_POST['str_price_title'],
        'Afin de régler votre facture n°: '.$_POST['str_invoice_id'].
        'https://coursdecuisinealouer.com/ vous prie de bien régler votre frais de cours sur '.$_POST['str_price_title'].'.'.$payment_link->url.'">Payer mon frais de cours'
        );
    print(json_encode(array('succes' => $send_email)));
}



