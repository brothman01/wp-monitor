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

			'at_user_timeout' => 5,

			'at_how_often'	=>	'daily',

			'at_send_email' => false,

			'at_check_plugins' => false,

			'at_check_themes' => false,

			'at_check_wordpress' => false,

			'at_check_php' => false,

			'at_check_ssl' => false,

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
		include_once( plugin_dir_path( __FILE__ ) . 'settings.php' );

		include_once( plugin_dir_path( __FILE__ ) . 'user-log.php' );

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

								echo '<h3>Summary</h3>';

										echo $this->ssl_cell( 'SSL', 'onethird' );

										echo $this->counter_cell( 'Total Updates', ( intval( self::$updates['plugins'] ) + intval( self::$updates['themes'] ) + intval( self::$updates['WordPress'] ) + self::$updates['PHP_update'] ) );

										echo $this->counter_cell( 'Overall Grade', $this->calculate_grade() . '<br />' . '<span id="ssl_note">(' . $this->ssl_check( true ) . ')</span>');

							echo '</div>';

						echo '</div>';


						echo '<div class="onethird" >';


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
						<h3 style="text-align: center;">User Logins:</h3>

								<table class="wp-list-table widefat fixed striped at_half_table">

									<thead>
										<tr>
											<th>Username</th>
											<th>Last Login Date/Time</th>
											<th>Last IP Used</th>
										</tr>
									</thead>';



							 $this->list_last_logins();


						echo '</table>


						<h3 style="text-align: center;">Referrals:</h3>

								<table class="wp-list-table widefat fixed striped at_half_table">

									<thead>
										<tr>
											<th>URL</th>
											<th>Count</th>
										</tr>
									</thead>';



							 $this->list_last_logins();


						echo '</table>

						</div>


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

							<input id="php_action_field" type="text" maxlength="14" size="14" style="text-align: center; font-size: 18px; font-style: bold;" readonly />

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

								if (  ssl == 1 ) {

									ssl_green_light.style.background = "#01FC27";

								} else {

									ssl_red_light.style.background = "red";

								}

							}, 2000);


						} );
					</script>

				</div>';

			}

			public function counter_cell( $title, $value ) {

				return '<div class="onethird cell">
				<h3>' . $title . '</h3>

					<div class="gauge overall">' .

						$value .

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

		/* Gauges */
		wp_register_style( 'at_justgage_css',  plugin_dir_url( __FILE__ ) . '/library/css/justgage.css', false, '1.0.0' );
		wp_enqueue_style( 'at_justgage_css' );

		wp_register_script( 'at_raphael',  plugin_dir_url( __FILE__ ) . '/library/js/raphael-2.1.4.min.js' );
		wp_enqueue_script( 'at_raphael' );

		wp_register_script( 'at_justgage',  plugin_dir_url( __FILE__ ) . '/library/js/justgage.js' );
		wp_enqueue_script( 'at_justgage' );

	}



}

$admin_tools = new AdminTools();
