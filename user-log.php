<?php

class UserLog extends AdminTools {

		public function __construct() {


			if ( null == ( get_option( 'at_users' ) ) ) {

				add_option( 'at_users', '' );

			}

			add_action('wp_login', 'user_login');

		}

		public function user_login() {

			$current_user = wp_get_current_user();

			$values = 'Username: ' . $current_user->user_login . '<br />';
	     'User email: ' . $current_user->user_email . '<br />';
	     'User first name: ' . $current_user->user_firstname . '<br />';
	     'User last name: ' . $current_user->user_lastname . '<br />';
	     'User display name: ' . $current_user->display_name . '<br />';
	     'User ID: ' . $current_user->ID . '<br />';

			 update_option( 'at_users', $values );

		}

}

$user_log = new UserLog();
