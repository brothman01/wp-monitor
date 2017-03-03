<?php
/*
 * Plugin Name: Updates Notifier
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

		// check for updates
		add_action( 'admin_bar_menu', [ $this, 'un_check_for_updates' ] );

		// add the options page
		add_action( 'admin_menu', [ $this, 'add_plugin_page' ] );

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

		if ( get_option( 'brothman_option1' ) ) {

			$watched_updates = $watched_updates + self::$updates['plugins'];

			$message = $message . self::$updates['plugins'] . ' plugins.' . "\r\n";

		}

		if ( get_option( 'brothman_option2' ) ) {

			$watched_updates = $watched_updates + self::$updates['themes'];

			$message = $message . self::$updates['themes'] . ' themes.' . "\r\n";

		}

		if ( get_option( 'brothman_option3' ) ) {

			$watched_updates = $watched_updates + self::$updates['WordPress'];

			$message = $message . self::$updates['WordPress'] . ' WordPress Core Updates.' . "\r\n";

		}

		if ( $watched_updates > 0 ) {

			wp_mail( get_option( 'admin_email' ), $watched_updates . 'for ' . get_option( 'siteurl' ) . ' available!', $message);

		}

	}

	/**
	* Add options page
	*
	* @since 1.0.0
	*/
	public function add_plugin_page() {
// 1. Add the page to settings
		// add_options_page(
		// 	'Updates Notifier Settings', // page title
		// 	'Updates Notifier', // menu title
		// 	'manage_options', // required capability of user
		// 	'updates-notifier', // menu slug
		// 	[ $this, 'create_admin_page' ] // callback function to build and display the page
		// );

// 2. Add options section
		// add_settings_section(
		// 	'default_section_id', // id of the section (for use in the id section)
		// 	'General Settings', // title of the section
		// 	array( $this, 'print_section_info' ), // callback function that put the reuired info into the section
		// 	'updates-notifier' // The menu page on which to display the section
		// );

// 3. Register one setting and one field per setting and add them to the section
	// - each setting needs register_setting() and add_settings_field() to appear on the correct page and allow changes to be saved
	// - each option has a callback() to create the field and a sanitize() to save the input in a clean way


	register_setting(
		'writing',                 // settings page
		'prevent_email_cron',          // option name
		[ $this, 'brothman_check_plugin_sanitize' ]  // validation callback
	);

		register_setting(
			'writing',                 // settings page
			'un_settings',          // option name
			[ $this, 'un_sanitize' ]  // validation callback
		);



		// register_setting(
		// 	'updates-notifier',                 // settings page
		// 	'brothman_option1',          // option name
		// 	[ $this, 'brothman_check_plugins_sanitize' ]  // validation callback
		// );

		add_settings_field(
			'brothman_check_plugins',      // id
			'Check Plugin Updates?',              // setting title
			[ $this, 'brothman_check_plugins_callback' ],    // display callback
			'updates-notifier',                 // settings page
			'default_section_id'                  // settings section
		);



		// register_setting(
		// 	'writing',                 // settings page
		// 	'brothman_option2',          // option name
		// 	[ $this, 'brothman_check_themes_sanitize' ]  // validation callback
		// );

		add_settings_field(
			'brothman_check_themes',      // id
			'Check Theme Updates?',              // setting title
			[ $this, 'brothman_check_themes_callback' ],    // display callback
			'writing',                 // settings page
			'default'                  // settings section
		);



		// register_setting(
		// 	'writing',                 // settings page
		// 	'brothman_option3',          // option name
		// 	[ $this, 'brothman_check_wordpress_sanitize' ]  // validation callback
		// );

		add_settings_field(
			'brothman_check_wordpress',      // id
			'Check WordPress Updates?',              // setting title
			[ $this, 'brothman_check_wordpress_callback' ],    // display callback
			'writing',                 // settings page
			'default'                  // settings section
		);

	}

	/**
	* Options page callback
	*
	* @since 1.0.0
	*/
	public function create_admin_page() {

		?>

			<div class="wrap">

				<h1>Updates Notifier</h1>

				<form method="post" action="options-general.php?page=updates-notifier">

					<?php

						printf(
							'<div class="notice notice-info updates_notifier">' .
									'<b>Updates Available:</b>' .
									'<p>' .
										'Plugins: ' . self::$updates['plugins'] . '<br />' .
										'Themes: ' . self::$updates['themes'] . '<br />' .
										'WordPress: ' . self::$updates['themes'] . '<br />' .
									'</p>' .
							'</div>'
						);

						// tell the page to use the setting?
						settings_fields( 'brothman_option1' );

						// add the section to the page
						do_settings_sections( 'updates-notifier' );

						submit_button();

					?>

				</form>

			</div>

		<?php
	}

	public function un_sanitize( $input ) {

		// create an empty 'clean' array
		$valid = array();

		// add the cleaned value to the clean array
		$valid['brothman_check_plugins'] = (bool) isset( $input['brothman_check_plugins'] ) ? true : false;
		$valid['brothman_check_themes'] = (bool) isset( $input['brothman_check_themes'] ) ? true : false;
		$valid['brothman_check_WordPress'] = (bool) isset( $input['brothman_check_WordPress'] ) ? true : false;

		// return the clean array
		return $valid;

	}

	public function print_section_info() {

	echo 'Adjust the settings below:';

}

