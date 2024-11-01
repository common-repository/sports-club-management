<?php

// include pre-installed competition group formats
include_once 'compgroupformat_list.php';
include_once 'compgroupformat_individual.php';

// custom post type

function scm_comp_group_custom_post_init(){
    
    // taxonomy: Competition Group Category
    $labels = array(
        'name' => __('Competition Group Categories', 'sports-club-management'),
        'singular_name' => __('Competition Group Category', 'sports-club-management'),
        'search_items' => __('Search Competition Group Categories', 'sports-club-management'),
        'all_items' => __('All Competition Group Categories', 'sports-club-management'),
        'parent_item' => __('Parent Competition Group Category', 'sports-club-management'),
        'parent_item_colon' => __('Parent Competition Group Category:', 'sports-club-management'),
        'edit_item' => __('Edit Competition Group Category', 'sports-club-management'), 
        'update_item' => __('Update Competition Group Category', 'sports-club-management'),
        'add_new_item' => __('Add New Competition Group Category', 'sports-club-management'),
        'new_item_name' => __('New Competition Group Category', 'sports-club-management'),
        'menu_name' => __('Competition Group Categories', 'sports-club-management'),
    ); 	
    register_taxonomy('scm_comp_group_category',array('scm_comp_group'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'scm_comp_group_category' ),
        'show_in_rest' => true,
        'rest_base' => 'compgroup_categories',
    ));

    // custom post type: Competition Group
    $labels = array(
        'name' => __('Competition Groups', 'sports-club-management'),
        'singular_name' => __('Competition Group', 'sports-club-management'),
        'add_new' => __('Add New Competition Group', 'sports-club-management'),
        'add_new_item' => __('Add New Competition Group', 'sports-club-management'),
        'edit_item' => __('Edit Competition Group', 'sports-club-management'),
        'new_item' => __('New Competition Group', 'sports-club-management'),
        'view_item' => __('View Competition Group', 'sports-club-management'),
        'search_items' => __('Search Competition Groups', 'sports-club-management'),
        'not_found' => __('No Competition Groups found', 'sports-club-management'),
        'not_found_in_trash' => __('No Competition Groups found in Trash', 'sports-club-management'),
        'parent_item_colon' => __('Parent Competition Group:', 'sports-club-management'),
        'menu_name' => __('Competition Groups', 'sports-club-management'),
    );
    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'supports' => array('title','author','editor','thumbnail'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => 'sportclub',
        'show_in_admin_bar' => true,
        'menu_position' => 110,
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'taxonomies' => array( 'scm_comp_group_category' ),
        'query_var' => true,
        'can_export' => true,
        'rewrite' => false,
        'capability_type' => 'page', 
        'map_meta_cap' => true,
        'show_in_rest' => true,
        'rest_base' => 'compgroups',
    );
    register_post_type('scm_comp_group', $args);
}

// hooks and filters for Competition Group
add_action('add_meta_boxes', 'scm_comp_group_add_fields_box');
add_action('save_post', 'scm_comp_group_save_fields');
add_filter('manage_scm_comp_group_posts_columns', 'scm_comp_group_columns');  
add_filter('manage_edit-scm_comp_group_sortable_columns', 'scm_comp_group_sortable_column');  
add_action('manage_scm_comp_group_posts_custom_column', 'scm_comp_group_column_content', 10, 2);   
add_action('restrict_manage_posts', 'scm_comp_group_filtered_by_category');
add_filter('parse_query', 'scm_comp_group_category_from_id_in_query');

// comp fields operations

