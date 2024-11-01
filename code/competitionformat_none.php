<?php 

define('SCM_CFMT_NONE', 1000);             

add_filter('scm_competition_get_formats', 'scm_comp_format_none_get_format', 10, 2 );
add_filter('scm_competition_display_format', 'scm_comp_format_none_display_format', 10, 2 );
add_filter('scm_competition_display_ranking', 'scm_comp_format_none_display_ranking', 10, 4 );
                   
function scm_comp_format_none_get_format( $html, $id ) {
    $html .= "<option ";      
    $html .= ($id == SCM_CFMT_NONE) ? "selected='selected' " : "";   
    $html .= "value=" . SCM_CFMT_NONE. ">" . __('none', 'sports-club-management') . "</option>\n";

    return $html; 
}

function scm_comp_format_none_display_format( $html, $compid ) {
     
    if ( get_post_meta( $compid, '_formatid', true) == SCM_CFMT_NONE ) {
        $html .= __('none', 'sports-club-management'); 
    }
        
    return $html; 
}

function scm_comp_format_none_display_ranking( $html, $compid, $display_all, $display_header ) {
   
    if ( get_post_meta( $compid, '_formatid', true) == SCM_CFMT_NONE ) {
        $html .= __('no ranking', 'sports-club-management'); 
    }
        
    return $html; 
}
