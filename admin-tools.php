<?php
/*
 * Plugin Name: Admin Tools
 * Description: Notify user when updates to WordPress are needed.
 * Version:     0.0.1
 * Author:      Ben Rothman
 * Slug:				???
 * Author URI:  http://www.BenRothman.org
 * License:     GPL-2.0+
 */

class AdminTools {

	public static $updates;

	public static $options;

	public static $grades;


	public function __construct( ) {

		// get option 'at_options' value from the database and put it in the array $options
		self::$options = get_option( 'at_options', [


			'at_how_often'	=>	'daily',

			'at_send_email' => true,

			'at_check_plugins' => true,

			'at_check_themes' => true,

			'at_check_wordpress' => true,

			'at_check_php' => true,

			'at_check_ssl' => true,

		] );

		if ( empty( get_option( 'at_prevent_email_cron' ) ) ) {

			update_option( 'at_prevent_email_cron', 0, 1 );

		}

		// check for updates
		add_action( 'init', [ $this, 'at_check_for_updates' ] );

		add_action( 'plugins_loaded', [ $this, 'init' ] );

		//add cron times
		add_filter( 'cron_schedules', [ $this, 'custom_cron_schedules' ] );

		// include other files
		include_once( plugin_dir_path( __FILE__ ) . 'PHPVersioner.php' );

		include_once( plugin_dir_path( __FILE__ ) . 'send-email.php' );



	}

	public function init() {

		// enqueue the admin stylesheet
		add_action( 'admin_enqueue_scripts', [ $this, 'at_enqueue_admin_styles' ] );

		// dashboard widget
		add_action( 'admin_footer', [ $this, 'at_dashboard_widget' ] );

	}

	public function custom_cron_schedules( $schedules ) {


		if ( ! isset( $schedules['weekly'] ) ) {

			$schedules['weekly'] = array(
				'interval' => 604800,
				'display'  => __( 'Once Per Week' ),
			);

		}

		if ( ! isset( $schedules['monthly'] ) ) {

			$schedules['monthly'] = array(
				'interval' => 2628000,
				'display'  => __( 'Once Per Month' ),
			);

		}

		return $schedules;

	}

	function at_dashboard_widget() {
	// Bail if not viewing the main dashboard page
	if ( get_current_screen()->base !== 'dashboard' ) {

		return;

	}
	?>

	<div id="custom-id" class="welcome-panel" style="display: none;">

		<?php $this->at_dashboard_callback(); ?>

	</div>

	<script>
		jQuery(document).ready(function($) {

			$('#welcome-panel').after($('#custom-id').show());

		});
	</script>

<?php }



	public function at_check_for_updates() {

		if ( ! current_user_can( 'install_plugins' ) ) {

			return;

		}

		// get update data (only after role of user has been checked)
			$update_data = wp_get_update_data();

			$php_info = PHPVersioner::$info;

			$current_php_version = ( 2 == substr_count( phpversion(), '.' ) ) ? substr(phpversion(), 0, -2) : phpversion();

			$user_version_info = $php_info[ $current_php_version ];

			$user_version_supported_until = $user_version_info[ 'supported_until' ];

			$current_date = date_create();

			$PHP_action = ($user_version_supported_until < date_timestamp_get($current_date) ) ? 'Upgrade Now' : 'Up To Date';

			//print_r( $user_version_supported_until . ' vs ' . date_timestamp_get($current_date) );

			if ( $PHP_action == "Upgrade Now" ) {

				$php_update = 1;

			} else {

				$php_update = 0;

			}

			$user_version_supported_until = gmdate("m-d-Y", $user_version_supported_until);


			self::$updates = array(

				'plugins'	=>	$update_data['counts']['plugins'],

				'themes'	=>	$update_data['counts']['themes'],

				'WordPress'	=>	$update_data['counts']['wordpress'],

				'PHP_supported_until' => $user_version_supported_until,

				'PHP_action'	=>	$PHP_action,

				'PHP_update'	=>	$php_update,

				'PHP_warning' => $user_version_info[ 'supported_until' ],

				'SSL'					=>	$this->ssl_check( false ),

			);

			update_option( 'at_update_info', self::$updates );


	}




