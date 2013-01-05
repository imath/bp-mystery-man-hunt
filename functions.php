<?php

/* languages : this child theme is available in english and french */
add_action( 'after_setup_theme', 'bpmmh_setup' );

function bpmmh_setup() {
	load_theme_textdomain( 'bp-mystery-man-hunt', get_stylesheet_directory() . '/languages' );
}

/* Avatar tricks */


/**
* Part 1 of tutorial explains how works bp_core_fetch_avatar
* You can read it (using google translate as it's in french) here :
* http://imathi.eu/2012/11/18/buddypress-avatar-management/
*/

/** Part 2 of tutorial : neutralize gravatar, set xprofile Service avatar, add extra info under avatar **/

/* Uncomment to neutralize gravatar */
//add_filter('bp_core_fetch_avatar_no_grav', 'bpmmh_no_gravatar', 10, 1);

function bpmmh_no_gravatar( $nograv ){
	return true;
}

add_filter( 'bp_core_default_avatar_user', 'bpmmh_get_service_avatar', 10, 2 );

function bpmmh_get_service_avatar( $avatar, $params) {
	
	// $params contains all the arguments sent to bp_core_fetch_avatar
	$user_id = $params['item_id'];
	$service = xprofile_get_field_data('Service', $user_id );
	
	// if no service is found, then mystery man is the man !
	if( empty( $service ) )
		return $avatar;
	
	// we have a service no let's try to get it
	$avatar_service = get_stylesheet_directory() . '/images/' . $service .'.png';
	
	if( file_exists( $avatar_service ) )
		$avatar = get_stylesheet_directory_uri() . '/images/' . $service .'.png';
	
	return $avatar;
}

function bpmmh_add_avatar_extra_info( $image, $params, $item_id, $avatar_dir, $css_id, $html_width, $html_height, $avatar_folder_url, $avatar_folder_dir ) {
	
	if( $params['object'] != "user") return $image;
	
	$image = apply_filters( 'bpmmh_check_mysterious', $image, $params, $item_id );
	
	if(!$params['width']){
		if($params['type']==thumb){
			$width_mb = 55;
		}
		if($params['type']==full){
			$width_mb = 155;
		}
	}
	//if image is too small then no banner
	elseif($params['width'] < 40) return $image;
	else{
		$width_mb = $params['width']+5;
	}
	
	$default = array();
	$extra_infos = apply_filters('bpmmh_avatar_extra_info', $default, $item_id, $params, $image );
	
	if( count( $extra_infos ) > 0 ) {
		foreach( $extra_infos as $k => $v ){
			if( !empty( $v ) )
				@$extra_info .= '<li class="'.$k.'">' . $v . '</li>' ;
		}
	}
	else return $image;
	
	return '<div class="avatar_container" style="width:'.$width_mb.'px;">'.$image.'<ul class="avatar_info">'.$extra_info.'</ul></div>';

}

add_filter('bp_core_fetch_avatar', 'bpmmh_add_avatar_extra_info', 1, 9 );


add_filter('bpmmh_avatar_extra_info', 'bpmmh_xprofile_add_info_under_avatar', 10, 4);

function bpmmh_xprofile_add_info_under_avatar( $extra_infos, $user_id, $avatar_params, $image ) {
	
	if( strpos( $avatar_params['class'], 'bp-show-friends' ) > 0 ) 
		return $extra_infos;
	
	$avatar_folder_dir = get_stylesheet_directory() .'/images';
	
	if ( file_exists( $avatar_folder_dir ) ) {

		// Open directory
		if ( $av_dir = opendir( $avatar_folder_dir ) ) {

			// Stash files in an array once to check for one that matches
			$avatar_files = array();
			while ( false !== ( $avatar_file = readdir( $av_dir ) ) ) {
				// Only add files to the array (skip directories)
				if ( 2 < strlen( $avatar_file ) ) {
					$avatar_files[] = get_stylesheet_directory_uri() . '/images/'.$avatar_file;
				}
			}
		}
	}
	
	if( is_array( $avatar_files ) && count( $avatar_files ) > 0 ) {
		preg_match('/src="([^"]*)"/i', $image, $match);
		
		// if we have an avatar, we use it so we don't need to add Service extra info
		if( in_array( $match[1], $avatar_files ) )
			return $extra_infos;
	}
	
	$extra_infos['profile'] = xprofile_get_field_data('Service', $user_id );
	return $extra_infos;
}

