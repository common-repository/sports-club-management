<?php

/* register widgets */
add_action( 'widgets_init', 'scm_match_register_widgets');
function scm_match_register_widgets() {
	register_widget( 'scm_match_widget_current' );
}

/* current matches widget */ 
class scm_match_widget_current extends WP_Widget {
	
	public $instance;
 
    public function __construct() {
     
        parent::__construct(
            'scm_match_widget_current',
            __( 'Sports Club', 'sports-club-management' ) . " - " . __( 'Current matches', 'sports-club-management' ),
            array(
                'classname'   => 'scm_match_widget_current',
                'description' => __( 'List of current matches', 'sports-club-management' )
                )
        );
    }
 
    public function widget( $args, $instance ) {    
         
        extract( $args );
         
        $title     = apply_filters( 'widget_title', $instance['title'] );
		$compgroup = $instance['compgroup'];
		$comp      = $instance['comp'];
        $start     = $instance['start'];
        $end       = $instance['end'];
        $emptylist = $instance['emptylist'];
		
		// get matches in [today-start .. today+end]
		$startdate = strtotime("-$start days");
		$enddate   = strtotime("+$end days");
		if (($compgroup == '') && ($comp == '')) {
			$matches = get_posts( array( 'post_type' => 'scm_match'
							, 'numberposts' => '-1'
							, 'meta_key' => '_date'
							, 'orderby' => 'meta_value'
							, 'order' => 'ASC'
							, 'meta_query' => array(
												array(
													'key' => '_date',
													'value' => array(date("Y-m-d", $startdate), date("Y-m-d", $enddate)),
													'compare' => 'BETWEEN'
												)  
											  )											
							) );
		} else if ($compgroup != '') {
			$competitions = get_posts( array( 'post_type' => 'scm_comp'
								, 'numberposts' => '-1'
								, 'meta_key' => '_groupid'
								, 'meta_value' => $compgroup									
								) );
			$matches = get_posts( array( 'post_type' => 'scm_match'
							, 'numberposts' => '-1'
							, 'meta_key' => '_date'
							, 'orderby' => 'meta_value'
							, 'order' => 'ASC'
							, 'meta_query' => array(
												array(
													'key' => '_date',
													'value' => array(date("Y-m-d", $startdate), date("Y-m-d", $enddate)),
													'compare' => 'BETWEEN'
												),  
												array(
													'key' => '_competitionid',
													'value' => wp_list_pluck( $competitions, 'ID'),
													'compare' => 'IN'
												)  
											  )											
							) );
		} else {
			$matches = get_posts( array( 'post_type' => 'scm_match'
							, 'numberposts' => '-1'
							, 'meta_key' => '_date'
							, 'orderby' => 'meta_value'
							, 'order' => 'ASC'
							, 'meta_query' => array(
												array(
													'key' => '_date',
													'value' => array(date("Y-m-d", $startdate), date("Y-m-d", $enddate)),
													'compare' => 'BETWEEN'
												),  
												array(
													'key' => '_competitionid',
													'value' => $comp,
													'compare' => '='
												)  
											  )											
							) );
		}
		
        echo $before_widget;
         
        if ( $title ) {
            echo $before_title . $title . $after_title. "\n";
        }
            
		// display selected matchs
		foreach ( $matches as $match ) {
			$values = get_post_custom($match->ID);

			$compid_1 = isset($values['_compid_1']) ? esc_attr($values['_compid_1'][0]) : '';
			$compid_2 = isset($values['_compid_2']) ? esc_attr($values['_compid_2'][0]) : '';
			$result = isset($values['_result']) ? esc_attr($values['_result'][0]) : '';
			$competitionid = isset($values['_competitionid']) ? esc_attr($values['_competitionid'][0]) : '';
			$date = isset($values['_date']) ? esc_attr($values['_date'][0]) : '';
			$time = isset($values['_time']) ? esc_attr($values['_time'][0]) : '';
			
			$dateresultstring = (chop($result,":") == '' ? date("M j", strtotime($date)) ."<br> $time" : $result);
			
			$compformat = apply_filters( 'scm_competition_get_format_name', "", $competitionid );
		
			echo "<div id=\"scm_widget_match\">\n";
			echo "<div id=\"scm_widget_competitor\" class=\"comp1 $compformat\">" . apply_filters( 'scm_comp_display_match_competitor_1', get_the_title( $compid_1 ), $match->ID, $matches, $competitionid ) . "</div>\n";
			echo "<div id=\"scm_widget_result\" class=\"$compformat\">" . ($match->post_content != "" ? "<a href=".get_post_permalink( $match->ID ).">".$dateresultstring."</a>" : $dateresultstring) . "</div>\n";
			echo "<div id=\"scm_widget_competitor\" class=\"comp2 $compformat\">" . apply_filters( 'scm_comp_display_match_competitor_2', get_the_title( $compid_2 ), $match->ID, $matches, $competitionid ) . "</div>\n";
			echo "</div>";
        }
		if ( count( $matches ) == 0 ) {
			echo $emptylist;
		}
		
        echo $after_widget;
         
    }
 
