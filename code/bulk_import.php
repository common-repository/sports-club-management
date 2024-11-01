<?php


function scm_bulk_import_wizard($step_description, $step1_content) {
	 
	$html = "
	<div class='wrap'>
		<div class='metabox-holder'>
			<div class='postbox'> 
				<p>
				<ol><li id='scm_bulk_item1'>".$step_description[0]."</li>
					<li id='scm_bulk_item2'>".$step_description[1]."</li>
					<li id='scm_bulk_item3'>".$step_description[2]."</li>
					<li id='scm_bulk_item4'>".$step_description[3]."</li>
				</ol>
				<button id='scm_bulk_previous_btn' class='button-secondary'></button>
				<button id='scm_bulk_next_btn' class='button-primary'></button>
				</p>
			</div>
		</div>
		<div id='scm_bulk_message'></div>
		<div>
			<p id='scm_bulk_control_poststatus'>
				<input type='radio' name='status' id='publish_step2' checked>".__('Publish')."</input>
				<input type='radio' name='status' id='draft_step2'>".__('Save Draft')."</input>
			</p>
			<p id='scm_bulk_control_confirm'>
				<input type='checkbox' id='scm_bulk_confirm'>".__('Confirm creation of listed entries', 'sports-club-management')."</input>
			</p>
			<hr>
			<p id='scm_bulk_control_checkboxes'>
			<button id='scm_bulk_select_all' class='button-secondary'>".__('Select all', 'sports-club-management')."</button>	
			<button id='scm_bulk_unselect_all' class='button-secondary'>".__('Unselect all', 'sports-club-management')."</button>
			</p>
		</div>
		<div id='scm_bulk_step1'>".$step1_content."<hr></div>
		<div id='scm_bulk_list'></div>
			
	</div>";
	
    return $html;
}

// translation for bulk wizard
function scm_bulk_strings() { 

    // map field headers
    $map = array( "previous_step" => __("Previous Step", 'sports-club-management'),
                  "next_step" => __("Next Step", 'sports-club-management'),
                  "publish" => __("Publish", 'sports-club-management'),
                  "save_draft" => __("Save Draft", 'sports-club-management'),
                  "start_again" => __("Start Again", 'sports-club-management'),
                  "done" => __("Done", 'sports-club-management')
                );
    
	return $map;
}