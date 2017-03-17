<?php

class EmailManager extends AdminTools {

	public $at_updates;

	public static $options;


		public function __construct() {

				$this->at_updates = get_option( 'at_update_info', false );

			// actions
			add_action( 'at_send_email', [ $this, 'at_send_email' ] );

			$this->init();

		}

		public function init() {

			$at_how_often = parent::$options['at_how_often'];

			//wp_die( $at_how_often );

			$prevent_email_cron = get_option( 'at_prevent_email_cron' );

			// schedule crontask if it has not already been scheduled
		// if ( 0 == $prevent_email_cron ||  ( 0 == $prevent_email_cron & $at_how_often != get_option( parent::$options['at_how_often'] ) ) ) {
		//
		// 	wp_schedule_event( time(), $at_how_often, 'at_send_email' );
		//
		// 	update_option( 'at_prevent_email_cron', 1 );
		//
		// }
		//
		// $current_frequency = wp_get_schedule( 'at_send_email' );
		//
		// if ( $current_frequency == 'never' ) {
		//
		// 	wp_clear_scheduled_hook( 'at_send_email' );
		//
		// }
		//
		//
		// if ( $current_frequency <> $at_how_often ) {
		//
		// 	wp_clear_scheduled_hook( 'at_send_email' );
		//
		// 	wp_schedule_event( time(), $at_how_often, 'at_send_email' );
		//
		// 	update_option( 'testing', 'wp_schedule_event( time()' . ', \'' . $at_how_often . '\', ' . '\'at_send_email\'' .  ' );' );
		//
		// 	update_option( 'at_prevent_email_cron', 1 );
		//
		//  }

		}

		public function at_send_email() {

			$options = get_option( 'at_options' );

			$admin_email = get_bloginfo('admin_email');

			$subject = 'Updates Available for ' . get_bloginfo( 'url' );

			if ( ! function_exists( 'get_plugins' ) ) {

				require_once ABSPATH . 'wp-admin/includes/plugin.php';

			}

			$plugins_that_need_updates = $this->get_plugins_that_need_updates( get_plugins() );

			update_option( 'at-testing', $plugins_that_need_updates );

			$themes_that_need_updates = $this->get_themes_that_need_updates( wp_get_themes() );


			$message =
			'<html>
				<head>
				<style>
						table {
							font-family: arial, sans-serif;
							border-collapse: collapse;
						}

						td, th {
							border: 1px solid #dddddd;
							text-align: left;
							padding: 8px;
						}

						tr:nth-child(even) {
							background-color: #dddddd;
						}
				</style>
				</head>

				<body>

			<center>
			<h1>' . 'Updates for ' . get_bloginfo( 'url' ) . '</h1>
			<table style="width: 700px;">' .

				'	<thead>
					<tr>
						<th>Update</th>
						<th>Details</th>
					</tr>
					</thead>';

					if ( $options['at_check_plugins'] == true ) {

					$message .= '<tr>
						<td>' . $this->at_updates['plugins'] . ' Plugin Update(s)</td>
						<td>';

					if ( $this->at_updates['plugins'] >= 1 ) {


						foreach( $plugins_that_need_updates as $plugin) {

							$message .= $plugin . "\r\n";

						}

					}

					$message .=
						'</td>
					</tr>';

				}



					if ( $options['at_check_themes'] == true ) {

					$message .=
				'	<tr>
						<td>' . $this->at_updates['themes'] . ' Theme Update(s)</td>
						<td>';

				if ( $this->at_updates['themes'] >= 1 ) {

						foreach( $themes_that_need_updates as $theme) {

							$message .= $theme . "\r\n";

						}

				}

					$message .=
						'</td>
					</tr>';

					}

					if ( $options['at_check_wordpress'] == true ) {

				$message .=
					'<tr>
						<td>' . $updates['WordPress'] . ' WordPress Update(s)'  . '</td>
				 		<td>' . $this->wp_update_message( $updates['WordPress'] ) . '</td>
					</tr>';

				}

				if ( $options['at_check_php'] == true ) {

				$message .=
				'<tr>
					<td>'. $updates['PHP_update'] . ' PHP Update(s)' . '</td>
					<td>' . 'PHP ' . phpversion() . ' supported until ' . date("m-d-Y", $this->at_updates['PHP_warning']) . '.' . '</td>
				</tr>';

			}

			if ( $options['at_check_ssl'] == true ) {

				$message .=
				'<tr>
					<td>' . 'SSL' . '</td>
					<td>' . $this->ssl_status($this->at_updates['SSL'] ) . '</td>
				</tr>';

			$message .=
			'</table>
			</center>';

		}

			$message .=
			'</body>
			</html>';

			$headers = "MIME-Version: 1.0\r\n";

			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";


			wp_mail( $admin_email, $subject, $message, $headers);

		}

		public function ssl_status( $on ) {

			if ( $on ) {

				return 'On';

			} else {

				return 'Off';

			}

			return;

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

					update_option( 'yhrstgf', $installed_plugins );

					$plugins_that_need_updates = array();

					foreach( $installed_plugins as $plugin ) {

						$plugin_name = $plugin['Name'];

						$plugin_version = $plugin['Version'];

						$repo_plugin = $this->get_plugin_info( 'https://api.wordpress.org/plugins/info/1.0/' . $plugin['TextDomain'] );

						if ( empty( $repo_plugin ) ) {

							continue;

						}

						if ( version_compare( $plugin_version, $repo_plugin[ 'version' ], '<' ) ) {

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
