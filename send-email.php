<?php

class EmailManager extends AdminTools {

		public function __construct() {

			// other stuff
			add_action( 'activity_box_end', [ $this, 'at_send_email' ] );

		}

		public function at_send_email() {

			AdminTools::at_check_for_updates();

			$updates = AdminTools::$updates;

			$admin_email = get_bloginfo('admin_email');

			$subject = 'Updates Available for ' . get_bloginfo( 'url' );

			$installed_plugins = get_plugins();

			// wp_die( print_r( $installed_plugins ) );


			foreach( $installed_plugins as $plugin ) {

				$plugin_name = 'Name: ' . $plugin['Name'];

				$plugin_version = 'Version: ' . $plugin['Version'];

				$repo_plugin_version = $this->get_remote_html();

				echo( $repo_plugin_version .  '<br />');


			}



			$message =
			'<h1>Updates:</h1> ' . "\r\n\r\n" .
			'<ol>' . "\r\n" .
				'	<li>Plugin Updates: ' . $updates['plugins'] . '</li>' . "\r\n" .
				'	<li>Theme Updates: ' . $updates['themes'] . '</li>' . "\r\n" .
				'	<li>WordPress Updates: ' . $updates['WordPress'] . '</li>' . "\r\n" .
				'	<li>PHP Updates: ' . $updates['PHP_update'] . '</li>' . "\r\n" .
			'</ol>';

		//	wp_die();
			//wp_mail( $admin_email, $subject, $message );

		}

		public function get_remote_html() {
			// Check for transient, if none, grab remote HTML file
		 	if ( false === ( $html = get_transient( 'foo_remote_html' ) ) ) {

		                 // Get remote HTML file
		 		$response = wp_remote_get( 'https://api.wordpress.org/plugins/info/1.0/timeline-express' );

		                        // Check for error
		 			if ( is_wp_error( $response ) ) {
		 				return;
		 			}

		                 // Parse remote HTML file
		 		$data = wp_remote_retrieve_body( $response );

				unset( $data['sections'] );

				$data = json_decode( wp_remote_retrieve_body( $response ), true );

		      // Check for error
		 			if ( is_wp_error( $data ) ) {
		 				return;
		 			}

		    // Store remote HTML file in transient, expire after 24 hours
		 		set_transient( 'foo_remote_html', $data, 24 * HOUR_IN_SECONDS );

		 	}


		 	return $html;

		}



}

$email_manager = new EmailManager();
