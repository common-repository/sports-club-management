<?php

// custom post type

function scm_member_custom_post_init(){
    // taxonomy: Member Category
    $labels = array(
        'name' => __('Member Categories', 'sports-club-management'),
        'singular_name' => __('Member Category', 'sports-club-management'),
        'search_items' => __('Search Member Categories', 'sports-club-management'),
        'all_items' => __('All Member Categories', 'sports-club-management'),
        'parent_item' => __('Parent Member Category', 'sports-club-management'),
        'parent_item_colon' => __('Parent Member Category:', 'sports-club-management'),
        'edit_item' => __('Edit Member Category', 'sports-club-management'), 
        'update_item' => __('Update Member Category', 'sports-club-management'),
        'add_new_item' => __('Add New Member Category', 'sports-club-management'),
        'new_item_name' => __('New Member Category', 'sports-club-management'),
        'menu_name' => __('Member Categories', 'sports-club-management'),
    ); 	
    register_taxonomy('scm_member_category',array('scm_member'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'scm_member_category' ),
        'show_in_rest' => true,
        'rest_base' => 'member_categories',
    ));

    // custom post type: Member
    $labels = array(
        'name' => __('Members', 'sports-club-management'),
        'singular_name' => __('Member', 'sports-club-management'),
        'add_new' => __('Add New Member', 'sports-club-management'),
        'add_new_item' => __('Add New Member', 'sports-club-management'),
        'edit_item' => __('Edit Member', 'sports-club-management'),
        'new_item' => __('New Member', 'sports-club-management'),
        'view_item' => __('View Member', 'sports-club-management'),
        'search_items' => __('Search Members', 'sports-club-management'),
        'not_found' => __('No members found', 'sports-club-management'),
        'not_found_in_trash' => __('No members found in Trash', 'sports-club-management'),
        'parent_item_colon' => __('Parent Member:', 'sports-club-management'),
        'menu_name' => __('Members', 'sports-club-management'),
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
        'taxonomies' => array( 'scm_member_category' ),
        'query_var' => true,
        'can_export' => true,
        'rewrite' => false,
        'capability_type' => 'page', 
        'map_meta_cap' => true,
        'show_in_rest' => true,
        'rest_base' => 'members',
    );
    register_post_type('scm_member', $args);
}

// hooks and filters for Member
add_action('add_meta_boxes', 'scm_member_add_fields_box');
add_action('save_post', 'scm_member_save_fields');
add_filter('manage_scm_member_posts_columns', 'scm_member_columns');  
add_filter('manage_edit-scm_member_sortable_columns', 'scm_member_sortable_column'); 
add_filter('request', 'scm_member_name_orderby' );
add_action('manage_scm_member_posts_custom_column', 'scm_member_column_content', 10, 2);   
add_action('restrict_manage_posts', 'scm_member_filtered_by_category');
add_filter('parse_query', 'scm_member_category_from_id_in_query');
add_action('rest_api_init', 'scm_member_register_rest_fields');

// member fields operations

