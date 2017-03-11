<?php

class UserLog extends AdminTools {

		public function __construct() {

			add_action('loop_start', [ $this, 'store_last_login' ], 10, 2);

			add_action( 'wp_footer', [ $this, 'at_tracker' ] );

			// register post type
			include_once( plugin_dir_path( __FILE__ ) . 'post-types/Visitor.php' );

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

		public function at_tracker() {
			//$current_visitor = new Visitor();

			$ip_address = $this->at_get_user_ip();

			$browser = $_SERVER['HTTP_USER_AGENT'];

			$device = $_SERVER['HTTP_USER_AGENT'];

			$os = php_uname();

		// 	$query = new WPQuery( array( 'title' => current_time('mysql', 1) . "-" . $ip_address ) );
		//
		// 	if( null == get_post_by_title( current_time('mysql', 1) . '-' . $ip_address ) ) {
		//   // Set the page ID so that we know the page was created successfully
		//   $post_id = wp_insert_post(
		//     array(
		//       'comment_status'  => 'closed',
		//       'ping_status'   => 'closed',
		//       'post_author'   => 1,
		//       'post_name'   => 'test',
		//       'post_title'    => current_time('mysql', 1) . '-' . $ip_address,
		//       'post_status'   => 'publish',
		//       'post_type'   => 'visitor'
		//     )
		//   );
		//
		// 	update_user_meta($browser, 'browser', current_time('mysql', 1));
		//
		// 	update_user_meta($device, 'device', current_time('mysql', 1));
		//
		// 	update_user_meta($os, 'OS', current_time('mysql', 1));
		// }



			echo '<span style="color: red;">the ip address of the visitor is ' . $ip_address . ' on ' . $device . '</span>';

		}


}

$user_log = new UserLog();
