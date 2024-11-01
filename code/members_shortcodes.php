<?php

// member shortcodes to be used in posts and pages
add_shortcode( 'scm_member_data', 'scm_sh_member_data' );
add_shortcode( 'scm_members', 'scm_sh_members' );
add_shortcode( 'scm_member_username_link', 'scm_sh_member_username_link' );

function scm_sh_member_data( $atts ) {
    global $current_user;
    
    if ( !is_user_logged_in() ) { 
        return "";
    }
 
	extract( shortcode_atts( array(
		'member_id' => '',
        'before' => '',
        'after' => '',
	), $atts ) );

    $values = get_post_custom($member_id);
        
    $firstname = isset($values['_firstname']) ? esc_attr($values['_firstname'][0]) : '';
    $middlename = isset($values['_middlename']) ? esc_attr($values['_middlename'][0]) : '';
    $name = isset($values['_name']) ? esc_attr($values['_name'][0]) : '';

    $street = isset($values['_street']) ? esc_attr($values['_street'][0]) : '';
    $number = isset($values['_number']) ? esc_attr($values['_number'][0]) : '';
    $zip = isset($values['_zip']) ? esc_attr($values['_zip'][0]) : '';
    $place = isset($values['_place']) ? esc_attr($values['_place'][0]) : '';
 
    $phone = isset($values['_phone']) ? esc_attr($values['_phone'][0]) : '';
    $cell = isset($values['_cell']) ? esc_attr($values['_cell'][0]) : '';

    $email = isset($values['_email']) ? esc_attr($values['_email'][0]) : '';

    $dateofbirth = isset($values['_dateofbirth']) ? esc_attr($values['_dateofbirth'][0]) : '0000-00-00';
    $gender = isset($values['_gender']) ? esc_attr($values['_gender'][0]) : 'male';

    $level = isset($values['_level']) ? esc_attr($values['_level'][0]) : '';
    
    $custom1 = isset($values['_custom1']) ? esc_attr($values['_custom1'][0]) : '';
    $custom2 = isset($values['_custom2']) ? esc_attr($values['_custom2'][0]) : '';
    $custom3 = isset($values['_custom3']) ? esc_attr($values['_custom3'][0]) : '';
    $custom4 = isset($values['_custom4']) ? esc_attr($values['_custom4'][0]) : '';

    $label_custom1 = get_option('scm_custom1_member'); 
    if ($label_custom1 == '') { $label_custom1 = '[Custom1]'; }
    $label_custom2 = get_option('scm_custom2_member'); 
    if ($label_custom2 == '') { $label_custom2 = '[Custom2]'; }
    $label_custom3 = get_option('scm_custom3_member'); 
    if ($label_custom3 == '') { $label_custom3 = '[Custom3]'; }
    $label_custom4 = get_option('scm_custom4_member'); 
    if ($label_custom4 == '') { $label_custom4 = '[Custom4]'; }

    $startdate = isset($values['_startdate']) ? esc_attr($values['_startdate'][0]) : '0000-00-00';
    $enddate = isset($values['_enddate']) ? esc_attr($values['_enddate'][0]) : '0000-00-00';
    $username = isset($values['_username']) ? esc_attr($values['_username'][0]) : '';

    $show_all = ("no_privacy" == get_option('scm_display_privacy_data'));
    wp_get_current_user();
    $show_all |= ( $username == $current_user->user_login );
    $show_all |= ( current_user_can( 'activate_plugins' ) );
    
    $show_partial = $show_all || ("no_addrmailphone_privacy" == get_option('scm_display_addrmailphone_privacy_data'));
    
    $html = "";
	$html .= "<div id=\"scm_member\"> \n";
    if ( $show_partial ) {
			
        $html .= "<div id=\"scm_member_contact\">" . sprintf("%s %s %s, %s %s, %s %s", $firstname, $middlename, $name, $street, $number, $zip, $place) . "</div>";
        $html .= "<div id=\"scm_member_data\"> \n";
		$html .= " <div id=\"scm_member_field\">" . __('Phone', 'sports-club-management') . "</div>";
		$html .= " <div id=\"scm_member_field_content\">" . sprintf("%s %s", $phone, $cell) . "</div>";
	    $html .= "</div> \n";
        $html .= "<div id=\"scm_member_data\"> \n";
		$html .= " <div id=\"scm_member_field\">" . __('E-mail', 'sports-club-management') . "</div>";
		$html .= " <div id=\"scm_member_field_content\">" . $email . "</div>";
	    $html .= "</div> \n";
    }
	$html .= "<div id=\"scm_member_data\"> \n";
	$html .= " <div id=\"scm_member_field\">" . __('Date of birth', 'sports-club-management') . "</div>";
	$html .= " <div id=\"scm_member_field_content\">" . $dateofbirth . "</div>";
	$html .= "</div> \n";
	if ($level != "") {
		$html .= "<div id=\"scm_member_data\"> \n";
		$html .= " <div id=\"scm_member_field\">" . __('Level', 'sports-club-management') . "</div>";
		$html .= " <div id=\"scm_member_field_content\">" . $level . "</div>";
		$html .= "</div> \n";
	}
    if ( $show_all ) {
		if ($custom1 != "") {
			$html .= "<div id=\"scm_member_data\"> \n";
			$html .= " <div id=\"scm_member_field\">" . $label_custom1 . "</div>";
			$html .= " <div id=\"scm_member_field_content\">" . $custom1 . "</div>";
			$html .= "</div> \n";
		}
		if ($custom2 != "") {
			$html .= "<div id=\"scm_member_data\"> \n";
			$html .= " <div id=\"scm_member_field\">" . $label_custom2 . "</div>";
			$html .= " <div id=\"scm_member_field_content\">" . $custom2 . "</div>";
			$html .= "</div> \n";
		}
		if ($custom3 != "") {
			$html .= "<div id=\"scm_member_data\"> \n";
			$html .= " <div id=\"scm_member_field\">" . $label_custom3 . "</div>";
			$html .= " <div id=\"scm_member_field_content\">" . $custom3 . "</div>";
			$html .= "</div> \n";
		}
		if ($custom4 != "") {
			$html .= "<div id=\"scm_member_data\"> \n";
			$html .= " <div id=\"scm_member_field\">" . $label_custom4 . "</div>";
			$html .= " <div id=\"scm_member_field_content\">" . $custom4 . "</div>";
			$html .= "</div> \n";
		}
    }
	$html .= "<div id=\"scm_member_data\"> \n";
	$html .= " <div id=\"scm_member_field\">" . __('Start date membership', 'sports-club-management') . "</div>";
	$html .= " <div id=\"scm_member_field_content\">" . $startdate . "</div>";
	$html .= "</div> \n";
	if (($enddate != "") && ($enddate != "0000-00-00")) {
		$html .= "<div id=\"scm_member_data\"> \n";
		$html .= " <div id=\"scm_member_field\">" . __('End date membership', 'sports-club-management') . "</div>";
		$html .= " <div id=\"scm_member_field_content\">" . $enddate . "</div>";
		$html .= "</div> \n";
	}   
	$html .= "</div> \n";
	
	return "$before \n" . $html . "$after \n";
} 

