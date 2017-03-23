<?php

class Settings extends AdminTools {

	public static $options;

	public function __construct() {

		self::$options = AdminTools::$options;

		// add the options page
		add_action( 'admin_menu', array( $this, 'at_add_plugin_page' ) );

		// build options page
		add_action( 'admin_init', array( $this, 'at_settings_init' ) );

		 //wp_die( print_r( self::$options ) );

	}

	public function at_add_plugin_page() {

			 // 1. Add the settings page
			 add_management_page(
				 'Options Page', // page title
				 'Admin Tools', // menu title
				 'manage_options', // capability required of user
				 'options_page', // menu slug
				 array( $this, 'create_admin_page' ) // callback function
			 );

	}

	// 3. Build the setting page with this callback
	public function create_admin_page() {
							?>
							<div class="wrap">
								<h1>Admin Tools</h1>
								<form method="post" action="options.php"> <!-- the action needs to be 'options.php' -->
									<?php

										settings_fields( 'at_options_group' );

										do_settings_sections( 'options_page' ); // 4. add the page sections to the page (by entering the page name!)

										submit_button();

										?>
									</form>
								</div>
								<?php
	}

	public function at_settings_init() {

				add_settings_section(
					'general_section_id', // id for use in id attribute
					'General Settings', // title of the section
					array( $this, 'at_general_section_callback' ), // callback function
					'options_page' // page
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
						 $this->get_color( 'emailaddon'),
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
