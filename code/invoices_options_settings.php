<div class="wrap">

<?php 
    $scm_invoice_option_hidden = isset($_POST['scm_invoice_option_hidden']) ? 'yes' : 'no';    

    if ( $scm_invoice_option_hidden == 'yes' ) {
        
        $include_invoices = isset($_POST['scm_include_invoices_data']) ? esc_attr($_POST['scm_include_invoices_data']) : '';
        update_option('scm_include_invoices_data', $include_invoices);
        
		$custom1_invoice = isset($_POST['scm_custom1_invoice']) ? esc_attr($_POST['scm_custom1_invoice']) : '';
		update_option('scm_custom1_invoice', $custom1_invoice);
		$custom2_invoice = isset($_POST['scm_custom2_invoice']) ? esc_attr($_POST['scm_custom2_invoice']) : '';
		update_option('scm_custom2_invoice', $custom2_invoice);
        
        do_action('scm_invoices_options_save', $_POST);
            
?>
<div class="updated">
    <p><strong><?php _e('Invoice options saved.', 'sports-club-management'); ?></strong></p>
</div>          
<?php 

    } else {
    	$include_invoices = get_option('scm_include_invoices_data');
        
        $custom1_invoice = get_option('scm_custom1_invoice');
        $custom2_invoice = get_option('scm_custom2_invoice');
    }
    
    if ($include_invoices == 'use_invoices') {
        $include_invoices_checked = "checked";
    } else {
        $include_invoices_checked = "";
    } 
    
?>

<form name="scm_invoice_option_form" method="post" action="<?php echo admin_url('admin.php').'?page='.$_GET['page']; ?>">
    <div class="metabox-holder ">
        <div class="postbox"> 
            <h3><?php _e('Invoice Options', 'sports-club-management') ?></h3>
        </div>
    </div>
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Include invoices', 'sports-club-management') ?></th>
            <td> <fieldset><legend class="screen-reader-text"></legend><label for="include_invoices">
            <input name="scm_include_invoices_data" type="checkbox" value="use_invoices" <?php echo $include_invoices_checked; ?> />
            <?php _e('Granted', 'sports-club-management') ?></label>
            </fieldset></td>
        </tr>
        <tr>
            <th scope="row"><label for="custom_invoice"><?php _e('Custom values', 'sports-club-management') ?></label></th>
            <td>
            <input name="scm_custom1_invoice" type="text" value="<?php echo $custom1_invoice; ?>" class="regular-text" />
            <p class="description"><?php _e('Name for custom value #1', 'sports-club-management') ?></p>
            <input name="scm_custom2_invoice" type="text" value="<?php echo $custom2_invoice; ?>" class="regular-text" />
            <p class="description"><?php _e('Name for custom value #2', 'sports-club-management') ?></p>
            </td>
        </tr>
<?php
    echo apply_filters( 'scm_invoices_options_display', "" );
?>        
    </table>
    <input type="submit" name="submit_invoice_option" class="button-primary" value="<?php _e('Save Invoice Options', 'sports-club-management') ?>" />
    <input type="hidden" name="scm_invoice_option_hidden" value="yes">
</form>

</div>
