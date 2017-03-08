<?php
/*
 * Plugin Name: Admin Tools
 * Description: Notify user when updates to WordPress are needed.
 * Version:     1.0.0
 * Author:      Ben Rothman
 * Author URI:  http://www.BenRothman.org
 * License:     GPL-2.0+
 */

class AdminTools {

	public static $updates;

	public static $options;

	public function __construct() {

		// get option 'at_options' value from the database and put it in the array $options
		self::$options = get_option( 'at_options', [
			'at_prevent_email_cron' => true,
			'at_user_timeout' => '',
			'at_send_email' => false,
			'at_check_plugins' => false,
			'at_check_themes' => false,
			'at_check_wordpress' => false,
			'at_check_php' => false,
		] );

		// check for updates
		add_action( 'admin_bar_menu', [ $this, 'at_check_for_updates' ] );

		// add the options page
		add_action( 'admin_menu', [ $this, 'at_add_plugin_page' ] );

		// build options page
		add_action( 'admin_init', [ $this, 'at_settings_init' ] );

		// include other files
		include_once( plugin_dir_path( __FILE__ ) . 'user-log.php' );

		// other stuff
		$this->init();

	}

	public function init() {

		// get the option that is set when the crontask is scheduled
		$prevent_email_cron = self::$options['at_prevent_email_cron'];

		// schedule crontask if it has not already been scheduled
		if ( 0 == $prevent_email_cron ) {

				wp_schedule_event(time(), 'daily', 'send_my_updates_notification');

				//set the option to say the crontask has already been scheduled
				self::$options['at_prevent_email_cron'] = true;

				update_option( 'at_options', self::$options );

	}

		// add action to send email when cron task is triggered
		add_action( 'send_my_updates_notification', [ $this, 'un_send_email' ] );


		// enqueue the admin stylesheet
		add_action( 'admin_enqueue_scripts', [ $this, 'at_enqueue_admin_styles' ] );

		// dashboard widget
		add_action( 'admin_footer', [ $this, 'at_custom_dashboard_widget' ] );


	}

	function at_custom_dashboard_widget() {
	// Bail if not viewing the main dashboard page
	if ( get_current_screen()->base !== 'dashboard' ) {
		return;
	}
	?>

	<div id="custom-id" class="welcome-panel" style="display: none;">

		<?php $this->at_dashboard_callback(); ?>

	</div>

	<script>
		jQuery(document).ready(function($) {

			$('#welcome-panel').after($('#custom-id').show());

		});
	</script>

<?php }

	public function at_add_plugin_page() {

			 // 1. Add the settings page
			 add_options_page(
				 'Options Page', // page title
					'Admin Tools', // menu title
					'manage_options', // capability required of user
					'options_page', // menu slug
					[ $this, 'create_admin_page' ] // callback function
				);

	}

	public function at_settings_init() {

		register_setting( // (actually a settings group)
			'at_options_group',                 // group name
			'at_options',          // option name
			[ $this, 'at_sanitize' ]  // validation callback
		);

		$this->dashboard_section();


		add_settings_section(
			'general_section_id', // id for use in id attribute
			'General Settings', // title of the section
			[ $this, 'at_general_section_callback' ], // callback function
			'options_page' // page
		);


					add_settings_field(
						'at_user_timeout',      // id
						'How Long Until A User Is Logged Out For Inactivity?',              // setting title
						[ $this, 'at_user_timeout_callback' ],    // display callback
						'options_page',                 // settings page
						'general_section_id'                  // settings section
					);

		// 2. Add the section to the setting page
		add_settings_section(
			'options_br_id', // id for use in id attribute
			'Email Settings', // title of the section
			[ $this, 'at_email_section_callback' ], // callback function
			'options_page' // page
		);

						// 5. Add each settings field
						// add_settings_field(
						// 	'at_settings1',      // id
						// 	'Text Field',              // setting title
						// 	[ $this, 'at_text_field_callback' ],    // display callback
						// 	'options_page',                 // settings page
						// 	'options_br_id'                  // settings section
						// );

						add_settings_field(
							'at_send_email',      // id
							'Send Email?',              // setting title
							[ $this, 'at_send_email_callback' ],    // display callback
							'options_page',                 // settings page
							'options_br_id'                  // settings section
						);

						add_settings_field(
							'at_check_plugins',      // id
							'Check Plugins?',              // setting title
							[ $this, 'at_check_plugins_callback' ],    // display callback
							'options_page',                 // settings page
							'options_br_id'                  // settings section
						);

						add_settings_field(
							'at_check_themes',      // id
							'Check Themes?',              // setting title
							[ $this, 'at_check_themes_callback' ],    // display callback
							'options_page',                 // settings page
							'options_br_id'                  // settings section
						);

						add_settings_field(
							'at_check_wordpress',      // id
							'Check WordPress?',              // setting title
							[ $this, 'at_check_wordpress_callback' ],    // display callback
							'options_page',                 // settings page
							'options_br_id'                  // settings section
						);

						add_settings_field(
							'at_check_php',      // id
							'Check PHP?',              // setting title
							[ $this, 'at_check_php_callback' ],    // display callback
							'options_page',                 // settings page
							'options_br_id'                  // settings section
						);

		}

