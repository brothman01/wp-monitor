<?php
/*
 * Plugin Name: Admin Tools
 * Description: Notify user when updates to WordPress are needed.
 * Version:     1.0.0
 * Author:      Ben Rothman
 * Author URI:  http://www.BenRothman.org
 * License:     GPL-2.0+
 */

class UpdatesNotifier {

	public static $updates;

	public static $options;

	public function __construct() {

		// get option 'at_options' value from the database and put it in the array $options
		self::$options = get_option( 'at_options', [
			'at_settings1' => '',
			'at_settings2' => '',
			'at_checkbox1' => false,
		] );
		// check for updates
		add_action( 'admin_bar_menu', [ $this, 'un_check_for_updates' ] );

		// add the options page
		add_action( 'admin_menu', [ $this, 'at_add_plugin_page' ] );

		// build options page
		add_action( 'admin_init', [ $this, 'at_settings_init' ] );

		// other stuff
		$this->init();

	}

	public function init() {

		// get the option that is set when the crontask is scheduled
		$prevent_email_cron = get_option( 'prevent_email_cron' );

		// schedule crontask if it has not already been scheduled
		if ( $prevent_email_cron == 0 ) {

				wp_schedule_event(time(), 'daily', 'send_my_updates_notification');

				//set the option to say the crontask has already been scheduled
				update_option( 'prevent_email_cron', 1, true );

	}

		// add action to send email when cron task is triggered
		add_action( 'send_my_updates_notification', [ $this, 'un_send_email' ] );


		// enqueue the admin stylesheet
		add_action( 'admin_enqueue_scripts', [ $this, 'un_enqueue_admin_styles' ] );

	}

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

		// 2. Add the section to the setting page
		add_settings_section(
			'options_br_id', // id for use in id attribute
			'General Settings', // title of the section
			[ $this, 'at_section_callback' ], // callback function
			'options_page' // page
		);

			// 5. Add each settings field
			add_settings_field(
				'at_settings1',      // id
				'Text Field',              // setting title
				[ $this, 'at_text_field_callback' ],    // display callback
				'options_page',                 // settings page
				'options_br_id'                  // settings section
			);

			add_settings_field(
				'at_settings2',      // id
				'Tinkerbell\'s Vagina',              // setting title
				[ $this, 'at_tinkerbells_vagina_callback' ],    // display callback
				'options_page',                 // settings page
				'options_br_id'                  // settings section
			);



			add_settings_field(
				'at_checkbox1',      // id
				'Can I see your big penis?',              // setting title
				[ $this, 'at_checkbox1_callback' ],    // display callback
				'options_page',                 // settings page
				'options_br_id'                  // settings section
			);

		}


	public function un_check_for_updates() {

		if ( ! current_user_can( 'install_plugins' ) ) {

			return;

		}
		// get update data (only after role of user has been checked)
			$update_data = wp_get_update_data();

			self::$updates = array(
				'plugins'	=>	$update_data['counts']['plugins'],
				'themes'	=>	$update_data['counts']['themes'],
				'WordPress'	=>	$update_data['counts']['themes'],
			);

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
										printf('<div class="notice notice-info is-dismissible"><p>test</p></div>');

										settings_fields( 'at_options_group' );

										do_settings_sections( 'options_page' ); // 4. add the page sections to the page (by entering the page name!)

										submit_button( 'Call Davey Crocket A Pussy' );
										?>
									</form>
								</div>
								<?php
			}

			public function at_sanitize( $input ) {

				// create an empty 'clean' array
				$valid = array();

				// add the cleaned values of each field to the clean array on submit
				$valid['at_settings1'] = empty( $input['at_settings1'] ) ? '' : sanitize_text_field( $input['at_settings1'] );

				$valid['at_settings2'] = empty( $input['at_settings2'] ) ? '' : sanitize_text_field( $input['at_settings2'] );

				$valid['at_checkbox1']       	= (bool) empty( $input['at_checkbox1'] ) ? false : true;


				// return the clean array
				return $valid;

			}

			public function at_section_callback() {

				echo 'This is the only section on the page, so wtf?';

			}

			public function at_text_field_callback() {

				// print the HTML to create the field
				printf(
					'<input id="at_settings1" name="at_options[at_settings1]" type="text" value="%1$s" />',
					self::$options['at_settings1']
				);

			}

			public function at_tinkerbells_vagina_callback() {

				// print the HTML to create the field
				printf(
					'<input id="at_settings2" name="at_options[at_settings2]" type="text" value="%1$s" />',
					self::$options['at_settings2']
				);

			}

			public function at_checkbox1_callback() {

				// print the HTML to create the field
				printf(
					'<input id="at_checkbox1" name="at_options[at_checkbox1]" type="checkbox" value="1" %1$s />',
					checked( true, self::$options['at_checkbox1'], false )
				);

			}


	public function un_enqueue_admin_styles() {

		wp_register_style( 'un_admin_css',  plugin_dir_url( __FILE__ ) . '/library/css/admin-style.css', false, '1.0.0' );

		wp_enqueue_style( 'un_admin_css' );

	}

}

$updates_notifier = new UpdatesNotifier();
