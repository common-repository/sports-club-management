<?php

// member shortcodes to be used in posts and pages
add_shortcode( 'scm_competition_group_ranking', 'scm_sh_competition_group_ranking' );

function scm_sh_competition_group_ranking( $atts ) {
    
    if ('use_competitions' != get_option('scm_include_competitions_data')) { 
        return "";
    }
	extract( shortcode_atts( array(
		'compgroup_id' => '',
        'before' => '',
        'after' => '',
	), $atts ) );
    
    $html = "";
    $html .= "<p><table> \n";
    $html .= apply_filters( 'scm_compgroup_display_ranking', "", $compgroup_id );    
    $html .= "</table></p> \n";
    
	return "$before \n" . $html . "$after \n";
} 
