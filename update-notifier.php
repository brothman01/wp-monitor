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

		add_action( 'admin_bar_menu', [ $this, 'un_check_for_updates' ] );

		// add the options page
		add_action( 'admin_menu', [ $this, 'add_plugin_page' ] );

		// create and add the fields to the options page
		//add_action( 'admin_init', [ $this, 'page_init' ] );

	}


	public function un_check_for_updates() {

		if( ! current_user_can( 'install_plugins' ) ) {

			return;

		}

			$update_data = wp_get_update_data();

			self::$updates = array(
				'plugins'	=>	$update_data['counts']['plugins'],
				'themes'	=>	$update_data['counts']['themes'],
				'WordPress'	=>	$update_data['counts']['themes'],
			);

			print_r( self::$updates);

			if (self::$updates['plugins'] + self::$updates['themes'] + self::$updates['WordPress'] != 0) {

				$message =
					'<b>Available Updates:</b>' .
					'<p>Plugin Updates: ' . self::$updates['plugins'] . '<br />Theme Updates: ' . self::$updates['themes'] . '<br />WordPress Core Updates: ' . self::$updates['WordPress'];

				wp_mail( get_option( 'admin_email' ), 'Updates for ' . get_option( 'siteurl' ) . ' available', $message );
			}

	}

	/**
	* Add options page
	*
	* @since 1.0.0
	*/
	public function add_plugin_page() {

		// add_options_page(
		// 	'Updates Notifier Settings',
		// 	'Updates Notifier',
		// 	'manage_options',
		// 	'updates-notifier',
		// 	[ $this, 'create_admin_page' ]
		// );

		register_setting(
			'writing',                 // settings page
			'brothman_option1',          // option name
			[ $this, 'brothman_check_plugins_sanitize' ]  // validation callback
		);

		add_settings_field(
			'brothman_check_plugins',      // id
			'Check Plugin Updates?',              // setting title
			[ $this, 'brothman_check_plugins_callback' ],    // display callback
			'writing',                 // settings page
			'default'                  // settings section
		);



		register_setting(
			'writing',                 // settings page
			'brothman_option2',          // option name
			[ $this, 'brothman_check_themes_sanitize' ]  // validation callback
		);

		add_settings_field(
			'brothman_check_themes',      // id
			'Check Theme Updates?',              // setting title
			[ $this, 'brothman_check_themes_callback' ],    // display callback
			'writing',                 // settings page
			'default'                  // settings section
		);



		register_setting(
			'writing',                 // settings page
			'brothman_option3',          // option name
			[ $this, 'brothman_check_wordpress_sanitize' ]  // validation callback
		);

		add_settings_field(
			'brothman_check_wordpress',      // id
			'Check WordPress Updates?',              // setting title
			[ $this, 'brothman_check_wordpress_callback' ],    // display callback
			'writing',                 // settings page
			'default'                  // settings section
		);

	}



	public function brothman_check_plugins_callback() {
		// get option 'boss_email' value from the database
		self::$options = get_option( 'brothman_option1' );

		$value = (bool) empty( self::$options['brothman_check_plugins'] ) ? false : true;

		// print the field !!
		printf(
				'<input id="brothman_check_plugins" name="brothman_option1[brothman_check_plugins]" type="checkbox" value="1" %s />',
				checked( 1, $value, false )
		);


	}

	public function brothman_check_plugins_sanitize( $input ) {

		$valid = array();

		$valid['brothman_check_plugins'] = (bool) isset( $input['brothman_check_plugins'] ) ? true : false;

		return $valid;

	}



	public function brothman_check_themes_callback() {
		// get option 'boss_email' value from the database
		self::$options = get_option( 'brothman_option2' );

		$value = (bool) empty( self::$options['brothman_check_themes'] ) ? false : true;

		// print the field !!
		printf(
				'<input id="brothman_check_plugins" name="brothman_option2[brothman_check_themes]" type="checkbox" value="1" %s />',
				checked( 1, $value, false )
		);

	}

	// Validate user input
	public function brothman_check_themes_sanitize( $input ) {

		$valid = array();

		$valid['brothman_check_themes'] = (bool) isset( $input['brothman_check_themes'] ) ? true : false;

		return $valid;

	}



	public function brothman_check_wordpress_callback() {
		// get option 'boss_email' value from the database
		self::$options = get_option( 'brothman_option3' );

		$value = (bool) empty( self::$options['brothman_check_wordpress'] ) ? false : true;

		// print the field !!
		printf(
				'<input id="brothman_check_wordpress" name="brothman_option3[brothman_check_wordpress]" type="checkbox" value="1" %s />',
				checked( 1, $value, false )
		);


	}

	// Validate user input
	public function brothman_check_wordpress_sanitize( $input ) {

		$valid = array();

		$valid['brothman_check_wordpress'] = (bool) isset( $input['brothman_check_wordpress'] ) ? true : false;

		return $valid;

	}


	public function brothman_how_often_callback() {

		$options = array(
			'never'   => 'Never',
			'daily'   => 'Daily',
			'weekly'  => 'Weekly',
			'monthly' => 'Monthly',
		);

		print( '<select name="brothman_option4[brothman_how_often]">' );

		foreach ( $options as $value => $label ) {

			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $value ),
				selected( self::$options['brothman_how_often'], $value ),
				esc_html( $label )
			);

		}

		print( '</select>' );

	}

	// Validate user input
	public function brothman_how_often_sanitize( $input ) {

		$valid = array();

		$valid['brothman_how_often'] = (bool) isset( $input['brothman_check_wordpress'] ) ? true : false;

		return $valid;

	}

}

$updates_notifier = new UpdatesNotifier();
