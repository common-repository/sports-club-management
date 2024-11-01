<?php

// invoice shortcodes to be used in posts and pages
add_shortcode( 'scm_invoice_data', 'scm_sh_invoice' );

function scm_sh_invoice( $atts ) {
    global $current_user;

    if ( !is_user_logged_in() ) { 
        return "";
    }
 
    if ('use_invoices' != get_option('scm_include_invoices_data')) { 
        return "";
    }
    
	extract( shortcode_atts( array(
		'member_id' => '',
		'header' => 'A',		// valid options 'A', 'S', 'D', 'T'
        'before' => '',
        'after' => '',
	), $atts ) );

    $username = get_post_meta( $member_id , '_username' , true );
    wp_get_current_user();
    if ( ( $username != $current_user->user_login ) && ( !current_user_can( 'activate_plugins' ) ) ) {
        return "";
    }
     
    $invoices_all = get_posts( array( 'post_type' => 'scm_invoice'
                                , 'numberposts' => '-1'
                                , 'meta_key' => '_memberid'
                                , 'meta_value' => $member_id
                                ) );

    $invoices = array();
    foreach ( $invoices_all as $invoice ) {
        $date = get_post_meta( $invoice->ID , '_paymentdate' , true );
        if (( $date == '' ) | ( $date == '0000-00-00' )) {
            array_push($invoices, $invoice);
        }
    }
                                        
    if ( count( $invoices ) > 0 ) {
        $html = "";
		$html .= "<div id=\"scm_invoice\"> \n";
		
        foreach ( $invoices as $invoice ) {
			if ($header == 'S') {
				$htext = get_post_meta( $invoice->ID , '_service' , true );
				$line1name = __('Amount', 'sports-club-management');
				$line1text = get_post_meta( $invoice->ID , '_amount' , true );
				$line2name = __('Due date', 'sports-club-management');
				$line2text = get_post_meta( $invoice->ID , '_duedate' , true );
				$line3name = $line3text = "";
			} else if ($header == 'D') {
				$htext = get_post_meta( $invoice->ID , '_duedate' , true );
				$line1name = __('Amount', 'sports-club-management');
				$line1text = get_post_meta( $invoice->ID , '_amount' , true );
				$line2name = __('Service', 'sports-club-management');
				$line2text = get_post_meta( $invoice->ID , '_service' , true );
				$line3name = $line3text = "";
			} else if ($header == 'T') {
				$htext = get_the_title($invoice->ID);
				$line1name = __('Amount', 'sports-club-management');
				$line1text = get_post_meta( $invoice->ID , '_amount' , true );
				$line2name = __('Service', 'sports-club-management');
				$line2text = get_post_meta( $invoice->ID , '_service' , true );
				$line3name = __('Due date', 'sports-club-management');
				$line3text = get_post_meta( $invoice->ID , '_duedate' , true );
			} else {
				/* default: $header == 'A' */
				$htext = get_post_meta( $invoice->ID , '_amount' , true );
				$line1name = __('Service', 'sports-club-management');
				$line1text = get_post_meta( $invoice->ID , '_service' , true );
				$line2name = __('Due date', 'sports-club-management');
				$line2text = get_post_meta( $invoice->ID , '_duedate' , true );
				$line3name = $line3text = "";
			}				
			$html .= " <div id=\"scm_invoice_header\">" . $htext . "</div>";	
			$html .= "<div id=\"scm_invoice_data\"> \n";
			$html .= " <div id=\"scm_invoice_field\">" . $line1name . "</div>";	
			$html .= " <div id=\"scm_invoice_field_content\">" . $line1text . "</div>";	
			$html .= "</div> \n";
			$html .= "<div id=\"scm_invoice_data\"> \n";
			$html .= " <div id=\"scm_invoice_field\">" . $line2name . "</div>";	
			$html .= " <div id=\"scm_invoice_field_content\">" . $line2text . "</div>";	
			$html .= "</div> \n";
			if ($line3text != "") {
				$html .= "<div id=\"scm_invoice_data\"> \n";
				$html .= " <div id=\"scm_invoice_field\">" . $line3name . "</div>";	
				$html .= " <div id=\"scm_invoice_field_content\">" . $line3text . "</div>";	
				$html .= "</div> \n";
			}
        }    
        
		$html .= "</div> \n";
        
		return "$before \n" . $html . "$after \n";
    }
} 