<?php

// custom post type

function scm_invoice_custom_post_init(){
    
    // taxonomy: Invoice Category
    $labels = array(
        'name' => __('Invoice Categories', 'sports-club-management'),
        'singular_name' => __('Invoice Category', 'sports-club-management'),
        'search_items' => __('Search Invoice Categories', 'sports-club-management'),
        'all_items' => __('All Invoice Categories', 'sports-club-management'),
        'parent_item' => __('Parent Invoice Category', 'sports-club-management'),
        'parent_item_colon' => __('Parent Invoice Category:', 'sports-club-management'),
        'edit_item' => __('Edit Invoice Category', 'sports-club-management'), 
        'update_item' => __('Update Invoice Category', 'sports-club-management'),
        'add_new_item' => __('Add New Invoice Category', 'sports-club-management'),
        'new_item_name' => __('New Invoice Category', 'sports-club-management'),
        'menu_name' => __('Invoice Categories', 'sports-club-management'),
    ); 	
    register_taxonomy('scm_invoice_category',array('scm_invoice'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'scm_invoice_category' ),
        'show_in_rest' => true,
        'rest_base' => 'invoice_categories',
    ));

    // custom post type: Invoice
    $labels = array(
        'name' => __('Invoices', 'sports-club-management'),
        'singular_name' => __('Invoice', 'sports-club-management'),
        'add_new' => __('Add New Invoice', 'sports-club-management'),
        'add_new_item' => __('Add New Invoice', 'sports-club-management'),
        'edit_item' => __('Edit Invoice', 'sports-club-management'),
        'new_item' => __('New Invoice', 'sports-club-management'),
        'view_item' => __('View Invoice', 'sports-club-management'),
        'search_items' => __('Search Invoices', 'sports-club-management'),
        'not_found' => __('No Invoices found', 'sports-club-management'),
        'not_found_in_trash' => __('No Invoices found in Trash', 'sports-club-management'),
        'parent_item_colon' => __('Parent Invoice:', 'sports-club-management'),
        'menu_name' => __('Invoices', 'sports-club-management'),
    );
    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'supports' => array('title','author'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => 'sportclub',
        'show_in_admin_bar' => true,
        'menu_position' => 110,
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'taxonomies' => array( 'scm_invoice_category' ),
        'query_var' => true,
        'can_export' => true,
        'rewrite' => false,
        'capability_type' => 'page', 
        'map_meta_cap' => true,
        'show_in_rest' => true,
        'rest_base' => 'invoices',
    );
    register_post_type('scm_invoice', $args);
}

// hooks and filters for Invoice
add_action('add_meta_boxes', 'scm_invoice_add_fields_box');
add_action('save_post', 'scm_invoice_save_fields');
add_filter('manage_scm_invoice_posts_columns', 'scm_invoice_columns');  
add_filter('manage_edit-scm_invoice_sortable_columns', 'scm_invoice_sortable_column');  
add_action('manage_scm_invoice_posts_custom_column', 'scm_invoice_column_content', 10, 2);   
add_action('restrict_manage_posts', 'scm_invoice_filtered_by_category');
add_filter('parse_query', 'scm_invoice_category_from_id_in_query');
add_action('rest_api_init', 'scm_invoice_register_rest_fields');

// invoice fields operations

