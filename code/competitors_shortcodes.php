<?php

// member shortcodes to be used in posts and pages
add_shortcode( 'scm_team_data', 'scm_sh_team_data' );

function scm_sh_team_data( $atts ) {
    global $current_user;
    
    if ('use_competitions' != get_option('scm_include_competitions_data')) { 
        return "";
    }
	extract( shortcode_atts( array(
		'competitor_id' => '',
        'enable_contact_info' => false,
		'before' => '',
        'after' => '',
	), $atts ) );

    $teamplayers = get_posts( array( 'post_type' => 'scm_teamplayer'
                                , 'numberposts' => '-1'
                                , 'meta_key' => '_competitorid'
                                , 'meta_value' => $competitor_id
                                ) );
                                
    $html = "";
    $html .= "<div id=\"scm_team\"> \n";
    if ( count( $teamplayers ) == 0 ) {
        $html .= __('none', 'sports-club-management');
    } else {
		
		// determine whether to show contact info of team members
		$show  = false;
		if ( $enable_contact_info == true ) {
			$show  = ("no_privacy" == get_option('scm_display_privacy_data'));
			$show |= ("no_addrmailphone_privacy" == get_option('scm_display_addrmailphone_privacy_data'));
			$show |= ( current_user_can( 'activate_plugins' ) );
			wp_get_current_user();
			foreach ( $teamplayers as $teamplayer ) { 
				$memberid = get_post_meta( $teamplayer->ID , '_member' , true );
				$username = get_post_meta( $memberid , '_username' , true );
				$show |= ( $username == $current_user->user_login );
			}
			$show &= is_user_logged_in();
		}
		
        foreach ( $teamplayers as $teamplayer ) {
            $memberid = get_post_meta( $teamplayer->ID , '_member' , true );
			
            $html .= " <div id=\"scm_team_player\">" . get_the_post_thumbnail( $memberid, 'thumbnail' ) . "</div>\n";	
            $html .= " <div id=\"scm_team_player\">" . sprintf("<a href=%s>%s</a>", get_post_permalink( $memberid ), get_the_title( $memberid )) . "</div>\n";	
			if ( $show ) {
				$address    = " <div id=\"scm_team_player_contact\">";		
				$address   .= sprintf("%s %s, %s %s", get_post_meta( $memberid , '_street' , true ), get_post_meta( $memberid , '_number' , true )
							 					    , get_post_meta( $memberid , '_zip' , true ), get_post_meta( $memberid , '_place' , true ) );
				$address   .= "</div>";		
				$mailphone  = " <div id=\"scm_team_player_contact\">";		
				$mailphone .= sprintf("%s, %s %s", get_post_meta( $memberid , '_email' , true )
												 , get_post_meta( $memberid , '_phone' , true ), get_post_meta( $memberid , '_cell' , true ) );
												
				$mailphone .= "</div>";		
				$html .= apply_filters( 'scm_competitor_do_not_display', ($address . $mailphone), $competitor_id);
			}
        }
    }
    $html .= "</div> \n";
	
	return "$before \n" . $html . "$after \n"; 
} 
  