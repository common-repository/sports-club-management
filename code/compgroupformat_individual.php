<?php 

define('SCM_CGFMT_INDIVIDUAL', 1001);             

add_filter('scm_compgroup_get_formats', 'scm_compgroup_format_individual_get_format', 10, 2 );
add_filter('scm_compgroup_display_format', 'scm_compgroup_format_individual_display_format', 10, 2 );
// add_filter('scm_compgroup_display_format_fields', 'scm_compgroup_format_individual_display_format_fields', 10, 2 );
// add_action('scm_compgroup_save_format_fields', 'scm_compgroup_format_individual_save_format_fields');
// add_action('scm_compgroup_create_matches','scm_compgroup_format_individual_create_matches');
add_filter('scm_compgroup_display_ranking', 'scm_compgroup_format_individual_display_ranking', 10, 2 );

                   
function scm_compgroup_format_individual_get_format( $html, $id ) {
    $html .= "<option ";      
    $html .= ($id == SCM_CGFMT_INDIVIDUAL) ? "selected='selected' " : "";   
    $html .= "value=" . SCM_CGFMT_INDIVIDUAL. ">" . __('Individual', 'sports-club-management') . "</option>\n";

    return $html; 
}

function scm_compgroup_format_individual_display_format( $html, $compid ) {
     
    if ( get_post_meta( $compid, '_format', true) == SCM_CGFMT_INDIVIDUAL ) {
        $html .= __('Individual', 'sports-club-management'); 
    }
        
    return $html; 
}

function scm_compgroup_format_individual_display_ranking( $html, $compgroupid ) {

    // if scoring format does not match, bail
    if ( get_post_meta( $compgroupid, '_format', true) == SCM_CGFMT_INDIVIDUAL ) {

        $competitions = get_posts( array( 'post_type' => 'scm_comp'
                                    , 'numberposts' => '-1'
                                    , 'meta_key' => '_groupid'
                                    , 'meta_value' => $compgroupid
                                    , 'orderby' => 'date'
                                    , 'order' => 'ASC'
                                    ) );
        
        foreach ( $competitions as $comp ) {
            $html = apply_filters( 'scm_competition_display_ranking', $html, $comp->ID, false, true );
        }    
    }
    
    return $html;

}
