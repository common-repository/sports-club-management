<?php 

define('SCM_CGFMT_LIST', 1000);             

add_filter('scm_compgroup_get_formats', 'scm_compgroup_format_list_get_format', 10, 2 );
add_filter('scm_compgroup_display_format', 'scm_compgroup_format_list_display_format', 10, 2 );
// add_filter('scm_compgroup_display_format_fields', 'scm_compgroup_format_list_display_format_fields', 10, 2 );
// add_action('scm_compgroup_save_format_fields', 'scm_compgroup_format_list_save_format_fields');
// add_action('scm_compgroup_create_matches','scm_compgroup_format_list_create_matches');
add_filter('scm_compgroup_display_ranking', 'scm_compgroup_format_list_display_ranking', 10, 2 );

                   
function scm_compgroup_format_list_get_format( $html, $id ) {
    $html .= "<option ";      
    $html .= ($id == SCM_CGFMT_LIST) ? "selected='selected' " : "";   
    $html .= "value=" . SCM_CGFMT_LIST. ">" . __('List', 'sports-club-management') . "</option>\n";

    return $html; 
}

function scm_compgroup_format_list_display_format( $html, $compid ) {
     
    if ( get_post_meta( $compid, '_format', true) == SCM_CGFMT_LIST ) {
        $html .= __('List', 'sports-club-management'); 
    }
        
    return $html; 
}

function scm_compgroup_format_list_display_ranking( $html, $compgroupid ) {

    // if scoring format does not match, bail
    if ( get_post_meta( $compgroupid, '_format', true) == SCM_CGFMT_LIST ) {

        $competitions = get_posts( array( 'post_type' => 'scm_comp'
                                    , 'numberposts' => '-1'
                                    , 'meta_key' => '_groupid'
                                    , 'meta_value' => $compgroupid
                                    , 'orderby' => 'date'
                                    , 'order' => 'ASC'
                                    ) );
        
        $header = true;
        foreach ( $competitions as $comp ) {
            $html = apply_filters( 'scm_competition_display_ranking', $html, $comp->ID, false, $header );
            $header = false;
        }    
    }
    
    return $html;

}
