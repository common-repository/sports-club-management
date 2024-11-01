<?php 

    $scm_any_export_hidden = isset($_POST['scm_any_export_hidden']) ? 'yes' : 'no';    
    $scm_competition_export_hidden = isset($_POST['scm_competition_export_hidden']) ? 'yes' : 'no';    
    $scm_competition_export_category = isset($_POST['scm_comp_category']) ? esc_attr( $_POST['scm_comp_category'] ) : -1;

    if ( $scm_competition_export_hidden == 'yes' ) {
        header("Content-type: text/csv;");
        header("Content-Disposition: attachment; filename=" . __('Matches', 'sports-club-management') . ".csv");

        scm_competition_list( $scm_competition_export_category );

        exit();
    } else if ( $scm_any_export_hidden == 'yes' ) {
        // other export in progress
        return;
    }
?>

<div class="wrap">

<form name="scm_competition_export_form" method="post" action="<?php echo admin_url('admin.php').'?page='.$_GET['page'].'&noheader=true'; ?>">
    <div class="metabox-holder ">
        <div class="postbox"> 
            <h3><?php _e('Export Competitions', 'sports-club-management') ?></h3>
        </div>
    </div>
<?php
    echo apply_filters( 'scm_competitions_export_selection', "" );
?>        
    <input type="submit" name="submit_competition_export" class="button-primary" value="<?php _e('Export Competitions to File', 'sports-club-management')?>" />
    <input type="hidden" name="scm_competition_export_hidden" value="yes">
    <input type="hidden" name="scm_any_export_hidden" value="yes">
</form>

</div>
