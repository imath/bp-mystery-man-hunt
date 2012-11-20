<?php
/* CubePoints functions */

add_filter('bpmmh_avatar_extra_info', 'bpmmh_cubepoints_bp_show_friends', 11, 4 );

function bpmmh_cubepoints_bp_show_friends( $extra_infos, $user_id, $avatar_params, $image ) {
	/* comment here if you want to add this info in other places than bp-show-friend widget */
	if( !strpos( $avatar_params['class'], 'bp-show-friends' ) ) 
		return $extra_infos;
	
	$extra_infos['cubepoints'] = cp_displayPoints( $user_id, 1 );
	return $extra_infos;
	
}

/* Bonus ! order by cubepoints DESC members */
add_action( 'bp_members_directory_order_options', 'bpmmh_cubepoints_filter_option' );
add_action( 'bp_member_blog_order_options', 'bpmmh_cubepoints_filter_option' );

function bpmmh_cubepoints_filter_option() {
	?>
	<option value="cpoints"><?php _e( 'Points', 'bp-mystery-man-hunt' ); ?></option>
	<?php
}

/**
* BP 1.7 introduces a new way to query users
* see class BP_User_Query in buddypress/bp-core/bp-core-classes.php
*/
function bpmmh_is_buddypress_one_point_seven(){
	global $bp;
	
	if( version_compare( $bp->version, '1.7-bleeding-6326', '>=' ) ){
		return true;
	}
	else return false;
}

if( bpmmh_is_buddypress_one_point_seven() ) {
	
	add_action( 'bp_pre_user_query', 'bpmmh_cubepoints_change_query' );
	
} else {
	
	/** We use old method **/


	/**
	* the main trick is to directly apply a filter on the request
	* that BP_Core_User::get_users will play
	*/
	add_filter('bp_core_get_paged_users_sql', 'bpmmh_cubepoints_umeta_filter_select', 10, 2);

	function bpmmh_cubepoints_umeta_filter_select($query, $sql) {
		/* check the meta before modifying the query...*/
		if( !empty($sql['where_meta']) && strpos( $sql['where_meta'], 'cpoints') >= 0 ) {
			/**
			* by default the ORDER BY is on um.meta_value
			* but our meta_value is umm.meta_value, so we need to change this!
			* you can also change the order (DESC or ASC)
			*/ 
			$sql[0] = 'ORDER BY umm.meta_value + 0 DESC';
			$sql['select_meta'] = ', CONVERT(umm.meta_value,UNSIGNED INTEGER) as cubepoints';
			$query = join( ' ', (array)$sql );	
		}

		return $query;

	}


	/**
	* we must also filter the count request for pagination
	* that BP_Core_User::get_users will play
	*/
	add_filter('bp_core_get_total_users_sql', 'bpmmh_cubepoints_umeta_filter_count', 10, 2);

	function bpmmh_cubepoints_umeta_filter_count($query, $sql) {
		/* check the meta before modifying the query...*/
		if( !empty($sql['where_meta']) && strpos( $sql['where_meta'], 'cpoints') >= 0 ) {
			// you can change order by pref (DESC or ASC)
			$sql[1] = 'ORDER BY umm.meta_value + 0 DESC';
			$sql['select_meta'] = '';
			$query = join( ' ', (array)$sql );	
		}

		return $query;
	}

	/**
	* as i play directly on the members directory
	* i need to filter the ajax querystring to add the meta_key argument
	* meta_key=cpoints
	*/
	add_filter( 'bp_dtheme_ajax_querystring', 'bpmmh_cubepoints_dtheme_ajax_query', 10, 7 );

	function bpmmh_cubepoints_dtheme_ajax_query( $query_string, $object, $object_filter, $object_scope, $object_page, $object_search_terms, $object_extras ){

		/* check filter before modifying querystring...*/
		if( $object_filter == 'cpoints' ) {

			if( strpos($query_string, 'type=cpoints&action=cpoints') >= 0 )
				$query_string = str_replace('type=cpoints&action=cpoints', 'meta_key=cpoints', $query_string);

		}

		return $query_string;

	}

}

add_filter( 'bp_show_friends_args', 'bpmmh_cubepoints_order_by_trick', 10, 1);

function bpmmh_cubepoints_order_by_trick( $args ) {
	if( bpmmh_is_buddypress_one_point_seven() )
		$args['type'] = 'cpoints';
		
	else $args['meta_key'] = 'cpoints';

	return $args;
}


/**
* Only in BuddyPress 1.7
**/
function bpmmh_cubepoints_change_query( $q_members ){
	global $wpdb;
	if( $q_members->query_vars['type'] == 'cpoints') {
		if( !empty( $q_members->uid_clauses['where'] ) && !empty( $q_members->query_vars['user_id'] ) )
			$sql['where'][] = str_replace('WHERE u.ID', 'u.user_id', $q_members->uid_clauses['where']);
		
		$q_members->uid_clauses['select'] = "SELECT DISTINCT u.user_id as id FROM {$wpdb->usermeta} u";
		$sql['where'][] = $wpdb->prepare( "u.meta_key = %s", 'cpoints' );
		$q_members->uid_clauses['where'] = ! empty( $sql['where'] ) ? 'WHERE ' . implode( ' AND ', $sql['where'] ) : '';
		$q_members->uid_clauses['orderby'] = ' ORDER BY u.meta_value + 0';
		$q_members->uid_clauses['order'] = ' DESC';
		
		add_filter( 'bp_found_user_query', 'bpmmh_cubepoints_filter_total', 10, 2 );
	}
}

function bpmmh_cubepoints_filter_total( $sql_count, $object ) {
	return str_replace('u.ID', 'u.user_id', $sql_count);
}