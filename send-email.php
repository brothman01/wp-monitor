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

			$plugins_that_need_updates = $this->get_plugins_that_need_updates( get_plugins() );

			$themes_that_need_updates = $this->get_themes_that_need_updates( wp_get_themes() );


			$message =
			'<h1>Updates:</h1> ' . "\r\n\r\n" .
			'<ol>' . "\r\n" .
				'	<li>Plugin Updates: ' . $updates['plugins'] . '</li>' . "\r\n";

					if ( AdminTools::$updates['plugins'] >= 1 ) {


						foreach( $plugins_that_need_updates as $plugin) {

							$message = $message . '	- ' . $plugin . "\n\n";

						}


					} else {

						$message = $message . "\n\n";

					}

				$message = $message .
				'	<li>Theme Updates: ' . $updates['themes'] . '</li>' . "\r\n";

				if ( AdminTools::$updates['themes'] >= 1 ) {

					foreach( $themes_that_need_updates as $theme) {

						$message = $message . '	- ' . $theme . "\n\n";

					}

			} else {

				$message = $message . "\n\n";

			}


				$message = $message .
				'	<li>WordPress Updates: ' . $updates['WordPress'] . '</li>' . "\r\n" .
				'	- ' . $this->wp_update_message( $updates['WordPress'] ) . "\r\n\r\n";

				$message = $message .
				'	<li>PHP Updates: ' . $updates['PHP_update'] . '</li>' . "\r\n" .

				'	' . '- PHP ' . phpversion() . ' supported until ' . date("m-d-Y", AdminTools::$updates['PHP_warning']) . '.' . "\r\n" .

			'</ol>';

			wp_mail( $admin_email, $subject, $message );

		}



		public function get_plugin_info( $slug ) {

					if ( false !== ( $data = get_transient( $slug . '_remote_html' ) ) ) {

						return $data;

					}

					$response = wp_remote_get( $slug );

					if ( is_wp_error( $response ) ) {

						return;

					}

					$data = (array) maybe_unserialize( wp_remote_retrieve_body( $response ) );

					// Check for error
					if ( is_wp_error( $data ) ) {

						return;

					}

					unset( $data['sections'] );

					// Store remote HTML file in transient, expire after 24 hours
					set_transient( $slug . '_remote_html', $data, 24 * HOUR_IN_SECONDS );

					return $data;

				}

		public function get_theme_info( $slug ) {
				// Make request and extract plug-in object

		$response = wp_remote_post( 'http://api.WordPress.org/themes/info/1.0/', [
			'body' => [
				'action' => 'theme_information',
				'request' => serialize( (object) [
					'slug' => $slug
				] ),
			],
		] );

		$wow = maybe_unserialize( wp_remote_retrieve_body( $response ) );

		//print_r( $response );
		return $wow;

		}


		public function wp_update_message( $updates ) {

			if ( $updates == 0 ) {

				return 'WordPress core is up to date.';

			} else {

				return 'Update WordPress immediately.';

			}

		}



				public function get_plugins_that_need_updates( $installed_plugins ) {

					$skip_plugins = array( 'BR Options Page', 'Home Slider', 'Invalid Login Redirect', 'PHP Notifier' );

					$plugins_that_need_updates = array();

					foreach( $installed_plugins as $plugin ) {

						$plugin_name = $plugin['Name'];

						// print_r( $plugin_name . '<br />' );

						if ( in_array( $plugin_name, $skip_plugins, true ) ) {

							continue;

						}


						$plugin_version = $plugin['Version'];

						$slug = sanitize_title( $plugin['Name'] );

						$repo_plugin = $this->get_plugin_info( 'https://api.wordpress.org/plugins/info/1.0/' . $slug );

						if ( empty( $repo_plugin ) ) {

							continue;

						}

						// print_r( $repo_plugin[ 'name' ] . $repo_plugin[ 'version' ] . '<br />');



						if ( version_compare($plugin_version, $repo_plugin[ 'version' ], '<') ) {

							array_push( $plugins_that_need_updates, $plugin_name );

						}


					}

					return $plugins_that_need_updates;

				}


				public function get_themes_that_need_updates( $installed_themes ) {

					$a_theme = array_slice( $installed_themes, 0, 1 );

					// print_r( array_slice( $a_theme, 0, 1 ) );

					$themes_that_need_updates = array();

					foreach( $installed_themes as $theme ) {

						$theme_name = $theme->get( 'Name' );

						// print_r( $plugin_name . '<br />' );

						$theme_version = $theme->get( 'Version' );

						$slug = $theme->get( 'TextDomain' );

						$repo_theme = $this->get_theme_info( $slug );

						if ( empty( $repo_theme ) ) {

							continue;

						}



						if ( version_compare( $theme_version, $repo_theme->version, '<' ) ) {
						//
							array_push( $themes_that_need_updates, $theme_name );

							//print_r( $theme_name . ': ' . $repo_theme->version . '<br />');
						//
						}


					}

					return $themes_that_need_updates;

				}


}

$email_manager = new EmailManager();
