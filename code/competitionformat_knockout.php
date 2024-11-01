<?php 

define('SCM_CFMT_KNOCKOUT', 1002); 

include_once 'scores.php';

add_filter('scm_competition_get_format_name', 'scm_comp_format_knockout_get_format_name', 10, 2 );
add_filter('scm_competition_get_formats', 'scm_comp_format_knockout_get_format', 10, 2 );
add_filter('scm_competition_display_format', 'scm_comp_format_knockout_display_format', 10, 2 );
add_filter('scm_competition_display_format_fields', 'scm_comp_format_knockout_display_format_fields', 10, 2 );
add_action('scm_competition_save_format_fields', 'scm_comp_format_knockout_save_format_fields');
add_action('scm_competition_create_matches','scm_comp_format_knockout_create_matches');
add_filter('scm_competition_display_ranking', 'scm_comp_format_knockout_display_ranking', 10, 4 );
add_filter('scm_comp_display_match_meta_header', 'scm_comp_format_knockout_display_meta_header', 10, 2 ); 
add_filter('scm_comp_display_match_meta', 'scm_comp_format_knockout_display_meta', 10, 3 ); 
add_filter('scm_comp_display_match_competitor_1', 'scm_comp_format_knockout_display_competitor_1', 10, 4 ); 
add_filter('scm_comp_display_match_competitor_2', 'scm_comp_format_knockout_display_competitor_2', 10, 4 ); 
//add_filter('scm_comp_display_all_matches', 'scm_comp_format_knockout_display_all_matches', 10, 2 ); 
add_filter('scm_match_display_competitors_field', 'scm_comp_format_knockout_display_competitors_field', 10, 2 ); 
add_filter('scm_match_display_format_fields', 'scm_comp_format_knockout_display_format_match_fields', 10, 3 ); 
add_action('scm_match_save_format_fields', 'scm_comp_format_knockout_save_format_match_fields');
add_filter('scm_comp_match_list_header', 'scm_comp_format_knockout_match_list_header', 10, 2 ); 
add_filter('scm_comp_match_list_entry', 'scm_comp_format_knockout_match_list_entry', 10, 3 ); 

function scm_comp_format_knockout_get_format_name( $html, $compid ) {

    if ( get_post_meta( $compid, '_formatid', true) == SCM_CFMT_KNOCKOUT ) {
        $html = "knockout"; 
    }
        
    return $html; 
}

function scm_comp_format_knockout_get_format( $html, $id ) {
    $html .= "<option ";      
    $html .= ($id == SCM_CFMT_KNOCKOUT) ? "selected='selected' " : "";   
    $html .= "value=" . SCM_CFMT_KNOCKOUT . ">" . __('Knockout', 'sports-club-management') . "</option>\n";

    return $html; 
}

function scm_comp_format_knockout_display_format( $html, $compid ) {

    if ( get_post_meta( $compid, '_formatid', true) == SCM_CFMT_KNOCKOUT ) {
        $html .= sprintf("%s [%s]", __('Knockout', 'sports-club-management'), get_post_meta( $compid , '_size' , true )); 
    }
        
    return $html; 
}

function scm_comp_format_knockout_display_format_fields( $html, $compid ) {

    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return $html;

    $values = get_post_custom($compid);
    
    $formatok      = isset($values['_formatok']) ? esc_attr($values['_formatok'][0]) : 'false';
    $size          = isset($values['_size']) ? esc_attr($values['_size'][0]) : ''; 
    
    $size_options = "";
    for ($h = 1; $h < 8; $h++) {
        $val = pow(2,$h);
        $size_options .= "<option ";  
        $size_options .= ($size == $val) ? "selected='selected' " : "";           
        $size_options .= "value='$val'> $val </option>\n";
    }  
    
    $html .= "<tr>\n";
    $html .= "<td align='right'><label>" . __('Scheme size', 'sports-club-management') . "</label></td>";
    if ($formatok == 'false') {
        $html .= "<td><select name='_size'>\n". $size_options ."</select></td>\n";        
        //$html .= "<td><input id='_size' type='text' name='_size' value='$size' /></td>\n";        
    } else {
        $html .= "<td>$size</td>\n";                
    }
    $html .= "</tr>\n";

    return $html; 
}

