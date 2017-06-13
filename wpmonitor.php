<?php
/*
 * Plugin Name: WP Monitor
 * Description: Collects important data from site and displays it on the dashboard
 * Version:     1.0.4
 * Author:      Ben Rothman
 * Slug:				wp-monitor
 * Author URI:  http://www.BenRothman.org
 * License:     GPL-2.0+
 */

class WPMonitor {

		public static $updates;

	public static $options;

	public static $grades;

	public function __construct() {

		self::$options = get_option( 'wpm_options', array(

			'wpm_how_often'	=> __( 'daily', 'wp-monitor' ),

			'wpm_send_email' => false,

			'wpm_how_often'	=> 'daily',

			'wpm_send_email' => true,

			'wpm_check_plugins' => true,

			'wpm_check_themes' => true,

			'wpm_check_wordpress' => true,

			'wpm_check_php' => true,

			'wpm_check_ssl' => true,

		) );

		update_option( 'wpm_options', self::$options );


		if ( ! function_exists( 'get_plugins' ) ) {

				require_once ABSPATH . 'wp-admin/includes/plugin.php';

		}

		add_action( 'plugins_loaded', array( $this, 'wpm_check_for_updates' ) );

		add_action( 'plugins_loaded', array( $this, 'init' ) );

		include_once( plugin_dir_path( __FILE__ ) . 'PHPVersioner.php' );

		include_once( plugin_dir_path( __FILE__ ) . 'settings.php' );

	}

	public function init() {

		add_action( 'admin_enqueue_scripts', array( $this, 'wpm_enqueue_admin_styles' ) );

		if ( current_user_can( 'manage_options' ) ) {

			add_action( 'admin_notices', array( $this, 'wpm_dashboard_widget' ) );

		}

		if ( get_option( 'wpm_config' ) !== 'active' ) {

			update_option( 'wpm_config', 'active' );

		}

		register_deactivation_hook( __FILE__, array( $this, 'wpm_deactivate' ) );

	}

	public function wpm_deactivate() {

		update_option( 'wpm_config', 'inactive' );

	}

	public function wpm_mail_indicator() {

		//return ! isset( self::$options['wpm_send_email'] ) || false === self::$options['wpm_send_email'] ? 'Email Indicator:  <img title="Email Not Scheduled." style="float: right; margin-right: 15px; width: 24px;" src="' . plugins_url( 'library/images/no-mail.png', __FILE__ ) . '"  />' : 'Email Indicator:  <img title="Email Scheduled." style="float: right; margin-right: 15px; width: 24px;" src="' . plugins_url( 'library/images/yes-mail.png', __FILE__ ) . '"  />';

		return '';

	}



	function wpm_dashboard_widget() {

		if ( get_current_screen()->base !== 'dashboard' ) {

			return;

		}
	?>

	<div id="wpm_main" class="welcome-panel" style="display: none;">

		<?php $this->wpm_dashboard_callback(); ?>

	</div>

	<script>
		jQuery(document).ready(function($) {

			$('#welcome-panel').after($('#wpm_main').show());

		});
	</script>

<?php }


	public function wpm_check_for_updates() {

		if ( ! current_user_can( 'install_plugins' ) ) {

			return;

		}

			$update_data = wp_get_update_data();

			$php_info = PHPVersioner::$info;

			$current_php_version = $this->php_version( 2 );

			$user_version_info = $php_info[ $current_php_version ];

			$user_version_supported_until = $user_version_info['supported_until'];

			$current_date = date_create();

			$php_action = ( $user_version_supported_until < date_timestamp_get( $current_date ) ) ? 'Upgrade Now' : 'Up To Date';

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

				'SSL'					=> is_ssl() ? 1 : 0,

			);