function scm_member_add_fields_box() {
    global $pagenow, $typenow;

    add_meta_box('scm_member_fields_box_id', __('Member Data', 'sports-club-management'), 'scm_member_display_fields', 'scm_member');
	if ($pagenow == 'post-new.php') {
		add_meta_box('scm_member_bulk_box_id', __('Import Members from a CSV file', 'sports-club-management'), 'scm_member_bulk', 'scm_member');
	}
    if ($pagenow == 'post.php') {
		do_action('scm_invoice_list_member_invoices');
        add_meta_box('scm_member_shortcodes_box_id', __('Shortcodes', 'sports-club-management'), 'scm_member_list_shortcodes', 'scm_member', 'side', 'high');
    }
}
function scm_member_display_fields() {
    global $post;
 
    $values = get_post_custom($post->ID);
    
    $firstname = isset($values['_firstname']) ? esc_attr($values['_firstname'][0]) : '';
    $middlename = isset($values['_middlename']) ? esc_attr($values['_middlename'][0]) : '';
    $name = isset($values['_name']) ? esc_attr($values['_name'][0]) : '';

    $street = isset($values['_street']) ? esc_attr($values['_street'][0]) : '';
    $number = isset($values['_number']) ? esc_attr($values['_number'][0]) : '';
    $zip = isset($values['_zip']) ? esc_attr($values['_zip'][0]) : '';
    $place = isset($values['_place']) ? esc_attr($values['_place'][0]) : '';
 
    $phone = isset($values['_phone']) ? esc_attr($values['_phone'][0]) : '';
    $cell = isset($values['_cell']) ? esc_attr($values['_cell'][0]) : '';

    $email = isset($values['_email']) ? esc_attr($values['_email'][0]) : '';

    $dateofbirth = isset($values['_dateofbirth']) ? esc_attr($values['_dateofbirth'][0]) : '0000-00-00';
    $gender = isset($values['_gender']) ? esc_attr($values['_gender'][0]) : 'male';
    if ($gender == 'male') { 
        $male_checked = 'checked'; 
        $female_checked = ''; 
    } else { 
        $male_checked = ''; 
        $female_checked = 'checked'; 
    }
    
    $level = isset($values['_level']) ? esc_attr($values['_level'][0]) : '';
    
    $custom1 = isset($values['_custom1']) ? esc_attr($values['_custom1'][0]) : '';
    $custom2 = isset($values['_custom2']) ? esc_attr($values['_custom2'][0]) : '';
    $custom3 = isset($values['_custom3']) ? esc_attr($values['_custom3'][0]) : '';
    $custom4 = isset($values['_custom4']) ? esc_attr($values['_custom4'][0]) : '';
    
    $label_custom1 = get_option('scm_custom1_member'); 
    if ($label_custom1 == '') { $label_custom1 = '[Custom1]'; }
    $label_custom2 = get_option('scm_custom2_member'); 
    if ($label_custom2 == '') { $label_custom2 = '[Custom2]'; }
    $label_custom3 = get_option('scm_custom3_member'); 
    if ($label_custom3 == '') { $label_custom3 = '[Custom3]'; }
    $label_custom4 = get_option('scm_custom4_member'); 
    if ($label_custom4 == '') { $label_custom4 = '[Custom4]'; }

    $startdate = isset($values['_startdate']) ? esc_attr($values['_startdate'][0]) : '0000-00-00';
    $enddate = isset($values['_enddate']) ? esc_attr($values['_enddate'][0]) : '0000-00-00';
    $username = isset($values['_username']) ? esc_attr($values['_username'][0]) : '';

    wp_nonce_field('scm_member_frm_nonce', 'scm_member_frm_nonce');
 
    $html = "
        <tr><td align='right'><label>".__('Name', 'sports-club-management')."</label></td><td><input id='name' type='text' name='name' value='$name' /></td></tr>
        <tr><td align='right'><label>".__('Middle', 'sports-club-management')."</label></td><td><input id='middlename' type='text' name='middlename' value='$middlename' /></td></tr>
        <tr><td align='right'><label>".__('First name', 'sports-club-management')."</label></td><td><input id='firstname' type='text' name='firstname' value='$firstname' /></td></tr>
        <tr><td align='right'><label>".__('Street', 'sports-club-management')."</label></td><td><input id='street' type='text' name='street' value='$street' /></td></tr>
        <tr><td align='right'><label>".__('Number', 'sports-club-management')."</label></td><td><input id='number' type='text' name='number' value='$number' /></td></tr>
        <tr><td align='right'><label>".__('Zip', 'sports-club-management')."</label></td><td><input id='zip' type='text' name='zip' value='$zip' /></td></tr>
        <tr><td align='right'><label>".__('City', 'sports-club-management')."</label></td><td><input id='place' type='text' name='place' value='$place' /></td></tr>
        <tr><td align='right'><label>".__('Phone', 'sports-club-management')."</label></td><td><input id='phone' type='text' name='phone' value='$phone' /></td></tr>
        <tr><td align='right'><label>".__('Cell', 'sports-club-management')."</label></td><td><input id='cell' type='text' name='cell' value='$cell' /></td></tr>
        <tr><td align='right'><label>".__('E-mail', 'sports-club-management')."</label></td><td><input id='email' type='text' name='email' value='$email' /></td></tr>
        <tr><td align='right'><label>".__('Date of birth', 'sports-club-management')."</label></td><td><input id='dateofbirth' class='scm_datepicker' type='text' name='dateofbirth' value='$dateofbirth' /></td></tr>
        <tr><td align='right'><label>".__('Gender', 'sports-club-management')."</label></td><td>
            <input type='radio' name='gender' value='male' ". $male_checked ."/> ".__('Male', 'sports-club-management')."
            <input type='radio' name='gender' value='female' ". $female_checked ."/> ".__('Female', 'sports-club-management')."</td>
        </tr>
        <tr><td align='right'><label>".__('Level', 'sports-club-management')."</label></td><td><input id='level' type='text' name='level' value='$level' /></td></tr>
        <tr><td align='right'><label>$label_custom1</label></td><td><input id='custom1' type='text' name='custom1' value='$custom1' /></td></tr>
        <tr><td align='right'><label>$label_custom2</label></td><td><input id='custom2' type='text' name='custom2' value='$custom2' /></td></tr>
        <tr><td align='right'><label>$label_custom3</label></td><td><input id='custom3' type='text' name='custom3' value='$custom3' /></td></tr>
        <tr><td align='right'><label>$label_custom4</label></td><td><input id='custom4' type='text' name='custom4' value='$custom4' /></td></tr>
        <tr><td align='right'><label>".__('Start date membership', 'sports-club-management')."</label></td><td><input id='startdate' class='scm_datepicker' type='text' name='startdate' value='$startdate' /></td></tr>
        <tr><td align='right'><label>".__('End date membership', 'sports-club-management')."</label></td><td><input id='enddate' class='scm_datepicker' type='text' name='enddate' value='$enddate' /></td></tr>
        <tr><td align='right'><label>".__('User name', 'sports-club-management')."</label></td><td><input id='username' type='text' name='username' value='$username' /></td></tr>
        ";
    echo "<table>" . $html . "</table>";
}
function scm_member_bulk() {
	
	$description = array( __('Specify CSV-file', 'sports-club-management')
						, __('View and select entries for creation', 'sports-club-management')
						, __('Confirm', 'sports-club-management')
						, __('Creating entries', 'sports-club-management')
						);
						
	$label_custom1 = get_option('scm_custom1_member'); 
	if ($label_custom1 == '') { $label_custom1 = 'custom1'; }
	$label_custom2 = get_option('scm_custom2_member'); 
	if ($label_custom2 == '') { $label_custom2 = 'custom2'; }
	$label_custom3 = get_option('scm_custom3_member'); 
	if ($label_custom3 == '') { $label_custom3 = 'custom3'; }
	$label_custom4 = get_option('scm_custom4_member'); 
	if ($label_custom4 == '') { $label_custom4 = 'custom4'; }
	$custom_labels = $label_custom1.", ".$label_custom2.", ".$label_custom3.", ".$label_custom4;
					
	$html_step1 = "
		<p>
		<table>
		<tr>
		<td><label>".__('CSV file', 'sports-club-management')."</label></td>
		<td><input id='filename' type='file'></td>
		<td>
			<p>
				<ol>
					<li>
						".__('CSV file must have field names in first row', 'sports-club-management')."
					</li>
					<li> 
						".__('Field names must include', 'sports-club-management').":<br>
						".__('first name', 'sports-club-management').",
						".__('name', 'sports-club-management')." 
					</li>
					<li> 
						".__('Additional field names to be used', 'sports-club-management').":<br>
						".__('middle name', 'sports-club-management').", 
						".__('street', 'sports-club-management').",
						".__('number', 'sports-club-management').",
						".__('city', 'sports-club-management').",
						".__('zip', 'sports-club-management').",
						".__('e-mail', 'sports-club-management').",
						".__('phone', 'sports-club-management').",
						".__('cell', 'sports-club-management').",
						".__('date of birth', 'sports-club-management').",
						".__('gender', 'sports-club-management').",
						".__('level', 'sports-club-management').",
						".__('start date', 'sports-club-management').",
						".__('end date', 'sports-club-management').",
						$custom_labels,
						".__('user name', 'sports-club-management')."
					</li>
					<li> 
						".__('Fields must be seperated by a comma', 'sports-club-management')."
					</li>
				</ol>
			</p>
		</td>
		</tr>
		<tr><td><a href='".plugins_url( '/templates/members.csv', dirname(__FILE__) )."'>".__('example file', 'sports-club-management')."</a><br>(".__('english', 'sports-club-management').")</td></tr>
		</table>
		</p>";
	
	echo scm_bulk_import_wizard($description, $html_step1);
}
function scm_member_list_shortcodes() {
	global $post;
	
	$html = __('Use the following shortcodes by copy/pasting into a post or page (see documentation).', 'sports-club-management');

	$html .= sprintf("<p>[scm_member_data member_id='%s']</p>", $post->ID);
	$html .= sprintf("<p>[scm_members]</p>", $post->ID);
	$html .= sprintf("<p>[scm_member_username_link]</p>", $post->ID);
	if ('use_invoices' == get_option('scm_include_invoices_data')) {
		$html .= sprintf("<p>[scm_invoice_data member_id='%s']</p>", $post->ID);
	}
	if ('use_competitions' == get_option('scm_include_competitions_data')) {
		$html .= sprintf("<p>[scm_competition_data member_id='%s']</p>", $post->ID);
	}
	
	echo $html;
}

