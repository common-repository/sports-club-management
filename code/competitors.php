<?php

// custom post type

function scm_competitor_custom_post_init(){
    
    // custom post type: Competitor
    $labels = array(
        'name' => __('Competitors', 'sports-club-management'),
        'singular_name' => __('Competitor', 'sports-club-management'),
        'add_new' => __('Add New Competitor', 'sports-club-management'),
        'add_new_item' => __('Add New Competitor', 'sports-club-management'),
        'edit_item' => __('Edit Competitor', 'sports-club-management'),
        'new_item' => __('New Competitor', 'sports-club-management'),
        'view_item' => __('View Competitor', 'sports-club-management'),
        'search_items' => __('Search Competitors', 'sports-club-management'),
        'not_found' => __('No Competitors found', 'sports-club-management'),
        'not_found_in_trash' => __('No Competitors found in Trash', 'sports-club-management'),
        'parent_item_colon' => __('Parent Competitor:', 'sports-club-management'),
        'menu_name' => __('Competitors', 'sports-club-management'),
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
        'exclude_from_search' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => false,
        'capability_type' => 'page', 
        'map_meta_cap' => true,
        'show_in_rest' => true,
        'rest_base' => 'competitors',
    );
    register_post_type('scm_competitor', $args);
}

// hooks and filters for Competitor
add_action('add_meta_boxes', 'scm_competitor_add_fields_box');
add_action('save_post', 'scm_competitor_save_fields');
add_filter('manage_scm_competitor_posts_columns', 'scm_competitor_columns');  
add_filter('manage_edit-scm_competitor_sortable_columns', 'scm_competitor_sortable_column');  
add_action('manage_scm_competitor_posts_custom_column', 'scm_competitor_column_content', 10, 2);   
add_action('rest_api_init', 'scm_competitor_register_rest_fields');

// comp fields operations

