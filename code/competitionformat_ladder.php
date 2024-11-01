<?php 

define('SCM_CFMT_LADDER', 1004); 

include_once 'scores.php';

add_filter('scm_competition_get_format_name', 'scm_comp_format_ladder_get_format_name', 10, 2 );
add_filter('scm_competition_get_formats', 'scm_comp_format_ladder_get_format', 10, 2 );
add_filter('scm_competition_display_format', 'scm_comp_format_ladder_display_format', 10, 2 );
add_filter('scm_competition_display_ranking', 'scm_comp_format_ladder_display_ranking', 10, 4 );

function scm_comp_format_ladder_get_format_name( $html, $compid ) {

    if ( get_post_meta( $compid, '_formatid', true) == SCM_CFMT_LADDER ) {
        $html = "ladder"; 
    }
        
    return $html; 
}

function scm_comp_format_ladder_get_format( $html, $id ) {
    $html .= "<option ";      
    $html .= ($id == SCM_CFMT_LADDER) ? "selected='selected' " : "";   
    $html .= "value=" . SCM_CFMT_LADDER . ">" . __('Ladder', 'sports-club-management') . "</option>\n";

    return $html; 
}

function scm_comp_format_ladder_display_format( $html, $compid ) {

    if ( get_post_meta( $compid, '_formatid', true) == SCM_CFMT_LADDER ) {
        $html .= __('Ladder', 'sports-club-management'); 
    }
        
    return $html; 
}

function scm_comp_format_ladder_display_ranking( $html, $compid, $display_all, $display_header ) {
    
    // if competition format does not match, bail
    if ( get_post_meta( $compid, '_formatid', true) != SCM_CFMT_LADDER ) 
        return $html;

    if ( $display_header == true ) {
		if ( is_admin() ) {
			$html .= "<tr><th>"
					.join( "</th><th>", array( "", __('Played', 'sports-club-management'), __('W - D - L', 'sports-club-management')) )
					."</th></tr>\n";
		} else {
			$html .= "<div id=\"scm_ranking\" class=\"header ladder\">\n"; 
			$html .= "<div id=\"scm_ranking_competitor\" class=\"header ladder\">&nbsp;</div>\n"; 
			$html .= "<div id=\"scm_ranking_fields\" class=\"header ladder\">\n";
			$html .= "<div id=\"scm_ranking_played\" class=\"header ladder\">" . __('Played', 'sports-club-management') . "</div>\n";
			$html .= "<div id=\"scm_ranking_wondrawlost\" class=\"header ladder\">" . __('W - D - L', 'sports-club-management') . "</div>\n";
			$html .= "</div>\n";	 
			$html .= "</div>\n"; 
		}
    }
            
    $rankings = scm_comp_format_ladder_get_ranking($compid);
	
	// move competitors that did not play any match to the end of the list
	$i = 0;
	$j = count($rankings);
	while ($i < $j) {
		if ( $rankings[$i]['played'] != 0 ) {
			$i++;
		} else if ( $rankings[$j - 1]['played'] == 0 ) {
			$j--;
		} else {
			/* move position i to j-1 and shift rest */
			$tmp = $rankings[$i];
			for ($h = $i; $h < $j-1; $h++) {
				$rankings[$h] = $rankings[$h+1];
			}
			$rankings[$j-1] = $tmp;
			$j--;
		}
	}
	
    
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
				$html .= sprintf("<tr><td>%s</td>", $link);
				if ( $display_all == true ) {
					$html .= sprintf("%s</tr>\n", $rankings[$h]['results']);
				}
			} else {
				$html .= "<div id=\"scm_ranking\" class=\"ladder\">\n"; 
				$html .= "<div id=\"scm_ranking_competitor\" class=\"ladder\">" . $link . "</div>\n"; 
				$html .= $rankings[$h]['results'];
				$html .= "</div>\n"; 
			}
        }
    }
             
    return $html;   
}