function scm_member_save_fields($post_id) {
    // Bail if we're doing an auto save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
 
    // if our nonce isn't there, or we can't verify it, bail
    if (!isset($_POST['scm_member_frm_nonce']) || !wp_verify_nonce($_POST['scm_member_frm_nonce'], 'scm_member_frm_nonce'))
        return;
 
    // if our current user can't edit this post, bail
    if (!current_user_can('edit_posts'))
        return;
        
    if (isset($_POST['name']))
        update_post_meta($post_id, '_name', esc_attr($_POST['name']));
    if (isset($_POST['middlename']))
        update_post_meta($post_id, '_middlename', esc_attr($_POST['middlename']));
    if (isset($_POST['firstname']))
        update_post_meta($post_id, '_firstname', esc_attr($_POST['firstname']));
    if (isset($_POST['street']))
        update_post_meta($post_id, '_street', esc_attr($_POST['street']));
    if (isset($_POST['number']))
        update_post_meta($post_id, '_number', esc_attr($_POST['number']));
    if (isset($_POST['zip']))
        update_post_meta($post_id, '_zip', esc_attr($_POST['zip']));
    if (isset($_POST['place']))
        update_post_meta($post_id, '_place', esc_attr($_POST['place']));
    if (isset($_POST['phone']))
        update_post_meta($post_id, '_phone', esc_attr($_POST['phone']));
    if (isset($_POST['cell']))
        update_post_meta($post_id, '_cell', esc_attr($_POST['cell']));
    if (isset($_POST['email']))
        update_post_meta($post_id, '_email', esc_attr($_POST['email']));
    if (isset($_POST['dateofbirth']))
        update_post_meta($post_id, '_dateofbirth', esc_attr($_POST['dateofbirth']));
    if (isset($_POST['gender']))
        update_post_meta($post_id, '_gender', esc_attr($_POST['gender']));
    if (isset($_POST['level']))
        update_post_meta($post_id, '_level', esc_attr($_POST['level']));
    if (isset($_POST['custom1']))
        update_post_meta($post_id, '_custom1', esc_attr($_POST['custom1']));
    if (isset($_POST['custom2']))
        update_post_meta($post_id, '_custom2', esc_attr($_POST['custom2']));
    if (isset($_POST['custom3']))
        update_post_meta($post_id, '_custom3', esc_attr($_POST['custom3']));
    if (isset($_POST['custom4']))
        update_post_meta($post_id, '_custom4', esc_attr($_POST['custom4']));
    if (isset($_POST['startdate']))
        update_post_meta($post_id, '_startdate', esc_attr($_POST['startdate']));
    if (isset($_POST['enddate']))
        update_post_meta($post_id, '_enddate', esc_attr($_POST['enddate']));
    if (isset($_POST['username']))
        update_post_meta($post_id, '_username', esc_attr($_POST['username']));
        
    // remove action to prevent infinite loop as create_matches() creates/saves posts
    remove_action('save_post', 'scm_member_save_fields');
    
    do_action( 'scm_member_create', $post_id );
    
    // reassign action again
    add_action('save_post', 'scm_member_save_fields');
}