function scm_comp_format_knockout_save_format_fields( $compid ) {

    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return;

    if (isset($_POST['_size'])) 
        update_post_meta($compid, '_size', esc_attr($_POST['_size']));
}

function scm_comp_format_knockout_display_ranking( $html, $compid, $display_all, $display_header ) {
    
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return $html;

    if ( $display_header == true ) {
		if ( is_admin() ) {
			$html .= "<tr><th>".__('Competition', 'sports-club-management')."</th><th>".__('Rank', 'sports-club-management')."</th><th>".__('Competitor', 'sports-club-management')."</th></tr>\n";
		} else {
			$html .= "<div id=\"scm_ranking\" class=\"header knockout\">\n"; 
			$html .= "<div id=\"scm_ranking_competition\" class=\"header knockout\">" . __('Competition', 'sports-club-management') . "</div>\n"; 
			$html .= "<div id=\"scm_ranking_rank\" class=\"header knockout\">" . __('Rank', 'sports-club-management') . "</div>\n"; 
			$html .= "<div id=\"scm_ranking_competitor\" class=\"header knockout\">&nbsp;</div>\n"; 
			$html .= "</div>\n"; 
		}
    }
            
    $rankings = scm_comp_format_knockout_get_ranking($compid);
    
    for ($h = 0; $h != count($rankings); $h++) {
		if ( is_admin() ) {
			$html .= sprintf("<tr><td>%s</td>%s</tr>\n", get_the_title( $compid ), $rankings[$h]['results']);
		} else {
			$html .= "<div id=\"scm_ranking\" class=\"knockout\">\n"; 
			$html .= "<div id=\"scm_ranking_competition\" class=\"knockout\">" . get_the_title( $compid ) . "</div>\n"; 
			$html .= $rankings[$h]['results']; 
			$html .= "</div>\n"; 
		}
    }
             
    return $html;   
}

function scm_comp_format_knockout_create_matches( $compid ) {

    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return;

    $size = get_post_meta( $compid, '_size', true); 
    
    $comp_ids = array();
    
    for ($idx = 0; $idx < $size; $idx++) {
        // create fixed number of competitors
        $new_competitor = array(
              'post_title'    => $compid.".c".$idx,
              'post_type'     => 'scm_competitor',
              'post_content'  => '',
              'post_status'   => 'publish',
        );
        
        // insert the post into the database
        $competitor_id  = wp_insert_post( $new_competitor );
        
        // initialize competitor data
        update_post_meta($competitor_id, '_competitor_type', 'external');
        update_post_meta($competitor_id, '_name', "comp".$idx);
        update_post_meta($competitor_id, '_competitionid', $compid);
        
        $comp_ids[$idx] = $competitor_id;
    }

    $match_ids = array();
    
    for ($idx = 1; $idx < $size; $idx++) {
        // create new match
        $new_match = array(
              'post_title'    => $compid.".m".$idx,
              'post_type'     => 'scm_match',
              'post_content'  => '',
              'post_status'   => 'publish',
        );
        
        // insert the post into the database
        $match_id = wp_insert_post( $new_match );
        
        // initialize match data
        update_post_meta($match_id, '_number', ($size - $idx));
        update_post_meta($match_id, '_competitionid', $compid);
        update_post_meta($match_id, '_date', '0000-00-00');
        
        //array_push($match_ids, $match_id);
		$match_ids[$idx] = $match_id;
    }

    for ($idx = 0; $idx < ($size / 2); $idx++) {
		update_post_meta($match_ids[$idx+1], '_compid_1', $comp_ids[2*$idx]);
		update_post_meta($match_ids[$idx+1], '_compid_2', $comp_ids[2*$idx + 1]);
    }
	
    for ($idx = 1, $idy = ($size/2 + 1); $idx < ($size - 1); $idy++) {
		update_post_meta($match_ids[$idx++], '_nextmatchid', $match_ids[$idy]); 
		update_post_meta($match_ids[$idx++], '_nextmatchid', $match_ids[$idy]); 
    }

}

