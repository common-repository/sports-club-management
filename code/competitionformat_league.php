<?php 

define('SCM_CFMT_LEAGUE', 1001);             

include_once 'scores.php';

add_filter('scm_competition_get_format_name', 'scm_comp_format_league_get_format_name', 10, 2 );
add_filter('scm_competition_get_formats', 'scm_comp_format_league_get_format', 10, 2 );
add_filter('scm_competition_display_format', 'scm_comp_format_league_display_format', 10, 2 );
add_filter('scm_competition_display_format_fields', 'scm_comp_format_league_display_format_fields', 10, 2 );
add_action('scm_competition_save_format_fields', 'scm_comp_format_league_save_format_fields');
//add_action('scm_competition_create_matches','scm_comp_format_league_create_matches');
add_filter('scm_competition_display_ranking', 'scm_comp_format_league_display_ranking', 10, 4 );
add_filter('scm_competitor_display_format_fields', 'scm_comp_format_league_display_competitor_format_fields', 10, 3 );
add_action('scm_competitor_save_format_fields', 'scm_comp_format_league_save_competitor_format_fields', 10, 2);


function scm_comp_format_league_get_format_name( $html, $compid ) {

    if ( get_post_meta( $compid, '_formatid', true) == SCM_CFMT_LEAGUE ) {
        $html = "league"; 
    }
        
    return $html; 
}

function scm_comp_format_league_get_format( $html, $id ) {
    $html .= "<option ";      
    $html .= ($id == SCM_CFMT_LEAGUE) ? "selected='selected' " : "";   
    $html .= "value=" . SCM_CFMT_LEAGUE . ">" . __('League', 'sports-club-management') . "</option>\n";

    return $html; 
}

function scm_comp_format_league_display_format( $html, $compid ) {

    if ( get_post_meta( $compid, '_formatid', true) == SCM_CFMT_LEAGUE ) {
        $html .= __('League', 'sports-club-management'); 
    }
        
    return $html; 
}

function scm_comp_format_league_display_format_fields( $html, $compid ) {

    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_LEAGUE ) 
        return $html;

    $values = get_post_custom($compid);
    
    $scoringsystem = isset($values['_scoringsystem']) ? esc_attr($values['_scoringsystem'][0]) : '';
    
    $score_systems = array( __('Points Won', 'sports-club-management'),
                            __('Win-Draw-Loss = 3-1-0', 'sports-club-management'),
                            __('Win-Draw-Loss = 2-1-0', 'sports-club-management')
                          );
    
    $html .= "<tr>\n";
    $html .= "<td align='right'><label>" . __('Scoring System', 'sports-club-management') . "</label></td>\n";
    $html .= "<td><select name='scoringsystem'>\n";        
    for ($h = 0; $h != 3; $h++) {
        $html .= "<option ";       
        $html .= ($scoringsystem == $h) ? "selected='selected' " : "";   
        $html .= "value='$h'>" . $score_systems[$h] . "</option>\n";
    }
    $html .= "</select></td>";        
    $html .= "</tr>\n";

    return $html; 
}

function scm_comp_format_league_save_format_fields( $compid ) {

    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_LEAGUE ) 
        return;

    if (isset($_POST['scoringsystem']))
        update_post_meta($compid, '_scoringsystem', esc_attr($_POST['scoringsystem']));
}