function scm_competitor_add_fields_box() {
    global $pagenow, $typenow;
	
    add_meta_box('scm_competitor_fields_box_id', __('Competitor Data', 'sports-club-management'), 'scm_competitor_display_fields', 'scm_competitor');
	if ($pagenow == 'post-new.php') {
		add_meta_box('scm_competitor_bulk_box_id', __('Add competitors for members in selected category', 'sports-club-management'), 'scm_competitor_bulk', 'scm_competitor');
	}
    if ($pagenow == 'post.php') {
		add_meta_box('scm_teamplayer_list_box_id', __('Team Players', 'sports-club-management'), 'scm_competitor_display_team', 'scm_competitor');
        add_meta_box('scm_competitor_shortcodes_box_id', __('Shortcodes', 'sports-club-management'), 'scm_competitor_list_shortcodes', 'scm_competitor', 'side', 'high');
	}
}
function scm_competitor_display_fields() {
    global $post;
 
    $values = get_post_custom($post->ID); 

    $name = $teamname = isset($values['_name']) ? esc_attr($values['_name'][0]) : '';
    $member = isset($values['_member']) ? esc_attr($values['_member'][0]) : '';
    $competitor_type = isset($values['_competitor_type']) ? esc_attr($values['_competitor_type'][0]) : '';
    $member_checked = $external_checked = $team_checked = "";
    if ($competitor_type == 'member') {
        $member_checked = "checked";
    } else if ($competitor_type == 'external') {
        $external_checked = "checked";
        $teamname = '';
    } else if ($competitor_type == 'team') {
        $team_checked = "checked";
        $name = '';
    }
    $disqualified = isset($values['_disqualified']) ? esc_attr($values['_disqualified'][0]) : '';
    if ($disqualified == 'yes') {
        $disqualified_checked = "checked";
    } else {
        $disqualified_checked = "";
    }
    $competitionid = isset($values['_competitionid']) ? esc_attr($values['_competitionid'][0]) : '';
    
    wp_nonce_field('scm_competitor_frm_nonce', 'scm_competitor_frm_nonce');
        
    $members = get_posts( array( 'post_type' => 'scm_member'
                            , 'numberposts' => '-1'
                            , 'meta_key' => '_name'
                            , 'orderby' => 'meta_value'
                            , 'order' => 'ASC'
                            ) );
    $member_options = "<option value=''> ".__('none', 'sports-club-management')." </option>\n";

    foreach ($members as $mem) {
        $member_values = get_post_custom($mem->ID);
        $member_options .= "<option " . (($member == $mem->ID) ? "selected='selected' " : "") . "value='$mem->ID'>" 
                          . $member_values['_name'][0] . ", " . $member_values['_firstname'][0] . " " . $member_values['_middlename'][0]
						  . "</option>\n";
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
        <tr> 
        <td align='right'><label>".__('Member', 'sports-club-management')."</label></td>
        <td><input type='radio' name='competitor_type' value='member' ". $member_checked ."/>
        <select name='member'>\n". $member_options ."</select></td>
        </tr>
        <tr>
        <td align='right'><label>".__('Name', 'sports-club-management')."</label></td>
        <td><input type='radio' name='competitor_type' value='external' ". $external_checked ."/>
        <input id='name' type='text' name='name' value='$name' /></td>
        </tr>
        <tr>
        <td align='right'><label>".__('Team Name', 'sports-club-management')."</label></td>
        <td><input type='radio' name='competitor_type' value='team' ". $team_checked ."/>
        <input id='name' type='text' name='teamname' value='$teamname' /></td>
        </tr>
        <tr>
        <td align='right'><label>".__('Disqualified', 'sports-club-management')."</label></td><td>
        <input type='checkbox' name='disqualified' value='yes' ". $disqualified_checked ." /></td>
        " . apply_filters( 'scm_competitor_display_format_fields', "", $post->ID, $competitionid ) . "
        <tr>
        <td align='right'><label>".__('Competition', 'sports-club-management')."</label></td>
        <td><select name='competitionid'>\n". $comp_options ."</select></td>
        </tr>
        ";
    echo "<table>" . $html . "</table>";

}
function scm_competitor_bulk() {
	
	$description = array( __('Define entries', 'sports-club-management')
						, __('View and select entries for creation', 'sports-club-management')
						, __('Confirm', 'sports-club-management')
						, __('Creating entries', 'sports-club-management')
						);
						
	$html_step1 = "
		<p>
		<table>
		<tr>
		<td><label>".__('Member Category', 'sports-club-management')."</label></td>
		<td>
		".wp_dropdown_categories(array(
					'show_option_none' => __('select category from list...', 'sports-club-management'),
					'taxonomy' => 'scm_member_category',
					'id' => 'bulk_member_category',
					'orderby' => 'name',
					'show_count' => true,
					'hide_empty' => false,
					'hierarchical' => 1, 
					'depth' => 4,
					'echo' => 0
			  ))
		."
		</td>
		</tr>
		<tr>
		<td><label>".__('Display format', 'sports-club-management')."</label></td>
		<td><input type='radio' name='competitor_type' id='formatF' value='f' selected='selected'/>" . "First name only" . "</td>
		<td>(".__('example', 'sports-club-management').": John)</td>
		</tr>
		<tr>
		<td></td>
		<td><input type='radio' name='competitor_type' id='formatFMN' value='fmn'/>". "Full name, starting with first name" ."</td>
		<td>(".__('example', 'sports-club-management').": John F Kennedy)</td>
		</tr>
		<tr>
		<td></td>
		<td><input type='radio' name='competitor_type' id='formatNFM' value='nfm'/>". "Full name" ."</td>
		<td>(".__('example', 'sports-club-management').": Kennedy, John F)</td>
		</tr>
		</table>
		</p>";
	
	echo scm_bulk_import_wizard($description, $html_step1);
}
function scm_competitor_display_team() {
    global $post;
 
    $players = get_posts( array( 'post_type' => 'scm_teamplayer'
                                , 'numberposts' => '-1'
                                , 'meta_key' => '_competitorid'
                                , 'meta_value' => $post->ID
                                , 'orderby' => 'meta_value'
                                , 'order' => 'ASC'
                                ) );
    foreach ( $players as $player ) {
        $member_values = get_post_custom($player->ID);
        echo sprintf("<p><a href=%s target=\"_blank\">%s</a></p>"
                    , site_url( "/wp-admin/post.php?post=".$player->ID."&action=edit" )
                    , get_the_title( $member_values['_member'][0] ));
    }    

}
function scm_competitor_list_shortcodes() {
	global $post;
	
	$html = __('Use the following shortcodes by copy/pasting into a post or page (see documentation).', 'sports-club-management');

	$html .= sprintf("<p>[scm_team_data competitor_id='%s']</p>", $post->ID);
	$html .= sprintf("<p>[scm_match_data competitor_id='%s']</p>", $post->ID);
	
	echo $html;
}