function scm_comp_format_knockout_get_ranking($compid) {
	
    $matches = get_posts( array( 'post_type' => 'scm_match'
                               , 'numberposts' => '-1'
                               , 'meta_key' => '_competitionid'
                               , 'meta_value' => $compid
                               ) );
							   
    foreach ( $matches as $match ) {
		$number = get_post_meta( $match->ID , '_number' , true );
		$results[$number] = get_post_meta( $match->ID , '_result' , true );
	}

	$size = get_post_meta( $compid , '_size' , true );
    $rankings = array();   
	$h = 1;	
	for ($cnt = 1; $h < $size; $cnt *= 2) {
		$firstvalid = 0;
		for ($k = 0; $k < $cnt; $k++, $h++) {
			// search match id $h
			for ($j = 0; $j < count($matches); $j++) {
				if ($h == get_post_meta($matches[$j]->ID, '_number', true)) break;
			}
			$match = $matches[$j];
			
			$compid_1 = get_post_meta($match->ID , '_compid_1' , true);
			$compid_2 = get_post_meta($match->ID , '_compid_2' , true);
				
			$result = get_post_meta($match->ID , '_result' , true);
			if ( '' == $result ) {
				if (($compid_1 != '') || ($compid_2 != '')) {
					if ( is_admin() ) {
						$string = sprintf("<td>%s</td><td>%s - %s</td>"
					                 , ($k == $firstvalid ? "1/".$cnt." ".__('finalist', 'sports-club-management') : "")
									 , ( $compid_1 != '' 
									   ? ( sprintf("<a href=%s target=\"_blank\">%s</a>", site_url( "/wp-admin/post.php?post=$compid_1&action=edit" ), get_the_title($compid_1)))
									   : ( ($results[2 * $h] == '') ? sprintf("%s #%s", __('Winner', 'sports-club-management'), (2 * $h) ) : $results[2 * $h])
									   )
									 , ( $compid_2 != '' 
									   ? ( sprintf("<a href=%s target=\"_blank\">%s</a>", site_url( "/wp-admin/post.php?post=$compid_2&action=edit" ), get_the_title($compid_2)))
									   : ( ($results[1 + 2 * $h] == '') ? sprintf("%s #%s", __('Winner', 'sports-club-management'), (1 + 2 * $h) ) : $results[1 + 2 * $h])
									   )
									 );
					} else {
						$string = "<div id=\"scm_ranking_rank\" class=\"knockout\">" . ($k == $firstvalid ? "1/".$cnt." ".__('finalist', 'sports-club-management') : "&nbsp") . "</div>\n"; 
						$string .= "<div id=\"scm_ranking_competitor\" class=\"knockout\">";
						$string .= sprintf("%s ". __('vs', 'sports-club-management') ." %s"
					                 , ( $compid_1 != '' ? ( get_the_title($compid_1) )
									   : ( ($results[2 * $h] == '') ? sprintf("%s #%s", __('Winner', 'sports-club-management'), (2 * $h) ) : $results[2 * $h])
									   )
									 , ( $compid_2 != '' ? ( get_the_title($compid_2) )
									   : ( ($results[1 + 2 * $h] == '') ? sprintf("%s #%s", __('Winner', 'sports-club-management'), (1 + 2 * $h) ) : $results[1 + 2 * $h])
									   )
									 );
						$string .= "</div>\n"; 
					}
					$ranking = array( "results" => $string );
					array_push( $rankings, $ranking );
				} else {
					$firstvalid++;
				}
			} else if ( 'bye' != $result ) {
				
				if ( $compid_1 == '' ) {
					$winner = $compid_2;
					$loser  = $compid_1;
				} else if ( $compid_2 == '' ) {
					$winner = $compid_1;
					$loser  = $compid_2;
				} else {	
					$match_result = scm_comp_get_result_match( $result );
					if ( $match_result['valid'] == 1 ) {
						if ( $match_result['home_sets'] > $match_result['away_sets'] ) {
							$winner = $compid_1;
							$loser  = $compid_2;
						} else if ( $match_result['home_sets'] < $match_result['away_sets'] ) {
							$winner = $compid_2;
							$loser  = $compid_1;
						} else {
						$winner = $loser = '';
						}
					} else {
						$winner = $loser = '';
					}
				}
				
				if ($cnt == 1) {
					if ( is_admin() ) {
						$string = sprintf("<td>%s</td><td>%s</td>"
									 , __('Winner', 'sports-club-management')
									 , ( sprintf("<a href=%s target=\"_blank\">%s</a>", site_url( "/wp-admin/post.php?post=$winner&action=edit" ), get_the_title($winner))  )
									 );
					} else {
						$string  = "<div id=\"scm_ranking_rank\" class=\"knockout\">" . __('Winner', 'sports-club-management') . "</div>\n"; 
						$string .= "<div id=\"scm_ranking_competitor\" class=\"knockout\">" . get_the_title($winner) . "</div>\n"; 
					}
					$ranking = array( "results" => $string );
					array_push( $rankings, $ranking );
				}
				
				if ( is_admin() ) {
					$string = sprintf("<td>%s</td><td>%s</td>"
								, ($k == $firstvalid ? "1/".$cnt." ".__('finalist', 'sports-club-management') : "")
								, ( sprintf("<a href=%s target=\"_blank\">%s</a>", site_url( "/wp-admin/post.php?post=$loser&action=edit" ), get_the_title($loser)) )
								);
				} else {
					$string  = "<div id=\"scm_ranking_rank\" class=\"knockout\">" . ($k == $firstvalid ? "1/".$cnt." ".__('finalist', 'sports-club-management') : "&nbsp") . "</div>\n"; 
					$string .= "<div id=\"scm_ranking_competitor\" class=\"knockout\">" . get_the_title($loser) . "</div>\n"; 
				}
				$ranking = array( "results" => $string );
				array_push( $rankings, $ranking );
			} else {
				$firstvalid++;
			}
		}
    }
    
    return $rankings;
}

