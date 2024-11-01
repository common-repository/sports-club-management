<?php

// custom post type

function scm_match_custom_post_init(){
    
    // custom post type: Match
    $labels = array(
        'name' => __('Matches', 'sports-club-management'),
        'singular_name' => __('Match', 'sports-club-management'),
        'add_new' => __('Add New Match', 'sports-club-management'),
        'add_new_item' => __('Add New Match', 'sports-club-management'),
        'edit_item' => __('Edit Match', 'sports-club-management'),
        'new_item' => __('New Match', 'sports-club-management'),
        'view_item' => __('View Match', 'sports-club-management'),
        'search_items' => __('Search Matches', 'sports-club-management'),
        'not_found' => __('No Matches found', 'sports-club-management'),
        'not_found_in_trash' => __('No Matches found in Trash', 'sports-club-management'),
        'parent_item_colon' => __('Parent Match:', 'sports-club-management'),
        'menu_name' => __('Matches', 'sports-club-management'),
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
        'query_var' => true,
        'can_export' => true,
        'rewrite' => false,
        'capability_type' => 'page', 
        'map_meta_cap' => true,
        'show_in_rest' => true,
        'rest_base' => 'matches',
    );
    register_post_type('scm_match', $args);
}

// hooks and filters for Match
add_action('add_meta_boxes', 'scm_match_add_fields_box');
add_action('save_post', 'scm_match_save_fields');
add_filter('manage_scm_match_posts_columns', 'scm_match_columns');  
add_filter('manage_edit-scm_match_sortable_columns', 'scm_match_sortable_column');  
add_action('manage_scm_match_posts_custom_column', 'scm_match_column_content', 10, 2);   
add_action('rest_api_init', 'scm_match_register_rest_fields');

// comp fields operations