			function list_last_logins() {

				$all_users = get_users( 'blog_id=1' );

				foreach ($all_users as $user) {

						echo '<tr>' .

						'<th>' . $user->user_login . '</th>' .

						'<th>' . get_user_meta(  $user->ID, 'last_login_timestamp', true ) . '</th>' .

						'<th>' . get_user_meta(  $user->ID, 'last_ip', true ) . '</th>' .

						'</tr>';

				}

		}

			public function at_dashboard_callback() {

					echo '<div id="dashboard_main">';

						echo '<div class="twothirds">

						<h1 style="text-align: center; background: #F9F9F9;">Site Status:</h1>';

							echo '<div id="first_gauge_row" style="width: 100%; float: left; text-align: left;">';

								echo '<h3>Updates</h3>';

										echo $this->gauge_cell( 'Plugins', 'g1', sizeof( get_plugins() ) - self::$updates['plugins'], sizeof( get_plugins() ) );

										echo $this->gauge_cell( 'Themes', 'g2', sizeof( wp_get_themes() ) - self::$updates['themes'], sizeof( wp_get_themes() ) );

										echo $this->indicator_cell( 'WordPress Core', 'wordpress', self::$updates['WordPress'] );

										echo $this->php_cell( 'PHP' );

							echo '</div>';


							echo '<div id="second_gauge_row" style="width: 100%; background: #F9F9F9; float: left;">';

								echo '<h3>Summary (Pro Version Only)</h3>';

										echo $this->ssl_cell( 'SSL', 'onethird' );

										echo $this->counter_cell( 'Total Updates', '#' );

										echo $this->counter_cell( 'Overall Grade', '#' . '<br />' . '<span id="ssl_note">(' . 'SSL On/Off' . ')</span>');

							echo '</div>';

						echo '</div>';


						echo '<div class="tablesthird" >';


							echo '<div class="half left_half">';

							echo '<h3 style="text-align: center;">Variables</h3>';

								echo '<table class="wp-list-table widefat fixed striped at_table">';

									echo '<thead>';

										echo '<tr>
											<th>Variable</th>
											<th>Value</th>
										</tr>';

										echo '</thead>';

									echo $this->variable_table();

						echo '</table>';

						echo '</div>';

						echo '<div class="half">
						<h3 style="text-align: center;">User Logins:</h3>';

						// 		<table class="wp-list-table widefat fixed striped at_table">
						//
						// 			<thead>
						// 				<tr>
						// 					<th>Username</th>
						// 					<th>Last Login Date/Time</th>
						// 					<th>Last IP Used</th>
						// 				</tr>
						// 			</thead>';
						//
						//
						//
						// 	 $this->list_last_logins();
						//
						//
						// echo '</table>

						echo '
						<div id="tabs">
						  <ul>
						    <li><a href="#tabs-1">Nunc tincidunt</a></li>
						    <li><a href="#tabs-2">Proin dolor</a></li>
						    <li><a href="#tabs-3">Aenean lacinia</a></li>
						  </ul>
						  <div id="tabs-1">
						    <p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. Nam elementum quam ullamcorper ante. Etiam aliquet massa et lorem. Mauris dapibus lacus auctor risus. Aenean tempor ullamcorper leo. Vivamus sed magna quis ligula eleifend adipiscing. Duis orci. Aliquam sodales tortor vitae ipsum. Aliquam nulla. Duis aliquam molestie erat. Ut et mauris vel pede varius sollicitudin. Sed ut dolor nec orci tincidunt interdum. Phasellus ipsum. Nunc tristique tempus lectus.</p>
						  </div>
						  <div id="tabs-2">
						    <p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>
						  </div>
						  <div id="tabs-3">
						    <p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce sodales. Quisque eu urna vel enim commodo pellentesque. Praesent eu risus hendrerit ligula tempus pretium. Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>
						    <p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra justo vitae neque. Praesent blandit adipiscing velit. Suspendisse potenti. Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros, id euismod lacus dolor eget odio. Nam scelerisque. Donec non libero sed nulla mattis commodo. Ut sagittis. Donec nisi lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean vehicula velit eu tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit. Fusce in lacus. Vivamus a libero vitae lectus hendrerit hendrerit.</p>
						  </div>
						</div>';

					echo '</div>


						</div>

					</div>';

			}