function scm_comp_format_knockout_display_meta_header( $html, $compid ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return $html;
	
	if ( is_admin() ) {
		$html = "<th></th>" . $html;
	} else {
		$html = "&nbsp;" . $html;
	}
	

	return $html;	
}
    
function scm_comp_format_knockout_display_meta( $html, $matchid, $compid ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return $html;
	
	if ( is_admin() ) {
		$html = sprintf("<td>%s</td>", get_post_meta( $matchid , '_number' , true )) . $html;
	} else {
		$html = get_post_meta( $matchid , '_number' , true ) . $html;
	}

	return $html;	
}
    
function scm_comp_format_knockout_display_competitor_1( $html, $matchid, $matches, $compid ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return $html;

	$compid_1 = get_post_meta( $matchid , '_compid_1' , true );
	$number   = get_post_meta( $matchid , '_number' , true );
	
	if ( $compid_1 == '' ) {
		$html = scm_comp_format_knockout_display_empty_competitor( (2 * $number), $matches, $compid);
	}
	
	return $html;
}	

function scm_comp_format_knockout_display_competitor_2( $html, $matchid, $matches, $compid ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return $html;

	$compid_2 = get_post_meta( $matchid , '_compid_2' , true );
	$number   = get_post_meta( $matchid , '_number' , true );
	
	if ( $compid_2 == '' ) {
		$html = scm_comp_format_knockout_display_empty_competitor( (1 + 2 * $number), $matches, $compid);
	}
	
	return $html;
}	