function scm_invoice_add_fields_box() {
    global $pagenow, $typenow;

    add_meta_box('scm_invoice_fields_box_id', __('Invoice Data', 'sports-club-management'), 'scm_invoice_display_fields', 'scm_invoice');
	if ($pagenow == 'post-new.php') {
		add_meta_box('scm_invoice_bulk_box_id', __('Add invoices for members in selected category', 'sports-club-management'), 'scm_invoice_bulk', 'scm_invoice');
	}
    if ($pagenow == 'post.php') {
        add_meta_box('scm_invoice_member_box_id', __('Member Data', 'sports-club-management'), 'scm_invoice_member_data', 'scm_invoice');
    }
}
function scm_invoice_display_fields() {
    global $post;
 
    $values = get_post_custom($post->ID);

    $memberid = isset($values['_memberid']) ? esc_attr($values['_memberid'][0]) : '';    
    
    $issuedate = isset($values['_issuedate']) ? esc_attr($values['_issuedate'][0]) : '0000-00-00';
    $duedate = isset($values['_duedate']) ? esc_attr($values['_duedate'][0]) : '0000-00-00';
    $paymentdate = isset($values['_paymentdate']) ? esc_attr($values['_paymentdate'][0]) : '0000-00-00';

    $service = isset($values['_service']) ? esc_attr($values['_service'][0]) : '';
 
    $amount = isset($values['_amount']) ? esc_attr($values['_amount'][0]) : '0.00';
    $creditdebet = isset($values['_creditdebet']) ? esc_attr($values['_creditdebet'][0]) : 'debet';
    if ($creditdebet == 'debet') { 
        $debet_checked = 'checked'; 
        $credit_checked = ''; 
    } else { 
        $debet_checked = ''; 
        $credit_checked = 'checked'; 
    }

    $custom1 = isset($values['_custom1']) ? esc_attr($values['_custom1'][0]) : '';
    $custom2 = isset($values['_custom2']) ? esc_attr($values['_custom2'][0]) : '';

    $label_custom1 = get_option('scm_custom1_invoice'); 
    if ($label_custom1 == '') { $label_custom1 = '[Custom1]'; }
    $label_custom2 = get_option('scm_custom2_invoice'); 
    if ($label_custom2 == '') { $label_custom2 = '[Custom2]'; }

    wp_nonce_field('scm_invoice_frm_nonce', 'scm_invoice_frm_nonce');
    
    $members = get_posts( array( 'post_type' => 'scm_member'
                            , 'numberposts' => '-1'
                            , 'meta_key' => '_name'
                            , 'orderby' => 'meta_value'
                            , 'order' => 'ASC'
                            ) );

    $member_options = "";
    foreach ($members as $member) {
        $member_values = get_post_custom($member->ID);
        $member_options .= "<option " . (($memberid == $member->ID) ? "selected='selected' " : "") . "value='$member->ID'>" 
                          . $member_values['_name'][0] . ", " . $member_values['_firstname'][0] . " " . $member_values['_middlename'][0]
						  . "</option>\n";
    }
	
	
    $html = "
        <tr><td align='right'><label>".__('Member', 'sports-club-management')."</label></td><td><select name='memberid'>\n". $member_options ."</select></td></tr>
        <tr><td align='right'><label>".__('Date of issue', 'sports-club-management')."</label></td><td><input id='issuedate' class='scm_datepicker' type='text' name='issuedate' value='$issuedate' /></td></tr>
        <tr><td align='right'><label>".__('Due date', 'sports-club-management')."</label></td><td><input id='duedate' class='scm_datepicker' type='text' name='duedate' value='$duedate' /></td></tr>
        <tr><td align='right'><label>".__('Date of payment', 'sports-club-management')."</label></td><td><input id='paymentdate' class='scm_datepicker' type='text' name='paymentdate' value='$paymentdate' /></td></tr>
        <tr><td align='right'><label>".__('Service', 'sports-club-management')."</label></td><td><input id='service' type='text' name='service' value='$service' /></td></tr>
        <tr><td align='right'><label>".__('Amount', 'sports-club-management')."</label></td><td><input id='amount' type='text' name='amount' value='$amount' /></td></tr>
        <tr><td align='right'><label>".__('C/D', 'sports-club-management')."</label></td><td>
            <input type='radio' name='creditdebet' id='credit' value='credit' ". $credit_checked ."/> ".__('Credit', 'sports-club-management')."
            <input type='radio' name='creditdebet' id='debet' value='debet' ". $debet_checked ."/> ".__('Debet', 'sports-club-management')."</td>
        </tr>
        <tr><td align='right'><label>$label_custom1</label></td><td><input id='custom1' type='text' name='custom1' value='$custom1' /></td></tr>
        <tr><td align='right'><label>$label_custom2</label></td><td><input id='custom2' type='text' name='custom2' value='$custom2' /></td></tr>
        ";
    echo "<table>" . $html . "</table>";
}
function scm_invoice_bulk() {
	
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
		<td><label>".__('Invoice prefix', 'sports-club-management')."</label></td>
		<td><input id='prefix' type='text'></td>
		<td>".__('Invoice name is prefix.date-of-issue.####', 'sports-club-management')."</td>
		</tr>
		</table>
		</p>";
	
	echo scm_bulk_import_wizard($description, $html_step1);
}
function scm_invoice_member_data() {
    global $post;
    
    $values = get_post_custom($post->ID);

    $memberid = isset($values['_memberid']) ? esc_attr($values['_memberid'][0]) : '';    

    $member_values = get_post_custom($memberid);
    
    $html  = sprintf("<tr><td></td><td><a href=%s target=\"_blank\">%s</a></td></tr>\n"
                , site_url( "/wp-admin/post.php?post=".$memberid."&action=edit" )
                , get_the_title( $memberid ));
    $html .= sprintf("<tr><td>%s</td><td><a href=mailto:%s>%s</a></td></tr>\n"
                , __('E-mail', 'sports-club-management')
                , $member_values['_email'][0], $member_values['_email'][0]);
    $html .= sprintf("<tr><td>%s</td><td>%s</td></tr>\n", __('Phone', 'sports-club-management'), $member_values['_phone'][0]);
    $html .= sprintf("<tr><td>%s</td><td>%s</td></tr>\n", __('Cell', 'sports-club-management'), $member_values['_cell'][0]);
    
    echo "<table>" . $html . "</table>";
}

