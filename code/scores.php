<?php 

function scm_comp_get_result_match( $result ) {
    $valid = $home_pts = $away_pts = $home_sets = $away_sets = $draw_sets = 0;
	// filter comments first
	$state = "start";
	$error = 0;
	for ( $h = 0; $h != strlen( $result ); $h++ ) {
		if ( $result[ $h ] == "(" ) {
			$result[ $h ] = " ";
			if ( $state == "start" ) {
				$state = "comment";
			} else {
				$error++;
			}
		} else if ( $result[ $h ] == ")" ) {
			$result[ $h ] = " ";
			if ( $state == "comment" ) {
				$state = "start";
			} else {
				$error++;
			}
		} else if ( $state == "comment" ) {
			$result[ $h ] = " ";			
		}
	} 
	if ( ( $state == "start" ) && ( $error == 0 ) ) {
		$sets = explode( '/', $result);
		foreach ($sets as $set) {
			$homeaway = explode( '-', $set );
			if ( count( $homeaway ) == 2 ) {
				$home = trim( $homeaway[0] );
				$away = trim( $homeaway[1] );
				if ( is_numeric( $home ) && is_numeric( $away ) ) {
					$home_pts += $home;
					$away_pts += $away;
					if ( $home > $away ) {
						$home_sets++;
					}
					if ( $home < $away ) {
						$away_sets++;
					}
					if ( $home == $away ) {
						$draw_sets++;
					}
				} else {
					$error++;
				}
			} else {
				$error++;
			}
		}
		if ( ($home_sets + $away_sets + $draw_sets) != count( $sets ) ) {
			$error++; 
		}
	}
	
	if ( $error == 0 ) {
		$valid = 1;
	}
	
    return array ( "valid" => $valid, "home_sets" => $home_sets, "away_sets" => $away_sets , "home_pts" => $home_pts, "away_pts" => $away_pts );
}