function scm_comp_format_knockout_display_empty_competitor( $predecessor, $matches, $compid) {
	
	$results = array();
    foreach ( $matches as $match ) {
		$number = get_post_meta( $match->ID , '_number' , true );
		$results[$number] = get_post_meta( $match->ID , '_result' , true );
	}

	$size = get_post_meta( $compid , '_size' , true );

	$html = __('bye', 'sports-club-management');
	if (($predecessor < $size) && ( $results[$predecessor] == '')) {
		$html = sprintf("%s #%s", __('Winner', 'sports-club-management'), $predecessor );
	}	
	
	return $html;
}

function scm_comp_format_knockout_display_all_matches( $html, $compid ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return $html;
	
    $size = get_post_meta( $compid, '_size', true); 
	
	$html = "<div id=\"scm_comp_format_knockout\">\n";
	for ($r = 0; $r < 2 * $size; $r++) {
		$html .= "<div id=\"scm_comp_format_knockout_row\" style=\"clear:both;\">\n";
		for ($k = 1; $k <= $size; $k=2*$k) {
			$style = "border-style:solid; border-width:1px; border-color:transparent; width:24%; height:25px; float:left; text-align:center; overflow:hidden; text-overflow:ellipsis;";
			$cell  = "&nbsp";
			// cell containing competitor info
			if ( (($r-$k+1) % (2*$k)) == 0 ) {
				$style .= " border-bottom-color : inherit;";
				if ($k != $size) {
					if (((($r/$k) % (4*$k)) % 4) == 0) {
						$match_number = intval(($r + 2*$size)/(4*$k));
						$matches = get_posts( array( 'post_type' => 'scm_match'
						   , 'numberposts' => '-1'
						   , 'meta_key' => '_competitionid'
						   , 'meta_value' => $compid
						   ) );
						foreach ( $matches as $match ) {
							if ( $match_number == get_post_meta( $match->ID, '_number', true ) ) {
								$competitor_1 = get_post_meta( $match->ID, '_compid_1', true );
								$cell = ( $competitor_1 != "" ? get_the_title( $competitor_1 ) : __('bye', 'sports-club-management') );
							}
						}
					} else if (((($r/$k) % (4*$k)) % 4) == 2) {
						$match_number = intval(($r + 2*$size)/(4*$k));
						$matches = get_posts( array( 'post_type' => 'scm_match'
						   , 'numberposts' => '-1'
						   , 'meta_key' => '_competitionid'
						   , 'meta_value' => $compid
						   ) );
						foreach ( $matches as $match ) {
							if ( $match_number == get_post_meta( $match->ID, '_number', true ) ) {
								$competitor_2 = get_post_meta( $match->ID, '_compid_2', true );
								$cell = ( $competitor_2 != "" ? get_the_title( $competitor_2 ) : __('bye', 'sports-club-management') );
							}
						}
					} 			
				} else {
					$cell = "winner 1";
				}
			}
			// cell containing result or date/time
			if ($k != 1) {
				if ( (($r-$k) % (2*$k)) == 0 ) {
					$match_number = intval(($r + 2*$size)/(2*$k));
					$matches = get_posts( array( 'post_type' => 'scm_match'
					   , 'numberposts' => '-1'
					   , 'meta_key' => '_competitionid'
					   , 'meta_value' => $compid
					   ) );
					foreach ( $matches as $match ) {
						if ( $match_number == get_post_meta( $match->ID, '_number', true ) ) {
							$result = get_post_meta( $match->ID, '_result', true );
							$cell = ( $result == "" ? "date/time" : ( $result != "bye" ? $result : "&nbsp" ) );
						}
					}
				}
			}
			// draw vertical lines
			if ($k != $size) {
				if ( (($r % (4*$k)) >= $k) && (($r % (4*$k)) < (3*$k)) ) {
				$style .= " border-right-color : inherit;";					
				}
			}
			$html .= "<div id=\"scm_comp_format_knockout_cell\" style=\"$style\">\n"; 
			$html .= $cell;
			$html .= "</div>\n";
		}
		$html .= "</div>\n";
	}
	$html .= "</div>\n";

	return $html;
}