// display member data on admin page

function scm_member_columns($defaults) {  
    // no author, date
    unset($defaults['author']);  
    unset($defaults['date']);  
    
    // new column titles
    $defaults['name']  = __('Name', 'sports-club-management');
    $defaults['telmail']  = __('Phone', 'sports-club-management').' / '.__('Mail', 'sports-club-management');
    $defaults['category']  = __('Category', 'sports-club-management');
    
    $label_custom1 = get_option('scm_custom1_member'); 
    if ($label_custom1 == '') { $label_custom1 = '[Custom1]'; }
    $label_custom2 = get_option('scm_custom2_member'); 
    if ($label_custom2 == '') { $label_custom2 = '[Custom2]'; }
    $label_custom3 = get_option('scm_custom3_member'); 
    if ($label_custom3 == '') { $label_custom3 = '[Custom3]'; }
    $label_custom4 = get_option('scm_custom4_member'); 
    if ($label_custom4 == '') { $label_custom4 = '[Custom4]'; }
    $defaults['custom']  = $label_custom1 . ' / ' . $label_custom2 . ' / ' . $label_custom3 . ' / ' . $label_custom4;
    
    return $defaults;  
}  

function scm_member_sortable_column( $columns ) {  
    $columns['name'] = 'name';  
  
    //To make a column 'un-sortable' remove it from the array  
    //unset($columns['date']);  
  
    return $columns;  
}   

