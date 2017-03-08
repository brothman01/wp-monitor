<?php

class UserLog extends AdminTools {

		public function __construct() {


			if ( null == ( get_option( 'at_users' ) ) ) {

				add_option( 'at_users', '' );

			}

			add_action( 'loop_start', [ $this, 'get_user_information' ] );

		}

		public function get_user_information() {

			if ( is_user_logged_in() ) {

						 $current_user = wp_get_current_user();

							 $timestamp = date('Y-m-d');

							 $at_users_update = get_option( 'at_users' ) . ':' . $current_user->user_login . ',' . $current_user->ID . ',' . $timestamp . ',' . $this->get_user_ip();

							 update_option( 'at_users', $at_users_update );

		}

	 }

	 public function get_user_ip() {

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