function scm_comp_format_knockout_display_competitors_field( $html, $compid ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return $html;

	return "<option value=''> ".__('unknown', 'sports-club-management')." </option>\n" . $html;
}

function scm_comp_format_knockout_display_format_match_fields( $html, $matchid, $compid ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return $html;

	$number      = get_post_meta( $matchid , '_number' , true );
	$nextmatchid = get_post_meta( $matchid , '_nextmatchid' , true );
	
	$html  = "<tr><td align='right'><label>".__('Number #', 'sports-club-management')."</label></td><td><input id='number' type='text' name='number' value='$number' /></td></tr>";
	$html .= "<tr><td align='right'><label>".__('Winner to', 'sports-club-management')."</label></td><td><input id='nextmatchid' type='text' name='nextmatchid' value='$nextmatchid' /></td></tr>";
	
	return $html;
}
		 
function scm_comp_format_knockout_save_format_match_fields( $matchid ) {

    // if competition format does not match, bail
	$compid = get_post_meta( $matchid, '_competitionid', true);
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return;

    if (isset($_POST['number']))
        update_post_meta($matchid, '_number', esc_attr($_POST['number']));
	
    if (isset($_POST['nextmatchid']))
        update_post_meta($matchid, '_nextmatchid', esc_attr($_POST['nextmatchid']));
	
	// if one of the competitors is empty, the result must be 'bye'
	$compid_1 = get_post_meta( $matchid, '_compid_1', true);
	$compid_2 = get_post_meta( $matchid, '_compid_2', true);
	if (( $compid_1 == '' ) || ( $compid_2 == '' )) {
		update_post_meta($matchid, '_result', esc_attr("bye")); 
	}
	
	$result      = get_post_meta( $matchid, '_result', true);
	$nextmatchid = get_post_meta( $matchid, '_nextmatchid', true);
	
	if (($result != '') && ($nextmatchid != '')) { 	
		$current  = get_post_meta( $matchid, '_number', true);
		
		$valid = $home = $away = 0;
		if ( $compid_1 == '' ) {
			$winner = $compid_2;
		} else if ( $compid_2 == '' ) {
			$winner = $compid_1;
		} else {		
			$match_result = scm_comp_get_result_match( $result );
			if ( $match_result['valid'] == 1 ) {
				if ( $match_result['home_sets'] > $match_result['away_sets'] ) {
					$winner = $compid_1;
				} else if ( $match_result['home_sets'] < $match_result['away_sets'] ) {
					$winner = $compid_2;
				} else {
					$winner = '';
				}
			} else {
				$winner = '';
			}
		}
		if (($current % 2) == 0) {
			update_post_meta($nextmatchid, '_compid_1', $winner);
		} else  {
			update_post_meta($nextmatchid, '_compid_2', $winner);
		}
	}
}

function scm_comp_format_knockout_match_list_header( $html, $compid ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return $html;
	
	$names = array( __("Number #", 'sports-club-management'), __("Winner to", 'sports-club-management') );

	return $html . '"' . join( '","', $names ) . '",';	
}

function scm_comp_format_knockout_match_list_entry( $html, $compid, $matchid ) {
	
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_KNOCKOUT ) 
        return $html;
	
    $number = get_post_meta( $matchid, '_number', true);
    $nextid = get_post_meta( $matchid, '_nextmatchid', true);
    $winner = get_post_meta( $nextid, '_number', true);

	$entries = array( $number, $winner );

	return $html . '"' . join( '","', $entries ) . '",';	
}
    