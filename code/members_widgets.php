<?php

/* register widgets */
add_action( 'widgets_init', 'scm_member_register_widgets');
function scm_member_register_widgets() {
	register_widget( 'scm_member_widget_birthdays' );
}

/* birthday widget */ 
class scm_member_widget_birthdays extends WP_Widget {
	
	public $instance;
 
    public function __construct() {
     
        parent::__construct(
            'scm_member_widget_birthdays',
            __( 'Sports Club', 'sports-club-management' ) . " - " . __( 'Birthdays', 'sports-club-management' ),
            array(
                'classname'   => 'scm_member_widget_birthdays',
                'description' => __( 'List of member\'s birthdays', 'sports-club-management' )
                )
        );
    }
 
    public function widget( $args, $instance ) {    
         
        extract( $args );
         
        $title     = apply_filters( 'widget_title', $instance['title'] );
        $start     = $instance['start'];
        $end       = $instance['end'];
        $emptylist = $instance['emptylist'];
        $needlogin = $instance['needlogin'];
		
		// only proceed if user is logged in when needed
		if (($needlogin =="yes") && !is_user_logged_in()) {
			return;
		}
		
		// get members with birthday in [today-start .. today+end]
		$members = get_posts( array( 'post_type' => 'scm_member'
						, 'numberposts' => '-1'
						, 'meta_key' => '_dateofbirth'
						, 'orderby' => 'meta_value'
						, 'order' => 'ASC'
						, 'tax_query' => array(
											array(
												'taxonomy' => 'scm_member_category',
												'field' => 'id',
												'terms' => array( apply_filters('scm_member_exclude_categories', "") ),      
												'operator' => 'NOT IN'
											)
										 )                            
						) );
         
        echo $before_widget;
         
        if ( $title ) {
            echo $before_title . $title . $after_title;
        }
            
		// display selected members
		$startday   = strtotime("- $start days");
		$endday     = strtotime("+ $end days");
		
		$count = 0;
        foreach ( $members as $member ) {
			$values = get_post_custom($member->ID);
    
			$firstname  = $values['_firstname'][0];
			$middlename = $values['_middlename'][0];
			$name       = $values['_name'][0];
			$date       = $values['_dateofbirth'][0];
			
			$year       = date("Y", time());
			$month      = date("m", strtotime($date));
			$day        = date("j", strtotime($date));
			$birthday   = strtotime("$year-$month-$day");
			
			$diffstart  = ($birthday - $startday)/(24*60*60);
			$diffend    = ($endday - $birthday)/(24*60*60);
			
			if (( $diffstart > 0 ) && ( $diffend > 0 )) {
				echo "<div id=\"scm_widget_birthday\">";
				echo "<div id=\"scm_widget_date\">" . date("M j", $birthday) . "</div>";
				echo "<div id=\"scm_widget_member\">" . sprintf("<a href=%s>%s %s %s</a>", get_post_permalink( $member ), $firstname, $middlename, $name) . "</div>";
				echo "</div>";

				$count++;
			}
        }
		if ( $count == 0 ) {
			echo $emptylist;
		}
		
        echo $after_widget;
         
    }
 
    public function update( $new_instance, $old_instance ) {        
         
        $instance = $old_instance; print_r ( $new_instance );
         
        $instance['title']     = strip_tags( $new_instance['title'] );
        $instance['start']     = strip_tags( $new_instance['start'] );
        $instance['end']       = strip_tags( $new_instance['end'] );
        $instance['emptylist'] = strip_tags( $new_instance['emptylist'] );
        $instance['needlogin'] = strip_tags( $new_instance['needlogin'] );
         
        return $instance;
         
    }
  
    public function form( $instance ) {    
     
        $title     = esc_attr( $instance['title'] );
        $start     = esc_attr( $instance['start'] );
        $end       = esc_attr( $instance['end'] );
        $emptylist = esc_attr( $instance['emptylist'] );
        $needlogin = esc_attr( $instance['needlogin'] );
        ?>
         
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
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
        <p>
            <input class="widefat" id="<?php echo $this->get_field_id('needlogin'); ?>" name="<?php echo $this->get_field_name('needlogin'); ?>" type="checkbox" value="yes" <?php if ($needlogin == "yes") echo "checked"; ?> />
            <label for="<?php echo $this->get_field_id('needlogin'); ?>"><?php echo __('Visible after login only', 'sports-club-management'); ?></label> 
        </p>
     
    <?php 
    }
     
}
 
