<?php

class UserLog extends AdminTools {

		public function __construct() {

			add_action('loop_start', [ $this, 'store_last_login' ], 10, 2);

			//add_action('wp_logout', [ $this, 'at_user_logged_out' ] );

		}

		public function store_last_login( $current_user ) {

				$current_user = wp_get_current_user();

 				$user = $current_user->user_login;

		    update_user_meta($current_user->ID, 'last_login_timestamp', current_time('mysql', 1));

				update_user_meta($current_user->ID, 'last_ip', $this->at_get_user_ip() );

		}



	 public function at_get_user_ip() {

		 if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {

			 $ip = $_SERVER['HTTP_CLIENT_IP'];

		 } else if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {

			 $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

		 } else {

			 $ip = $_SERVER['REMOTE_ADDR'];

		 }

		 return $ip;

	 }

}

$user_log = new UserLog();