// public function brothman_prevent_email_cron_sanitize( $input ) {
//
// 	// create an empty 'clean' array
// 	$valid = array();
//
// 	// add the cleaned value to the clean array
// 	$valid['prevent_email_cron'] = (bool) isset( $input['prevent_email_cron'] ) ? true : false;
//
// 	// return the clean array
// 	return $valid;
//
// }


	public function brothman_check_plugins_callback() {

		// get option 'brothman_option1' value from the database and put it in the array $options
		self::$options = get_option( 'brothman_option1' );

		// get the value of the option from the $options array (set to no if empty)
		$value = (bool) empty( self::$options['brothman_check_plugins'] ) ? false : true;

		// print the HTML to create the field
		printf(
				'<input id="brothman_check_plugins" name="brothman_option1[brothman_check_plugins]" type="checkbox" value="1" %s />',
				checked( 1, $value, false )
		);


	}

	// public function brothman_check_plugins_sanitize( $input ) {
	//
	// 	// create an empty 'clean' array
	// 	$valid = array();
	//
	// 	// add the cleaned value to the clean array
	// 	$valid['brothman_check_plugins'] = (bool) isset( $input['brothman_check_plugins'] ) ? true : false;
	//
	// 	// return the clean array
	// 	return $valid;
	//
	// }



	public function brothman_check_themes_callback() {

		self::$options = get_option( 'brothman_option2' );

		$value = (bool) empty( self::$options['brothman_check_themes'] ) ? false : true;

		printf(
				'<input id="brothman_check_plugins" name="brothman_option2[brothman_check_themes]" type="checkbox" value="1" %s />',
				checked( 1, $value, false )
		);

	}


	// public function brothman_check_themes_sanitize( $input ) {
	//
	// 	$valid = array();
	//
	// 	$valid['brothman_check_themes'] = (bool) isset( $input['brothman_check_themes'] ) ? true : false;
	//
	// 	return $valid;
	//
	// }



	public function brothman_check_wordpress_callback() {

		self::$options = get_option( 'brothman_option3' );

		$value = (bool) empty( self::$options['brothman_check_wordpress'] ) ? false : true;


		printf(
				'<input id="brothman_check_wordpress" name="brothman_option3[brothman_check_wordpress]" type="checkbox" value="1" %s />',
				checked( 1, $value, false )
		);


	}


	// public function brothman_check_wordpress_sanitize( $input ) {
	//
	// 	$valid = array();
	//
	// 	$valid['brothman_check_wordpress'] = (bool) isset( $input['brothman_check_wordpress'] ) ? true : false;
	//
	// 	return $valid;
	//
	// }




	public function un_enqueue_admin_styles() {

		wp_register_style( 'un_admin_css',  plugin_dir_url( __FILE__ ) . '/library/css/admin-style.css', false, '1.0.0' );

		wp_enqueue_style( 'un_admin_css' );

	}

}

$updates_notifier = new UpdatesNotifier();
