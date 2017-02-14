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
		add_action( 'admin_init', [ $this, 'page_init' ] );

	}

	public function un_check_for_updates() {

		if( ! current_user_can( 'administrator' ) ) {

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

			echo '<div class="notice notice-error">'.
			'<b>Available Updates:</b><br />' .
			'Plugins: ' . self::$updates['plugins'] . '<br />' .
			'Themes: ' . self::$updates['themes'] . '<br />' .
			'WordPress: ' . self::$updates['WordPress'] . '<br />' .
			'Translations: ' . self::$updates['translations'] .
			'</div>';

			if (self::$updates['plugins'] + self::$updates['themes'] + self::$updates['WordPress'] + self::$updates['translations'] != 0) {

				$message =
					'<b>Available Updates:</b>' .
					'<p>Plugin Updates: ' . self::$updates['plugins'] . '<br />Theme Updates: ' . self::$updates['themes'] . '<br />WordPress Core Updates: ' . self::$updates['WordPress'] . '<br />Translation Updates: ' . self::$updates['translations'];

				// wp_mail( get_option( 'admin_email' ), 'Updates for ' . get_option( 'siteurl' ) . ' available', $message );
			}

	}

	/**
	* Add menu page
	*
	* @since 1.0.0
	*/
	public function add_plugin_page() {

		//create new top-level menu
	add_menu_page('My Cool Plugin Settings', 'Cool Settings', 'administrator', 'my_cool_plugin_settings_page' , [ $this, 'create_admin_page' ] );

	}

	public function create_admin_page() {
		?>
<div class="wrap">
<h1>Updates Notifier</h1>

<form method="post" action="options.php">
    <?php settings_fields( 'my-cool-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'my-cool-plugin-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">New Option Name</th>
        <td><input type="text" name="new_option_name" value="<?php echo esc_attr( get_option('new_option_name') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Some Other Option</th>
        <td><input type="text" name="some_other_option" value="<?php echo esc_attr( get_option('some_other_option') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Options, Etc.</th>
        <td><input type="text" name="option_etc" value="<?php echo esc_attr( get_option('option_etc') ); ?>" /></td>
        </tr>
    </table>

    <?php submit_button(); ?>

</form>
</div>
<?php }


	public function alert_type() {

		// return 'info' or 'error'
		if (self::$updates['plugins'] + self::$updates['themes'] + self::$updates['WordPress'] + self::$updates['translations'] == 0) {

		return 'info';

	} else {

		return 'error';

	}

	}

	public function page_init() {

		register_setting( 'my-cool-plugin-settings-group', 'new_option_name' );
		register_setting( 'my-cool-plugin-settings-group', 'some_other_option' );
		register_setting( 'my-cool-plugin-settings-group', 'option_etc' );

	}


	public function sanitize( $input ) {

		$new_input = array();

		$new_input['un-check-plugins-id'] = $input['un-check-plugins-id'];
		self::$options['un-check-plugins-id'] = $input['un-check-plugins-id'];

		return $new_input;

	}

}

$updates_notifier = new UpdatesNotifier();
