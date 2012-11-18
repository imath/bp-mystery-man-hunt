<?php
/* BuddyBooth functions */


add_action('bp_actions', 'buddybooth_load_cssjs');

function buddybooth_load_cssjs(){
	if( bp_is_user_change_avatar() ) {
		global $bp;
		
		//js
		wp_enqueue_script( 'buddybooth', get_stylesheet_directory_uri() .'/js/buddybooth.js', array('jquery'), "1.0", 1);
		wp_localize_script('buddybooth', 'buddybooth_vars', array(
					'swfurl'            => get_stylesheet_directory_uri() . '/swf/buddybooth.swf',
					'info'              => __('Hit the camera to take a Snapshot, then click the rec button to save your avatar','bp-mystery-man-hunt'),
					'displayeduser_id'  => $bp->displayed_user->id,
					'alternative'       => __('<h3>Or</h3>Use the BuddyBooth !', 'bp-mystery-man-hunt'),
					'errormessage'      => __('Oops, something went wrong', 'bp-mystery-man-hunt'),
					'snapbtn'           => __('Snap shot', 'bp-mystery-man-hunt'),
					'savebtn'           => __('Save shot', 'bp-mystery-man-hunt'),
					'messagesuccess'    => __('Bravo! really nice avatar!', 'bp-mystery-man-hunt'),
					'messagewait'       => __('Please wait for the video to load', 'bp-mystery-man-hunt'),
					'messagealert'      => __('Please take a snapshot first', 'bp-mystery-man-hunt'),
					'noaccess'          => __('You denied camera access..', 'bp-mystery-man-hunt'),
					'messagevideo'      => __('Video stream loaded', 'bp-mystery-man-hunt'),
					'messagewin'        => __('BuddyBooth requires window and navigator objects', 'bp-mystery-man-hunt'),
					'messagectxt'       => __('Html context error', 'bp-mystery-man-hunt'),
					'messageask'        => __('Requesting video stream', 'bp-mystery-man-hunt'),
					'messagenohtmlfive' => __('Your browser does not support getUserMedia()', 'bp-mystery-man-hunt')
				)
			);
			
		//css
		wp_enqueue_style( 'buddybooth-style', get_stylesheet_directory_uri() .'/css/buddybooth.css' );
	}
}



/* as we only allow it fot loggedin users no need for nopriv */

add_action('wp_ajax_buddybooth_save_avatar', 'buddybooth_save_image');

function buddybooth_save_image() {
	
	$img = $_POST['encodedimg'];
	$lv= $_POST['tab'];
	$user_avatar_folder = bp_core_avatar_upload_path() .'/avatars/' . $_POST['user_id'];
	
	if( $img ) {
		
		$img = str_replace('data:image/png;base64,', '', $img);
		$img = str_replace(' ', '+', $img);
		$data = base64_decode($img);

		$original_file = $user_avatar_folder .'/buddybooth-'.$_POST['user_id'].'.png';

		if( !file_exists( $user_avatar_folder ) )
			mkdir( $user_avatar_folder );
			
		$success = file_put_contents( $original_file, $data );
		
	} elseif( $lv ) {
		
		$temp = explode("," ,$lv );
		settype( $temp[1], 'integer' );

		$sortie = imagecreatetruecolor( 150, 150 );

		$k = 0;
		for( $i = 0; $i < 150; $i++ ){
			for( $j = 0; $j < 150; $j++){
		   		imagesetpixel( $sortie, $j, $i, $temp[$k] );
		   		$k++;
			}
		}

		if( !file_exists( $user_avatar_folder ) )
			mkdir( $user_avatar_folder );

		$original_file = $user_avatar_folder .'/buddybooth-'.$_POST['user_id'].'.jpg';
		$success = imagejpeg($sortie, $original_file, 100);
		imagedestroy($sortie);
		
	}
	
	if( $success ) {
		
		$avatar_to_crop = str_replace( bp_core_avatar_upload_path(), '', $original_file );

		bp_core_delete_existing_avatar( array( 'item_id' => $_POST['user_id'],'avatar_path' => bp_core_avatar_upload_path() .'/avatars/' . $_POST['user_id'] ) );

		$crop_args = array( 'item_id' => $_POST['user_id'], 'original_file' => $avatar_to_crop, 'crop_x' => 0, 'crop_y' => 0);

		if( bp_core_avatar_handle_crop( $crop_args ) ) {
			$avatar_url = bp_core_fetch_avatar( array( 'type' => 'full', 'html' => false, 'item_id' => $_POST['user_id'] ) ); 
			
			// making sure the flash request updates the user meta.
			update_user_meta( $_POST['user_id'], 'bpmmh_has_avatar', 1 );
			
			// this hook allow cubepoints to add points
			do_action( 'xprofile_avatar_uploaded' );
			
			echo 'imageUrl='. $avatar_url;
		} else {
			echo 'imageUrl=dang';
		}
		
	} else {
		echo 'imageUrl=dang';
	}
	
	die();
}