<?php

// member shortcodes to be used in posts and pages
add_shortcode( 'scm_competition_data', 'scm_sh_competition_data' );
add_shortcode( 'scm_competition_ranking', 'scm_sh_competition_ranking' );

function scm_sh_competition_data( $atts ) {
    
    if ('use_competitions' != get_option('scm_include_competitions_data')) { 
        return "";
    }
	extract( shortcode_atts( array(
		'member_id' => '',
        'before' => '',
        'after' => '',
	), $atts ) );
    
    $competitors = get_posts( array( 'post_type' => 'scm_competitor'
                                , 'numberposts' => '-1'
                                , 'meta_key' => '_member'
                                , 'meta_value' => $member_id
                                ) );
                        
    $teamplayers = get_posts( array( 'post_type' => 'scm_teamplayer'
                                , 'numberposts' => '-1'
                                , 'meta_key' => '_member'
                                , 'meta_value' => $member_id
                                ) );
    foreach ($teamplayers as $teamplayer) {
        $id = get_post_meta( $teamplayer->ID , '_competitorid' , true );
        array_push( $competitors, get_post($id) );
    }
                     
    $html = "";
    $html .= "<div id=\"scm_comp\"> \n";
    if ( count( $competitors ) == 0 ) {
        $html .= __('none', 'sports-club-management');
    } else {
        
        foreach ( $competitors as $competitor ) {
            $id = get_post_meta( $competitor->ID , '_competitionid' , true );
            $type = get_post_meta( $competitor->ID , '_competitor_type' , true );
            $name = ($type == 'team') ? get_the_title( $competitor->ID ) : '';
            $groupid = get_post_meta( $id, '_groupid', true);
			$html .= "<div id=\"scm_comp_data\"> \n";
            if ($groupid == '') {
				$html .= " <div id=\"scm_comp_field\">" . sprintf("<a href=%s>%s</a>", get_post_permalink( $id ), get_the_title( $id )) . "</div>";
            } else {
                $html .= " <div id=\"scm_comp_field\">" . sprintf("<a href=%s>%s</a> (<a href=%s>%s</a>)", get_post_permalink( $groupid ), get_the_title( $groupid ), get_post_permalink( $id ), get_the_title( $id )) . "</div>";
            }
			$html .= " <div id=\"scm_comp_competitor\">" . sprintf("<a href=%s>%s</a>", get_post_permalink( $competitor->ID ), $name) . "</div>";		
			$html .= "</div> \n";
        }
    }
	$html .= "</div> \n";
	
	return "$before \n" . $html . "$after \n";
} 
  
function scm_sh_competition_ranking( $atts ) {
    
    if ('use_competitions' != get_option('scm_include_competitions_data')) { 
        return "";
    }
	extract( shortcode_atts( array(
		'competition_id' => '',
        'before' => '',
        'after' => '',
	), $atts ) );
    
	$html = "";
    $html .= "<p><table> \n";
    $html .= apply_filters( 'scm_competition_display_ranking', "", $competition_id, false, true );
    $html .= "</table></p> \n";

	return "$before \n" . $html . "$after \n";	
} 