function scm_comp_format_league_display_ranking( $html, $compid, $display_all, $display_header ) {
    
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_LEAGUE ) 
        return $html;

    if ( $display_header == true ) {
		if ( is_admin() ) {
			$html .= "<tr><th>"
					.join( "</th><th>", array( "", __('Played', 'sports-club-management'), __('Points', 'sports-club-management'), __('W - D - L', 'sports-club-management'), __('Diff', 'sports-club-management')) )
					."</th></tr>\n";
		} else {
			$html .= "<div id=\"scm_ranking\" class=\"header league\">\n"; 
			$html .= "<div id=\"scm_ranking_competitor\" class=\"header league\">&nbsp;</div>\n"; 
			$html .= "<div id=\"scm_ranking_fields\" class=\"header league\">\n";
			$html .= "<div id=\"scm_ranking_played\" class=\"header league\">" . __('Played', 'sports-club-management') . "</div>\n";
			$html .= "<div id=\"scm_ranking_points\" class=\"header league\">" . __('Points', 'sports-club-management') . "</div>\n";
			$html .= "<div id=\"scm_ranking_wondrawlost\" class=\"header league\">" . __('W - D - L', 'sports-club-management') . "</div>\n";
			$html .= "<div id=\"scm_ranking_diff\" class=\"header league\">" . __('Diff', 'sports-club-management') . "</div>\n";
			$html .= "</div>\n";	 
			$html .= "</div>\n"; 
		}
    }
            
    $rankings = scm_comp_format_league_get_ranking($compid);
    
    for ($h = 0; $h != count($rankings); $h++) {
        $competitorid = $rankings[$h]['competitorid'];
        $disqualified = get_post_meta( $competitorid , '_disqualified' , true );
        
        if ($disqualified != 'yes') {
        
            $disable = get_post_meta( $competitorid , '_disableranking' , true );
            
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
				if (( $disable == 'yes' ) && ( $display_all == true )) {
					$html .= sprintf("<tr><td><strike>%s</strike></td>", $link );
				} else if ( $disable != 'yes' ) {
					$html .= sprintf("<tr><td>%s</td>", $link);
				}
				if (( $disable != 'yes' ) || ( $display_all == true )) {
					$html .= sprintf("%s</tr>\n", $rankings[$h]['results']);
				}
			} else {
				if ( $disable != 'yes' ) {
					$html .= "<div id=\"scm_ranking\" class=\"league\">\n"; 
					$html .= "<div id=\"scm_ranking_competitor\" class=\"league\">" . $link . "</div>\n"; 
					$html .= $rankings[$h]['results'];
					$html .= "</div>\n"; 
				}
			}
        }
    }
             
    return $html;   
}

