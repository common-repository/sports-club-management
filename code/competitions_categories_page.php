<div class="wrap">

    <div class="metabox-holder ">
        <div class="postbox"> 
            <h3><?php _e('Competitions', 'sports-club-management') ?></h3>
        </div>
    </div>
	<?php echo sprintf("<p><a href=%s> %s %s </a>", admin_url('edit-tags.php?taxonomy=scm_comp_category'), __('Competition', 'sports-club-management'), __('Categories')); ?>
	<?php echo sprintf("<p><a href=%s> %s %s </a>", admin_url('edit-tags.php?taxonomy=scm_comp_group_category'), __('Competition Group', 'sports-club-management'), __('Categories')); ?>

</div>