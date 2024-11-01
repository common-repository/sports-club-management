<?php
/*
 * Plugin Name: Sports Club Management
 * Plugin URI: http://www.sportplugins.nl
 * Description: Create members, competitions (e.g. leagues, knockout tournaments, ladder), invoices, etc. for your (sports) club. Easy to manage and to publish on your site. For all sports. 
 * Version: 1.12.9
 * Author: Pieter Struik
 * Author URI: https://profiles.wordpress.org/pstruik
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: sports-club-management
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Setting constants
define('SCM_VERSION', "1.12.8");        // version
define('SCM_DIR', dirname( __FILE__ )); // absolute path to plugin directory

include_once 'code/bulk_import.php';

include_once 'code/members.php';
include_once 'code/members_shortcodes.php';
include_once 'code/members_widgets.php';
if ('use_invoices' == get_option('scm_include_invoices_data')) {
    include_once 'code/invoices.php';
    include_once 'code/invoices_shortcodes.php';
}
if ('use_competitions' == get_option('scm_include_competitions_data')) {
    include_once 'code/competitions.php';
    include_once 'code/compgroups.php';
    include_once 'code/matches.php';
    include_once 'code/competitors.php';
    include_once 'code/teamplayers.php';
    include_once 'code/competitions_shortcodes.php';
    include_once 'code/compgroups_shortcodes.php';
    include_once 'code/matches_shortcodes.php';
    include_once 'code/competitors_shortcodes.php';
    include_once 'code/matches_widgets.php';
}

register_activation_hook( __FILE__,'scm_plugin_activate');
function scm_plugin_activate() {
    $old_version = get_option('scm_version');
	if( $old_version == '' ) {
        // update version
        update_option('scm_version', SCM_VERSION);
    }
    else if ( SCM_VERSION > $old_version ) {
        // upgrade from earlier plugin version
        
        
        // update version
        update_option('scm_version', SCM_VERSION);
    }
    
}

register_deactivation_hook( __FILE__,'scm_plugin_deactivate');
function scm_plugin_deactivate() {
	//delete_option('scm_version');
}

add_filter('plugins_loaded','scm_plugins_loaded');
function scm_plugins_loaded() {
    load_plugin_textdomain( 'sports-club-management', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}

add_action( 'wp_enqueue_scripts', 'scm_enqueue_scripts' );
function scm_enqueue_scripts() {
	wp_enqueue_style( 'scm_spclmgt', plugins_url( '/css/spclmgt.css', __FILE__ ) );
}

add_action( 'admin_enqueue_scripts', 'scm_admin_enqueue_scripts' );
function scm_admin_enqueue_scripts() {
    global $pagenow, $typenow;
    
    if (  (($pagenow == 'post-new.php') || ($pagenow == 'post.php')) 
       && (($typenow == 'scm_member') || ($typenow == 'scm_invoice') || ($typenow == 'scm_match')) 
       ) {
        wp_enqueue_style( 'jquery-style', plugins_url( '/css/jquery-ui.css', __FILE__ ) );
        wp_enqueue_script( 'scm_spclmgt', plugins_url( '/js/spclmgt.js', __FILE__ ), array( 'jquery-ui-core', 'jquery-ui-datepicker'), '20151231', true ); 
    }
	
	/* global bulk js */
    if (  ($pagenow == 'post-new.php') 
       && (($typenow == 'scm_member') || ($typenow == 'scm_invoice') || ($typenow == 'scm_match') || ($typenow == 'scm_competitor') || ($typenow == 'scm_teamplayer')) 
       ) {
		wp_enqueue_script( 'wp-api' );
		wp_enqueue_script( 'scm_bulk_js', plugins_url( '/js/scm_bulk.js', __FILE__ ), array( 'wp-api' ), '20171231', true ); 
		$global_data = array(
			'map_strings' => scm_bulk_strings(),
		);
		wp_localize_script( 'scm_bulk_js', 'scm_bulk_globals', $global_data );
    }
	/* invoice bulk js */
    if (($pagenow == 'post-new.php') && ($typenow == 'scm_invoice')) {
		wp_enqueue_script( 'scm_invoice_bulk_js', plugins_url( '/js/scm_invoice_bulk.js', __FILE__ ), array( 'wp-api', 'scm_bulk_js' ), '20171231', true ); 
		$global_data = array(
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'url_done' => admin_url( '/edit.php?post_type=scm_invoice', __FILE__ ),
		);
		wp_localize_script( 'scm_invoice_bulk_js', 'scm_invoice_bulk_globals', $global_data );
    }
	/* member bulk js */
    if (($pagenow == 'post-new.php') && ($typenow == 'scm_member')) {
		wp_enqueue_script( 'scm_member_bulk_js', plugins_url( '/js/scm_member_bulk.js', __FILE__ ), array( 'wp-api', 'scm_bulk_js' ), '20171231', true ); 
		$global_data = array(
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'url_done' => admin_url( '/edit.php?post_type=scm_member', __FILE__ ),
			'mapheader' => scm_member_import(),
		);
		wp_localize_script( 'scm_member_bulk_js', 'scm_member_bulk_globals', $global_data );
    }
	/* match bulk js */
    if (($pagenow == 'post-new.php') && ($typenow == 'scm_match')) {
		wp_enqueue_script( 'scm_match_bulk_js', plugins_url( '/js/scm_match_bulk.js', __FILE__ ), array( 'wp-api', 'scm_bulk_js' ), '20171231', true ); 
		$global_data = array(
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'url_done' => admin_url( '/edit.php?post_type=scm_match', __FILE__ ),
		);
		wp_localize_script( 'scm_match_bulk_js', 'scm_match_bulk_globals', $global_data );
    }
	/* competitor bulk js */
    if (($pagenow == 'post-new.php') && ($typenow == 'scm_competitor')) {
		wp_enqueue_script( 'scm_competitor_bulk_js', plugins_url( '/js/scm_competitor_bulk.js', __FILE__ ), array( 'wp-api', 'scm_bulk_js' ), '20171231', true ); 
		$global_data = array(
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'url_done' => admin_url( '/edit.php?post_type=scm_competitor', __FILE__ ),
		);
		wp_localize_script( 'scm_competitor_bulk_js', 'scm_competitor_bulk_globals', $global_data );
    }
	/* team player bulk js */
    if (($pagenow == 'post-new.php') && ($typenow == 'scm_teamplayer')) {
		wp_enqueue_script( 'scm_teamplayer_bulk_js', plugins_url( '/js/scm_teamplayer_bulk.js', __FILE__ ), array( 'wp-api', 'scm_bulk_js' ), '20171231', true ); 
		$global_data = array(
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'url_done' => admin_url( '/edit.php?post_type=scm_teamplayer', __FILE__ ),
		);
		wp_localize_script( 'scm_teamplayer_bulk_js', 'scm_teamplayer_bulk_globals', $global_data );
    }
}

