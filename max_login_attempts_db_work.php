<?php

global $mla_db_version;
$mla_db_version = '1.0';

function mla_install() {
	global $wpdb;
	global $mla_db_version;

	$table_name = $wpdb->prefix . 'mla_login_data';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_ip varchar(50) NOT NULL,
		last_attempt_time datetime NOT NULL,
		fail_count int(1) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'mla_db_version', $mla_db_version );
}

function mla_uninstall() {
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'mla_login_data';		
	$sql = "DROP TABLE $table_name";
	$wpdb->query($sql);

	delete_option( 'mla_db_version' );
}

function mla_insert_data($ip) {
	global $wpdb;
			
	$table_name = $wpdb->prefix . 'mla_login_data';

	$an_ip_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE user_ip = %s", $ip ) );

	if($an_ip_row == null) {
		$wpdb->insert( 
			$table_name, 
			array( 
				'user_ip' => $ip, 
				'last_attempt_time' => current_time( 'mysql' ), 
				'fail_count' => 1, 
			) 
		);
	} else {
		$max_try = 4;

		if($an_ip_row->fail_count < $max_try)
			$wpdb->update(
				$table_name, 
				array( 
					'last_attempt_time' => current_time( 'mysql' ), 
					'fail_count' => $an_ip_row->fail_count + 1, 
				), 
				array('id'=>$an_ip_row->id)
			);
	}	
}

function mla_get_fail_count($ip) {
	global $wpdb;
			
	$table_name = $wpdb->prefix . 'mla_login_data';

	$an_ip_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE user_ip = %s", $ip ) );

	return $an_ip_row==null ? 0 : $an_ip_row->fail_count;	
}

function mla_reset_fail_count($ip) {
	global $wpdb;
			
	$table_name = $wpdb->prefix . 'mla_login_data';

	$an_ip_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE user_ip = %s", $ip ) );

	if($an_ip_row != null) {
		$wpdb->update(
			$table_name, 
			array( 
				'fail_count' => 0, 
			), 
			array('id'=>$an_ip_row->id)
		);
	}
}

?>