function scm_member_name_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'name' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => '_name',
            'orderby' => 'meta_value'
        ) );
    }
 
    return $vars;
}
  
function scm_member_column_content($column_name, $post_ID) {  
    switch($column_name){
        case 'name':
            echo sprintf('%s %s, %s', 
                    get_post_meta( $post_ID , '_name' , true ), 
                    get_post_meta( $post_ID , '_middlename' , true ), 
                    get_post_meta( $post_ID , '_firstname' , true )
                );
            return;
        case 'telmail':
            echo sprintf("%s &nbsp &nbsp %s <br> %s", 
                    get_post_meta( $post_ID , '_phone' , true ), 
                    get_post_meta( $post_ID , '_cell' , true ), 
                    get_post_meta( $post_ID , '_email' , true )
                );
            return;
        case 'category':
            $categories = wp_get_post_terms( $post_ID, 'scm_member_category', array("fields" => "names") );
            echo sprintf("%s",  implode(", ", $categories) );
            return;
        case 'custom':
            echo sprintf("%s <br> %s <br> %s <br> %s", 
                    get_post_meta( $post_ID , '_custom1' , true ),
                    get_post_meta( $post_ID , '_custom2' , true ),
                    get_post_meta( $post_ID , '_custom3' , true ),
                    get_post_meta( $post_ID , '_custom4' , true ) 
                );
            return;
        default:
            echo print_r($post_ID,true); //Show the whole array for troubleshooting purposes
            return;
    }
} 

// filter members by category

