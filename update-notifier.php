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

	public function __construct() {

		add_action( 'admin_bar_menu', [ $this, 'un_check_for_updates' ] );

		// add the options page
		add_action( 'admin_menu', [ $this, 'add_plugin_page' ] );

		// create and add the fields to the options page
		add_action( 'admin_init', [ $this, 'page_init' ] );

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
				'translations' =>	$update_data['counts']['themes'],
			);

			print_r( self::$updates);

			if (self::$updates['plugins'] + self::$updates['themes'] + self::$updates['WordPress'] + self::$updates['translations'] != 0) {

				$message =
					'<b>Available Updates:</b>' .
					'<p>Plugin Updates: ' . self::$updates['plugins'] . '<br />Theme Updates: ' . self::$updates['themes'] . '<br />WordPress Core Updates: ' . self::$updates['WordPress'] . '<br />Translation Updates: ' . self::$updates['translations'];

				wp_mail( get_option( 'admin_email' ), 'Updates for ' . get_option( 'siteurl' ) . ' available', $message );
			}

	}

	/**
	* Add options page
	*
	* @since 1.0.0
	*/
	public function add_plugin_page() {

		add_options_page(
			'Updates Notifier Settings',
			'Updates Notifier',
			'manage_options',
			'updates-notifier',
			[ $this, 'create_admin_page' ]
		);

	}

	public function create_admin_page() {

		?>

			<div class="wrap">

				<h1><?php esc_html_e( 'Updates Notifier', 'updates-notifier' ); ?></h1>

				<form method="post" action="options.php">

					<?php

						printf(
							'<div class="notice notice-' . $this->alert_type() . ' is-dismissible">' .
							'<b>Available Updates:</b>' .
							'<p>Plugin Updates: ' . self::$updates['plugins'] . '<br />Theme Updates: ' . self::$updates['themes'] . '<br />WordPress Core Updates: ' . self::$updates['WordPress'] . '<br />Translation Updates: ' . self::$updates['translations'] .
							'</p></div>'
						);

						settings_fields( 'updates_notifier_settings_group' );

						do_settings_sections( 'updates-notifier' );

						submit_button();

					?>

				</form>

			</div>

		<?php
	}

	public function alert_type() {

		// return 'info' or 'error'
		if (self::$updates['plugins'] + self::$updates['themes'] + self::$updates['WordPress'] + self::$updates['translations'] == 0) {

		return 'info';

	} else {

		return 'error';

	}

	}

	/**
	* Register and add settings
	*
	* @since 1.0.0
	*/
	public function page_init() {

		register_setting(
			'updates_notifier_settings_group',
			'updates_notifier_settings',
			array( $this, 'sanitize' )
		);

		add_settings_section( 'updates-notifier-id', 'Watch These For Updates:', array( $this, 'print_section_info' ), 'updates-notifier' );

		add_settings_field( 'un-check-plugins', 'Plugin Updates', array( $this, 'updates-notifier'), 'updates-notifier', 'updates-notifier' );

	}

	/**
	* Sanitize each setting field as needed
	*
	* @param array $input Contains all settings fields as array keys
	*
	* @since 1.0.0
	*/
	public function sanitize( $input ) {

		$new_input = $input;

		return $new_input;

	}

	/**
	* Print the Section text
	*
	* @since 1.0.0
	*/
	public function print_section_info() {

		echo 'Adjust the settings below:';

	}

	/**
	* Get the settings option array and print one of its values
	*
	* @since 1.0.0
	*/
	public function send_email_callback() {

		printf('<input type="checkbox" id="send_email" name="php_notifier_settings[send_email]" value="1" />');

	}

}

$updates_notifier = new UpdatesNotifier();
