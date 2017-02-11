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


	public static $how_many;

	public function __construct() {

		add_action( 'admin_bar_menu', [ $this, 'un_check_for_updates' ] );

		include_once( plugin_dir_path( __FILE__ ) . '/options.php' );

	}

	public function un_check_for_updates() {

		if( ! current_user_can( 'install_plugins' ) ) {
			return;
		}
			$update_data = wp_get_update_data();

			$updates = array(
				'plugins'	=>	$update_data['counts']['plugins'],
				'themes'	=>	$update_data['counts']['themes'],
				'WordPress'	=>	$update_data['counts']['themes'],
				'translations' =>	$update_data['counts']['themes'],
			);

			print_r( $updates );


	}


}

$updates_notifier = new UpdatesNotifier();
