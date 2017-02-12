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


	public $updates;

	public function __construct() {

		add_action( 'admin_bar_menu', [ $this, 'un_check_for_updates' ] );

	}

	public function un_check_for_updates() {

		if( ! current_user_can( 'install_plugins' ) ) {
			return;
		}
			$update_data = wp_get_update_data();

			$this->$updates = array(
				'plugins'	=>	$update_data['counts']['plugins'],
				'themes'	=>	$update_data['counts']['themes'],
				'WordPress'	=>	$update_data['counts']['themes'],
				'translations' =>	$update_data['counts']['themes'],
			);

			print_r( $this->$updates );

	}

}

class UpdatesNotifier_Settings extends UpdatesNotifier {



	public function __construct( $updates ) {

				add_action( 'admin_menu', [ $this, 'add_plugin_page' ] );

				print_r( 'second ' . $updates );

				$this->init();

	}

	public function init() {

	}

	/**
	* Add options page
	*
	* @since 1.0.0
	*/
	public function add_plugin_page() {

		add_options_page(
			__( 'Updates Settings', 'updates-notifier' ),
			__( 'Updates Notifier', 'updates-notifier' ),
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
							'<div class="notice notice-' . $this->alert_type() . ' is-dismissible"><p>' .
							'Plugin Updates: ' . '0' . '<br />Theme Updates: ' . '0' . '<br />WordPress Core Updates: ' . '0' . '<br />Translation Updates: ' . '0' .
							'</p></div>'
						);

						submit_button();

					?>

				</form>

			</div>

		<?php
	}

	public function alert_type() {
		// return 'info' or 'error'
		return 'info';
	}

}

$updates_notifier = new UpdatesNotifier();

$settings = new UpdatesNotifier_Settings( $updates_notifier->$updates );
