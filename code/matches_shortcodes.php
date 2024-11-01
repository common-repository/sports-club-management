<?php

// member shortcodes to be used in posts and pages
add_shortcode( 'scm_match_data', 'scm_sh_match_data' );
add_shortcode( 'scm_match_current', 'scm_sh_match_current');

function scm_sh_match_data( $atts ) {
    
    if ('use_competitions' != get_option('scm_include_competitions_data')) { 
        return "";
    }
	extract( shortcode_atts( array(
		'competition_id' => '',
		'competitor_id' => '',
		'match_id' => '',
        'self_link_ok' => false,
		'order' => 'ASC',
        'before' => '',
        'after' => '',
	), $atts ) );
	
	$html = "";
	
	if ( $match_id != '' ) {
		// ignore competitor_id and competition_id
		
		$html = scm_sh_match( $match_id );		
	} else {    
		// if both competitor_id and competition_id are provided, check for consistency
		if ( $competition_id != '' ) {
			if ( $competitor_id != '' ) {
				if ( $competition_id != get_post_meta( $competitor_id , '_competitionid' , true ) ) {
					return __('Competitor does not participate in competition', 'sports-club-management');
				}
			}
		} else {
			if ( $competitor_id != '' ) {
				$competition_id = get_post_meta( $competitor_id , '_competitionid' , true );
			}
		}
		
		$html = scm_sh_matches( $competition_id, $competitor_id, $self_link_ok, $order );
		// if competitor_id not specified, competition format may have different scheme to display matches
		$html = apply_filters( 'scm_comp_display_all_matches', $html, $competition_id);		
	}
	
    return "$before \n" . "<p>" . $html . "</p>" . "$after \n";
} 
  
