<?php 

define('SCM_CFMT_INDIVIDUAL', 1003); 

include_once 'scores.php';

add_filter('scm_competition_get_format_name', 'scm_comp_format_individual_get_format_name', 10, 2 );
add_filter('scm_competition_get_formats', 'scm_comp_format_individual_get_format', 10, 2 );
add_filter('scm_competition_display_format', 'scm_comp_format_individual_display_format', 10, 2 );
add_filter('scm_competition_display_format_fields', 'scm_comp_format_individual_display_format_fields', 10, 2 );
add_action('scm_competition_save_format_fields', 'scm_comp_format_individual_save_format_fields');
//add_action('scm_competition_create_matches','scm_comp_format_individual_create_matches');
add_filter('scm_competition_display_ranking', 'scm_comp_format_individual_display_ranking', 10, 4 );
//add_filter('scm_comp_display_match_meta_header', 'scm_comp_format_individual_display_meta_header', 10, 2 ); 
//add_filter('scm_comp_display_match_meta', 'scm_comp_format_individual_display_meta', 10, 3 ); 
//add_filter('scm_comp_display_match_competitor_1', 'scm_comp_format_individual_display_competitor_1', 10, 4 ); 
add_filter('scm_comp_display_match_competitor_2', 'scm_comp_format_individual_display_competitor_2', 10, 4 ); 
add_filter('scm_match_display_single_competitor_field', 'scm_comp_format_individual_display_single_competitor_field', 10, 2 ); 
//add_filter('scm_match_display_format_fields', 'scm_comp_format_individual_display_format_match_fields', 10, 3 ); 
add_filter('scm_match_display_format_result_field', 'scm_comp_format_individual_display_format_match_result_field', 10, 4 );
add_filter('scm_match_display_result',  'scm_comp_format_individual_display_result', 10, 2 );
//add_action('scm_match_save_format_fields', 'scm_comp_format_individual_save_format_match_fields');
//add_filter('scm_comp_match_list_header', 'scm_comp_format_individual_match_list_header', 10, 2 ); 
//add_filter('scm_comp_match_list_entry', 'scm_comp_format_individual_match_list_entry', 10, 3 ); 

function scm_comp_format_individual_get_format_name( $html, $compid ) {

    if ( get_post_meta( $compid, '_formatid', true) == SCM_CFMT_INDIVIDUAL ) {
        $html = "individual"; 
    }
        
    return $html; 
}

function scm_comp_format_individual_get_format( $html, $id ) {
    $html .= "<option ";      
    $html .= ($id == SCM_CFMT_INDIVIDUAL) ? "selected='selected' " : "";   
    $html .= "value=" . SCM_CFMT_INDIVIDUAL . ">" . __('Individual', 'sports-club-management') . "</option>\n";

    return $html; 
}

function scm_comp_format_individual_display_format( $html, $compid ) {

    if ( get_post_meta( $compid, '_formatid', true) == SCM_CFMT_INDIVIDUAL ) {
        $html .= sprintf("%s [%s]", __('Individual', 'sports-club-management'), get_post_meta( $compid , '_size' , true )); 
    }
        
    return $html; 
}