function scm_comp_group_add_fields_box() {
    global $pagenow, $typenow;
	
    add_meta_box('scm_comp_group_fields_box_id', __('Competition Group Data', 'sports-club-management'), 'scm_comp_group_display_fields', 'scm_comp_group');
    if ($pagenow == 'post.php') {
		add_meta_box('scm_comp_group_ranking_id', __('Ranking', 'sports-club-management'), 'scm_comp_group_display_ranking', 'scm_comp_group');
		add_meta_box('scm_comp_group_list_id', __('Competitions', 'sports-club-management'), 'scm_comp_group_display_list', 'scm_comp_group');
        add_meta_box('scm_comp_group_shortcodes_box_id', __('Shortcodes', 'sports-club-management'), 'scm_comp_group_list_shortcodes', 'scm_comp_group', 'side', 'high');
	}
}
function scm_comp_group_display_fields() {
    global $post;
 
    $values = get_post_custom($post->ID);
    
    $format = isset($values['_format']) ? esc_attr($values['_format'][0]) : __('none', 'sports-club-management');
    $groupid = isset($values['_groupid']) ? esc_attr($values['_groupid'][0]) : '';
    
    wp_nonce_field('scm_comp_group_frm_nonce', 'scm_comp_group_frm_nonce');
    
    $html = "
        <tr>
        <td align='right'><label>".__('Group Format', 'sports-club-management')."</label></td>\n
        <td><select name='format'>\n". apply_filters( 'scm_compgroup_get_formats', "", $format ) ."</select></td>\n
        </tr>
        ";
    echo "<table>" . $html . "</table>";
}
function scm_comp_group_display_ranking() {
    global $post;
 
    echo "<table>" . apply_filters( 'scm_compgroup_display_ranking', "", $post->ID ) . "</table>";    
}
function scm_comp_group_display_list() {
    global $post;
 
    $competitions = get_posts( array( 'post_type' => 'scm_comp'
                                , 'numberposts' => '-1'
                                , 'meta_key' => '_groupid'
                                , 'meta_value' => $post->ID
                                , 'orderby' => 'meta_value'
                                , 'order' => 'ASC'
                                ) );
    
    foreach ( $competitions as $comp ) {
        echo sprintf("<p><a href=%s target=\"_blank\">%s</a></p>"
                    , site_url( "/wp-admin/post.php?post=".$comp->ID."&action=edit" )
                    , get_the_title( $comp->ID ));
    }    

}
function scm_comp_group_list_shortcodes() {
	global $post;
	
	$html = __('Use the following shortcodes by copy/pasting into a post or page (see documentation).', 'sports-club-management');

	$html .= sprintf("<p>[scm_competition_group_ranking compgroup_id='%s']</p>", $post->ID);
	
	echo $html;
}

function scm_comp_group_save_fields($post_id) {
    // Bail if we're doing an auto save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

        // if our nonce isn't there, or we can't verify it, bail
    if (!isset($_POST['scm_comp_group_frm_nonce']) || !wp_verify_nonce($_POST['scm_comp_group_frm_nonce'], 'scm_comp_group_frm_nonce'))
        return;

    // if our current user can't edit this post, bail
    if (!current_user_can('edit_posts'))
        return;

    if (isset($_POST['format']))
        update_post_meta($post_id, '_format', esc_attr($_POST['format']));
}

// display comp data on admin page

function scm_comp_group_columns($defaults) {  
    // no author
    unset($defaults['author']);  
    
    // new column titles
    $defaults['format'] = __('Group Format', 'sports-club-management');
    $defaults['category'] = __('Category', 'sports-club-management');
    
    return $defaults;  
}  
  
function scm_comp_group_sortable_column( $columns ) {  
    $columns['format'] = 'format';  
    
    //To make a column 'un-sortable' remove it from the array  
    //unset($columns['date']);  
  
    return $columns;  
}   
  
function scm_comp_group_column_content($column_name, $post_ID) {  
    switch($column_name){
        case 'format':
            echo apply_filters( 'scm_compgroup_display_format', "", $post_ID );            
            return;
        case 'category':
            $categories = wp_get_post_terms( $post_ID, 'scm_comp_group_category', array("fields" => "names") );
            echo sprintf("%s",  implode(", ", $categories) );
            return;
        default:
            echo print_r($post_ID,true); //Show the whole array for troubleshooting purposes
            return;
    }
} 

// filter comps by category

function scm_comp_group_filtered_by_category() {
    global $typenow;
    $post_type = 'scm_comp_group'; 
    $taxonomy = 'scm_comp_group_category'; 
    if ($typenow == $post_type) {
        $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
        $info_taxonomy = get_taxonomy($taxonomy);
        wp_dropdown_categories(array(
            'show_option_all' => __("Show All {$info_taxonomy->label}", 'sports-club-management'),
            'taxonomy' => $taxonomy,
            'name' => $taxonomy,
            'orderby' => 'name',
            'selected' => $selected,
            'show_count' => true,
            'hide_empty' => false,
            'hierarchical' => 1, 
            'depth' => 4,
	        ));
    };
}

function scm_comp_group_category_from_id_in_query($query) {
    global $pagenow;
    $post_type = 'scm_comp_group'; 
    $taxonomy = 'scm_comp_group_category'; 
    $q_vars = &$query->query_vars;
    if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type ) {
    
        // if not filtering on member category, exclude (optional) categories from member list
        if ( !isset($q_vars[$taxonomy]) ) {
            $cat_excluded = get_option('scm_cat_excluded_default_comp');
            $q_vars['tax_query'] = array(
                                        array(
                                            'taxonomy' => $taxonomy,
                                            'field' => 'id',
                                            'terms' => array($cat_excluded),      
                                            'operator' => 'NOT IN'
                                        )
                                   );
        }
        else  if ( is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }
}

