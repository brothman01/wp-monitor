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

			$referrer = $_SERVER['HTTP_REFERER'];

			$the_query = new WP_Query( ['post_title'	=>	$ip_address, 'post_type'	=>	'visitor'] );

					if ( empty( $the_array ) ) {
						// wtf??
						$args = array(
			        'post_author' => 1,
			        'post_content' => '',
			        'post_content_filtered' => '',
			        'post_title' => $ip_address,
			        'post_excerpt' => '',
			        'post_status' => 'draft',
			        'post_type' => 'visitor',
			        'comment_status' => '',
			        'ping_status' => '',
			        'post_password' => '',
			        'to_ping' =>  '',
			        'pinged' => '',
			        'post_parent' => 0,
			        'menu_order' => 0,
			        'guid' => '',
			        'import_id' => 0,
			        'context' => '',
			    	);

						wp_insert_post( $args );

				}


		}


}

$user_log = new UserLog();
