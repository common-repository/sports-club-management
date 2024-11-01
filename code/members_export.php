<?php 

    $scm_any_export_hidden = isset($_POST['scm_any_export_hidden']) ? 'yes' : 'no';    
    $scm_member_export_hidden = isset($_POST['scm_member_export_hidden']) ? 'yes' : 'no'; 
	$scm_member_export_columns = isset($_POST['scm_member_columns']) ? $_POST['scm_member_columns'] : array();
    $scm_member_export_category = isset($_POST['scm_member_category']) ? esc_attr( $_POST['scm_member_category'] ) : -1;

    if ( $scm_member_export_hidden == 'yes' ) { 
		
		header("Content-type: text/csv;");
        header("Content-Disposition: attachment; filename=" . __('Members', 'sports-club-management') . ".csv"); 
        
		if ( $scm_member_export_columns == "all_columns" ) {
			$scm_member_export_columns = array( __('Date of birth', 'sports-club-management'), __('Age', 'sports-club-management'),
												__('Gender', 'sports-club-management'), __('Level', 'sports-club-management'),
												__('Start date membership', 'sports-club-management'), __('End date membership', 'sports-club-management'), 
												( get_option('scm_custom1_member') == '' ? "[Custom1]" : get_option('scm_custom1_member')),
												( get_option('scm_custom2_member') == '' ? "[Custom2]" : get_option('scm_custom2_member')),
												( get_option('scm_custom3_member') == '' ? "[Custom3]" : get_option('scm_custom3_member')),
												( get_option('scm_custom4_member') == '' ? "[Custom4]" : get_option('scm_custom4_member')),
												__('User name', 'sports-club-management')			
											  );
		}
        scm_member_list( $scm_member_export_columns, $scm_member_export_category );

        exit();
    } else if ( $scm_any_export_hidden == 'yes' ) {
        // other export in progress
        return;
	}
?>

<div class="wrap">
    <div id="icon-tools" class="icon32"><br/></div>
    <h2><span class="dashicons dashicons-media-text"></span> <?php _e('Club Management', 'sports-club-management')?> - <?php _e('Export Data', 'sports-club-management')?></h2>
</div>

<div class="wrap">

<form name="scm_member_export_form" method="post" action="<?php echo admin_url('admin.php').'?page='.$_GET['page'].'&noheader=true'; ?>">
    <div class="metabox-holder ">
        <div class="postbox"> 
            <h3><?php _e('Export Members', 'sports-club-management') ?></h3>
        </div>
    </div>
<?php
    echo apply_filters( 'scm_members_export_selection', "<input type='hidden' name='scm_member_columns' value='all_columns'>" );
?>        
    <input type="submit" name="submit_member_export" class="button-primary" value="<?php _e('Export Members to File', 'sports-club-management')?>" />
    <input type="hidden" name="scm_member_export_hidden" value="yes">
    <input type="hidden" name="scm_any_export_hidden" value="yes">
</form>

</div>