    public function update( $new_instance, $old_instance ) {        
         
        $instance = $old_instance; 
         
        $instance['title']     = strip_tags( $new_instance['title'] );
        $instance['compgroup'] = strip_tags( $new_instance['compgroup'] );
        $instance['comp']      = strip_tags( $new_instance['comp'] );
        $instance['start']     = strip_tags( $new_instance['start'] );
        $instance['end']       = strip_tags( $new_instance['end'] );
        $instance['emptylist'] = strip_tags( $new_instance['emptylist'] );
         
        return $instance;
         
    }
  
    public function form( $instance ) {    
     
        $title     = esc_attr( $instance['title'] );
		$compgroup = esc_attr( $instance['compgroup'] );
		$comp      = esc_attr( $instance['comp'] );
        $start     = esc_attr( $instance['start'] );
        $end       = esc_attr( $instance['end'] );
        $emptylist = esc_attr( $instance['emptylist'] );
        ?>
         
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('compgroup'); ?>"><?php echo __('Select Competition Group (\'no selection\' selects all groups)', 'sports-club-management'); ?></label> 
            <select class="widefat" id="<?php echo $this->get_field_id('compgroup'); ?>" name="<?php echo $this->get_field_name('compgroup'); ?>" >
            <?php 
				$groups = get_posts( array( 'post_type' => 'scm_comp_group'
										, 'numberposts' => '-1'
										, 'order'=> 'ASC'
										, 'orderby' => 'title'
										) );

				$group_options = "<option value=''> ".__('no selection', 'sports-club-management')." </option>\n";
				foreach ($groups as $group) {
					$group_options .= "<option ";  
					$group_options .= ($compgroup == $group->ID) ? "selected='selected' " : "";           
					$group_options .= "value='$group->ID'>" . get_the_title($group->ID) . "</option>\n";
				}				
				echo $group_options;
				?>
			</select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('comp'); ?>"><?php echo __('If no Competition Group has been selected, select Competition (\'no selection\' selects all competitions)', 'sports-club-management'); ?></label> 
            <select class="widefat" id="<?php echo $this->get_field_id('comp'); ?>" name="<?php echo $this->get_field_name('comp'); ?>" >
            <?php 
				$competitions = get_posts( array( 'post_type' => 'scm_comp'
										 , 'numberposts' => '-1'
										 , 'order'=> 'ASC'
										 , 'orderby' => 'title'
										 ) );

				$comp_options = "<option value=''> ".__('no selection', 'sports-club-management')." </option>\n";
				foreach ($competitions as $competition) {
					$comp_values = get_post_custom($competition->ID);
					$groupid = (($competition->ID != "") ? $comp_values['_groupid'][0] : "");
					$comp_options .= "<option ";  
					$comp_options .= ($comp == $competition->ID) ? "selected='selected' " : "";           
					$comp_options .= "value='$competition->ID'>" . get_the_title($competition->ID) . (($groupid != "") ? (" - [" . get_the_title( $groupid ) . "]") : "") . "</option>\n";
				}
				echo $comp_options;
				?>
			</select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('start'); ?>"><?php echo __('Number of days before today', 'sports-club-management'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('start'); ?>" name="<?php echo $this->get_field_name('start'); ?>" type="number" min="1" max="50" value="<?php echo $start; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('end'); ?>"><?php echo __('Number of days after today', 'sports-club-management'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('end'); ?>" name="<?php echo $this->get_field_name('end'); ?>" type="number" min="1" max="50" value="<?php echo $end; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('emptylist'); ?>"><?php echo __('Text for empty list', 'sports-club-management'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('emptylist'); ?>" name="<?php echo $this->get_field_name('emptylist'); ?>" type="text" value="<?php echo $emptylist; ?>" />
        </p>
     
    <?php 
    }
     
}
 