function scm_invoice_save_fields($post_id) {
    // Bail if we're doing an auto save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
 
    // if our nonce isn't there, or we can't verify it, bail
    if (!isset($_POST['scm_invoice_frm_nonce']) || !wp_verify_nonce($_POST['scm_invoice_frm_nonce'], 'scm_invoice_frm_nonce'))
        return;
 
    // if our current user can't edit this post, bail
    if (!current_user_can('edit_posts'))
        return;
 
    if (isset($_POST['memberid']))
        update_post_meta($post_id, '_memberid', esc_attr($_POST['memberid']));
    if (isset($_POST['issuedate']))
        update_post_meta($post_id, '_issuedate', esc_attr($_POST['issuedate']));
    if (isset($_POST['duedate']))
        update_post_meta($post_id, '_duedate', esc_attr($_POST['duedate']));
    if (isset($_POST['paymentdate']))
        update_post_meta($post_id, '_paymentdate', esc_attr($_POST['paymentdate']));
    if (isset($_POST['service']))
        update_post_meta($post_id, '_service', esc_attr($_POST['service']));
    if (isset($_POST['amount']))
        update_post_meta($post_id, '_amount', esc_attr($_POST['amount']));
    if (isset($_POST['creditdebet']))
        update_post_meta($post_id, '_creditdebet', esc_attr($_POST['creditdebet']));
    if (isset($_POST['custom1']))
        update_post_meta($post_id, '_custom1', esc_attr($_POST['custom1']));
    if (isset($_POST['custom2']))
        update_post_meta($post_id, '_custom2', esc_attr($_POST['custom2']));
}

// display invoice data on admin page

function scm_invoice_columns($defaults) {  
    // no author, date
    unset($defaults['author']);  
    unset($defaults['date']);  
    
    // new column titles
    $defaults['name']  = __('Name', 'sports-club-management');
    $defaults['duedate']  = __('Due Date', 'sports-club-management');
    $defaults['paymentdate']  = __('Date of Payment', 'sports-club-management');
    $defaults['amountservice']  = __('Amount', 'sports-club-management').' / '.__('Description', 'sports-club-management');
    $defaults['category']  = __('Category', 'sports-club-management');
    
    $label_custom1 = get_option('scm_custom1_invoice'); 
    if ($label_custom1 == '') { $label_custom1 = '[Custom1]'; }
    $label_custom2 = get_option('scm_custom2_invoice'); 
    if ($label_custom2 == '') { $label_custom2 = '[Custom2]'; }
    $defaults['custom']  = $label_custom1 . ' / ' . $label_custom2;
    
    return $defaults;  
}  
  
function scm_invoice_sortable_column( $columns ) {  
    $columns['name'] = 'name';  
    $columns['duedate'] = 'duedate';  
    $columns['paymentdate'] = 'paymentdate';  
  
    //To make a column 'un-sortable' remove it from the array  
    //unset($columns['date']);  
  
    return $columns;  
}   
  
function scm_invoice_column_content($column_name, $post_ID) {  
    switch($column_name){
        case 'name':
            $member_ID = get_post_meta( $post_ID , '_memberid' , true );
            echo sprintf('%s %s, %s', 
                    get_post_meta( $member_ID , '_name' , true ), 
                    get_post_meta( $member_ID , '_middlename' , true ), 
                    get_post_meta( $member_ID , '_firstname' , true )
                );
            return;
        case 'duedate':
            echo sprintf("%s", get_post_meta( $post_ID , '_duedate' , true )
                );
            return;
        case 'paymentdate':
            echo sprintf("%s", get_post_meta( $post_ID , '_paymentdate' , true )
                );
            return;
        case 'amountservice':
            echo sprintf('%s (%s)', 
                    get_post_meta( $post_ID , '_amount' , true ), 
                    get_post_meta( $post_ID , '_service' , true )
                );
            return;
        case 'category':
            $categories = wp_get_post_terms( $post_ID, 'scm_invoice_category', array("fields" => "names") );
            echo sprintf("%s",  implode(", ", $categories) );
            return;
        case 'custom':
            echo sprintf("%s <br> %s", 
                    get_post_meta( $post_ID , '_custom1' , true ),
                    get_post_meta( $post_ID , '_custom2' , true )
                );
            return;
        default:
            echo print_r($post_ID,true); //Show the whole array for troubleshooting purposes
            return;
    }
} 