function scm_member_filtered_by_category() {
    global $typenow;
    $post_type = 'scm_member'; 
    $taxonomy = 'scm_member_category'; 
    if ($typenow == $post_type) {
        $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
        $info_taxonomy = get_taxonomy($taxonomy);
        wp_dropdown_categories(array(
            'show_option_all' => __("Show All {$info_taxonomy->label}"),
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

function scm_member_category_from_id_in_query($query) {
    global $pagenow;
    $post_type = 'scm_member'; 
    $taxonomy = 'scm_member_category'; 
    $q_vars = &$query->query_vars;
    
    if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type) {
    
        // by default, order members by name
        if ( !isset($q_vars['orderby']) ) {
            $q_vars['order'] = 'ASC';
            $q_vars['orderby'] = 'meta_value';
            $q_vars['meta_key'] = '_name';
        }

        // if not filtering on member category, exclude (optional) categories from member list
        if ( !isset($q_vars[$taxonomy]) ) {
            $q_vars['tax_query'] = array(
                                        array(
                                            'taxonomy' => $taxonomy,
                                            'field' => 'id',
                                            'terms' => array( apply_filters('scm_member_exclude_categories', "") ),      
                                            'operator' => 'NOT IN'
                                        )
                                   );
        } 
        else if ( is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }
}

// rest api
function scm_member_register_rest_fields(){
	register_rest_field( 'scm_member', 'name', array( 'get_callback' => 'scm_member_get_name' 
													, 'update_callback' => 'scm_member_update_memberid' 
													, 'schema' => array( 'description' => __('Name', 'sports-club-management') , 'type' => 'string', 'required' => 'true' )) ); 
	register_rest_field( 'scm_member', 'middle_name', array( 'get_callback' => 'scm_member_get_middle_name' 
														   , 'update_callback' => 'scm_member_update_memberid' 
														   , 'schema' => array( 'description' => __('Middle', 'sports-club-management') , 'type' => 'string', 'required' => 'true' )) ); 
	register_rest_field( 'scm_member', 'first_name', array( 'get_callback' => 'scm_member_get_first_name' 
														  , 'update_callback' => 'scm_member_update_memberid' 
														  , 'schema' => array( 'description' => __('First name', 'sports-club-management') , 'type' => 'string', 'required' => 'true' )) ); 
	register_rest_field( 'scm_member', 'street', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('Street', 'sports-club-management') , 'type' => 'string')) ); 
	register_rest_field( 'scm_member', 'number', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('Number', 'sports-club-management') , 'type' => 'string')) ); 
	register_rest_field( 'scm_member', 'zip', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('Zip', 'sports-club-management') , 'type' => 'string')) ); 
	register_rest_field( 'scm_member', 'city', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('City', 'sports-club-management') , 'type' => 'string')) ); 
	register_rest_field( 'scm_member', 'e-mail', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('E-mail', 'sports-club-management') , 'type' => 'string')) ); 
	register_rest_field( 'scm_member', 'phone', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('Phone', 'sports-club-management') , 'type' => 'string')) ); 
	register_rest_field( 'scm_member', 'cell', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('Cell', 'sports-club-management') , 'type' => 'string')) ); 
	register_rest_field( 'scm_member', 'date_of_birth', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('Date of birth', 'sports-club-management') , 'type' => 'date')) ); 
	register_rest_field( 'scm_member', 'gender', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('Gender', 'sports-club-management') , 'type' => 'string')) ); 
	register_rest_field( 'scm_member', 'level', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('Level', 'sports-club-management') , 'type' => 'number')) ); 
	register_rest_field( 'scm_member', 'start_date', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('Start date', 'sports-club-management') , 'type' => 'date')) ); 
	register_rest_field( 'scm_member', 'end_date', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('End date', 'sports-club-management') , 'type' => 'date')) ); 
	register_rest_field( 'scm_member', 'custom1', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('custom1', 'sports-club-management') , 'type' => 'string')) ); 
	register_rest_field( 'scm_member', 'custom2', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('custom2', 'sports-club-management') , 'type' => 'string')) ); 
	register_rest_field( 'scm_member', 'custom3', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('custom3', 'sports-club-management') , 'type' => 'string')) ); 
	register_rest_field( 'scm_member', 'custom4', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('custom4', 'sports-club-management') , 'type' => 'string')) ); 
	register_rest_field( 'scm_member', 'user_name', array( 'update_callback' => 'scm_member_update_memberid' 
													  , 'schema' => array( 'description' => __('User name', 'sports-club-management') , 'type' => 'string')) ); 
}
function scm_member_get_name($post) { return get_post_meta( $post['id'], '_name', true ); }
function scm_member_get_middle_name($post) { return get_post_meta( $post['id'], '_middlename', true ); }
function scm_member_get_first_name($post) { return get_post_meta( $post['id'], '_firstname', true ); }

function scm_member_update_memberid($value, $post, $key) { 
	switch ( $key ) {
		case 'name' 		:  update_post_meta( $post->ID, '_name', $value ); break;
		case 'middle_name' 	:  update_post_meta( $post->ID, '_middlename', $value ); break;
		case 'first_name' 	:  update_post_meta( $post->ID, '_firstname', $value ); break;
		case 'street' 		:  update_post_meta( $post->ID, '_street', $value ); break;
		case 'number' 		:  update_post_meta( $post->ID, '_number', $value ); break;
		case 'zip' 			:  update_post_meta( $post->ID, '_zip', $value ); break;
		case 'city' 		:  update_post_meta( $post->ID, '_place', $value ); break;
		case 'e-mail' 		:  update_post_meta( $post->ID, '_email', $value ); break;
		case 'phone' 		:  update_post_meta( $post->ID, '_phone', $value ); break;
		case 'cell' 		:  update_post_meta( $post->ID, '_cell', $value ); break;
		case 'date_of_birth':  update_post_meta( $post->ID, '_dateofbirth', $value ); break;
		case 'gender' 		:  update_post_meta( $post->ID, '_gender', $value ); break;
		case 'level' 		:  update_post_meta( $post->ID, '_level', $value ); break;
		case 'start_date' 	:  update_post_meta( $post->ID, '_startdate', $value ); break;
		case 'end_date' 	:  update_post_meta( $post->ID, '_enddate', $value ); break;
		case 'custom1' 		:  update_post_meta( $post->ID, '_custom1', $value ); break;
		case 'custom2' 		:  update_post_meta( $post->ID, '_custom2', $value ); break;
		case 'custom3' 		:  update_post_meta( $post->ID, '_custom3e', $value ); break;
		case 'custom4' 		:  update_post_meta( $post->ID, '_custom4', $value ); break;
		case 'user name' 	:  update_post_meta( $post->ID, '_username', $value ); break;
		default : return false;
	}
	return true; 
}




