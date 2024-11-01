<div class="wrap">

<?php 
    $scm_comp_option_hidden = isset($_POST['scm_comp_option_hidden']) ? 'yes' : 'no';    

    if ( $scm_comp_option_hidden == 'yes' ) {
        
        $include_competitions = isset($_POST['scm_include_competitions_data']) ? esc_attr($_POST['scm_include_competitions_data']) : '';
        update_option('scm_include_competitions_data', $include_competitions);

        do_action('scm_competitions_options_save', $_POST);
                    
?>
<div class="updated">
    <p><strong><?php _e('Competition options saved.', 'sports-club-management'); ?></strong></p>
</div>          
<?php 

    } else {
    	$include_competitions = get_option('scm_include_competitions_data');
    }
    
    if ($include_competitions == 'use_competitions') {
        $include_competitions_checked = "checked";
    } else {
        $include_competitions_checked = "";
    } 

?>

<form name="scm_comp_option_form" method="post" action="<?php echo admin_url('admin.php').'?page='.$_GET['page']; ?>">
    <div class="metabox-holder ">
        <div class="postbox"> 
            <h3><?php _e('Competition Options', 'sports-club-management') ?></h3>
        </div>
    </div>
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Include Competitions', 'sports-club-management') ?></th>
            <td> <fieldset><legend class="screen-reader-text"></legend><label for="include_competitions">
            <input name="scm_include_competitions_data" type="checkbox" value="use_competitions" <?php echo $include_competitions_checked; ?> />
            <?php _e('Granted', 'sports-club-management') ?></label>
            </fieldset></td>
        </tr>
<?php
    echo apply_filters( 'scm_competitions_options_display', "" );
?>        
    </table>
    <input type="submit" name="submit_comp_option" class="button-primary" value="<?php _e('Save Competion Options', 'sports-club-management') ?>" />
    <input type="hidden" name="scm_comp_option_hidden" value="yes">
</form>

</div>
