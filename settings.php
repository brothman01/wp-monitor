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

				register_setting(
					'wpm_options_group',
					'wpm_options',
					[ $this, 'wpm_sanitize' ]
				);

				register_setting(
					'wpm_prevent_email_cron',
					'wpm_prevent_email_cron',
					[ $this, 'wpm_sanitize' ]
				);


								add_settings_field(
									'wpm_show_monitor',
									__( 'Show Classic Monitor? (not widget)', 'admin-tools' ),
									[ $this, 'wpm_show_monitor_callback' ],
									'options_page',
									'general_section_id'
								);

								add_settings_field(
									'wpm_how_often',
									__( 'Show Classic Monitor?', 'admin-tools' ),
									[ $this, 'wpm_show_monitor_callback' ],
									'options_page',
									'general_section_id2'
								);

	}

	public function wpm_sanitize( $input ) {

					$valid = array();

					$valid['wpm_show_monitor'] 	= (bool) empty( $input['wpm_show_monitor'] ) ? false : true;

					$valid['wpm_how_often']	= isset( $input['wpm_how_often'] ) ? sanitize_text_field( $input['wpm_how_often'] ) : 'Never';

					// $valid['wpm_send_email'] = (bool) empty( $input['wpm_send_email'] ) ? false : true;

					$valid['wpm_send_email'] = (bool) empty( $input['wpm_send_email'] ) ? false : true;;

					$valid['wpm_check_plugins'] = (bool) empty( $input['wpm_check_plugins'] ) ? false : true;

					$valid['wpm_check_themes'] = (bool) empty( $input['wpm_check_themes'] ) ? false : true;

					$valid['wpm_check_wordpress'] = (bool) empty( $input['wpm_check_wordpress'] ) ? false : true;

					$valid['wpm_check_php'] = (bool) empty( $input['wpm_check_php'] ) ? false : true;

					$valid['wpm_check_ssl'] = (bool) empty( $input['wpm_check_ssl'] ) ? false : true;

					//update_option( 'wpm_options', self::$options );

					return $valid;

	}



	public function wpm_show_monitor_callback() {

					printf(
						'<input id="wpm_show_monitor" name="wpm_options[wpm_show_monitor]" type="checkbox" value="1" %1$s />',
						checked( true, Settings::$options['wpm_show_monitor'], false )
					);

	}





	public function wpm_general_section_callback() {

		_e( 'Edit the settings for the plugin here.  For support or to check out the cool add-ons available for Admin Tools, visit us at', 'wp-monitor' );
					 echo ' <a href="http://www.wp-monitor.net">www.wp-monitor.net</a>.';

					 printf(
						 '<br />
							<h3>%1$s</h3>' .
							'<select multiple>
					       <option %2$s>%3$s</option>
								 </select>',
						 esc_html__( 'Active Addons', 'wp-monitor' ),
						 $this->get_color( 'emailaddon' ),
						 esc_html__( 'Email Notifications', 'wp-monitor' )
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