function scm_match_add_fields_box() {
    global $pagenow, $typenow;

    add_meta_box('scm_match_fields_box_id', __('Match Data', 'sports-club-management'), 'scm_match_display_fields', 'scm_match');
	if ($pagenow == 'post-new.php') {
		add_meta_box('scm_match_bulk_box_id', __('Add matches for selected competition', 'sports-club-management'), 'scm_match_bulk', 'scm_match');
	}
    if ($pagenow == 'post.php') {
        add_meta_box('scm_match_shortcodes_box_id', __('Shortcodes', 'sports-club-management'), 'scm_match_list_shortcodes', 'scm_match', 'side', 'high');
    }
}
function scm_match_display_fields() {
    global $post;
 
    $values = get_post_custom($post->ID);

    $compid_1 = isset($values['_compid_1']) ? esc_attr($values['_compid_1'][0]) : '';
    $compid_2 = isset($values['_compid_2']) ? esc_attr($values['_compid_2'][0]) : '';
    $result = isset($values['_result']) ? esc_attr($values['_result'][0]) : '';
    $competitionid = isset($values['_competitionid']) ? esc_attr($values['_competitionid'][0]) : '';
    $date = isset($values['_date']) ? esc_attr($values['_date'][0]) : '';
    $time = isset($values['_time']) ? esc_attr($values['_time'][0]) : '';

    wp_nonce_field('scm_match_frm_nonce', 'scm_match_frm_nonce');
	
	$results = isset($values['_result']) ? explode(":", esc_attr($values['_result'][0])) : Array(''); 
    
    $competitors = array();
	if ($competitionid != '') {
		$competitors = get_posts( array( 'post_type' => 'scm_competitor'
								, 'numberposts' => '-1'
								, 'meta_key' => '_competitionid'
								, 'meta_value' => $competitionid
								, 'order'=> 'ASC'
								, 'orderby' => 'title'
								) );
    }
	
    $competitor_1_options = $competitor_2_options = "";
    foreach ($competitors as $competitor) {
        $competitor_1_options .= "<option " . (($compid_1 == $competitor->ID) ? "selected='selected' " : "") . "value='$competitor->ID'>";
        $competitor_1_options .= ('member' != get_post_meta( $competitor->ID , '_competitor_type' , true )) 
								 ? get_post_meta( $competitor->ID , '_name' , true ) 
								 : get_the_title( get_post_meta( $competitor->ID , '_member' , true ) );
        $competitor_1_options .= "</option>\n";
        $competitor_2_options .= "<option " . (($compid_2 == $competitor->ID) ? "selected='selected' " : "") . "value='$competitor->ID'>";
        $competitor_2_options .= ('member' != get_post_meta( $competitor->ID , '_competitor_type' , true )) 
								 ? get_post_meta( $competitor->ID , '_name' , true ) 
								 : get_the_title( get_post_meta( $competitor->ID , '_member' , true ) );
        $competitor_2_options .= "</option>\n";
    }
    
    $competitions = get_posts( array( 'post_type' => 'scm_comp'
                            , 'numberposts' => '-1'
                            , 'order'=> 'ASC'
                            , 'orderby' => 'title'
                            ) );

    $comp_options = "<option value=''> ".__('none', 'sports-club-management')." </option>\n";
    foreach ($competitions as $comp) {
		$comp_values = get_post_custom($comp->ID);
		$groupid = (($comp->ID != "") ? $comp_values['_groupid'][0] : "");
        $comp_options .= "<option " . (($competitionid == $comp->ID) ? "selected='selected' " : "") . "value='$comp->ID'>" 
                        . get_the_title($comp->ID) . (($groupid != "") ? (" - [" . get_the_title( $groupid ) . "]") : "")
						. "</option>\n";
    }

    $html = "  
        <tr><td align='right'><label>".__('Competitors', 'sports-club-management')."</label></td>
        <td><select name='compid_1'>\n". apply_filters( 'scm_match_display_competitors_field', $competitor_1_options, $competitionid) . "</select> "
		. apply_filters( 'scm_match_display_single_competitor_field'
		               , __('vs.', 'sports-club-management') ." <select name='compid_2'>\n". apply_filters( 'scm_match_display_competitors_field', $competitor_2_options, $competitionid) ."</select>"
					   , $competitionid
					   ) ."
		</td>
        </tr>
        <tr><td align='right'><label>".__('Result', 'sports-club-management')."</label></td>
		    <td><input id='result' type='text' name='result[]' value='$results[0]' />" . apply_filters( 'scm_match_display_format_result_field', "", $post->ID, $competitionid, $results) . "</td>
		</tr>
        <tr><td align='right'><label>".__('Date')."</label></td><td><input id='date' class='scm_datepicker' type='text' name='date' value='$date' /></td></tr>
        <tr><td align='right'><label>".__('Time', 'sports-club-management')."</label></td><td><input id='time' type='text' name='time' value='$time' /></td></tr>
        <tr><td align='right'><label>".__('Competition', 'sports-club-management')."</label></td><td><select id='competitionid'>\n". $comp_options ."</select></td></tr>"
		. apply_filters( 'scm_match_display_format_fields', "", $post->ID , $competitionid);
    echo "<table>" . $html . "</table>";
}
function scm_match_bulk() {
	
	$description = array( __('Define entries', 'sports-club-management')
						, __('View and select entries for creation', 'sports-club-management')
						, __('Confirm', 'sports-club-management')
						, __('Creating entries', 'sports-club-management')
						);
						
	$html_step1 = "
		<p>
		<table>
		<tr>
		<th><label>".__('Competition format', 'sports-club-management')."</label></th>
		</tr>
		<tr>
		<td><input type='radio' name='competition_format' id='league_format' value='league' selected='selected'/>" . __('League', 'sports-club-management') . "</td>
		<td><label>".__('Number of matches vs. each competitor', 'sports-club-management')."</label></td>
		<td><input id='bulk_nrvsteam' type='text' /></td>
		</tr>
		<tr>
		<td><input type='radio' name='competition_format' id='knockout_format' value='knockout' />" . __('Knockout', 'sports-club-management') . "</td>
		</tr>
		<tr>
		<td><input type='radio' name='competition_format' id='individual_format' value='individual' />" . __('Individual', 'sports-club-management') . "</td>
		</tr>
		<tr>
		<td><input type='radio' name='competition_format' id='ladder_format' value='ladder' />" . __('Ladder', 'sports-club-management') . "</td>
		</tr>
		</table>
		</p>";
			
	echo scm_bulk_import_wizard($description, $html_step1);
}
function scm_match_list_shortcodes() {
	global $post;
	
	$html = __('Use the following shortcodes by copy/pasting into a post or page (see documentation).', 'sports-club-management');

	$html .= sprintf("<p>[scm_match_data match_id='%s']</p>", $post->ID);
	
	echo $html;
}