/** Part 3 of tutorial : Challenge user thanks to cubepoints / BuddyPress cubepoints integration and BP Profile Progression **/

/** 
* if bp_cubepoint_init exists then CubePoints & BP Integration are loaded
* we can extend it !
*/

if( function_exists( 'bp_cubepoint_init' ) ) {
	require( dirname( __FILE__ ) . '/includes/cubepoints.php' );
}

/** 
* if bppp_init exists then BP Profile Progression is loaded
* we can extend it !
*/

if( function_exists( 'bppp_init' ) ) {
	require( dirname( __FILE__ ) . '/includes/profile-progression.php' );
}


/** 
* ugly animated gif trick..
*/
add_filter('bpmmh_check_mysterious', 'bpmmh_user_is_mysterious', 10, 3);

function bpmmh_user_is_mysterious( $image, $params, $item_id ) {
	global $bp;
	
	$is_no_avatar = apply_filters('bp_core_fetch_avatar_no_grav', false);
	
	$has_gravatar = false;
	
	if( !$is_no_avatar )
		$has_gravatar = bpmmh_is_a_gravatar_user( $item_id );
	
	if( strpos( $image, 'mystery-man.jpg' ) > 0 && $item_id == $bp->loggedin_user->id ) {
		
		if( !$is_no_avatar && $has_gravatar )
			return $image;
		
		$image = preg_replace('/src="([^"]*)"/i', 'src="' .get_stylesheet_directory_uri() .'/images/mysterious-man.gif"', $image );
		$image = preg_replace('/class="([^"]*)"/i', 'class="avatar user-' .$item_id .'-avatar mysterious"', $image );
   	}

	if( !$is_no_avatar && $item_id != $bp->loggedin_user->id && !$has_gravatar ) {
		$service_url = bpmmh_get_service_avatar( false, $params);
		
		if( !empty( $service_url ) )
			$image = preg_replace('/src="([^"]*)"/i', ' src="' .$service_url.'"', $image );
	}
	
	return $image;
}

function bpmmh_is_a_gravatar_user( $item_id ){
	$gravatar_checked = get_transient('gravatar_checked_'.$item_id );
	
	if( $gravatar_checked == 1 ) {
		if( 1 == get_user_meta( $item_id, 'bpmmh_has_avatar', true ) )
			return true;

		else return false;
	}

	$email = bp_core_get_user_email( $item_id );
	$hash = md5( strtolower( $email ) );

	$url = 'http://www.gravatar.com/' . $hash .'.php';
	$request = new WP_Http;
	$result = $request->request( $url );
	
	if( $result->errors )
		return false;
		
	$profile = unserialize( $result['body'] );
	
	set_transient('gravatar_checked_'.$item_id, 1, 60 * 60 * 12);

	if ( is_array( $profile ) && isset( $profile['entry'] ) ){
		if($profile['entry'][0]['displayName'] != "gravatarmysteryman"){
			update_user_meta( $item_id, 'bpmmh_has_avatar', 1);
			return true;
		}
		else return false;
	}
	else return false;
}

add_action('bp_actions', 'bpmmh_load_js', 11);

function bpmmh_load_js(){
	wp_enqueue_script( 'bp-mm-hunt-js', get_stylesheet_directory_uri() .'/js/bp-mm-hunt.js', array('jquery'), "1.0", 1);
}


/** Part 4 of tutorial : Meet BuddyBooth **/