function scm_sh_match( $match_id ) {
	
	$values = get_post_custom($match_id);

	$competition_id = isset($values['_competitionid']) ? esc_attr($values['_competitionid'][0]) : '';
    $compid_1 = isset($values['_compid_1']) ? $values['_compid_1'][0] : '';
	$compid_2 = isset($values['_compid_2']) ? $values['_compid_2'][0] : '';
	$result = isset($values['_result']) ? $values['_result'][0] : '';
	$date = isset($values['_date']) ? $values['_date'][0] : '';
	$time = isset($values['_time']) ? $values['_time'][0] : '';

	$compformat = apply_filters( 'scm_competition_get_format_name', "", $competition_id );

	$resultstring = ( chop($result,":") == '' ? __('vs', 'sports-club-management') : apply_filters( 'scm_match_display_result', $result, $competition_id ));
	$comp1string  = scm_match_get_linked_competitor( apply_filters( 'scm_comp_display_match_competitor_1', get_the_title( $compid_1 ), $match_id, array(), $competition_id )
													   , $compid_1, 0, true 
													   );
	$comp2string  = scm_match_get_linked_competitor( apply_filters( 'scm_comp_display_match_competitor_2', get_the_title( $compid_2 ), $match_id, array(), $competition_id )
													   , $compid_2, 0, true 
													   );
													   
	$html  = "<div id=\"scm_single_match\" class=\"$compformat\">\n";
	$html .= "<div id=\"scm_single_match_datetime\" class=\"$compformat\">";  
	$html .= sprintf("<a href=%s>%s</a>", get_post_permalink( $competition_id ), get_the_title( $competition_id ));
	$html .= "</div>\n";
	$html .= "<div id=\"scm_single_match_datetime\" class=\"$compformat\">" . __('Date', 'sports-club-management').": " . date("M j, Y", strtotime($date)) . "</div>\n";
	$html .= "<div id=\"scm_single_match_datetime\" class=\"$compformat\">" . __('Time', 'sports-club-management'). ": $time </div>\n";
	$html .= "<div id=\"scm_single_match_event\" class=\"$compformat\">\n";
	$html .= "<div id=\"scm_single_match_competitor\" class=\"comp1 $compformat\">" . $comp1string . "</div>\n";
	$html .= "<div id=\"scm_single_match_result\" class=\"$compformat\">" . $resultstring . "</div>\n";
	$html .= "<div id=\"scm_single_match_competitor\" class=\"comp2 $compformat\">" . $comp2string . "</div>\n";
	$html .= "</div>";
	$html .= "</div>";
	

	return $html;
}	

  
function scm_sh_matches( $competition_id, $competitor_id, $self_link_ok, $order ) {
	
    $matches = get_posts( array( 'post_type' => 'scm_match'
                                , 'numberposts' => '-1'
								, 'meta_query' => array(
											array(
												'key'   => '_competitionid',
												'value' => $competition_id
											)
								  )
                                , 'meta_key' => '_date'
								, 'meta_type' => 'DATE'
                                , 'orderby'  => 'meta_value'
								, 'order' => $order
                                ) );

	if ( $competitor_id != '' ) {
        $filtered_matches = array();
        foreach ( $matches as $match ) {
        
            $values = get_post_custom($match->ID);

            $compid_1 = isset($values['_compid_1']) ? $values['_compid_1'][0] : '';
            $compid_2 = isset($values['_compid_2']) ? $values['_compid_2'][0] : '';
            
            if (($compid_1 == $competitor_id) | ($compid_2 == $competitor_id)) {
                array_push( $filtered_matches, $match );
            }
        }
        $matches = $filtered_matches;
    }
	        
	$html = "";

    if ( count( $matches ) == 0 ) {
        $html = __('none', 'sports-club-management');
    } else {
        if ( $competitor_id != '' ) {
            $html .= sprintf("<p><a href=%s>%s</a></p>", get_post_permalink( $competition_id ), get_the_title( $competition_id ));
        }
		
		$compformat = apply_filters( 'scm_competition_get_format_name', "", $competition_id );

		$html .= "<div id=\"scm_match\" class=\"header $compformat\">\n";
		$html .= "<div id=\"scm_match_meta\" class=\"header $compformat\">" . apply_filters( 'scm_comp_display_match_meta_header', "", $competition_id) . "</div>\n";
		$html .= "<div id=\"scm_match_date\" class=\"header $compformat\">" . __('Date', 'sports-club-management') . "</div>\n";
		$html .= "<div id=\"scm_match_event\" class=\"header $compformat\">" . __('Match', 'sports-club-management') . "</div>\n";
		$html .= "</div>";

        foreach ( $matches as $match ) {
        
            $values = get_post_custom($match->ID);

            $compid_1 = isset($values['_compid_1']) ? $values['_compid_1'][0] : '';
            $compid_2 = isset($values['_compid_2']) ? $values['_compid_2'][0] : '';
            $result = isset($values['_result']) ? $values['_result'][0] : '';
            $date = isset($values['_date']) ? $values['_date'][0] : '';
            $time = isset($values['_time']) ? $values['_time'][0] : '';
			
			$timeresultstring = ( chop($result,":") == '' ? $time : apply_filters( 'scm_match_display_result', $result, $competition_id ) );
			$comp1string      = scm_match_get_linked_competitor( apply_filters( 'scm_comp_display_match_competitor_1', get_the_title( $compid_1 ), $match->ID, $matches, $competition_id )
															   , $compid_1, $competitor_id, $self_link_ok 
															   );
			$comp2string      = scm_match_get_linked_competitor( apply_filters( 'scm_comp_display_match_competitor_2', get_the_title( $compid_2 ), $match->ID, $matches, $competition_id )
															   , $compid_2, $competitor_id, $self_link_ok 
															   );
			
			$html .= "<div id=\"scm_match\" class=\"$compformat\">\n";
			$html .= "<div id=\"scm_match_meta\" class=\"$compformat\">" . apply_filters( 'scm_comp_display_match_meta', "", $match->ID, $competition_id) . "</div>\n";
			$html .= "<div id=\"scm_match_date\" class=\"$compformat\">" . date("M j, Y", strtotime($date)) . "</div>\n";
			$html .= "<div id=\"scm_match_event\" class=\"$compformat\">\n";
			$html .= "<div id=\"scm_match_competitor\" class=\"comp1 $compformat\">" . $comp1string . "</div>\n";
			$html .= "<div id=\"scm_match_result\" class=\"$compformat\">&nbsp;" . ($match->post_content != "" ? "<a href=".get_post_permalink( $match->ID ).">".$timeresultstring."</a>" : $timeresultstring) . "&nbsp;</div>\n";
			$html .= "<div id=\"scm_match_competitor\" class=\"comp2 $compformat\">" . $comp2string . "</div>\n";
			$html .= "</div>";
			$html .= "</div>";
        }
    }

	return $html;
}	
  

function scm_match_get_linked_competitor( $title, $comp_id, $self, $self_link_ok ) {

    $html = $title;
    
    if ( 'yes' == get_post_meta( $comp_id, '_disqualified', true ) ) {
        $html .= " (" . __('DSQ', 'sports-club-management') . ")";
    }
    
    if ( ($comp_id != $self) | $self_link_ok ) {    
        $id = -1;
        
        switch ( get_post_meta( $comp_id, '_competitor_type', true ) ) {
            case 'member' : {
                $id = get_post_meta( $comp_id, '_member', true );
                break;
            }
            case 'team' : {
                $id = $comp_id;
                break;
            }
        }
        if ($id > 0) {
            $html = sprintf("<a href=%s>%s</a>", get_post_permalink( $id ), $html);
        }
    }

    return $html;
}