			public function gauge_cell( $title, $gauge_class, $value, $max ) {

				return '<div class="onequarter cell">

					<div id="' . $gauge_class . '" class="gauge"></div>
						<script>
							var g1;
							document.addEventListener( "DOMContentLoaded", function( event ) {
								var g1 = new JustGage( {
									id: "' . $gauge_class . '",
									value: ' . $value . ',
									min: 0,
									max: ' . $max . ',
									title: "' . $title . '",
									} );
							} );
						</script>

				</div>';

			}

			public function indicator_cell( $title, $class_prefix, $setting ) {

				return '<div class="onequarter cell">
				<h3>' . $title . '</h3>

					<div class="gauge indicator">

						<div class="inner_indicator">

							<div class="indicator_light" id="' . $class_prefix . '_red_light">&nbsp;</div>

							<div class="indicator_light" id="' . $class_prefix . '_green_light">&nbsp;</div>

						</div>

					</div>

								<script>

									document.addEventListener( "DOMContentLoaded", function( event ) {

										var green_light = document.getElementById("' . $class_prefix . '_green_light");

										var red_light = document.getElementById("' . $class_prefix . '_red_light");

										setTimeout(function(){

											if (' . $setting .' == 1) {

												green_light.style.background = "#01FC27";

											} else {

												red_light.style.background = "#FF0000";

											}

										}, 1500);

									} );
								</script>

								</div>';

			}

			public function php_cell( $title ) {

						return '<div class="onequarter cell" style="text-align: center;">
						<h3 style="margin-bottom: 5px;">' . $title . '</h3>

							<p>Running Version: ' . phpversion() . '</p>

							<p>Supported Until: ' . self::$updates['PHP_supported_until'] . '</p>

							<input id="php_action_field" type="text" maxlength="14" size="14" style="text-align: center; font-style: bold;" readonly />

							<script>

								document.addEventListener( "DOMContentLoaded", function( event ) {

									var php_action_field = document.getElementById("php_action_field");


									setTimeout(function(){

											if ("' . self::$updates['PHP_action'] .'" == "Up To Date") {

												php_action_field.style.background = "#01FC27";

												php_action_field.value = "' . self::$updates['PHP_action'] .'";

											} else {

												php_action_field.style.background = "red";

												php_action_field.style.color = "white";

											}

									}, 1000);

								} );
							</script>

					</div>';

			}

			public function ssl_cell( $title, $class) {

					return '<div class="' . $class . ' cell">

					<h3>' . $title . '</h3>

					<div class="gauge indicator">

						<div class="inner_indicator">

							<div class="indicator_light" id="ssl_red_light">&nbsp;</div>

							<div class="indicator_light" id="ssl_green_light">&nbsp;</div>

						</div>

					</div>

					<script>

						document.addEventListener( "DOMContentLoaded", function( event ) {

							var ssl = ' . $this->ssl_check( false ) . ';' . '

							var ssl_green_light = document.getElementById("ssl_green_light");

							var ssl_red_light = document.getElementById("ssl_red_light");


							setTimeout(function(){

									ssl_green_light.style.background = "#01FC27";

									ssl_red_light.style.background = "red";



							}, 2000);


						} );
					</script>

				</div>';

			}

			public function counter_cell( $title, $value ) {

				return '<div class="onethird cell">
				<h3>' . $title . '</h3>

					<div class="gauge overall">

						<div class="counter">' . $value . '</div>' .

					'</div>

				</div>';

			}