		public function dashboard_section() {

			add_settings_section(
				'options_dashboard_id', // id for use in id attribute
				'Site Status', // title of the section
				[ $this, 'at_dashboard_callback' ], // callback function
				'options_page' // page
			);

		}

	public function at_check_for_updates() {

		if ( ! current_user_can( 'install_plugins' ) ) {

			return;

		}
		// get update data (only after role of user has been checked)
			$update_data = wp_get_update_data();

			self::$updates = array(
				'plugins'	=>	$update_data['counts']['plugins'],
				'themes'	=>	$update_data['counts']['themes'],
				'WordPress'	=>	$update_data['counts']['themes'],
				'PHP' => phpversion(),
			);

			// print_r( self::$updates );

	}


	public function un_send_email() {

		// send email about selected updates here by building the email based on the options
		$watched_updates = 0;

		$message = 'There are updates available for ' . get_option( 'siteurl' ) . ' available.' . "\r\n";

		if ( get_option( 'at_option1' ) ) {

			$watched_updates = $watched_updates + self::$updates['plugins'];

			$message = $message . self::$updates['plugins'] . ' plugins.' . "\r\n";

		}

		if ( get_option( 'at_option2' ) ) {

			$watched_updates = $watched_updates + self::$updates['themes'];

			$message = $message . self::$updates['themes'] . ' themes.' . "\r\n";

		}

		if ( get_option( 'at_option3' ) ) {

			$watched_updates = $watched_updates + self::$updates['WordPress'];

			$message = $message . self::$updates['WordPress'] . ' WordPress Core Updates.' . "\r\n";

		}

		if ( $watched_updates > 0 ) {

			wp_mail( get_option( 'admin_email' ), $watched_updates . 'for ' . get_option( 'siteurl' ) . ' available!', $message);

		}

	}

	// 3. Build the setting page with this callback
			public function create_admin_page() {
			 				?>
							<div class="wrap">
								<h1>Admin Tools</h1>
								<form method="post" action="options.php"> <!-- the action needs to be 'options.php' -->
									<?php
										//printf('<div class="notice notice-info is-dismissible"><p>test</p></div>');

										settings_fields( 'at_options_group' );

										do_settings_sections( 'options_page' ); // 4. add the page sections to the page (by entering the page name!)

										submit_button();
										?>
									</form>
								</div>
								<?php
			}

			public function at_sanitize( $input ) {

				// create an empty 'clean' array
				$valid = array();

				// add the cleaned values of each field to the clean array on submit
				// $valid['at_settings1'] = empty( $input['at_settings1'] ) ? '' : sanitize_text_field( $input['at_settings1'] );

				$valid['at_prevent_email_cron'] = (bool) empty( $input['at_prevent_email_cron'] ) ? false : true;

				$valid['at_user_timeout']       	=  isset( $input['at_user_timeout'] ) ? $input['at_user_timeout'] : '0.05.00';

				$valid['at_send_email']       	= (bool) empty( $input['at_send_email'] ) ? false : true;

				$valid['at_check_plugins']       	= (bool) empty( $input['at_check_plugins'] ) ? false : true;

				$valid['at_check_themes']       	= (bool) empty( $input['at_check_themes'] ) ? false : true;

				$valid['at_check_wordpress']      = (bool) empty( $input['at_check_wordpress'] ) ? false : true;

				$valid['at_check_php']      = (bool) empty( $input['at_check_php'] ) ? false : true;


				// return the clean array
				return $valid;

			}