// generate list of members
function scm_member_list( $columns, $category ) {
    echo "sep=,\n";
    scm_member_list_header( $columns );
    echo "\n";

    if ($category == -1) {
		$members = get_posts( array( 'post_type' => 'scm_member'
									, 'numberposts' => '-1'
									, 'meta_key' => '_name'
									, 'orderby' => 'meta_value'
									, 'order' => 'ASC'
									) );
	} else {								
		$members = get_posts( array( 'post_type' => 'scm_member'
								, 'tax_query' => array(
									array(
										'taxonomy' => 'scm_member_category',
										'field' => 'id',
										'terms' => array( $category )
									)
								  )                            
								, 'numberposts' => '-1'
								, 'meta_key' => '_name'
								, 'orderby' => 'meta_value'
								, 'order' => 'ASC'
								) );
	}
    
    foreach ( $members as $member ) {
        scm_member_list_entry($member->ID, $columns);
        echo "\n";
    }
}

function scm_member_list_header($columns) {

    $names = array( __("Name", 'sports-club-management'), __("First name", 'sports-club-management'), __("Middle", 'sports-club-management'), 
                    __("Street", 'sports-club-management'), __("Number", 'sports-club-management'), __("Zip", 'sports-club-management'), __("City", 'sports-club-management'), 
                    __("E-mail", 'sports-club-management'), 
                    __("Categories", 'sports-club-management'), 
                    __("Phone", 'sports-club-management'), __("Cell", 'sports-club-management')
                  );

    echo '"' . join( '","', array_merge($names, $columns) ) . '"';
}