function scm_match_save_fields($post_id) {
    // Bail if we're doing an auto save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

        // if our nonce isn't there, or we can't verify it, bail
    if (!isset($_POST['scm_match_frm_nonce']) || !wp_verify_nonce($_POST['scm_match_frm_nonce'], 'scm_match_frm_nonce'))
        return;

    // if our current user can't edit this post, bail
    if (!current_user_can('edit_posts'))
        return;

    if (isset($_POST['compid_1']))
        update_post_meta($post_id, '_compid_1', esc_attr($_POST['compid_1']));
    if (isset($_POST['compid_2']))
        update_post_meta($post_id, '_compid_2', esc_attr($_POST['compid_2']));
    if (isset($_POST['competitionid']))
        update_post_meta($post_id, '_competitionid', esc_attr($_POST['competitionid']));
    if (isset($_POST['date']))
        update_post_meta($post_id, '_date', esc_attr($_POST['date']));
    if (isset($_POST['time']))
        update_post_meta($post_id, '_time', esc_attr($_POST['time']));
    if (isset($_POST['result']))
        update_post_meta($post_id, '_result', esc_attr( implode(":", $_POST['result']) ));
	
    do_action( 'scm_match_save_format_fields', $post_id);     
}

// display comp data on admin page

function scm_match_columns($defaults) {  
    // no author, (post)date
    unset($defaults['author']);  
    unset($defaults['date']);  
    
    // new column titles
    $defaults['competition']  = __('Competition', 'sports-club-management');
    $defaults['match']  = __('Competitors', 'sports-club-management');
    $defaults['result']  = __('Result', 'sports-club-management');
    $defaults['datetime']  = __('Date', 'sports-club-management')." (".__('Time', 'sports-club-management').")";
    
    return $defaults;  
}  
  
function scm_match_sortable_column( $columns ) {  
    $columns['competition'] = 'competition';  
    $columns['datetime'] = 'datetime';  
    
    //To make a column 'un-sortable' remove it from the array  
    //unset($columns['date']);  
  
    return $columns;  
}   
  
function scm_match_column_content($column_name, $post_ID) {  
    switch($column_name){
        case 'competition':
            $id = get_post_meta( $post_ID , '_competitionid' , true );
            if ( $id != '' ) {
                echo sprintf("%s", get_the_title( $id ));
            }
            return;
        case 'match':
            $compid_1 = get_post_meta( $post_ID , '_compid_1' , true );
            $compid_2 = get_post_meta( $post_ID , '_compid_2' , true );
            echo sprintf("%s - %s", get_post_meta( $compid_1 , '_name' , true )
                                  , get_post_meta( $compid_2 , '_name' , true )
                );
            return;
        case 'result':
            $result = get_post_meta( $post_ID , '_result' , true );
            echo sprintf("%s", $result
                );
            return;
        case 'datetime':
            $date = get_post_meta( $post_ID , '_date' , true );
            $time = get_post_meta( $post_ID , '_time' , true );
            echo sprintf("%s (%s)", $date
                                  , $time
                );
            return;
        default:
            echo print_r($post_ID,true); //Show the whole array for troubleshooting purposes
            return;
    }
} 

// generate list of matches
function scm_matches_list( $comp_id ) {
	
	$matches = get_posts( array( 'post_type' => 'scm_match'
							   , 'numberposts' => '-1'
							   , 'meta_key' => '_competitionid'
							   , 'meta_value' => $comp_id
							   ) );

    foreach ( $matches as $match ) {
        scm_match_list_entry($match->ID);
        echo "\n";
    }
}