function scm_competitor_save_fields($post_id) {
    // Bail if we're doing an auto save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

        // if our nonce isn't there, or we can't verify it, bail
    if (!isset($_POST['scm_competitor_frm_nonce']) || !wp_verify_nonce($_POST['scm_competitor_frm_nonce'], 'scm_competitor_frm_nonce'))
        return;

    // if our current user can't edit this post, bail
    if (!current_user_can('edit_posts'))
        return;

    if (isset($_POST['competitor_type'])) {
        $type = $_POST['competitor_type'];
        update_post_meta($post_id, '_competitor_type', $type);
        switch ( $type ) {
            case 'member' : {
                update_post_meta($post_id, '_name', '');
                if (isset($_POST['member']))
                    update_post_meta($post_id, '_member', esc_attr($_POST['member']));
                break;
            }
            case 'external' : {
                update_post_meta($post_id, '_member', '');
                if (isset($_POST['name']))
                    update_post_meta($post_id, '_name', esc_attr($_POST['name']));
                break;
            }
            case 'team' : {
                update_post_meta($post_id, '_member', '');
                if (isset($_POST['name']))
                    update_post_meta($post_id, '_name', esc_attr($_POST['teamname']));
                break;
            }
        }
    }

    if (isset($_POST['disqualified']))
        update_post_meta($post_id, '_disqualified', esc_attr($_POST['disqualified']));
	
	$competition_id = -1;
    if (isset($_POST['competitionid'])) {
		$competition_id = esc_attr($_POST['competitionid']);
        update_post_meta($post_id, '_competitionid', $competition_id);
	}

	do_action( 'scm_competitor_save_format_fields', $post_id, $competition_id );     
}

// display comp data on admin page

function scm_competitor_columns($defaults) {  
    // no author, (post)date
    unset($defaults['author']);  
    unset($defaults['date']);  
    
    // new column titles
    $defaults['name']  = __('Name', 'sports-club-management');
    $defaults['member']  = __('Member', 'sports-club-management');
    $defaults['disqualified']  = __('Disqualified', 'sports-club-management');
    $defaults['competition']  = __('Competition', 'sports-club-management');
    
    return $defaults;  
}  
  
function scm_competitor_sortable_column( $columns ) {  
    $columns['name'] = 'name';  
    $columns['competition'] = 'competition';  
    
    //To make a column 'un-sortable' remove it from the array  
    //unset($columns['date']);  
  
    return $columns;  
}   
  
function scm_competitor_column_content($column_name, $post_ID) {  
    switch($column_name){
        case 'competition':
            $id = get_post_meta( $post_ID , '_competitionid' , true );
            echo sprintf("%s", get_the_title( $id )
                );
            return;
        case 'member':
            $member = get_post_meta( $post_ID , '_member' , true );
            echo sprintf("%s", $member
                );
            return;
        case 'name':
            $name = get_post_meta( $post_ID , '_name' , true );
            echo sprintf("%s", $name
                );
            return;
        case 'disqualified':
            $disqualified = get_post_meta( $post_ID , '_disqualified' , true );
            echo sprintf("%s", $disqualified
                );
            return;
        default:
            echo print_r($post_ID,true); //Show the whole array for troubleshooting purposes
            return;
    }
} 

// rest api
function scm_competitor_register_rest_fields(){
	register_rest_field( 'scm_competitor', 'name', array( /*'get_callback' => 'scm_member_get_name' 
														, */'update_callback' => 'scm_competitor_update' 
														, 'schema' => array( 'description' => __('Name', 'sports-club-management') , 'type' => 'string', 'required' => 'false' )) ); 
	register_rest_field( 'scm_competitor', 'member_id', array( /*'get_callback' => 'scm_member_get_middle_name' 
															 , */'update_callback' => 'scm_competitor_update' 
															 , 'schema' => array( 'description' => 'scm_member post id' , 'type' => 'integer', 'required' => 'false' )) ); 
	register_rest_field( 'scm_competitor', 'competitor_type', array( /*'get_callback' => 'scm_member_get_first_name' 
																   , */'update_callback' => 'scm_competitor_update' 
																   , 'schema' => array( 'description' => __('Competitor type', 'sports-club-management') , 'type' => 'string', 'required' => 'true' )) ); 
	register_rest_field( 'scm_competitor', 'competition_id', array( 'get_callback' => 'scm_competitor_get_competition_id' 
																  , 'update_callback' => 'scm_competitor_update' 
																  , 'schema' => array( 'description' => 'scm_comp post id' , 'type' => 'integer', 'required' => 'true' )) ); 
}
//function scm_member_get_name($post) { return get_post_meta( $post['id'], '_name', true ); }
//function scm_member_get_middle_name($post) { return get_post_meta( $post['id'], '_middlename', true ); }
//function scm_member_get_first_name($post) { return get_post_meta( $post['id'], '_firstname', true ); }
function scm_competitor_get_competition_id($post) { return get_post_meta( $post['id'], '_competitionid', true ); }

function scm_competitor_update($value, $post, $key) { 
	switch ( $key ) {
		case 'name' 			:  update_post_meta( $post->ID, '_name', $value ); break;
		case 'member_id' 		:  update_post_meta( $post->ID, '_member', $value ); break;
		case 'competitor_type' 	:  update_post_meta( $post->ID, '_competitor_type', $value ); break;
		case 'competition_id' 	:  update_post_meta( $post->ID, '_competitionid', $value ); break;
		default : return false;
	}
	return true; 
}
