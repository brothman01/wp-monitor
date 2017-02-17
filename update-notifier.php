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

		// add_options_page(
		// 	'Updates Notifier Settings',
		// 	'Updates Notifier',
		// 	'manage_options',
		// 	'updates-notifier',
		// 	[ $this, 'create_admin_page' ]
		// );



	register_setting(
	'writing',                 // settings page
	'brothman_check_plugins',          // option name
	[ $this, 'ozhwpe_validate_options' ]  // validation callback
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
	'brothman_check_themes',          // option name
	[ $this, 'ozhwpe_validate_options' ]  // validation callback
);

	add_settings_field(
		'brothman_check_themes',      // id
		'Check Theme Updates?',              // setting title
		[ $this, 'brothman_check_themes_callback' ],    // display callback
		'writing',                 // settings page
		'default'                  // settings section
	);

	}



	public function brothman_check_plugins_callback() {
		// get option 'boss_email' value from the database
		$options = get_option( 'brothman_options' );

		$value = $options['brothman_check_plugins'];

		// echo the field
		?>
	<input id='brothman_check_plugins' name='brothman_options[brothman_check_plugins]'
	 type="checkbox" value="1" <?php echo checked( 1, $value, false ); ?> />

		<?php
	}

	public function brothman_check_themes_callback() {
		// get option 'boss_email' value from the database
		$options = get_option( 'brothman_options' );

		$value = $options['brothman_check_themes'];

		// echo the field
		?>
	<input id='brothman_check_themes' name='brothman_options[brothman_check_themes]'
	 type="checkbox" value="1" <?php echo checked( 1, $value, false ); ?> />

		<?php
	}


}

$updates_notifier = new UpdatesNotifier();