function scm_match_list_header( $comp_id ) {

    $names = array( __("Date", 'sports-club-management'), __("Time", 'sports-club-management'),
                    __("Competitor 1", 'sports-club-management'), __("Competitor 2", 'sports-club-management'),
                    __("Result", 'sports-club-management')
                  );
    
	echo apply_filters( 'scm_comp_match_list_header', '"' . join( '","', $names ) . '",', $comp_id);
        
}

function scm_match_list_entry($match_id) {
    $values = get_post_custom($match_id);

    $compid_1 = isset($values['_compid_1']) ? esc_attr($values['_compid_1'][0]) : '';
    $compid_2 = isset($values['_compid_2']) ? esc_attr($values['_compid_2'][0]) : '';
    $result = isset($values['_result']) ? esc_attr($values['_result'][0]) : '';
    $competitionid = isset($values['_competitionid']) ? esc_attr($values['_competitionid'][0]) : '';
    $date = isset($values['_date']) ? esc_attr($values['_date'][0]) : '';
    $time = isset($values['_time']) ? esc_attr($values['_time'][0]) : '';
	
	$compgroupid = get_post_meta( $competitionid , '_groupid' , true );
	$compgroup = ( $compgroupid != '' ? get_the_title( $compgroupid ) : __('none', 'sports-club-management') );
    
    $categories = get_the_terms( $competitionid, 'scm_comp_category' );
    if ( $categories && ! is_wp_error( $categories ) ) {
        $cats = array();
        foreach ( $categories as $cat ) {
            $cats[] = $cat->name;
        }                  
        $terms = join(" ", $cats);
    } else {
        $terms = '';
    }        

    $entries = array( $compgroup, get_the_title($competitionid), 
					  $terms,
					  $date, $time,
                      get_the_title($compid_1), get_the_title($compid_2),
                      $result
                    ); 
	
	echo apply_filters( 'scm_comp_match_list_entry', '"' . join( '","', $entries ) . '",', $competitionid, $match_id);
}

// rest api
function scm_match_register_rest_fields(){
	register_rest_field( 'scm_match', 'competition_id', array( /*'get_callback' => 'scm_member_get_middle_name' 
															 , */'update_callback' => 'scm_match_update' 
															 , 'schema' => array( 'description' => 'scm_comp post id' , 'type' => 'integer', 'required' => 'true' )) ); 
	register_rest_field( 'scm_match', 'competitor_id_1', array( 'update_callback' => 'scm_match_update' 
															  , 'schema' => array( 'description' => 'scm_competitor 1 post id' , 'type' => 'integer', 'required' => 'true' )) ); 
	register_rest_field( 'scm_match', 'competitor_id_2', array( 'update_callback' => 'scm_match_update' 
															  , 'schema' => array( 'description' => 'scm_competitor 2 post id' , 'type' => 'integer', 'required' => 'true' )) ); 
	register_rest_field( 'scm_match', 'match_date', array( 'update_callback' => 'scm_match_update' 
														 , 'schema' => array( 'description' => 'date of match' , 'type' => 'date' )) ); 
	register_rest_field( 'scm_match', 'match_time', array( 'update_callback' => 'scm_match_update' 
														 , 'schema' => array( 'description' => 'time of match' , 'type' => 'text' )) ); 
}
//function scm_member_get_name($post) { return get_post_meta( $post['id'], '_name', true ); }
//function scm_member_get_middle_name($post) { return get_post_meta( $post['id'], '_middlename', true ); }
//function scm_member_get_first_name($post) { return get_post_meta( $post['id'], '_firstname', true ); }

function scm_match_update($value, $post, $key) { 
	switch ( $key ) {
		case 'competition_id' 	:  update_post_meta( $post->ID, '_competitionid', $value ); break;
		case 'competitor_id_1' 	:  update_post_meta( $post->ID, '_compid_1', $value ); break;
		case 'competitor_id_2' 	:  update_post_meta( $post->ID, '_compid_2', $value ); break;
		case 'match_date' 		:  update_post_meta( $post->ID, '_date', $value ); break;
		case 'match_time' 		:  update_post_meta( $post->ID, '_time', $value ); break;
		default : return false;
	}
	return true; 
}
