<?php

class Settings extends AdminTools {

	public static $options;

	public function __construct() {

		self::$options = AdminTools::$options;

		// add the options page
		add_action( 'admin_menu', [ $this, 'at_add_plugin_page' ] );

		// build options page
		add_action( 'admin_init', [ $this, 'at_settings_init' ] );

		 //wp_die( print_r( self::$options ) );

	}

	public function at_add_plugin_page() {

			 // 1. Add the settings page
			 add_management_page(
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

				register_setting( // (actually a settings group)
					'at_prevent_email_cron',                 // group name
					'at_prevent_email_cron',          // option name
					[ $this, 'at_sanitize' ]  // validation callback
				);



				add_settings_section(
					'general_section_id', // id for use in id attribute
					'General Settings', // title of the section
					[ $this, 'at_general_section_callback' ], // callback function
					'options_page' // page
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
									'at_how_often',      // id
									'Email Frequency',              // setting title
									[ $this, 'at_how_often_callback' ],    // display callback
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

								add_settings_field(
									'at_check_ssl',      // id
									'Check SSL?',              // setting title
									[ $this, 'at_check_ssl_callback' ],    // display callback
									'options_page',                 // settings page
									'options_br_id'                  // settings section
								);

				}

				public function at_sanitize( $input ) {

					// create an empty 'clean' array
					$valid = array();

					// add the cleaned values of each field to the clean array on submit
					// $valid['at_settings1'] = empty( $input['at_settings1'] ) ? '' : sanitize_text_field( $input['at_settings1'] );

					$valid['at_prevent_email_cron'] 	= (bool) empty( $input['at_prevent_email_cron'] ) ? false : true;

					$valid['at_send_email']       		= (bool) empty( $input['at_send_email'] ) ? false : true;

					$valid['at_how_often']  					= isset( $input['at_how_often'] ) ? sanitize_text_field( $input['at_how_often'] ) : 'Never';

					$valid['at_check_plugins']       	= (bool) empty( $input['at_check_plugins'] ) ? false : true;

					$valid['at_check_themes']       	= (bool) empty( $input['at_check_themes'] ) ? false : true;

					$valid['at_check_wordpress']      = (bool) empty( $input['at_check_wordpress'] ) ? false : true;

					$valid['at_check_php']     			  = (bool) empty( $input['at_check_php'] ) ? false : true;

					$valid['at_check_ssl']     			  = (bool) empty( $input['at_check_ssl'] ) ? false : true;


					// return the clean array
					return $valid;

				}

				public function at_general_section_callback() {

					echo 'Edit the settings for the plugin here.  For support or to check out the cool add-ons available for Admin Tools, visit us at <a href="http://www.nothing.com">www.nothing.com</a>.';

				}

				public function at_email_section_callback() {

					echo 'Edit the settings for the email here.';

				}

				public function at_send_email_callback() {

					// print the HTML to create the field
					printf(
						'<input id="at_send_email" name="at_options[at_send_email]" type="checkbox" value="1" %1$s />',
						checked( true, Settings::$options['at_send_email'], false )
					);

				}

				public function at_how_often_callback() {

					$options = array(
						'never'   => 'never',

						'hourly'	=>	'hourly',

						'daily'   => 'daily',

						'weekly'  => 'weekly',

						'monthly' => 'monthly',

					);

					print( '<select name="at_options[at_how_often]">' );

					foreach ( $options as $value => $label ) {

						printf(
							'<option value="%1$s" %2$s>%3$s</option>',
							esc_attr( $value ),
							selected( self::$options['at_how_often'], $value ),
							esc_html( $label )
						);

					}

					print( '</select>' );

				}


				public function at_check_plugins_callback() {

					// print the HTML to create the field
					printf(
						'<input id="at_check_plugins" name="at_options[at_check_plugins]" type="checkbox" value="1" %1$s />',
						checked( true, Settings::$options['at_check_plugins'], false )
					);

				}

				public function at_check_themes_callback() {

					// print the HTML to create the field
					printf(
						'<input id="at_check_themes" name="at_options[at_check_themes]" type="checkbox" value="1" %1$s />',
						checked( true, Settings::$options['at_check_themes'], false )
					);

				}

				public function at_check_wordpress_callback() {

					// print the HTML to create the field
					printf(
						'<input id="at_check_wordpress" name="at_options[at_check_wordpress]" type="checkbox" value="1" %1$s />',
						checked( true, Settings::$options['at_check_wordpress'], false )
					);

				}

				public function at_check_php_callback() {

					// print the HTML to create the field
					printf(
						'<input id="at_check_php" name="at_options[at_check_php]" type="checkbox" value="1" %1$s />',
						checked( true, Settings::$options['at_check_php'], false )
					);

				}

				public function at_check_ssl_callback() {

					// print the HTML to create the field
					printf(
						'<input id="at_check_ssl" name="at_options[at_check_ssl]" type="checkbox" value="1" %1$s />',
						checked( true, Settings::$options['at_check_ssl'], false )
					);

				}


}

$settings = new Settings();