function scm_comp_format_individual_display_format_fields( $html, $compid ) {

    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_INDIVIDUAL ) 
        return $html;

    $values = get_post_custom($compid);
    
    $formatok      		   		  = isset($values['_formatok']) ? esc_attr($values['_formatok'][0]) : 'false';
    $size         				  = isset($values['_size']) ? esc_attr($values['_size'][0]) : ''; 
    $separator             	  	  = isset($values['_separator']) ? esc_attr($values['_separator'][0]) : '-'; 
    $scoringsystem 		   	  	  = isset($values['_scoringsystem']) ? esc_attr($values['_scoringsystem'][0]) : '';
    $scoringsystem_matches 	  	  = isset($values['_scoringsystem_matches']) ? esc_attr($values['_scoringsystem_matches'][0]) : '';
    $scoringsystem_matches_bestof = isset($values['_scoringsystem_matches_bestof']) ? esc_attr($values['_scoringsystem_matches_bestof'][0]) : '0';
    $scoringsystem_order   	  	  = isset($values['_scoringsystem_order']) ? esc_attr($values['_scoringsystem_order'][0]) : '';
    
    $html .= "<tr>\n";
    $html .= "<td align='right'><label>" . __('Number of rounds', 'sports-club-management') . "</label></td>";
    if ($formatok == 'false') {
        $html .= "<td><input id='_size' type='text' name='_size' value='$size' /></td>\n";        
    } else {
        $html .= "<td>$size</td>\n";                
    }
    $html .= "</tr>\n";
	
    $html .= "<tr>\n";
    $html .= "<td align='right'><label>" . __('Round score separator', 'sports-club-management') . "</label></td>";
    $html .= "<td><input id='_separator' type='text' name='_separator' value='$separator' /></td>\n";        
    $html .= "</tr>\n";

    $score_systems = array( __('None', 'sports-club-management'),
                            __('Sum', 'sports-club-management')
                          );    
    $html .= "<tr>\n";
    $html .= "<td align='right'><label>" . __('Scoring System', 'sports-club-management') . " - " . __('Match', 'sports-club-management') . "</label></td>\n";        
    $html .= "<td><select name='scoringsystem'>\n";        
    for ($h = 0; $h != 2; $h++) {
        $html .= "<option ";       
        $html .= ($scoringsystem == $h) ? "selected='selected' " : "";   
        $html .= "value='$h'>" . $score_systems[$h] . "</option>\n";
    }
    $html .= "</select></td>";        
    $html .= "</tr>\n";
	
    $score_systems_matches = array( __('None', 'sports-club-management'),
									__('Sum', 'sports-club-management'),
									__('Average Sum', 'sports-club-management')
								  );
    $html .= "<tr>\n";
    $html .= "<td align='right'><label>" . __('Scoring System', 'sports-club-management') . " - " . __('Matches', 'sports-club-management') . "</label></td>\n";        
    $html .= "<td><select name='scoringsystem_matches'>\n";        
    for ($h = 0; $h != 3; $h++) {
        $html .= "<option ";       
        $html .= ($scoringsystem_matches == $h) ? "selected='selected' " : "";   
        $html .= "value='$h'>" . $score_systems_matches[$h] . "</option>\n";
    }
    $html .= "</select></td>";        
    $html .= "</tr>\n";

    $html .= "<tr>\n";
    $html .= "<td align='right'><label>" . __('Scoring System', 'sports-club-management') . " - " . __('Matches', 'sports-club-management') . " - " . __('Use best #', 'sports-club-management') . "</label></td>\n";
    $html .= "<td><input id='_scoringsystem_matches_bestof' type='text' name='_scoringsystem_matches_bestof' value='$scoringsystem_matches_bestof' /></td>\n";        
    $html .= "</tr>\n";

    $orders = array( __('More is better', 'sports-club-management'),
					 __('Less is better', 'sports-club-management'),
				   );
    $html .= "<tr>\n";
    $html .= "<td align='right'><label>" . __('Scoring System', 'sports-club-management') . " - " . __('Order', 'sports-club-management') . "</label></td>\n";        
    $html .= "<td><select name='scoringsystem_order'>\n";        
    for ($h = 0; $h != 2; $h++) {
        $html .= "<option ";       
        $html .= ($scoringsystem_order == $h) ? "selected='selected' " : "";   
        $html .= "value='$h'>" . $orders[$h] . "</option>\n";
    }
    $html .= "</select></td>";        
    $html .= "</tr>\n";

    return $html; 
}

function scm_comp_format_individual_save_format_fields( $compid ) {

    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_INDIVIDUAL ) 
        return;

    if (isset($_POST['_size'])) 
        update_post_meta($compid, '_size', esc_attr($_POST['_size']));
    if (isset($_POST['_separator'])) 
        update_post_meta($compid, '_separator', esc_attr($_POST['_separator']));
    if (isset($_POST['scoringsystem']))
        update_post_meta($compid, '_scoringsystem', esc_attr($_POST['scoringsystem']));
    if (isset($_POST['scoringsystem_matches']))
        update_post_meta($compid, '_scoringsystem_matches', esc_attr($_POST['scoringsystem_matches']));
    if (isset($_POST['_scoringsystem_matches_bestof']))
        update_post_meta($compid, '_scoringsystem_matches_bestof', esc_attr($_POST['_scoringsystem_matches_bestof']));
    if (isset($_POST['scoringsystem_order']))
        update_post_meta($compid, '_scoringsystem_order', esc_attr($_POST['scoringsystem_order']));
}