function scm_sh_match_current( $atts ) {
    
    if ('use_competitions' != get_option('scm_include_competitions_data')) { 
        return "";
    }
	extract( shortcode_atts( array(
		'competition_group_id' => '',
		'competition_id' => '',
		'start' => '1',
        'end' => '1',
        'emptylist' => '',
        'before' => '',
        'after' => '',
	), $atts ) );  
	
	// get matches in [today-start .. today+end]
	$startdate = strtotime("-$start days");
	$enddate   = strtotime("+$end days");
	if (($competition_group_id == '') && ($competition_id == '')) {
		$matches = get_posts( array( 'post_type' => 'scm_match'
						, 'numberposts' => '-1'
						, 'meta_key' => '_date'
						, 'orderby' => 'meta_value'
						, 'order' => 'ASC'
						, 'meta_query' => array(
											array(
												'key' => '_date',
												'value' => array(date("Y-m-d", $startdate), date("Y-m-d", $enddate)),
												'compare' => 'BETWEEN'
											)  
										  )											
						) );
	} else if ($competition_group_id != '') {
		$competitions = get_posts( array( 'post_type' => 'scm_comp'
							, 'numberposts' => '-1'
							, 'meta_key' => '_groupid'
							, 'meta_value' => $competition_group_id									
							) );
		$matches = get_posts( array( 'post_type' => 'scm_match'
						, 'numberposts' => '-1'
						, 'meta_key' => '_date'
						, 'orderby' => 'meta_value'
						, 'order' => 'ASC'
						, 'meta_query' => array(
											array(
												'key' => '_date',
												'value' => array(date("Y-m-d", $startdate), date("Y-m-d", $enddate)),
												'compare' => 'BETWEEN'
											),  
											array(
												'key' => '_competitionid',
												'value' => wp_list_pluck( $competitions, 'ID'),
												'compare' => 'IN'
											)  
										  )											
						) );
	} else {
		$matches = get_posts( array( 'post_type' => 'scm_match'
						, 'numberposts' => '-1'
						, 'meta_key' => '_date'
						, 'orderby' => 'meta_value'
						, 'order' => 'ASC'
						, 'meta_query' => array(
											array(
												'key' => '_date',
												'value' => array(date("Y-m-d", $startdate), date("Y-m-d", $enddate)),
												'compare' => 'BETWEEN'
											),  
											array(
												'key' => '_competitionid',
												'value' => $competition_id,
												'compare' => '='
											)  
										  )											
						) );
	}
	
	// display selected matchs
	$html = "";
	foreach ( $matches as $match ) {
		$values = get_post_custom($match->ID);

		$compid_1 = isset($values['_compid_1']) ? esc_attr($values['_compid_1'][0]) : '';
		$compid_2 = isset($values['_compid_2']) ? esc_attr($values['_compid_2'][0]) : '';
		$result = isset($values['_result']) ? esc_attr($values['_result'][0]) : '';
		$competitionid = isset($values['_competitionid']) ? esc_attr($values['_competitionid'][0]) : '';
		$date = isset($values['_date']) ? esc_attr($values['_date'][0]) : '';
		$time = isset($values['_time']) ? esc_attr($values['_time'][0]) : '';
		
		$dateresultstring = ($result == '' ? date("M j", strtotime($date)) ."<br> $time" : $result);
		
		$compformat = apply_filters( 'scm_competition_get_format_name', "", $competitionid );
	
		$html .= "<div id=\"scm_widget_match\">\n";
		$html .= "<div id=\"scm_widget_competitor\" class=\"comp1 $compformat\">" . apply_filters( 'scm_comp_display_match_competitor_1', get_the_title( $compid_1 ), $match->ID, $matches, $competitionid ) . "</div>\n";
		$html .= "<div id=\"scm_widget_result\" class=\"$compformat\">" . ($match->post_content != "" ? "<a href=".get_post_permalink( $match->ID ).">".$dateresultstring."</a>" : $dateresultstring) . "</div>\n";
		$html .= "<div id=\"scm_widget_competitor\" class=\"comp2 $compformat\">" . apply_filters( 'scm_comp_display_match_competitor_2', get_the_title( $compid_2 ), $match->ID, $matches, $competitionid ) . "</div>\n";
		$html .= "</div>";
	}
	if ( count( $matches ) == 0 ) {
		$html .= $emptylist;
	}
	
	return $competition_group_id . "$before \n" . "<p>" . $html . "</p>" . "$after \n";
} 
	
	