/** 
* BuddyBooth!
* comment to neutralize
*/
require( dirname( __FILE__ ) . '/includes/buddybooth.php' );


/* listening to avatar uploads and deletion */
add_action('xprofile_avatar_uploaded', 'bpmmh_avatar_uploaded' );

function bpmmh_avatar_uploaded(){
	global $bp;
	
	update_user_meta( $bp->displayed_user->id, 'bpmmh_has_avatar', 1 );
}


add_action( 'bp_core_delete_existing_avatar', 'bpmmh_avatar_removed');

function bpmmh_avatar_removed(){
	global $bp;
	
	delete_user_meta( $bp->displayed_user->id, 'bpmmh_has_avatar' );
}


/** Part 5 of tutorial : Taking care of groups and blogs **/

/**
* Bowe's trick
* found on http://bp-tricks.com/snippets/code/setting-default-avatars-groups-member-pages/ 
*/
function my_default_get_group_avatar($avatar) {
	global $bp, $groups_template;
	
	if( strpos($avatar,'group-avatars') ) {
		return $avatar;
	}
	else {
		$custom_avatar = get_stylesheet_directory_uri() . '/images/default-group.png';

		if($bp->current_action == "")
			return '<img width="'.BP_AVATAR_THUMB_WIDTH.'" height="'.BP_AVATAR_THUMB_HEIGHT.'" src="'.$custom_avatar.'" class="avatar" alt="' . attribute_escape( $groups_template->group->name ) . '" />';
		else
			return '<img width="'.BP_AVATAR_FULL_WIDTH.'" height="'.BP_AVATAR_FULL_HEIGHT.'" src="'.$custom_avatar.'" class="avatar" alt="' . attribute_escape( $groups_template->group->name ) . '" />';
	}
}

add_filter( 'bp_get_group_avatar', 'my_default_get_group_avatar');

/**
* if Gravatar is neutralized you can uncomment this trick and comment Bowe's one
*/
//add_filter( 'bp_core_default_avatar_group', 'bpmmh_get_group_avatar', 10, 2 );

function bpmmh_get_group_avatar( $avatar, $params) {
	
	$avatar_url = get_stylesheet_directory_uri() . '/images/mysterious-man.gif';
	
	return $avatar_url;
}

/* And finally Blogs avatar ! */

add_filter('bp_get_blog_avatar', 'bpmmh_handle_blog_avatar', 3, 9 );

function bpmmh_handle_blog_avatar( $avatar, $blog_id, $admin_avatar_args = false ) {
	global $blogs_template;
	
	switch_to_blog( $blog_id );
	
	if( isset( $blogs_template->blog->latest_post->guid ) && !empty( $blogs_template->blog->latest_post->guid) ) {
		
		$latest_post = explode('?p=', $blogs_template->blog->latest_post->guid );
		$post_id = intval( $latest_post[1] );
		
		if( has_post_thumbnail( $post_id ) ) {
		
			$thumbnail_id = get_post_thumbnail_id( $post_id );
			$vignette = wp_get_attachment_image_src( $thumbnail_id, array(50, 50) );
		}
		
	} 
	
	if( !$vignette || empty( $vignette[0] ) ){
		
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => 5
		);

		$attachment_ids = array();
		$blog_posts = get_posts( $args );


		foreach( $blog_posts as $attachment ) {
			
			if( strpos( $attachment->post_mime_type, 'image' ) !== false && $attachment->post_parent > 0 ) {
				$attachment_ids[] = $attachment->ID;
			}
		}

		$vignette = wp_get_attachment_image_src( $attachment_ids[0], array(50, 50) );
		
	}
	
	restore_current_blog();
	
	if( !empty( $vignette[0] ) )
		return '<img src="'. $vignette[0] .'" alt="Blog avatar" class="avatar blog-1-avatar" width="'.$vignette[1].'" height="'.$vignette[1].'">';
	
	else
		return $avatar;

}

?>