			public function at_dashboard_callback() {

					echo '<div id="dashboard_main">

					<h1 style="text-align: center;">Site Status:</h1>



					<div class="twothirds">

						<div class="onequarter cell">
						<h3 style="text-align: center;">Plugins:</h3>

							<div class="guage">
								<div class="guage_filling">&nbsp;' . ( sizeof( get_plugins() ) - self::$updates['plugins'] ) . ' / ' . sizeof( get_plugins() ) .

								'</div>
							</div>

						</div>

						<div class="onequarter cell">
						<h3 style="text-align: center;">Themes:</h3>

							<div class="guage">
								<div class="guage_filling">&nbsp;' . sizeof( wp_get_themes() ) .
								'</div>
							</div>

						</div>

						<div class="onequarter cell">
						<h3 style="text-align: center;">WordPress Core:</h3>

							<div class="guage">
								<div class="guage_filling">&nbsp;' . self::$updates['WordPress'] . '
								</div>
							</div>

						</div>

						<div class="onequarter cell">
						<h3 style="text-align: center;">PHP:</h3>

							<div class="guage">
								<div class="guage_filling">&nbsp;' . self::$updates['PHP'] .
								'</div>
							</div>

						</div>



						<div class="onethird cell">
						<h3 style="text-align: center;">SSL:</h3>

							<div class="guage">
								<div class="guage_filling">&nbsp;' . $this->sslCheck() .
								'</div>
							</div>

						</div>

						<div class="onethird cell">
						<h3 style="text-align: center;">???:</h3>

							<div class="guage">
								<div class="guage_filling">&nbsp;
								</div>
							</div>

						</div>

						<div class="onethird cell">
						<h3 style="text-align: center;">???:</h3>
						test
						</div>

						</div>





						<div class="onethird" style="border: solid green 1px;">

						<div class="half">

								<table class="wp-list-table widefat fixed striped">

							<thead>
								<tr>
									<th>Username</th>
									<th>Date/Time</th>
									<th>IP Address</th>
								</tr>
							</thead>';

							 $at_users = get_option( 'at_users' );

							 $at_users_entries = preg_split( '/[\s:]+/', $at_users );

							 $display_counter = 0;

							 foreach ( $at_users_entries as &$user) {

								 $user_data = preg_split ("/[\s,]+/", $user); ;

								 if ( $display_counter < 12 ) {

									echo '<tr>' .
									'<th>' . '<a href="' . get_edit_user_link( $user_data[1] ) . '">' . $user_data[0] . '</a>' . '</th>' .
									'<th>' . $user_data[2] . '</th>' .
									'<th>' . $user_data[3] . '</th>' .
									'</tr>';

									$display_counter++;

								} else {

									return;

								}

							 }

						echo '</table>

						</div>

						<div class="half" style="background: black;">

						test text sheebarl

						</div>



						</div>

					</div>';

			}

			public function sslCheck() {

				if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on') {

    			return 'SSL Not Installed';

				} else {

					return 'SSL Installed';

				}

			}

			public function at_general_section_callback() {

				echo 'Edit the settings for the plugin here.';

			}

			public function at_email_section_callback() {

				echo 'Edit the settings for the email here.';

			}

			public function at_user_timeout_callback() {

				// print the HTML to create the field
				printf(
					'<input id="at_user_timeout" name="at_options[at_user_timeout]" type="text" value="%1$s" /> %2$s',
					self::$options['at_user_timeout'], 'minutes'
				);

			}

			public function at_send_email_callback() {

				// print the HTML to create the field
				printf(
					'<input id="at_send_email" name="at_options[at_send_email]" type="checkbox" value="1" %1$s />',
					checked( true, self::$options['at_send_email'], false )
				);

			}

			public function at_check_plugins_callback() {

				// print the HTML to create the field
				printf(
					'<input id="at_check_plugins" name="at_options[at_check_plugins]" type="checkbox" value="1" %1$s />',
					checked( true, self::$options['at_check_plugins'], false )
				);

			}

			public function at_check_themes_callback() {

				// print the HTML to create the field
				printf(
					'<input id="at_check_themes" name="at_options[at_check_themes]" type="checkbox" value="1" %1$s />',
					checked( true, self::$options['at_check_themes'], false )
				);

			}

			public function at_check_wordpress_callback() {

				// print the HTML to create the field
				printf(
					'<input id="at_check_wordpress" name="at_options[at_check_wordpress]" type="checkbox" value="1" %1$s />',
					checked( true, self::$options['at_check_wordpress'], false )
				);

			}

			public function at_check_php_callback() {

				// print the HTML to create the field
				printf(
					'<input id="at_check_php" name="at_options[at_check_php]" type="checkbox" value="1" %1$s />',
					checked( true, self::$options['at_check_php'], false )
				);

			}


	public function at_enqueue_admin_styles() {

		wp_register_style( 'at_admin_css',  plugin_dir_url( __FILE__ ) . '/library/css/admin-style.css', false, '1.0.0' );
		wp_enqueue_style( 'at_admin_css' );

	}



}

$admin_tools = new AdminTools();