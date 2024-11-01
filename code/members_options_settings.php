<div class="wrap">

<?php 
    $scm_member_option_hidden = isset($_POST['scm_member_option_hidden']) ? 'yes' : 'no';    

    if ( $scm_member_option_hidden == 'yes' ) {
    
        $display_privacy = isset($_POST['scm_display_privacy_data']) ? esc_attr($_POST['scm_display_privacy_data']) : '';
        update_option('scm_display_privacy_data', $display_privacy);
        $display_addrmailphone_privacy = isset($_POST['scm_display_addrmailphone_privacy_data']) ? esc_attr($_POST['scm_display_addrmailphone_privacy_data']) : '';
        update_option('scm_display_addrmailphone_privacy_data', $display_addrmailphone_privacy);
        
		$custom1_member = isset($_POST['scm_custom1_member']) ? esc_attr($_POST['scm_custom1_member']) : '';
		update_option('scm_custom1_member', $custom1_member);
		$custom2_member = isset($_POST['scm_custom2_member']) ? esc_attr($_POST['scm_custom2_member']) : '';
		update_option('scm_custom2_member', $custom2_member);
		$custom3_member = isset($_POST['scm_custom3_member']) ? esc_attr($_POST['scm_custom3_member']) : '';
		update_option('scm_custom3_member', $custom3_member);
		$custom4_member = isset($_POST['scm_custom4_member']) ? esc_attr($_POST['scm_custom4_member']) : '';
		update_option('scm_custom4_member', $custom4_member);
        
        do_action('scm_members_options_save', $_POST);
            
?>
<div class="updated">
    <p><strong><?php _e('Member options saved.', 'sports-club-management'); ?></strong></p>
</div>
<?php 

    } else {
    	$display_privacy = get_option('scm_display_privacy_data');
    	$display_addrmailphone_privacy = get_option('scm_display_addrmailphone_privacy_data');
        
        $custom1_member = get_option('scm_custom1_member');
        $custom2_member = get_option('scm_custom2_member');
        $custom3_member = get_option('scm_custom3_member');
        $custom4_member = get_option('scm_custom4_member');
    }
    
    if ($display_privacy == 'no_privacy') {
        $display_privacy_checked = "checked";
    } else {
        $display_privacy_checked = "";
    }
    if ($display_addrmailphone_privacy == 'no_addrmailphone_privacy') {
        $display_addrmailphone_privacy_checked = "checked";
    } else {
        $display_addrmailphone_privacy_checked = "";
    }
    
?>

<form name="scm_member_option_form" method="post" action="<?php echo admin_url('admin.php').'?page='.$_GET['page']; ?>">
    <div class="metabox-holder ">
        <div class="postbox"> 
            <h3><?php _e('Member Options', 'sports-club-management') ?></h3>
        </div>
    </div>
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Privacy', 'sports-club-management') ?></th>
            <td> 
			<fieldset><legend class="screen-reader-text"></legend><label for="display_privacy">
            <input name="scm_display_privacy_data" type="checkbox" value="no_privacy" <?php echo $display_privacy_checked; ?> />
            <?php _e('Display privacy related data of other members', 'sports-club-management') ?></label>
            </fieldset>
            <fieldset><legend class="screen-reader-text"></legend><label for="display_addrmailphone_privacy">
            <input name="scm_display_addrmailphone_privacy_data" type="checkbox" value="no_addrmailphone_privacy" <?php echo $display_addrmailphone_privacy_checked; ?> />
            <?php _e('Display address, e-mail, and phone numbers of other members', 'sports-club-management') ?></label>
            </fieldset>
			</td>
        </tr>
        <tr>
            <th scope="row"><label for="custom_member"><?php _e('Custom values', 'sports-club-management') ?></label></th>
            <td>
            <input name="scm_custom1_member" type="text" value="<?php echo $custom1_member; ?>" class="regular-text" />
            <p class="description"><?php _e('Name for custom value #1', 'sports-club-management') ?></p>
            <input name="scm_custom2_member" type="text" value="<?php echo $custom2_member; ?>" class="regular-text" />
            <p class="description"><?php _e('Name for custom value #2', 'sports-club-management') ?></p>
            <input name="scm_custom3_member" type="text" value="<?php echo $custom3_member; ?>" class="regular-text" />
            <p class="description"><?php _e('Name for custom value #3', 'sports-club-management') ?></p>
            <input name="scm_custom4_member" type="text" value="<?php echo $custom4_member; ?>" class="regular-text" />
            <p class="description"><?php _e('Name for custom value #4', 'sports-club-management') ?></p>
            </td>
        </tr>
<?php
    echo apply_filters( 'scm_members_options_display', "" );
?>        
    </table>
    <input type="submit" name="submit_member_option" class="button-primary" value="<?php _e('Save Member Options', 'sports-club-management') ?>" />
    <input type="hidden" name="scm_member_option_hidden" value="yes">
</form>

</div>
