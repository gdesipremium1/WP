<?php
    function de_get_liste_peyement_prof(){
        require_once(DE_STRIPE_PAY_MULTIPLE_PATH.'/libs/stripe-libs/vendor/stripe/stripe-php/init.php');
        $secret = WC_Stripe_API::get_secret_key();  
		$stripe = new \Stripe\StripeClient($secret); 

		//var_dump($secret);
        $facts = $stripe->invoices->all();
        
        $html_output  = '<link rel="stylesheet"  href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" media="all">';
        $html_output .= '<script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>';
        $html_output .= '<table id="de-list-pay-prof" class="display compact hover" style="width:100%">';
        $html_output .=     '<thead>';
        $html_output .=         '<tr>';
        $html_output .=             '<td>Description</td>';
        $html_output .=             '<td>Prix</th>';
        $html_output .=             '<td>Elève</th>';
        $html_output .=             '<td>Professeur</td>';
        $html_output .=             '<td>N° Facture Stripe</td>';
        $html_output .=             '<td>A payer le</td>';
        $html_output .=             '<td>Status</td>';
        $html_output .=             '<td>Retard</td>';
        $html_output .=             '<td>Action</td>';
        $html_output .=         '</tr>';
        $html_output .=     '</thead>';
        $html_output .=     '<tbody>'; 
        foreach ($facts->autoPagingIterator() as $fact) {  
            $prof_id = get_user_id_by_meta_key_and_value('de_stripe_id', $fact->transfer_data->destination);
            $prof = get_user_by('ID', $prof_id);  
            $amount = $fact->amount_due / 100; 
            $period_end = date("Y-m-d H:i:s", $fact->period_end);
            
            $booking_id = $fact->metadata->booking_id;
            $invoice_id = $fact->metadata->invoice_id;
            
            $booking_to_date_selection    =   get_post_meta($booking_id, 'booking_to_date_selection', true);
            if(is_array($booking_to_date_selection)){
                    $booking_to_date_selection_sel = $booking_to_date_selection[0];
                }else{
                    $booking_to_date_selection_sel  = $booking_to_date_selection;
                }
            $datet=date_create($booking_to_date_selection_sel);
            //date_add($datet,date_interval_create_from_date_string("24 hours"));
            $booking_to_date_day = date_format($datet,'d/m/Y H\hi');
            
            $date_due = $fact->due_date;
            if($date_due){
            $date_due = date('d/m/Y H\hi', $date_due);
            }else{
                $date_due = $booking_to_date_day;
            }
            
            $due_date_comp = date("Y-m-d H:i:s", $fact->due_date); 
            $now = date("Y-m-d H:i:s");
            $due_date_tmp = strtotime($due_date_comp. " + 2 hours");
            $now_tmp = strtotime($now);
            
            if($fact->status == 'open' && $due_date_tmp < $now_tmp){
               $class ="red";
               $retard = "En retard de paiement";
            }else{
                $class ="";
                $retard = "";
            }
            $html_output .=             '<tr  class="'.$class.'">';
            $html_output .=                 '<td>'.$fact->description.'</td>';
            $html_output .=                 '<td>'.$amount.' '.$fact->currency.'</td>';
            $html_output .=                 '<td>'.$fact->customer_name.'<br>'.$fact->customer_email.'</td>';
            $html_output .=                 '<td>'.$prof->data->display_name.'<br>'.$prof->data->user_email.'</td>'; 
            $html_output .=                 '<td>'.$fact->number.'</td>';
            $html_output .=                 '<td>'.$date_due.'</td>';
            $html_output .=                 '<td>'.$fact->status.'</td>';
            $html_output .=                 '<td>'.$retard.'</td>';
            $cmd_id = $fact->metadata->order_id;
            $pm_id =get_post_meta($cmd_id,'_stripe_source_id', true );
            $fact_id = $fact->id;
            if($fact->status != 'open'){
                 $html_output .=                 '<td>*</td>'; 
            }else{
                $html_output .=                 '<td><button class="payerprof" type="button" data-pm_id="'.$pm_id.'" data-cmd_id="'.$cmd_id.'" data-fact_id="'.$fact_id.'" data-cust_mail="'.$fact->customer_email.'" data-paim_descr="'.$fact->description.'" data-prof_mail="'.$prof->data->user_email.'">Payer le Professeur</button></td>'; 
            }
            $html_output .=             '</tr>'; 
            if($fact->status == 'paid'){
                update_post_meta($invoice_id, 'invoice_status_full', 'confirmed'); 
                update_post_meta($invoice_id, 'balance', 0);
                update_post_meta($booking_id, 'booking_status_full', 'confirmed'); 
                update_post_meta($booking_id, 'balance', 0);
            } 
        }
        $html_output .=     '</tbody>';
        $html_output .= '</table>';
        $html_output .= '<script>';
        $html_output .= '
                         //Gestion dataTable
                        let de_table = new DataTable("table#de-list-pay-prof");  
                        jQuery("table.dataTable tbody tr.red.even").css("background-color","#ff00003d");
                         
                        //envoyer payement
                        jQuery("button.payerprof").click(function(){
                            jQuery.ajax({
                				url: "'.DE_STRIPE_PAY_MULTIPLE_URI.'view/ajax/stripe/payment/link/envoi.php",
                				type: "POST",
                				dataType: "html",
                				data: {
                						   action: "de-str-pay-process",
                						   pm_id : jQuery(this).data("pm_id"),
                						   cmd_id : jQuery(this).data("cmd_id"), 
                						   fact_id : jQuery(this).data("fact_id"), 
                						   cust_mail : jQuery(this).data("cust_mail"), 
                						   prof_mail : jQuery(this).data("prof_mail"), 
                						   paim_descr : jQuery(this).data("paim_descr"), 
                					   },
                				async: true,
                				success: function (response) {
                					location.reload(); 
                				}
                			});
                         });
        ';
        $html_output .= '</script>';
        return $html_output;
        
        
        
        /*
        $product_list = de_get_liste_product_prof();
        
        $html_output  = '<link rel="stylesheet"  href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" media="all">';
        $html_output .= '<script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>';
        $html_output .= '<table id="de-list-pay-prof" class="display compact hover" style="width:100%">';
        $html_output .=     '<thead>';
        $html_output .=         '<tr>';
        $html_output .=             '<th>Designation client</th>';
        $html_output .=             '<th>Email client</th>';
        $html_output .=             '<th>Designation prof</th>';
        $html_output .=             '<th>Email prof</th>';
        $html_output .=             '<th>Titre du cours</th>';
        $html_output .=             '<th>Description du cours</th>';
        $html_output .=             '<th>Date du cours</th>';
        $html_output .=             '<th>Prix à payer</th>';
        $html_output .=             '<th>Action(s)</th>';
        $html_output .=         '</tr>';
        $html_output .=     '</thead>';
        $html_output .=     '<tbody>';
        foreach($product_list as $key => $data){
            
             //Netoyage des indetiants
            
            $data['booking_id'] = str_replace(
                                    'f',
                                    '',
                                    $data['booking_id']
                                );
            $data['product_invoice_id'] = str_replace(
                                    'f',
                                    '',
                                    $data['product_invoice_id']
                                );
            $total_price = floatval(get_post_meta( $data['booking_id'], 'total_price', true ));
            $left_price = ($total_price * 90)/100;
            $invoice_tite = explode(',', $data['invoice_title']);
            $invoice_tit_nom_produit = (isset($invoice_tite[2]))? str_replace('Nom: ','Cours sur: ',$invoice_tite[2]) : "-----"; 
            $invoice_tit_periode_produit = (isset($invoice_tite[3]))? str_replace('Nom: ','Cours sur: ',$invoice_tite[3]) : "-----";
            $js_desc = "Frais de cours; Payement du frais de cours de la facture n°: " . $data['product_invoice_id'] . "; " . $invoice_tit_nom_produit .";";
            
            $html_output .=             '<tr>';
            $html_output .=                 '<td>'.$data['designation_eleve'].'</td>';
            $html_output .=                 '<td>'.$data['email_eleve'].'</td>';
            $html_output .=                 '<td>'.get_prof_by_invoice_id($data['invoice_id'], 'display_name').'</td>';
            $html_output .=                 '<td>'. get_prof_by_invoice_id($data['invoice_id'], 'user_email').'</td>';
            $html_output .=                 '<td>Frais de cours</td>';
            $html_output .=                 '<td>
                                                <h4>Payement du frais de cours de la facture <strong>n°: '. $data['product_invoice_id'] .'</strong></h4><hr/>
                                                <h4>'. $invoice_tit_nom_produit .'</strong></h4><hr/>
                                            </td>';
            $html_output .=                 '<td>' . $invoice_tit_periode_produit . '</td>';
            $html_output .=                 '<td>'. $left_price .'&euro;</td>';
            if(get_user_meta(get_prof_by_invoice_id( $data['invoice_id'], 'ID', true ), 'de_stripe_id', true) != ""){
            $html_output .=                 '<td><button class="str-pay-connect" data-str_invoice_id="'.$data['product_invoice_id'].'" data-str_price_title="' . $invoice_tit_nom_produit . '" data-str_price_desc="' . $js_desc . '" data-str_price="'.$left_price.'" data-str_customer_email="'.$data['email_eleve'].'" type="button" data-str_id="' . get_user_meta(get_prof_by_invoice_id( $data['invoice_id'], 'ID', true ), 'de_stripe_id', true) . '">Envoyer le lien de payement</button></td>'; 
            }else{
            $html_output .=                 '<td style="color:red;">Configuration (Stripe Connect) requise</td>'; 
            }
            $html_output .=             '</tr>';
                
        }
        $html_output .=     '</tbody>';
        $html_output .= '</table>';
        $html_output .= '<script>';
        $html_output .= '
                         //Gestion dataTable
                        let de_table = new DataTable("table#de-list-pay-prof");
                        
                         //Gestion Envoie du payment link AJAX
                         
                        jQuery("button.str-pay-connect").click(function(){
                            jQuery.ajax({
                				url: "'.DE_STRIPE_PAY_MULTIPLE_URI.'view/ajax/stripe/payment/link/create.php",
                				type: "POST",
                				dataType: "html",
                				data: {
                						   action: "de-str-pay-link-process",
                						   customer_email: jQuery(this).data("str_customer_email"),
                						   str_prof_id : jQuery(this).data("str_id"),
                						   str_price : jQuery(this).data("str_price"),
                						   str_price_desc : jQuery(this).data("str_price_desc"),
                						   str_price_title : jQuery(this).data("str_price_title"),
                						   str_invoice_id : jQuery(this).data("str_invoice_id")
                					   },
                				async: true,
                				success: function (response) {
                					//location.reload(); 
                					}
                			});
                         });
        ';
        $html_output .= '</script>';
        return $html_output;
    }
    
    function de_get_liste_product_prof(){
        global $wpdb;
        
          //Récuperation des produit et info du prof
         
        $produit_prof = $wpdb->get_results( 
        "SELECT 
            `invoice_table`.`ID` AS `invoice_id`,
            `invoice_table`.`post_title` AS `invoice_title`,
            `user_table`.`ID` AS `id_eleve`,
            `user_table`.`display_name` AS `designation_eleve`, 
            `user_table`.`user_email` AS `email_eleve`
        FROM 
                `{$wpdb->prefix}posts` 
            AS
                `invoice_table`
        LEFT JOIN 
            (
                    `{$wpdb->prefix}users` 
                AS
                    `user_table` 
            )
        ON 
            (
                `invoice_table`.`post_author` = `user_table`.`ID`
            )
        WHERE 
            `post_type` LIKE 'product';",
        //AND 
        //    `post_status` LIKE 'publish';", 
        ARRAY_A 
        );
        foreach($produit_prof as $key => $data){
            $produit_prof[$key]['product_price'] = get_post_meta( $data['invoice_id'], '_price', true );
            $produit_prof[$key]['product_invoice_id'] = get_post_meta( $data['invoice_id'], '_invoice_id', true );
            $produit_prof[$key]['booking_id'] = (get_post_meta( $data['invoice_id'], '_booking_id', true ) != "")? get_post_meta( $data['invoice_id'], '_booking_id', true ) : 0;
        }
        return $produit_prof;
    }
    
    function get_prof_by_invoice_id($invoice_id = 0, $user_field = ""){
        $_prop_id =  get_post_meta( $invoice_id, '_prop_id', true );
        if($invoice_id != 0){
            // if($_prop_id != null  $_prop_id != ""){
                global $wpdb;
                
                 // Récuperation les infos ou un champ du prof
                  
                 
                $prof_infos = $wpdb->get_results( 
                "SELECT 
                    * 
                FROM 
                     `{$wpdb->prefix}users` 
                WHERE 
                    `{$wpdb->prefix}users`.`ID` = " . get_post_meta( $_prop_id, 'original_author', true ) . ";", 
                ARRAY_A 
                );
            //}
        }
        if($user_field != ""){
            return $prof_infos = (isset($prof_infos[0]))? $prof_infos[0][$user_field] : '' ;  
        }else{
            return $prof_infos = (isset($prof_infos[0]))? $prof_infos[0] : array() ;
        }
    */
    }
  
    


?>