// filter invoices by category

function scm_invoice_filtered_by_category() {
    global $typenow;
    $post_type = 'scm_invoice'; 
    $taxonomy = 'scm_invoice_category'; 
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

function scm_invoice_category_from_id_in_query($query) {
    global $pagenow;
    $post_type = 'scm_invoice'; 
    $taxonomy = 'scm_invoice_category'; 
    $q_vars = &$query->query_vars;
    if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type ) {
    
        // if not filtering on member category, exclude (optional) categories from member list
        if ( !isset($q_vars[$taxonomy]) ) {
            $q_vars['tax_query'] = array(
                                        array(
                                            'taxonomy' => $taxonomy,
                                            'field' => 'id',
                                            'terms' => array( apply_filters('scm_invoice_exclude_categories', "") ),      
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

// add metabox on member edit page
add_action('scm_invoice_list_member_invoices', 'scm_invoice_list_member');
function scm_invoice_list_member() { 
    add_meta_box('scm_member_invoices_id', __('Invoices', 'sports-club-management'), 'scm_member_display_invoices', 'scm_member');
}
function scm_member_display_invoices($member) {

    $invoices = get_posts( array( 'post_type' => 'scm_invoice'
                                , 'numberposts' => '-1'
                                , 'post_status' => array('publish','draft')
                                , 'meta_key' => '_memberid'
                                , 'meta_value' => $member->ID
                                ) );
                                
    if ( count( $invoices ) == 0 ) {
        echo __('none', 'sports-club-management');
    } else {
        echo "<table>";
        echo "<tr><td>".__('Date of issue', 'sports-club-management')."</td><td>".__('Due date', 'sports-club-management')."</td><td>".__('Date of payment', 'sports-club-management')."</td><td>".__('Amount', 'sports-club-management')."</td><td>".__('Service', 'sports-club-management')."</td></tr>\n";
        foreach ( $invoices as $invoice ) {
            if ( 'draft' == get_post_status( $invoice->ID ) ) {
                $amount = __('none', 'sports-club-management');
            } else {
                $amount = get_post_meta( $invoice->ID , '_amount' , true );
            }
            echo sprintf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>"
								, get_post_meta( $invoice->ID , '_issuedate' , true )
								, get_post_meta( $invoice->ID , '_duedate' , true )
								, get_post_meta( $invoice->ID , '_paymentdate' , true )
								, $amount
								, get_post_meta( $invoice->ID , '_service' , true ));
            echo sprintf("<td><a href=%s target=\"_blank\">%s</a></td></tr>\n"
                        , site_url( "/wp-admin/post.php?post=".$invoice->ID."&action=edit" )
                        , get_the_title( $invoice->ID ));
        }    
        echo "</table>";
    }
}

// rest api
function scm_invoice_register_rest_fields(){
	register_rest_field( 'scm_invoice', 'member_id', array( 'update_callback' => 'scm_invoice_update_memberid' 
														 , 'schema' => array( 'description' => 'scm_member post id', 'type' => 'integer', 'required' => 'true' )
	) ); 
	register_rest_field( 'scm_invoice', 'issue_date', array( 'update_callback' => 'scm_invoice_update_memberid' 
														  , 'schema' => array( 'description' => __('issue date', 'sports-club-management'), 'type' => 'date', 'required' => 'true' )
	) ); 
	register_rest_field( 'scm_invoice', 'due_date', array( 'update_callback' => 'scm_invoice_update_memberid' 
														, 'schema' => array( 'description' => __('due date', 'sports-club-management'), 'type' => 'date', 'required' => 'true' )
	) ); 
	register_rest_field( 'scm_invoice', 'amount', array( 'update_callback' => 'scm_invoice_update_memberid' 
													   , 'schema' => array( 'description' => __('amount', 'sports-club-management'), 'type' => 'number', 'required' => 'true' )
	) ); 
	register_rest_field( 'scm_invoice', 'service', array( 'update_callback' => 'scm_invoice_update_memberid' 
														, 'schema' => array( 'description' => __('service', 'sports-club-management'), 'type' => 'string', 'required' => 'true' )
	) ); 
	register_rest_field( 'scm_invoice', 'credit_debet', array( 'update_callback' => 'scm_invoice_update_memberid' 
															, 'schema' => array( 'description' => __('credit/debet', 'sports-club-management'), 'type' => 'string', 'required' => 'true' )
	) ); 
}
function scm_invoice_update_memberid($value, $post, $key) { 
	switch ( $key ) {
		case 'member_id' 	:  update_post_meta( $post->ID, '_memberid', $value ); break;
		case 'issue_date' 	:  update_post_meta( $post->ID, '_issuedate', $value ); break;
		case 'due_date' 	:  update_post_meta( $post->ID, '_duedate', $value ); break;
		case 'service' 		:  update_post_meta( $post->ID, '_service', $value ); break;
		case 'amount' 		:  update_post_meta( $post->ID, '_amount', $value ); break;
		case 'credit_debet' :  update_post_meta( $post->ID, '_creditdebet', $value ); break;
		default : return false;
	}
	return true; 
}

// generate list of invoices
function scm_invoice_list( $category ) {
    echo "sep=,\n";
    scm_invoice_list_header();
    echo "\n";

    if ($category == -1) {
		$invoices = get_posts( array( 'post_type' => 'scm_invoice'
									, 'numberposts' => '-1'
									) );
	} else {								
		$invoices = get_posts( array( 'post_type' => 'scm_invoice'
								, 'tax_query' => array(
									array(
										'taxonomy' => 'scm_invoice_category',
										'field' => 'id',
										'terms' => array( $category )
									)
								  )                            
								, 'numberposts' => '-1'
								) );
	}

    
    foreach ( $invoices as $invoice ) {
        scm_invoice_list_entry($invoice->ID);
        echo "\n";
    }
}

function scm_invoice_list_header() {
    $label_custom1 = get_option('scm_custom1_invoice'); 
    if ($label_custom1 == '') { $label_custom1 = 'custom1'; }
    $label_custom2 = get_option('scm_custom2_invoice'); 
    if ($label_custom2 == '') { $label_custom2 = 'custom2'; }

    $names = array( __("issue date", 'sports-club-management'), __("due date", 'sports-club-management'), __("payment date", 'sports-club-management'),
                    __("service", 'sports-club-management'),
                    __("amount", 'sports-club-management'), __("credit/debet", 'sports-club-management'),
                    __("categories", 'sports-club-management'),
                    $label_custom1, $label_custom2
                  );
    echo '"' . join( '","', $names ) . '",' ;
    scm_member_list_header( array() );
}

function scm_invoice_list_entry($invoice_id) {
    $values = get_post_custom($invoice_id);

    $memberid = isset($values['_memberid']) ? esc_attr($values['_memberid'][0]) : '';    
    
    $issuedate = isset($values['_issuedate']) ? esc_attr($values['_issuedate'][0]) : '';
    $duedate = isset($values['_duedate']) ? esc_attr($values['_duedate'][0]) : '';
    $paymentdate = isset($values['_paymentdate']) ? esc_attr($values['_paymentdate'][0]) : '';

    $service = isset($values['_service']) ? esc_attr($values['_service'][0]) : '';
 
    $amount = isset($values['_amount']) ? esc_attr($values['_amount'][0]) : '0.00';
    $creditdebet = isset($values['_creditdebet']) ? esc_attr($values['_creditdebet'][0]) : 'debet';
    
    $custom1 = isset($values['_custom1']) ? esc_attr($values['_custom1'][0]) : '';
    $custom2 = isset($values['_custom2']) ? esc_attr($values['_custom2'][0]) : '';

    $categories = get_the_terms( $invoice_id, 'scm_invoice_category' );
    if ( $categories && ! is_wp_error( $categories ) ) {
        $cats = array();
        foreach ( $categories as $cat ) {
            $cats[] = $cat->name;
        }                  
        $terms = join(" ", $cats);
    } else {
        $terms = '';
    }    

    $entries = array( $issuedate, $duedate, $paymentdate,
                      $service,
                      $amount, $creditdebet,
                      $terms,
                      $custom1, $custom2
                    ); 
    echo '"' . join( '","', $entries ) . '",' ;
    scm_member_list_entry($memberid, array());
}