function scm_comp_format_individual_display_ranking( $html, $compid, $display_all, $display_header ) {
    
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_INDIVIDUAL ) 
        return $html;

    if ( $display_header == true ) {
		if ( is_admin() ) {
			$html .= "<tr><th>"
					.join( "</th><th>", array( "", __('Played', 'sports-club-management'), __('Points', 'sports-club-management')) )
					."</th></tr>\n";
		} else {
			$html .= "<div id=\"scm_ranking\" class=\"header individual\">\n"; 
			$html .= "<div id=\"scm_ranking_competitor\" class=\"header individual\">&nbsp;</div>\n"; 
			$html .= "<div id=\"scm_ranking_fields\" class=\"header individual\">\n";
			$html .= "<div id=\"scm_ranking_played\" class=\"header individual\">" . __('Played', 'sports-club-management') . "</div>\n";
			$html .= "<div id=\"scm_ranking_points\" class=\"header individual\">" . __('Points', 'sports-club-management') . "</div>\n";
			$html .= "</div>\n";	 
			$html .= "</div>\n"; 
		}
    }
            
    $rankings = scm_comp_format_individual_get_ranking($compid);
    
    for ($h = 0; $h != count($rankings); $h++) {
        $competitorid = $rankings[$h]['competitorid'];
        $disqualified = get_post_meta( $competitorid , '_disqualified' , true );
        
        if ($disqualified != 'yes') {
        
            if ( is_admin() ) {
                $link = site_url( "/wp-admin/post.php?post=$competitorid&action=edit" ) . " target=\"_blank\"";
            } else {
                $link = "#";
                switch ( get_post_meta( $competitorid, '_competitor_type', true ) ) {
                    case 'member' : {
                        $id = get_post_meta( $competitorid, '_member', true );
                        $link = get_post_permalink( $id );
                        break;
                    }
                    case 'team' : {
                        $link = get_post_permalink( $competitorid );
                        break;
                    }
                }
            }
        
            if ($link != "#") {
                $link = "<a href=" . $link . ">" . get_the_title( $competitorid ) . "</a>";
            } else {
                $link = get_the_title( $competitorid );
            }
			if ( is_admin() ) {			
				$html .= sprintf("<tr><td>%s</td>%s</tr>\n", $link, $rankings[$h]['results']);
			} else {
				$html .= "<div id=\"scm_ranking\" class=\"individual\">\n"; 
				$html .= "<div id=\"scm_ranking_competitor\" class=\"individual\">" . $link . "</div>\n"; 
				$html .= $rankings[$h]['results'];
				$html .= "</div>\n"; 
			}
        }
    }
             
    return $html;   
}

function scm_comp_format_individual_get_ranking($compid) {
    $competitors = get_posts( array( 'post_type' => 'scm_competitor'
                                   , 'numberposts' => '-1'
                                   , 'meta_key' => '_competitionid'
                                   , 'meta_value' => $compid
                                   ) );
    $rankings = array();                                
    foreach ($competitors as $competitor) {
        $results = scm_comp_format_individual_get_results_competitor( $competitor->ID
																	, get_post_meta( $compid, '_size' , true )
																	, get_post_meta( $compid, '_scoringsystem' , true )
																	, get_post_meta( $compid, '_scoringsystem_matches' , true ) 
																	, get_post_meta( $compid, '_scoringsystem_matches_bestof' , true ) 
																	, get_post_meta( $compid, '_scoringsystem_order' , true ) 
																	);
        switch ( get_post_meta( $compid , '_scoringsystem' , true ) ) {
            case 0: $points = $results['points']; break;
            case 1: $points = $results['points']; break;
        }
		if ( is_admin() ) {
			$string = "<td>". $results['played']."/".$results['matches'] ."</td>"
					 ."<td>". $points ."</td>";
		} else {
			$string  = "<div id=\"scm_ranking_fields\" class=\"individual\">\n";
			$string .= "<div id=\"scm_ranking_played\" class=\"individual\">" . $results['played']."/".$results['matches']. "</div>\n";
			$string .= "<div id=\"scm_ranking_points\" class=\"individual\">" . $points . "</div>\n";
			$string .= "</div>\n";	 
		}
	    $ranking = array( "competitorid" => $competitor->ID
                        , "points" => $points
						, "played" => $results['played']
                        , "results" => $string
                        );
        array_push( $rankings, $ranking ); 
    }
	
	function scm_comp_format_individual_sort_ascending($a,$b) {
		if ($a['points'] == $b['points']) return 0;
		if ($a['played'] == 0) return 1;
		if ($b['played'] == 0) return -1;
		return ( $a['points'] > $b['points'] ? -1 : 1);
	}
	function scm_comp_format_individual_sort_descending($a,$b) {
		if ($a['points'] == $b['points']) return 0;
		if ($a['played'] == 0) return 1;
		if ($b['played'] == 0) return -1;
		return ( $a['points'] < $b['points'] ? -1 : 1);
	}

	if ( 0 == get_post_meta( $compid, '_scoringsystem_order' , true ) ) {
		usort($rankings, "scm_comp_format_individual_sort_ascending");
	} else {
		usort($rankings, "scm_comp_format_individual_sort_descending");
	}		
    
    return $rankings;
}
    
