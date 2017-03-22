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


	public function __construct() {

		// get option 'at_options' value from the database and put it in the array $options
		self::$options = get_option( 'at_options', array(

			'at_how_often'	=> __( 'daily', 'admin-tools' ),

			'at_send_email' => true,

			'at_check_plugins' => true,

			'at_check_themes' => true,

			'at_check_wordpress' => true,

			'at_check_php' => true,

			'at_check_ssl' => true,

		) );

		add_action( 'init', array( $this, 'at_check_for_updates' ) );

		add_action( 'plugins_loaded', array( $this, 'init' ) );

		// include other files
		include_once( plugin_dir_path( __FILE__ ) . 'PHPVersioner.php' );

		include_once( plugin_dir_path( __FILE__ ) . 'settings.php' );

	}

	public function init() {

		add_action( 'admin_enqueue_scripts', array( $this, 'at_enqueue_admin_styles' ) );

		add_action( 'admin_footer', array( $this, 'at_dashboard_widget' ) );

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

			$current_php_version = ( 2 == substr_count( phpversion(), '.' ) ) ? substr( phpversion(), 0, -2 ) : phpversion();

			$user_version_info = $php_info[ $current_php_version ];

			$user_version_supported_until = $user_version_info['supported_until'];

			$current_date = date_create();

			$php_action = ( $user_version_supported_until < date_timestamp_get( $current_date ) ) ? 'Upgrade Now' : 'Up To Date';

			//print_r( $user_version_supported_until . ' vs ' . date_timestamp_get($current_date) );

		if ( 'Upgrade Now' == $php_action ) {

				$php_update = 1;

		} else {

				$php_update = 0;

		}

			$user_version_supported_until = gmdate( 'm-d-Y', $user_version_supported_until );

			self::$updates = array(

				'plugins'	=> $update_data['counts']['plugins'],

				'themes'	=> $update_data['counts']['themes'],

				'WordPress'	=> $update_data['counts']['wordpress'],

				'PHP_supported_until' => $user_version_supported_until,

				'php_action'	=> $php_action,

				'PHP_update'	=> $php_update,

				'PHP_warning' => $user_version_info['supported_until'],

				'SSL'					=> $this->ssl_check( false ),

			);

			update_option( 'at_update_info', self::$updates );

	}




	function list_last_logins() {

				$all_users = get_users( 'blog_id=1' );

				$response = wp_remote_get('http://www.ip-api.com/json/' . get_user_meta( $user->ID, 'last_ip', true ) );

				$body = wp_remote_retrieve_body( $response );

				$data = json_decode( $body, true );

		foreach ( $all_users as $user ) {

						echo '<tr>' .

						'<th>' . $user->user_login . '</th>' .

						'<th>' . get_user_meta( $user->ID, 'last_login_timestamp', true ) . '</th>' .

						'<th>' . get_user_meta( $user->ID, 'last_ip', true ) . '</th>' .

						'<th>' . $data['city'] . ', ' . $data['region'] . ', ' . $data['country'] . '</th>' .

						'</tr>';

		}

	}

	public function at_dashboard_callback() {

			echo '<div id="dashboard_main">';

				echo '<div class="twothirds">

				<h1 style="text-align: center; background: #F9F9F9;">Site Status:</h1>';

							echo '<div id="first_gauge_row" style="width: 100%; float: left; text-align: left;">';

								echo '<h3>Updates</h3>';

										echo $this->gauge_cell( __( 'Plugins',  'admin-tools' ), 'g1', sizeof( get_plugins() ) - self::$updates['plugins'], sizeof( get_plugins() ) );

										echo $this->gauge_cell( __( 'Themes',  'admin-tools' ), 'g2', sizeof( wp_get_themes() ) - self::$updates['themes'], sizeof( wp_get_themes() ) );

										echo $this->indicator_cell( __( 'WordPress Core',  'admin-tools' ), 'wordpress', self::$updates['WordPress'] );

										echo $this->php_cell( __( 'PHP',  'admin-tools' ) );

							echo '</div>';

							echo '<div id="second_gauge_row" style="width: 100%; background: #F9F9F9; float: left;">';

								echo '<h3>Summary</h3>';

										echo $this->ssl_cell( __( 'SSL',  'admin-tools' ), 'onethird' );

										$final_grade = ( intval( self::$updates['plugins'] ) + intval( self::$updates['themes'] ) + intval( self::$updates['WordPress'] ) + self::$updates['PHP_update'] );

										echo $this->counter_cell( __( 'Total Updates',  'admin-tools' ), 'total' );

										echo $this->counter_cell( __( 'Overall Grade',  'admin-tools' ), 'grade' );

										// echo  . '<br />' . '<span id="ssl_note">(' . __( $this->ssl_check( true ),  'admin-tools' ) . ')</span>';

							echo '</div>';

							echo '<div id="third_gauge_row">

							</div>';

						echo '</div>';

						echo '<div class="tablesthird" >';

						echo '
						<div id="tabs">
						  <ul>
						    <li><a href="#tabs-1">' . __( 'Variables',  'admin-tools' ) . '</a></li>
						    <li><a href="#tabs-2">' . __( 'User Logins',  'admin-tools' ) . '</a></li>
						  </ul>
						  <div id="tabs-1">';

							echo '<table class="wp-list-table widefat fixed striped at_table">';

								echo '<thead>';

									echo '<tr>
										<th>' . __( 'Variable',  'admin-tools' ) . '</th>
										<th>' . __( 'Value',  'admin-tools' ) . '</th>
									</tr>';

									echo '</thead>';

								echo $this->variable_table();

					echo '</table>';

						  echo '</div>
						  <div id="tabs-2">

									<table class="wp-list-table widefat fixed striped at_table">

										<thead>
											<tr>
												<th>' . __( 'Username',  'admin-tools' ) . '</th>
												<th>' . __( 'Last Login Date/Time',  'admin-tools' ) . '</th>
												<th>' . __( 'Last IP Used',  'admin-tools' ) . '</th>
												<th>' . __( 'Location',  'admin-tools' ) . '</th>
											</tr>
										</thead>';

								 $this->list_last_logins();

							echo '</table>
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

											if ("' . self::$updates['php_action'] . '" == "Up To Date") {

												php_action_field.style.background = "#00CB25";

												php_action_field.value = "' . self::$updates['php_action'] . '";

											} else {

												php_action_field.style.background = "#DA6768";

												php_action_field.style.color = "white";

											}

									}, 1000);

								} );
							</script>

					</div>';

	}

	public function ssl_cell( $title, $class ) {

				return '<div class="' . $class . ' cell">

				<h3>' . $title . '</h3>

				<div class="gauge indicator">

					<div class="inner_indicator">

						<div class="indicator_light" id="ssl_red_light">&nbsp;</div>

						<div class="indicator_light" id="ssl_green_light">&nbsp;</div>

					</div>

				</div>

			</div>';

	}


	public function counter_cell( $title, $prefix ) {

				return '<div class="onethird cell">

				<h3>' . $title . '</h3>

					<div class="gauge overall">

						<span class="counter" id="' . $prefix . '_counter">' . '&nbsp;' . '</span>' .

					'</div>

				</div>';

	}

	public function calculate_grade() {

				$grades = array(

						'Plugins' => ( ( sizeof( get_plugins() ) - self::$updates['plugins'] ) / sizeof( get_plugins() ) * 100),

						'Themes' => ( ( sizeof( wp_get_themes() ) - self::$updates['themes'] ) / sizeof( wp_get_themes() ) * 100),

						'WordPress' => ( 0 == self::$updates['WordPress'] ) ? 100 : 0,

						'PHP' => ( 0 == self::$updates['PHP_update'] ) ? 100 : 25,

				);

					$subtotal = $grades['Plugins'] + $grades['Themes'] + $grades['WordPress'] + $grades['PHP'];

				$subtotal = $subtotal / 4;

				$subtotal = round( $subtotal, 0 );

				return $subtotal;
	}



	public function variable_table() {

				$all_vars = '';

		if ( ( get_option( 'users_can_register' ) == 0 ) || empty( get_option( 'users_can_register' ) ) ) {

					$anyone_can_register = 'false';

		} else {

					$anyone_can_register = 'true';

		}

		if ( ( get_option( 'blog_public' ) == 0 ) || empty( get_option( 'blog_public' ) ) ) {

					$blog_public = 'true';

		} else {

					$blog_public = 'false';

		}

				$variables = array(

					__( 'WP Version', 'admin-tools' )	=> get_bloginfo( 'version' ),

					__( 'PHP Version', 'admin-tools' )	=> phpversion(),

					__( 'Name', 'admin-tools' )				=> get_bloginfo( 'name' ),

					__( 'URL', 'admin-tools' )					=> get_bloginfo( 'url' ),

					__( 'Charset', 'admin-tools' )			=> get_bloginfo( 'charset' ),

					__( 'Admin Email', 'admin-tools' )	=> get_bloginfo( 'admin_email' ),

					__( 'Language', 'admin-tools' )		=> get_bloginfo( 'language' ),

					__( 'Stylesheet Directory', 'admin-tools' )	=> get_bloginfo( 'stylesheet_directory' ),

					__( 'Anyone Can Register', 'admin-tools' )			=> $anyone_can_register,

					__( 'Front Page Displays', 'admin-tools' )			=> get_option( 'show_on_front' ),

					__( 'Posts Per Page', 'admin-tools' )					=> get_option( 'posts_per_page' ),

					__( 'Atom URL', 'admin-tools' )								=> get_bloginfo( 'atom_url' ),

					__( 'SMTP', 'admin-tools' )										=> ini_get( 'SMTP' ),

					__( 'Discourage Search Engines', 'admin-tools' )	=> $blog_public,

					__( 'PHP Memory Limit', 'admin-tools' )				=> ini_get( 'memory_limit' ),

				);

		foreach ( $variables as $key => $value ) {

					$all_vars = $all_vars .
					'<tr>
						<th>' . $key . '</th>
						<th>' . $value . '</th>
					</tr>';

		}

				return $all_vars;

	}

	public function ssl_check( $print ) {

		if ( $print ) {

			return is_ssl() ? __( 'SSL', 'admin-tools' ) : __( 'No SSL', 'admin-tools' );

		} else {

				return is_ssl() ? 1 : 0;

		}

	}

	public function at_general_section_callback() {

				echo 'Edit the settings for the plugin here.';

	}




	public function at_enqueue_admin_styles( $hook ) {

		// wp_die( $hook );

		if ( 'index.php' !== $hook ) {

			return;

		}

		wp_register_style( 'at_admin_css',  plugin_dir_url( __FILE__ ) . '/library/css/admin-style.css', false, '1.0.0' );
		wp_enqueue_style( 'at_admin_css' );

		/* Dashboard Scripts */
		wp_register_script( 'at_indicator', plugin_dir_url( __FILE__ ) . '/library/js/indicator.js', array('jquery'), '1.0.0' );
		wp_localize_script('at_indicator', 'at_data', array(

			'wordpress'	=> self::$updates['wordpress'],

			'ssl'	=> self::$updates['SSL'],

		) );
		wp_enqueue_script( 'at_indicator' );

		wp_register_script('at_counter', plugin_dir_url( __FILE__ ) . '/library/js/counter.js', array('jquery'), '1.0.0' );
		wp_localize_script('at_counter', 'at_data', array(

			'total'	=> self::$updates['plugins'] + self::$updates['themes'] + self::$updates['WordPress'] + self::$updates['php_update'],

			'grade'	=> (integer) $this->calculate_grade(),

		) );
		wp_enqueue_script( 'at_counter' );

		/* Tabs */
		wp_register_script( 'tabs-init',  plugin_dir_url( __FILE__ ) . '/library/js/tabs-init.jquery.js', array( 'jquery-ui-tabs' ) );
		wp_enqueue_script( 'tabs-init' );

		wp_register_style( 'at_tabs_css',  'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css', false, '1.0.0' );
		wp_enqueue_style( 'at_tabs_css' );

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
