<?php

class Settings extends WPMonitor {

	public static $options;

	public function __construct() {

		self::$options = WPMonitor::$options;

		add_action( 'admin_menu', array( $this, 'wpm_add_plugin_page' ) );

		add_action( 'admin_init', array( $this, 'wpm_settings_init' ) );

	}

	public function wpm_add_plugin_page() {

			 add_management_page(
				 'Options Page',
				 'WP Monitor',
				 'manage_options',
				 'options_page',
				 array( $this, 'create_admin_page' )
			 );

	}

	public function create_admin_page() {
							?>
							<div class="wrap">
								<h1>WP Monitor</h1>
								<form method="post" action="options.php">
									<?php

										settings_fields( 'wpm_options_group' );

										do_settings_sections( 'options_page' );

										submit_button();

										?>
									</form>
								</div>
								<?php
	}

	public function wpm_settings_init() {

				add_settings_section(
					'general_section_id',
					'General Settings',
					array( $this, 'wpm_general_section_callback' ),
					'options_page'
				);

	}



	public function wpm_general_section_callback() {

		_e( 'Edit the settings for the plugin here.  For support or to check out the cool add-ons available for Admin Tools, visit us at', 'admin-tools' );
					 echo ' <a href="http://www.wp-monitor.net">www.wp-monitor.net</a>.';

					 printf(
						 '<br />
							<h3>%1$s</h3>' .
							'<select multiple>
					       <option %2$s>%3$s</option>
								 </select>',
						 esc_html__( 'Active Addons', 'admin-tools' ),
						 $this->get_color( 'emailaddon' ),
						 esc_html__( 'Email Notifications', 'admin-tools' )
					 );

	}

	public function get_color( $line ) {

		$option = (string) get_option( 'wpm_addons' );

		if ( strpos( $option, $line ) !== false ) {

			return 'style="color: green;"';

		}

		return 'style="color: red;"';

	}

}

$settings = new Settings();