function scm_comp_format_ladder_get_ranking($compid) {
	
	// initial ranking, all competitors sorted by entry date
    $competitors = get_posts( array( 'post_type' => 'scm_competitor'
                                   , 'numberposts' => '-1'
                                   , 'meta_key' => '_competitionid'
                                   , 'meta_value' => $compid
								   , 'order' => 'ASC'
								   ) );
    $rankings = array();   
	foreach ($competitors as $competitor) {
	    $ranking = array( "competitorid" => $competitor->ID
						, "results" => 'no data'
						, "played" => 0
						, "won" => 0
						, "lost" => 0
						, "draw" => 0
                        );
        array_push( $rankings, $ranking );
    }
	
	// process all matches in order of play
    $matches = get_posts( array( 'post_type' => 'scm_match'
                               , 'numberposts' => '-1'
                               , 'meta_key' => '_competitionid'
                               , 'meta_value' => $compid
							   , 'order' => 'ASC'
                               ) );
	foreach ($matches as $match) {
		// collect WDL, unless one of the competitors has been disqualified; match must still be processed for ranking!
		$compid_h = get_post_meta( $match->ID , '_compid_1' , true );
		$compid_a = get_post_meta( $match->ID , '_compid_2' , true );
        if (( get_post_meta( $compid_h , '_disqualified' , true ) != 'yes' ) && ( get_post_meta( $compid_a , '_disqualified' , true ) != 'yes' )) {
            $match_result = scm_comp_get_result_match( get_post_meta( $match->ID , '_result' , true ) ); 
			if ( $match_result['valid'] == 1 ) {
				$idx_h = 0; 
				while ( $rankings[$idx_h]['competitorid'] != $compid_h ) {
					$idx_h++;
				}
				$idx_a = 0; 
				while ( $rankings[$idx_a]['competitorid'] != $compid_a ) {
					$idx_a++;
				}
				$update_ranking = false;
                $rankings[$idx_h]['played'] += 1;				
				$rankings[$idx_a]['played'] += 1;				
				if ( ($match_result['home_sets'] + $match_result['away_sets']) <= 1 ) {
					if ( $match_result['home_pts'] > $match_result['away_pts'] ) {
						$rankings[$idx_h]['won'] += 1;
						$rankings[$idx_a]['lost'] += 1;
						$update_ranking = ($idx_h > $idx_a);
					} else if ( $match_result['home_pts'] < $match_result['away_pts'] ) {
						$rankings[$idx_h]['lost'] += 1;
						$rankings[$idx_a]['won'] += 1;
						$update_ranking = ($idx_a > $idx_h);
					} else {
						$rankings[$idx_h]['draw'] += 1;
						$rankings[$idx_a]['draw'] += 1;
					}
				} else {
					// multiple sets
					if ( $match_result['home_sets'] > $match_result['away_sets'] ) {
						$rankings[$idx_h]['won'] += 1;
						$rankings[$idx_a]['lost'] += 1;
						$update_ranking = ($idx_h > $idx_a);
					} else if ( $match_result['home_sets'] < $match_result['away_sets'] ) {
						$rankings[$idx_h]['lost'] += 1;
						$rankings[$idx_a]['won'] += 1;
						$update_ranking = ($idx_a > $idx_h);
					} else {
						$rankings[$idx_h]['draw'] += 1;
						$rankings[$idx_a]['draw'] += 1;
					}					
				}
				if ($update_ranking == true) {
					if ($idx_h < $idx_a) {
						$idx_winner = $idx_a; $idx_loser = $idx_h;
					} else {
						$idx_winner = $idx_h; $idx_loser = $idx_a;
					}	
                    $winner = $rankings[$idx_winner];
					for ($h = 0; $h != ($idx_winner - $idx_loser); $h++) {
						$rankings[$idx_winner-$h] = $rankings[$idx_winner-$h-1];
						
					}
					$rankings[$idx_loser] = $winner;
				}
            }
        }
	}
	
	// post-process ranking results
    for ($h = 0; $h != count($rankings); $h++) {
		if ( is_admin() ) {
			$string  = "<td>".$rankings[$h]['played']."</td>";
			$string .= "<td>".$rankings[$h]['won']." - ".$rankings[$h]['draw']." - ".$rankings[$h]['lost']."</td>";
		} else {
			$string  = "<div id=\"scm_ranking_fields\" class=\"ladder\">\n";
			$string .= "<div id=\"scm_ranking_played\" class=\"ladder\">" . $rankings[$h]['played'] . "</div>\n";
			$string .= "<div id=\"scm_ranking_wondrawlost\" class=\"ladder\">" . $rankings[$h]['won']." - ".$rankings[$h]['draw']." - ".$rankings[$h]['lost'] . "</div>\n";
			$string .= "</div>\n";	 
		}
		$rankings[$h]['results'] = $string;
	}
	    
    return $rankings;
}
