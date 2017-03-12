<?php

class Settings extends AdminTools {

	public static $options;

	public function __construct() {

		self::$options = AdminTools::$options;

		// get option 'at_options' value from the database and put it in the array $options
		self::$options = get_option( 'at_options', [
			'at_prevent_email_cron' => true,
			'at_user_timeout' => 5,
			'at_send_email' => false,
			'at_check_plugins' => false,
			'at_check_themes' => false,
			'at_check_wordpress' => false,
			'at_check_php' => false,
		] );

		// add the options page
		add_action( 'admin_menu', [ $this, 'at_add_plugin_page' ] );

		// build options page
		add_action( 'admin_init', [ $this, 'at_settings_init' ] );

		 //wp_die( print_r( self::$options ) );

	}

	public function at_add_plugin_page() {

			 // 1. Add the settings page
			 add_options_page(
				 'Options Page', // page title
					'Admin Tools', // menu title
					'manage_options', // capability required of user
					'options_page', // menu slug
					[ $this, 'create_admin_page' ] // callback function
				);

	}

	// 3. Build the setting page with this callback
			public function create_admin_page() {
							?>
							<div class="wrap">
								<h1>Admin Tools</h1>
								<form method="post" action="options.php"> <!-- the action needs to be 'options.php' -->
									<?php
										//printf('<div class="notice notice-info is-dismissible"><p>test</p></div>');

										settings_fields( 'at_options_group' );

										do_settings_sections( 'options_page' ); // 4. add the page sections to the page (by entering the page name!)

										submit_button();
										?>
									</form>
								</div>
								<?php
			}

			public function at_settings_init() {

				register_setting( // (actually a settings group)
					'at_options_group',                 // group name
					'at_options',          // option name
					[ $this, 'at_sanitize' ]  // validation callback
				);



				add_settings_section(
					'general_section_id', // id for use in id attribute
					'General Settings', // title of the section
					[ $this, 'at_general_section_callback' ], // callback function
					'options_page' // page
				);


							add_settings_field(
								'at_user_timeout',      // id
								'How Long Until A User Is Logged Out For Inactivity?',              // setting title
								[ $this, 'at_user_timeout_callback' ],    // display callback
								'options_page',                 // settings page
								'general_section_id'                  // settings section
							);

				// 2. Add the section to the setting page
				add_settings_section(
					'options_br_id', // id for use in id attribute
					'Email Settings', // title of the section
					[ $this, 'at_email_section_callback' ], // callback function
					'options_page' // page
				);

								// 5. Add each settings field
								// add_settings_field(
								// 	'at_settings1',      // id
								// 	'Text Field',              // setting title
								// 	[ $this, 'at_text_field_callback' ],    // display callback
								// 	'options_page',                 // settings page
								// 	'options_br_id'                  // settings section
								// );

								add_settings_field(
									'at_send_email',      // id
									'Send Email?',              // setting title
									[ $this, 'at_send_email_callback' ],    // display callback
									'options_page',                 // settings page
									'options_br_id'                  // settings section
								);

								add_settings_field(
									'at_check_plugins',      // id
									'Check Plugins?',              // setting title
									[ $this, 'at_check_plugins_callback' ],    // display callback
									'options_page',                 // settings page
									'options_br_id'                  // settings section
								);

								add_settings_field(
									'at_check_themes',      // id
									'Check Themes?',              // setting title
									[ $this, 'at_check_themes_callback' ],    // display callback
									'options_page',                 // settings page
									'options_br_id'                  // settings section
								);

								add_settings_field(
									'at_check_wordpress',      // id
									'Check WordPress?',              // setting title
									[ $this, 'at_check_wordpress_callback' ],    // display callback
									'options_page',                 // settings page
									'options_br_id'                  // settings section
								);

								add_settings_field(
									'at_check_php',      // id
									'Check PHP?',              // setting title
									[ $this, 'at_check_php_callback' ],    // display callback
									'options_page',                 // settings page
									'options_br_id'                  // settings section
								);

				}

				public function at_sanitize( $input ) {

					// create an empty 'clean' array
					$valid = array();

					// add the cleaned values of each field to the clean array on submit
					// $valid['at_settings1'] = empty( $input['at_settings1'] ) ? '' : sanitize_text_field( $input['at_settings1'] );

					$valid['at_prevent_email_cron'] = (bool) empty( $input['at_prevent_email_cron'] ) ? false : true;

					$valid['at_user_timeout']       	=  isset( $input['at_user_timeout'] ) ? $input['at_user_timeout'] : '0.05.00';

					$valid['at_send_email']       	= (bool) empty( $input['at_send_email'] ) ? false : true;

					$valid['at_check_plugins']       	= (bool) empty( $input['at_check_plugins'] ) ? false : true;

					$valid['at_check_themes']       	= (bool) empty( $input['at_check_themes'] ) ? false : true;

					$valid['at_check_wordpress']      = (bool) empty( $input['at_check_wordpress'] ) ? false : true;

					$valid['at_check_php']      = (bool) empty( $input['at_check_php'] ) ? false : true;


					// return the clean array
					return $valid;

				}

}

$settings = new Settings();