function scm_member_list_entry($member_id, $columns) {
    $values = get_post_custom($member_id);

    $firstname = isset($values['_firstname']) ? esc_attr($values['_firstname'][0]) : '';
    $middlename = isset($values['_middlename']) ? esc_attr($values['_middlename'][0]) : '';
    $name = isset($values['_name']) ? esc_attr($values['_name'][0]) : '';

    $street = isset($values['_street']) ? esc_attr($values['_street'][0]) : '';
    $number = isset($values['_number']) ? esc_attr($values['_number'][0]) : '';
    $zip = isset($values['_zip']) ? esc_attr($values['_zip'][0]) : '';
    $place = isset($values['_place']) ? esc_attr($values['_place'][0]) : '';
 
    $phone = isset($values['_phone']) ? esc_attr($values['_phone'][0]) : '';
    $cell = isset($values['_cell']) ? esc_attr($values['_cell'][0]) : '';

    $email = isset($values['_email']) ? esc_attr($values['_email'][0]) : '';

    $dateofbirth = isset($values['_dateofbirth']) ? esc_attr($values['_dateofbirth'][0]) : '0000-00-00';
    $age = date_diff(date_create($dateofbirth), date_create(date('Y-m-d')));        
    $gender = isset($values['_gender']) ? esc_attr($values['_gender'][0]) : 'male';

    $level = isset($values['_level']) ? esc_attr($values['_level'][0]) : '';

    $custom1 = isset($values['_custom1']) ? esc_attr($values['_custom1'][0]) : '';
    $custom2 = isset($values['_custom2']) ? esc_attr($values['_custom2'][0]) : '';
    $custom3 = isset($values['_custom3']) ? esc_attr($values['_custom3'][0]) : '';
    $custom4 = isset($values['_custom4']) ? esc_attr($values['_custom4'][0]) : '';

    $startdate = isset($values['_startdate']) ? esc_attr($values['_startdate'][0]) : '0000-00-00';
    $enddate = isset($values['_enddate']) ? esc_attr($values['_enddate'][0]) : '0000-00-00';
    $username = isset($values['_username']) ? esc_attr($values['_username'][0]) : '';
    
    $categories = get_the_terms( $member_id, 'scm_member_category' );
    if ( $categories && ! is_wp_error( $categories ) ) {
        $cats = array();
        foreach ( $categories as $cat ) {
            $cats[] = $cat->name;
        }                  
        $terms = join(" ", $cats);
    } else {
        $terms = '';
    }        

    $entries = array( $name, $firstname, $middlename,
                      $street, $number, $zip, $place,
                      $email,
                      $terms,
                      $phone, $cell
                    ); 
	
	$extras  = array();
	if ( in_array( __("Date of birth", 'sports-club-management'), $columns ) ) { array_push($extras, $dateofbirth ); }
	if ( in_array( __("Age", 'sports-club-management'), $columns ) ) { array_push($extras, $age->format('%y') ); }
	if ( in_array( __("Gender", 'sports-club-management'), $columns ) ) { array_push($extras, $gender ); }
	if ( in_array( __("Level", 'sports-club-management'), $columns ) ) { array_push($extras, $level ); }
	if ( in_array( __("Start date membership", 'sports-club-management'), $columns ) ) { array_push($extras, $startdate ); }
	if ( in_array( __("End date membership", 'sports-club-management'), $columns ) ) { array_push($extras, $enddate ); }
	$label = get_option('scm_custom1_member'); if ($label == '') { $label = "[Custom1]"; } 
	if ( in_array( $label, $columns ) ) { array_push($extras, $custom1 ); }
	$label = get_option('scm_custom2_member'); if ($label == '') { $label = "[Custom2]"; } 
	if ( in_array( $label, $columns ) ) { array_push($extras, $custom2 ); }
	$label = get_option('scm_custom3_member'); if ($label == '') { $label = "[Custom3]"; } 
	if ( in_array( $label, $columns ) ) { array_push($extras, $custom3 ); }
	$label = get_option('scm_custom4_member'); if ($label == '') { $label = "[Custom4]"; } 
	if ( in_array( $label, $columns ) ) { array_push($extras, $custom4 ); }
	if ( in_array( __("User name", 'sports-club-management'), $columns ) ) { array_push($extras, $username ); }
					
    echo '"' . join( '","', array_merge($entries, $extras) ) . '"' ;
}

// map header for import from CSV
function scm_member_import() { 

    $label_custom1 = get_option('scm_custom1_member'); 
    if ($label_custom1 == '') { $label_custom1 = 'custom1'; }
    $label_custom2 = get_option('scm_custom2_member'); 
    if ($label_custom2 == '') { $label_custom2 = 'custom2'; }
    $label_custom3 = get_option('scm_custom3_member'); 
    if ($label_custom3 == '') { $label_custom3 = 'custom3'; }
    $label_custom4 = get_option('scm_custom4_member'); 
    if ($label_custom4 == '') { $label_custom4 = 'custom4'; }

    // map field headers
    $map = array(__("name", 'sports-club-management') => "name",
                __("first name", 'sports-club-management') => "first_name",
                __("middle name", 'sports-club-management') => "middle_name",
                __("street", 'sports-club-management') => "street",
                __("number", 'sports-club-management') => "number",
                __("zip", 'sports-club-management') => "zip",
                __("city", 'sports-club-management') => "city",
                __("e-mail", 'sports-club-management') => "e-mail",
                __("phone", 'sports-club-management') => "phone",
                __("cell", 'sports-club-management') => "cell",
                __("date of birth", 'sports-club-management') => "date_of_birth",
                __("gender", 'sports-club-management') => "gender",
                __("level", 'sports-club-management') => "level",
                __("start date", 'sports-club-management') => "start_date",
                __("end date", 'sports-club-management') => "end_date",
                $label_custom1 => "custom1",
                $label_custom2 => "custom2",
                $label_custom3 => "custom3",
                $label_custom4 => "custom4",
                __("user name", 'sports-club-management') => "username");
    
	return $map;
}