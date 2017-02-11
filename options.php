<?php
class UpdatesNotifier_Settings extends UpdatesNotifier {


	public function __construct() {

				add_action( 'admin_menu',            array( $this, 'add_plugin_page' ) );

				$this->init();

			}

	public function init() {
		print_r('asdfasdfasdf');
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

						print_r( $updates_notifier->$updates . 'ASDasdasdfasdf' );

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

$settings = new UpdatesNotifier_Settings();
