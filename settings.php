<?php

class Settings extends AdminTools {

	public static $options;

	public function __construct() {

		self::$options = AdminTools::$options;

		add_action( 'admin_menu', array( $this, 'at_add_plugin_page' ) );

		add_action( 'admin_init', array( $this, 'at_settings_init' ) );

	}

	public function at_add_plugin_page() {

			 add_management_page(
				 'Options Page',
				 'Admin Tools',
				 'manage_options',
				 'options_page',
				 array( $this, 'create_admin_page' )
			 );

	}

	public function create_admin_page() {
							?>
							<div class="wrap">
								<h1>Admin Tools</h1>
								<form method="post" action="options.php">
									<?php

										settings_fields( 'at_options_group' );

										do_settings_sections( 'options_page' );

										submit_button();

										?>
									</form>
								</div>
								<?php
	}

	public function at_settings_init() {

				add_settings_section(
					'general_section_id',
					'General Settings',
					array( $this, 'at_general_section_callback' ),
					'options_page'
				);

	}



	public function at_general_section_callback() {

		_e( 'Edit the settings for the plugin here.  For support or to check out the cool add-ons available for Admin Tools, visit us at', 'admin-tools' );
					 echo ' <a href="http://www.nothing.com">www.nothing.com</a>.';

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

		$option = (string) get_option( 'at_addons' );

		if ( strpos( $option, $line ) !== false ) {

			return 'style="color: green;"';

		}

		return 'style="color: red;"';

	}

}

$settings = new Settings();
