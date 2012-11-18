<?php
/*** This extend the BuddyPress Profile Progression behavior by including the avatar in the progression 
and adding links to ease the process of trying to be at 100% ***/

function bpmmh_progress_bar_link( $avatar = false ) {
	global $bp;
	
	$user_id = $bp->displayed_user->id;
	
	$user_domain  = bp_core_get_user_domain( $user_id );
	$profile_link = trailingslashit( $user_domain . $bp->profile->slug  );
	
	$avatar_or_profile = !empty( $avatar ) ? 'change-avatar' : 'edit/group/1';
	
	if( $bp->loggedin_user->id == $user_id )
		return trailingslashit( $profile_link . $avatar_or_profile );
	else 
		return $profile_link;
}
	
add_filter( 'bppp_get_profile_percentage_complete', 'bpmmh_a_profile_is_not_complete_without_an_avatar', 10, 3);

function bpmmh_a_profile_is_not_complete_without_an_avatar( $percentage, $fields, $fieldsum) {
	global $bp, $list_empty, $list_ids;
	
	$list_empty = array();
	
	if( 1 == get_user_meta( $bp->displayed_user->id, 'bpmmh_has_avatar', true ) )
		$numerateur += 1;
		
	else
		$list_empty[] = 'Avatar';
		
	$fieldsum += 1;
	
	foreach( $fields as $field ){
		$isset = trim( bp_get_profile_field_data( array( 'field' => $field['name'],  'user_id' => $bp->displayed_user->id ) ) );
		if( !empty( $isset ) )
			$numerateur += (int)$field['points'];
			
		else
			$list_empty[] = $field['name'];
			
		$list_ids[ $field['name'] ] = '#field_'.$field['id'];
	}
	
	
		
	add_filter('bppp_progress_bar', 'bpmmh_include_empty_field_list', 10, 2);
	
	$percent = round( ($numerateur/$fieldsum)*100 );

	return $percent;
}

function bpmmh_include_empty_field_list($image, $percent) {
	global $bp, $list_empty, $list_ids;
	
	if( $bp->loggedin_user->id == $bp->displayed_user->id ){
		var_dump($percent);
		
		$image['msg_after'] = '<div id="empty_fields" style="text-align:left;width:90px;display:none;border: solid 1px #ccc;padding:5px"><p style="margin-bottom:5px">'.__('Update these infos to be at 100% :', 'bp-mystery-man-hunt').'</p><ul>';
		
		if( count($list_empty) > 0 ) {
			foreach( $list_empty as $empty ){
				if( $empty == 'Avatar' )
					$image['msg_after'] .= '<li style="display:block;float:none"><a href="' . bpmmh_progress_bar_link(1) .'" title="'.__('Update','bp-mystery-man-hunt') .' '. $empty .'">'. __('Update','bp-mystery-man-hunt') .' '. $empty .'</a></li>';
				else
					$image['msg_after'] .= '<li style="display:block;float:none"><a href="' . bpmmh_progress_bar_link() . $list_ids[$empty] .'" title="'.__('Update','bp-mystery-man-hunt') .' '. $empty .'">'. __('Update','bp-mystery-man-hunt') .' '. $empty .'</a></li>';
			}
		}
		else
			$image['msg_after'] .= '<li>'. __('Bravo! your profile is complete!', 'bp-mystery-man-hunt') .'</li>';
		
		$image['msg_after'] .= '</ul></div>';
		
		$image['msg_after'] .= '<script type="text/javascript">jQuery(".bppp-stat-bar a").attr("href", "#");jQuery(".bppp-stat-bar a").click(function(){ jQuery("#empty_fields").slideToggle("slow");return false;});';
		
		if( $percent == 0 ){
			$image['url'] = get_stylesheet_directory_uri() . '/images/noprogress_bar.png';
			$image['msg_after'] .= 'jQuery(".bppp-stat-bar").attr("style", "width:100px");';
		}
		
		$image['msg_after'] .='</script>';
	} else {
		$user_domain  = bp_core_get_user_domain( $bp->displayed_user->id );
		$profile_link = trailingslashit( $user_domain . $bp->profile->slug  );
		$image['msg_after'] .= '<script type="text/javascript">jQuery(".bppp-stat-bar a").attr("href", "'.$profile_link.'");</script>';
	}
	
	return $image;
	
}