			update_option( 'wpm_update_info', self::$updates );

	}

	public function php_version( $parts ) {

		if ( 2 == $parts ) {

			return (string) substr( (string) phpversion(), 0, 3 );

		}

			return (string) phpversion();
	}




	function list_last_logins() {

				$all_users = get_users( 'blog_id=1' );

		foreach ( $all_users as $user ) {

			$timestamp = get_user_meta( $user->ID, 'last_login_timestamp', true ) ? get_user_meta( $user->ID, 'last_login_timestamp', true ) : ' - ';

			$ip = get_user_meta( $user->ID, 'last_ip', true ) ? get_user_meta( $user->ID, 'last_ip', true ) : ' - ';

			echo '<tr>' .

			'<td>' . $user->user_login . '</td>' .

			'<td class="centertext">' . $timestamp . '</td>' .

			'<td class="centertext">' . $ip . '</td>' .

			'<td class="centertext">' . '<a class="reveal-address" style="color: blue; text-decoration: underline; href="#" data-ip="' . $ip . '">Reveal</a>' . '</td>' .

			'</tr>';

		}

	}

	public function wpm_dashboard_callback() {

		echo '<div id="wpm_main">';

			echo '<div class="twothirds">

			<h1 style="text-align: center; background: #F9F9F9;">WP Monitor:'; // . '<div style="float: right; font-size: 14px;">' . apply_filters( 'wpm_mail_indicator', '' ) . '</div></h1>';

						echo '<div id="first_gauge_row" style="width: 100%; float: left; text-align: left;">';

							echo '<h3>Updates</h3>';

									echo $this->gauge_cell( __( 'Plugins Up To Date',  'wp-monitor' ), 'g1', sizeof( get_plugins() ) - self::$updates['plugins'], sizeof( get_plugins() ) );

									echo $this->gauge_cell( __( 'Themes Up To Date',  'wp-monitor' ), 'g2', sizeof( wp_get_themes() ) - self::$updates['themes'], sizeof( wp_get_themes() ) );

									echo $this->indicator_cell( __( 'WordPress Core',  'wp-monitor' ), 'wordpress');

									echo $this->php_cell( __( 'PHP',  'wp-monitor' ) );

						echo '</div>';

						echo '<div id="second_gauge_row" style="width: 100%; background: #F9F9F9; float: left;">';

							echo '<h3>Summary</h3>';

									echo $this->indicator_cell( __( 'SSL',  'wp-monitor' ), 'ssl' );

									$final_grade = ( intval( self::$updates['plugins'] ) + intval( self::$updates['themes'] ) + intval( self::$updates['WordPress'] ) + self::$updates['PHP_update'] );

									echo $this->counter_cell( __( 'Total Updates',  'wp-monitor' ), 'total' );

									echo $this->gauge_cell( __( 'Overall Grade',  'wp-monitor' ), 'g3', (integer) $this->calculate_grade(), 100 );

						echo '</div>';

						echo '<div id="third_gauge_row">

						</div>';

					echo '</div>';

					echo '<div class="tablesthird" >';

					echo '
					<div id="tabs">
					  <ul>

					    <li><a href="#tabs-1">' . __( 'Variables',  'wp-monitor' ) . '</a></li>

					    <li><a href="#tabs-2">' . __( 'User Logins',  'wp-monitor' ) . '</a></li>';

							echo apply_filters( 'wpm_tabs', '');

					  echo '</ul>';

					  echo '<div id="tabs-1">';

						echo '<table class="wp-list-table widefat fixed striped wpm_table">';

							echo '<thead>';

								echo '<tr>
									<th>' . __( 'Variable',  'wp-monitor' ) . '</th>
									<th>' . __( 'Value',  'wp-monitor' ) . '</th>
								</tr>';

								echo '</thead>';

							echo $this->variable_table();

				echo '</table>';

				echo '<a style="color: #0073aa;"href="http://wp-monitor.net/2017/03/30/what-does-that-value-mean/">What Does That Value Mean?</a>';

			echo '</div>';

					  echo '<div id="tabs-2">

								<table class="wp-list-table widefat fixed striped wpm_table">

									<thead>
										<tr>
											<th>' . __( 'Username',  'wp-monitor' ) . '</th>
											<th>' . __( 'Date/Time',  'wp-monitor' ) . '</th>
											<th>' . __( 'Last IP Used',  'wp-monitor' ) . '</th>
											<th>' . __( 'Location',  'wp-monitor' ) . '</th>
										</tr>
									</thead>';

							 $this->list_last_logins( 'wpm_table_tab', '' );

						echo '</table>';

					echo '</div>';

					echo apply_filters( 'wpm_table_tab' , '' );

				echo '</div>


					</div>

				</div>';

	}

	public function gauge_cell( $title, $gauge_class, $value, $max ) {

				$content =  '<div class="onequarter cell">';

				$content .= '<h3>' . $title . '</h3>';

				$content .= '<div id="' . $gauge_class . '" class="gauge"></div>
						<script>
							var g1_' . $gauge_class . ';
							document.addEventListener( "DOMContentLoaded", function( event ) {
								var g1_' . $gauge_class . ' = new JustGage( {
									id: "' . $gauge_class . '",
									value: ' . $value . ',
									min: 0,
									max: ' . $max . ',
									title: "' . $title . '",
									gaugeWidthScale: 0.6,
									customSectors: {
										percents: true,
										ranges: [{
											color : "#FF0000",
											lo : 0,
											hi : 50
										},{
											color : "#00FF00",
											lo : 51,
											hi : 100
										}]
									},
									counter: true
									});

							} );
						</script>';


						if ( $title == 'Overall Grade' ) {
							$content .= '<span id="grade_breakdown_link" class="breakdown_link" style="margin-left: 40%;">' . 'Why?' . '</span>';
						}

				$content .= '</div>';

				return $content;

	}

	public function indicator_cell( $title, $prefix ) {

		$content = '';

		if ( $prefix == 'wordpress' ) {

			$content = '<div class="onequarter cell">';

		}

		if ( $prefix == 'ssl' ) {

			$content = '<div class="onethird cell">';

		}


				$content .= '<h3>' . $title . '</h3>

					<div class="gauge indicator">

							<div class="indicator_light" id="' . $prefix . '_light">&nbsp;</div>

					</div>

					<p id="wpm_' . $prefix . '_message">&nbsp;</p>

					</div>';

					return $content;

	}

	public function php_cell( $title ) {

						return '<div class="onequarter cell" style="text-align: center;">

						<h3>' . $title . '</h3>

						<div id="wpm_php_indicator" class="indicator_light">&nbsp;</div>

							<p id="wpm_php_version">Running Version: ???</p>

							<p id="wpm_php_support">Supported Until: ' . '??-??-????' . '</p>

							<p id="php_message"></p>

					</div>';

	}



	public function counter_cell( $title, $prefix ) {

				return '<div class="onethird cell">

				<h3 style="padding-bottom: 5%;">' . $title . '</h3>

					<div class="gauge overall">

						<span class="counter" id="' . $prefix . '_counter">' . '&nbsp;' . '</span>

						<br />

						<span id="' . $prefix . '_breakdown_link" class="breakdown_link">' . '&nbsp;' . '</span>

					</div>

				</div>';

	}

	public function calculate_grade() {

				$grades = array(

						'Plugins' => ( ( ( sizeof( get_plugins() ) - self::$updates['plugins'] ) / sizeof( get_plugins() ) ) * 100 ),

						'Themes' => ( ( ( sizeof( wp_get_themes() ) - self::$updates['themes'] ) / sizeof( wp_get_themes() ) ) * 100 ),

						'WordPress' => ( 0 == self::$updates['WordPress'] ) ? 100 : 50,

						'PHP' => ( 0 == self::$updates['PHP_update'] ) ? 100 : 50,

						'SSL'	=> self::$updates['SSL'] ? 100 : 50,

				);

				$subtotal = $grades['Plugins'] + $grades['Themes'] + $grades['WordPress'] + $grades['PHP'] + $grades['SSL'];

				$subtotal = $subtotal / 5;

				$subtotal = round( $subtotal, 0 );

				return $subtotal;
	}



	public function variable_table() {

				$all_vars = '';



		if ( ( get_option( 'blog_public' ) == 0 ) || empty( get_option( 'blog_public' ) ) ) {

					$blog_public = 'true';

		} else {

					$blog_public = 'false';

		}


		$upload_dir = wp_upload_dir();

		$upload_dir = $upload_dir['path'];

		$upload_dir = strrev( $upload_dir );

		$upload_dir = substr( $upload_dir, strpos( $upload_dir, '/' ), strlen( $upload_dir ) );

		$upload_dir = substr( $upload_dir, strpos( $upload_dir, '/' ) + 1, strlen( $upload_dir ) );

		$upload_dir = substr( $upload_dir, strpos( $upload_dir, '/' ), strlen( $upload_dir ) );

		$upload_dir = substr( $upload_dir, strpos( $upload_dir, '/' ) + 1, strlen( $upload_dir ) );

		$upload_dir = strrev( $upload_dir );

		$uploads_size = $this->wpm_foldersize( $upload_dir );

				$variables = array(

					'Name'				=> get_bloginfo( 'name' ),

					'URL'					=> get_bloginfo( 'url' ),

					'WP Version'	=> get_bloginfo( 'version' ),

					'PHP Version'	=> phpversion(),

					'Server IP' 	=>	$_SERVER['SERVER_ADDR'],

					'Charset'			=> get_bloginfo( 'charset' ),

					'Admin Email'	=> get_bloginfo( 'admin_email' ),

					'Language'		=> get_bloginfo( 'language' ),

					'Stylesheet Directory'	=> get_bloginfo( 'stylesheet_directory' ),

					'Uploads Directory '	=>	$upload_dir,

					'Uploads Directory Size'	=>	round( $uploads_size / 1048576, 2 ) . ' MB',

					'Front Page Displays'			=> get_option( 'show_on_front' ),

					'Posts Per Page'					=> get_option( 'posts_per_page' ),

					'Atom URL'								=> get_bloginfo( 'atom_url' ),

					'SMTP'										=> ini_get( 'SMTP' ),

					'Discourage Search Engines'	=> $blog_public,

					'PHP Memory Limit'				=> ini_get( 'memory_limit' ),

				);

		update_option( 'wpm_variables', $variables );

		foreach ( $variables as $key => $value ) {

					$all_vars = $all_vars .
					'<tr>
						<th>' . $key . '</th>
						<th>' . $value . '</th>
					</tr>';

		}

				return $all_vars;

	}

	public function wpm_foldersize( $path ) {

		$total_size = 0;

		$files = scandir( $path );

		 $clean_path = rtrim( $path, '/' ) . '/';

		foreach ( $files as $t ) {

			if ( $t <> '.' && $t <> '..' ) {

				$current_file = $clean_path . $t;

				if (is_dir( $current_file )) {

					$size = $this->wpm_foldersize( $current_file );

					$total_size += $size;

				} else {

					$size = filesize( $current_file );

					$total_size += $size;

				}

			}

		}

		return $total_size;

	}

	public function ssl_check() {

		return is_ssl() ? 0 : 1;

	}

	public function wpm_general_section_callback() {

				echo 'Edit the settings for the plugin here.';

	}




	public function wpm_enqueue_admin_styles( $hook ) {

		if ( 'index.php' !== $hook ) {

			return;

		}

		wp_register_style( 'wpm_admin_css',  plugin_dir_url( __FILE__ ) . '/library/css/admin-style.css', false, '1.0.0' );
		wp_enqueue_style( 'wpm_admin_css' );

		wp_register_script( 'wpm_counter', plugin_dir_url( __FILE__ ) . 'library/js/renamed.js', array( 'jquery' ), '1.0.0' );
		wp_localize_script( 'wpm_counter', 'wpm_data', array(

			'total'	=> self::$updates['plugins'] + self::$updates['themes'] + self::$updates['WordPress'] + self::$updates['PHP_update'],

			'grade'	=> (integer) $this->calculate_grade(),

			'wordpress'	=> intval( self::$updates['WordPress'] ),

			'ssl'	=> self::$updates['SSL'],

			'plugin_updates' => self::$updates['plugins'],

			'total_plugins'	=>	sizeof ( get_plugins() ),

			'total_themes'	=>	sizeof ( wp_get_themes() ),

			'theme_updates' => self::$updates['themes'],

			'wordpress_updates' => self::$updates['WordPress'],

			'php_updates' => self::$updates['php_action'],

			'ssl' => self::$updates['SSL'] ? 'On' : 'Off',

		) );
		wp_enqueue_script( 'wpm_counter' );

		wp_register_script( 'wpm_phpcell', plugin_dir_url( __FILE__ ) . 'library/js/phpcell.js', array( 'jquery' ), '1.0.0' );
		wp_localize_script( 'wpm_phpcell', 'wpm_data_php', array(

			'current_version' => $this->php_version( 2 ),

			'state'	=> self::$updates['php_action'],

			'supported_until' =>	gmdate('m-d-Y', PHPVersioner::$info[$this->php_version( 2 )]['supported_until'] ),

		) );
		wp_enqueue_script( 'wpm_phpcell' );

		wp_register_script( 'tabs-init',  plugin_dir_url( __FILE__ ) . '/library/js/tabs-init.jquery.js', array( 'jquery-ui-tabs' ) );
		wp_enqueue_script( 'tabs-init' );

		wp_register_style( 'wpm_tabs_css',  'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css', false, '1.0.0' );
		wp_enqueue_style( 'wpm_tabs_css' );

		/* Gauges */
		wp_register_style( 'wpm_justgage_css',  plugin_dir_url( __FILE__ ) . '/library/css/justgage.css', false, '1.0.0' );
		wp_enqueue_style( 'wpm_justgage_css' );

		wp_register_script( 'wpm_raphael',  plugin_dir_url( __FILE__ ) . '/library/js/raphael-2.1.4.min.js' );
		wp_enqueue_script( 'wpm_raphael' );

		wp_register_script( 'wpm_justgage',  plugin_dir_url( __FILE__ ) . '/library/js/justgage.js' );
		wp_enqueue_script( 'wpm_justgage' );

		/* Reveal */
		 wp_register_script( 'wpm_revealer',  plugin_dir_url( __FILE__ ) . '/library/js/revealer.js', array( 'jquery' ), '1.0.0' );
		 wp_enqueue_script( 'wpm_revealer' );

	}



}

$wp_monitor = new WPMonitor();