			public function calculate_grade() {

				$grades = array(

						'Plugins' => ( ( sizeof( get_plugins() ) - self::$updates['plugins'] ) / sizeof( get_plugins() ) * 100),

						'Themes' => ( ( sizeof( wp_get_themes() ) - self::$updates['themes'] ) / sizeof( wp_get_themes() ) * 100),

						'WordPress' =>	( self::$updates['WordPress'] == 0 ) ? 100 : 0,

						'PHP' =>	( self::$updates['PHP_update'] == 0 ) ? 100 : 25,

				);

					$subtotal = $grades['Plugins'] + $grades['Themes'] + $grades['WordPress'] + $grades['PHP'];


				$subtotal = $subtotal / 4;

				$subtotal = round( $subtotal, 0 );



				return $subtotal;
			}



			public function variable_table() {

				$all_vars = '';

				if ( ( get_option('users_can_register') == 0 ) || empty( get_option('users_can_register') ) ) {

					$anyone_can_register = 'false';

				} else {

					$anyone_can_register = 'true';

				}


				if ( ( get_option('blog_public') == 0 ) || empty( get_option('blog_public') ) ) {

					$blog_public = 'true';

				} else {

					$blog_public = 'false';

				}


				$variables = array(

					'WP Version'	=> get_bloginfo('version'),

					'PHP Version'	=> phpversion(),

					'Name'				=> get_bloginfo('name'),

					'URL'					=>	get_bloginfo('url'),

					'Charset'			=>	get_bloginfo('charset'),

					'Admin Email'	=>	get_bloginfo('admin_email'),

					'Language'		=>	get_bloginfo('language'),

					'Stylesheet Directory'	=>	get_bloginfo('stylesheet_directory'),

					'Anyone Can Register'			=>	$anyone_can_register,

					'Front Page Displays'			=> get_option( 'show_on_front' ),

					'Posts Per Page'					=>	get_option( 'posts_per_page' ),

					'Atom URL'								=>	get_bloginfo('atom_url'),

					'SMTP'										=>	ini_get("SMTP"),

					'Discourage Search Engines'=>	$blog_public,

					'PHP Memory Limit'				=>	ini_get("memory_limit"),

				);

				foreach($variables as $key => $value) {

					$all_vars = $all_vars .
					'<tr>
						<th>' . $key . '</th>
						<th>' . $value .'</th>
					</tr>';

				}

				return $all_vars;




			}

			public function ssl_check( $print ) {

				if ( $print ) {

				return is_ssl() ? 'SSL' : 'No SSL';

			} else {

				return is_ssl() ? 1 : 0;

			}

			}

			public function at_general_section_callback() {

				echo 'Edit the settings for the plugin here.';

			}

			public function at_email_section_callback() {

				echo 'Edit the settings for the email here.';

			}

			public function at_user_timeout_callback() {

				// print the HTML to create the field
				printf(
					'<input id="at_user_timeout" name="at_options[at_user_timeout]" type="text" value="%1$s" /> %2$s',
					Settings::$options['at_user_timeout'], 'minutes'
				);

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


	public function at_enqueue_admin_styles( $hook ) {

		// wp_die( $hook );

		if ( 'index.php' !== $hook ) {

			return;

		}

		wp_register_style( 'at_admin_css',  plugin_dir_url( __FILE__ ) . '/library/css/admin-style.css', false, '1.0.0' );
		wp_enqueue_style( 'at_admin_css' );

		/* Tabs */
		wp_register_script( 'tabs-init',  plugin_dir_url( __FILE__ ) . '/library/js/tabs-init.jquery.js' );
		wp_enqueue_script( 'tabs-init' );

		/* Gauges */
		wp_register_style( 'at_justgage_css',  plugin_dir_url( __FILE__ ) . '/library/css/justgage.css', false, '1.0.0' );
		wp_enqueue_style( 'at_justgage_css' );

		wp_register_script( 'at_raphael',  plugin_dir_url( __FILE__ ) . '/library/js/raphael-2.1.4.min.js', array( 'jquery-ui-tabs' ) );
		wp_enqueue_script( 'at_raphael' );

		wp_register_script( 'at_justgage',  plugin_dir_url( __FILE__ ) . '/library/js/justgage.js' );
		wp_enqueue_script( 'at_justgage' );

	}



}

$admin_tools = new AdminTools();