function scm_sh_members( $atts ) {
    
    if ( !is_user_logged_in() ) { 
        return "";
    }
 
	extract( shortcode_atts( array(
		'syntax' => 'NFM',            	// valid options 'NFM', 'FMN'
		'orderby' => '',				// valid options 'N', 'F'
		'category' => '',
		'before' => '',
        'after' => '',
	), $atts ) );

	$mkey = ($syntax == 'FMN' ? '_firstname' : '_name');
	if ($orderby == 'N') {
		$mkey = '_name';
	} else if ($orderby == 'F') {
		$mkey = '_firstname';
	}
		
	if ($category == '') {
		$members = get_posts( array( 'post_type' => 'scm_member'
                            , 'numberposts' => '-1'
                            , 'meta_key' => $mkey
                            , 'orderby' => 'meta_value'
                            , 'order' => 'ASC'
                            ) );
	} else {
		$catid = get_term_by('name', $category, 'scm_member_category');
		$members = get_posts( array( 'post_type' => 'scm_member'
							, 'tax_query' => array(
								array(
									'taxonomy' => 'scm_member_category',
									'field' => 'id',
									'terms' => array( $catid->term_id ) 
								)
							  )      
                            , 'numberposts' => '-1'
                            , 'meta_key' => $mkey
                            , 'orderby' => 'meta_value'
                            , 'order' => 'ASC'
                            ) );
	}

	$html = "";
    if ( count( $members ) == 0 ) {
        $html .= __('none', 'sports-club-management');
    } else {
		$html .= "<p><ul style='list-style-type:none'>";
        foreach ( $members as $member ) {
			$values = get_post_custom($member->ID);
    
			$firstname  = $values['_firstname'][0];
			$middlename = $values['_middlename'][0];
			$name       = $values['_name'][0];
			
			if ($syntax == 'FMN') {
				$html .= sprintf("<li><a href=%s>%s %s %s</a></li>\n", get_post_permalink( $member ), $firstname, $middlename, $name);
			} else { // default 'NFM'
				$html .= sprintf("<li><a href=%s>%s, %s %s</a></li>\n", get_post_permalink( $member ), $name, $firstname, $middlename);
			}
        }
		$html .= "</ul></p>";
    }

	return "$before \n" . $html . "$after \n";
} 
  
function scm_sh_member_username_link( $atts ) {
    
	$current_user = wp_get_current_user();
	$html = $current_user->display_name;
 
	// find logged in user's membership record
    $members = get_posts( array( 'post_type' => 'scm_member'
                            , 'numberposts' => '-1'
                            , 'meta_key' => '_username'
							, 'meta_value' => $current_user->user_login
                            ) );
	// if found and unique						
	if ( count( $members ) == 1 ) {
		foreach ( $members as $member ) {
			$html = '<a href="'.get_post_permalink( $member->ID ).'">'.$current_user->display_name.'</a>';
		}
	}
	
	return $html;
}