// register custom post types
add_action('init','scm_custom_post_init');
function scm_custom_post_init() {

    scm_member_custom_post_init();    
    if ('use_invoices' == get_option('scm_include_invoices_data')) {
        scm_invoice_custom_post_init();
    }
    if ('use_competitions' == get_option('scm_include_competitions_data')) {
        scm_comp_custom_post_init();    
        scm_comp_group_custom_post_init();    
        scm_match_custom_post_init();    
        scm_competitor_custom_post_init();    
        scm_teamplayer_custom_post_init();
    }
}

// options and settings in admin menu
add_action('admin_menu' , 'scm_admin_settings_page'); 
function scm_admin_settings_page() {
    add_menu_page (
        __('Sports Club', 'sports-club-management'),
        __('Sports Club', 'sports-club-management'),
        'edit_others_posts',
        'sportclub',
        '',
        'dashicons-groups',
        '45.13'
    );
    add_submenu_page('sportclub',
        __('Club Management', 'sports-club-management').' - '.__('All Categories', 'sports-club-management'), 
        __('All Categories', 'sports-club-management'), 
        'edit_others_posts', 
        'scm_all_categories_page', 
        'scm_create_all_categories_page'
    );
    add_submenu_page('sportclub',
        __('Club Management', 'sports-club-management').' - '.__('Export Data', 'sports-club-management'), 
        __('Export Data', 'sports-club-management'), 
        'edit_others_posts', 
        'scm_export_data_page', 
        'scm_create_admin_export_page'
    );
    add_submenu_page('sportclub',
        __('Club Management', 'sports-club-management').' - '.__('General Options', 'sports-club-management'), 
        __('General Options', 'sports-club-management'), 
        'activate_plugins', 
        'scm_general_options_page', 
        'scm_create_admin_general_page'
    );
}

function scm_create_all_categories_page() {
    //must check that the user has the required capability 
    if (!current_user_can( 'edit_others_posts' )) {
        wp_die( __('You do not have sufficient permissions to access this page.', 'sports-club-management') );
    }
?>    
<div class="wrap">
    <div id="icon-options-general" class="icon32"><br/></div>
    <h2><?php _e('Club Management', 'sports-club-management') ?> - <?php _e('All Categories', 'sports-club-management') ?></h2>
</div>
<?php
    
    include('code/members_categories_page.php');
    if ('use_invoices' == get_option('scm_include_invoices_data')) {
        include('code/invoices_categories_page.php');
    }
    if ('use_competitions' == get_option('scm_include_competitions_data')) {
        include('code/competitions_categories_page.php');
    }
}

function scm_create_admin_export_page() {
    //must check that the user has the required capability 
    if (!current_user_can( 'edit_others_posts' )) {
        wp_die( __('You do not have sufficient permissions to access this page.', 'sports-club-management') );
    }
    
    include('code/members_export.php');
    if ('use_invoices' == get_option('scm_include_invoices_data')) {
        include('code/invoices_export.php');
    }
    if ('use_competitions' == get_option('scm_include_competitions_data')) {
        include('code/competitions_export.php');
    }
}

function scm_create_admin_general_page() {
    //must check that the user has the required capability 
    if (!current_user_can( 'activate_plugins' )) {
        wp_die( __('You do not have sufficient permissions to access this page.', 'sports-club-management') );
    }
?>    
<div class="wrap">
    <div id="icon-options-general" class="icon32"><br/></div>
    <h2><span class="dashicons dashicons-admin-generic"></span> <?php _e('Club Management', 'sports-club-management') ?> - <?php _e('General Options', 'sports-club-management') ?></h2>
    <p> <?php _e('SCM plugin version', 'sports-club-management') ?> ( <?php echo get_option('scm_version'); ?> ) </p>
</div>
<?php
    
    include('code/members_options_settings.php');
    include('code/invoices_options_settings.php');
    include('code/competitions_options_settings.php');
}