function scm_comp_format_league_get_ranking($compid) {
    $competitors = get_posts( array( 'post_type' => 'scm_competitor'
                                   , 'numberposts' => '-1'
                                   , 'meta_key' => '_competitionid'
                                   , 'meta_value' => $compid
                                   ) );
    $rankings = array();                                
    foreach ($competitors as $competitor) {
        $results = scm_comp_format_league_get_results_competitor( $competitor->ID );
		$points = 0;
        switch ( get_post_meta( $compid , '_scoringsystem' , true ) ) {
            case 0: $points = $results['points']; break;
            case 1: $points = (3*$results['won'] + $results['draw']); break;
            case 2: $points = (2*$results['won'] + $results['draw']); break;
        }
		if ( is_admin() ) {
			$string = "<td>".$results['played']."</td>"
					 ."<td>".$points."</td>"
					 ."<td>".$results['won']." - ".$results['draw']." - ".$results['lost']."</td>"
					 ."<td>".$results['diff1']."</td>";
		} else {
			$string  = "<div id=\"scm_ranking_fields\" class=\"league\">\n";
			$string .= "<div id=\"scm_ranking_played\" class=\"league\">" . $results['played'] . "</div>\n";
			$string .= "<div id=\"scm_ranking_points\" class=\"league\">" . $points . "</div>\n";
			$string .= "<div id=\"scm_ranking_wondrawlost\" class=\"league\">" . $results['won']." - ".$results['draw']." - ".$results['lost'] . "</div>\n";
			$string .= "<div id=\"scm_ranking_diff\" class=\"league\">" . $results['diff1'] . "</div>\n";
			$string .= "</div>\n";	 
		}
	    $ranking = array( "competitorid" => $competitor->ID
                        , "points" => $points
                        , "diff1" => $results['diff1']
                        , "diff2" => $results['diff2']
                        , "results" => $string
                        );
        array_push( $rankings, $ranking );
		usort($rankings, "scm_comp_format_league_rank_sort" );
    }
    
    return $rankings;
}
function scm_comp_format_league_rank_sort($a, $b) {
	$order = 0;
    if ( $a['points'] > $b['points'] ) {
		$order = -1;
	} else if ( $a['points'] < $b['points'] ) {
		$order = 1;
	} else if ( $a['diff1'] > $b['diff1'] ) {
		$order = -1;
	} else if ( $a['diff1'] < $b['diff1'] ) {
		$order = 1;
	} else if ( $a['diff2'] > $b['diff2'] ) {
		$order = -1;
	} else if ( $a['diff2'] < $b['diff2'] ) {
		$order = 1;
	}
		
	return $order;
}

    
function scm_comp_format_league_get_results_competitor( $competitorID ) {
    $matches_h = get_posts( array( 'post_type' => 'scm_match'
                                 , 'numberposts' => '-1'
                                 , 'meta_key' => '_compid_1'
                                 , 'meta_value' => $competitorID
                                 ) );
    $matches_a = get_posts( array( 'post_type' => 'scm_match'
                                 , 'numberposts' => '-1'
                                 , 'meta_key' => '_compid_2'
                                 , 'meta_value' => $competitorID
                                 ) );
    $played = $won = $lost = $draw = $points = $diff1 = $diff2 = $disq = 0;
    foreach ($matches_h as $match) {  
        $opponentdisq = get_post_meta( get_post_meta( $match->ID , '_compid_2' , true ) , '_disqualified' , true );
        if ( $opponentdisq == 'yes' ) {
            $disq += 1;
        } else {
            $match_result = scm_comp_get_result_match( get_post_meta( $match->ID , '_result' , true ) ); 
            if ( $match_result['valid'] == 1 ) {
                $played += 1;				
				if ( ($match_result['home_sets'] + $match_result['away_sets']) <= 1 ) {
					$points += $match_result['home_pts'];
					$diff1 += ($match_result['home_pts'] - $match_result['away_pts']);
					$diff2 = 0;
					if ( $match_result['home_pts'] > $match_result['away_pts'] ) {
						$won += 1;
					} else if ( $match_result['home_pts'] < $match_result['away_pts'] ) {
						$lost += 1;
					} else {
						$draw += 1;
					}
				} else {
					// multiple sets
					$points += $match_result['home_sets'];
					$diff1 += ($match_result['home_sets'] - $match_result['away_sets']);
					$diff2 += ($match_result['home_pts'] - $match_result['away_pts']);
					if ( $match_result['home_sets'] > $match_result['away_sets'] ) {
						$won += 1;
					} else if ( $match_result['home_sets'] < $match_result['away_sets'] ) {
						$lost += 1;
					} else {
						$draw += 1;
					}					
				}
            }
        }
    }
    foreach ($matches_a as $match) {  
        $opponentdisq = get_post_meta( get_post_meta( $match->ID , '_compid_1' , true ) , '_disqualified' , true );
        if ( $opponentdisq == 'yes' ) {
            $disq += 1;
        } else {
            $match_result = scm_comp_get_result_match( get_post_meta( $match->ID , '_result' , true ) );
            if ( $match_result['valid'] == 1 ) {
                $played += 1;
				if ( ($match_result['home_sets'] + $match_result['away_sets']) <= 1 ) {
					$points += $match_result['away_pts'];
					$diff1 += ($match_result['away_pts'] - $match_result['home_pts']);
					$diff2 = 0;
					if ( $match_result['away_pts'] > $match_result['home_pts'] ) {
						$won += 1;
					} else if ( $match_result['away_pts'] < $match_result['home_pts'] ) {
						$lost += 1;
					} else {
						$draw += 1;
					}
				} else {
					// multiple sets
					$points += $match_result['away_sets'];
					$diff1 += ($match_result['away_sets'] - $match_result['home_sets']);
					$diff2 += ($match_result['away_pts'] - $match_result['home_pts']);
					if ( $match_result['away_sets'] > $match_result['home_sets'] ) {
						$won += 1;
					} else if ( $match_result['away_sets'] < $match_result['home_sets'] ) {
						$lost += 1;
					} else {
						$draw += 1;
					}
				}
            }
        }
    }
    return array( "played" => $played."/".( count($matches_h) + count($matches_a) - $disq )
                , "won" => $won
                , "lost" => $lost
                , "draw" => $draw
                , "points" => $points
                , "diff1" => $diff1
                , "diff2" => $diff2
                );
}
    
function scm_comp_format_league_display_competitor_format_fields( $html, $competitor_id, $competition_id ) {

    // if competition format does not match, bail
    if ( get_post_meta( $competition_id, '_formatid', true) != SCM_CFMT_LEAGUE ) 
        return $html;

    $values = get_post_custom($competitor_id);
    
    $disableranking = isset($values['_disableranking']) ? esc_attr($values['_disableranking'][0]) : '';
    if ($disableranking == 'yes') {
        $disableranking_checked = "checked";
    } else {
        $disableranking_checked = "";
    }

    $html .= "<tr>\n";
    $html .= "<td align='right'><label>".__('Disable Ranking', 'sports-club-management')."</label></td>";
    $html .= "<td><input type='checkbox' name='disableranking' value='yes' ". $disableranking_checked ." /></td>";
    $html .= "</tr>\n";
    return $html; 
}

function scm_comp_format_league_save_competitor_format_fields( $competitor_id, $competition_id ) {

    // if competition format does not match, bail
    if ( get_post_meta( $competition_id, '_formatid', true) != SCM_CFMT_LEAGUE ) 
        return;

    if (isset($_POST['disableranking']))
        update_post_meta($competitor_id, '_disableranking', esc_attr($_POST['disableranking']));	
}