function scm_comp_format_individual_get_results_competitor( $competitorID, $nrofrounds, $match_scoring, $matches_scoring, $matches_bestof, $matches_order ) { 
    $matches = get_posts( array( 'post_type' => 'scm_match'
                               , 'numberposts' => '-1'
                               , 'meta_key' => '_compid_1'
                               , 'meta_value' => $competitorID
                               ) );
    $played = $points = 0;
	$validpoints = array();
    foreach ($matches as $match) {  
	
		$match_result = scm_comp_get_result_individual( get_post_meta( $match->ID , '_result' , true ), $nrofrounds ); 
		$pts = 0;
        switch ( $match_scoring ) {
            case 0: $pts = $match_result['none']; break;
            case 1: $pts = $match_result['sum']; break;
        }
		
		if ( $match_result['valid'] == 1 ) {
			$played++;
			if ( $matches_scoring == 0 ) {
				$points = $pts; 
			} else if ( $matches_bestof != 0 ) {
				array_push( $validpoints, $pts );
			} else { 
				$points += $pts; 			
			}
		}
    }
	
	if (( $matches_bestof != 0 ) && ($matches_scoring > 0)) {
		if ( 0 == $matches_order ) {
			rsort( $validpoints );
		} else {
			sort( $validpoints );
		}		
		$min = count( $validpoints );
		if ( $matches_bestof < $min ) {
			$min = $matches_bestof;
		}
		$points = 0; 
		for ($h = 0; $h < $min; $h++) {
			$points += $validpoints[$h];
		}
	}
	
	if (( $played > 0 ) && ( $matches_scoring == 2 )) {
		return array( "played" => $played 
					, "matches" => count($matches)
					, "points" => $points/$played
					);
	} else if ( $played > 0 ) {
		return array( "played" => $played 
					, "matches" => count($matches)
					, "points" => $points
					);
	} else {
		return array( "played" => $played 
					, "matches" => count($matches)
					, "points" => 0
					);
	}
}
 
function scm_comp_get_result_individual( $result, $nrofrounds ) {
	
	$rounds = explode(":", $result);
	
    $valid = $sum = $none = 0;

	$error = 0;
	if ( count( $rounds ) != $nrofrounds ) {
		$error++;
	}
	
	if ( $error == 0 ) {
		foreach ($rounds as $round) {
			$pts = trim( $round );
			if ( is_numeric( $pts ) ) {
				$sum += $pts;
				$none = $pts;
			} else {
				$error++;
			}
		}
	}
	
	if ( $error == 0 ) {
		$valid = 1;
	}
	
    return array ( "valid" => $valid, "sum" => $sum, "none" => $none);	
}
 
function scm_comp_format_individual_display_competitor_2( $html, $matchid, $matches, $compid ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_INDIVIDUAL ) 
        return $html;

	return "";
}	

function scm_comp_format_individual_display_single_competitor_field( $html, $compid ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_INDIVIDUAL ) 
        return $html;

	return "";
}

function scm_comp_format_individual_display_format_match_result_field( $html, $matchid, $compid, $results ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_INDIVIDUAL ) 
        return $html;
	
	$size = get_post_meta( $compid, '_size', true);
	for ($h = 1; $h < $size; $h++) {
		$value = ( $h < count($results) ? $results[$h] : $results[0] );
		$html .= "$h <br><input id='result' type='text' name='result[]' value='$value' />";
	}
	$html .= "$size";
	
	return $html;
}

function scm_comp_format_individual_display_result( $html, $compid ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_INDIVIDUAL ) 
        return $html;
	
	// display sum, if that scoring system has been selected
	$match_result = scm_comp_get_result_individual( $html, get_post_meta( $compid, '_size', true) );
	if ( 1 == get_post_meta( $compid, '_scoringsystem' , true ) ) {
		$html .= " (" . $match_result['sum'] . ")";
	}
	
	return str_replace(":", get_post_meta( $compid, '_separator', true), $html);
}
