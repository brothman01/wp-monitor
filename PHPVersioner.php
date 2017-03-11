<?php

class PHPVersioner extends AdminTools {

public static $info;

	public function __construct() {

		self::$info = $this->at_version_info();

		//print_r( $this->at_version_info() );

	}

	public function at_version_info() {



			$contents = wp_remote_get( 'http://php.net/supported-versions.php' );

			$body = str_replace( '<link rel="shortcut icon" href="http://php.net/favicon.ico">', '', wp_remote_retrieve_body( $contents ) );

			$dom = new DOMDocument;

			// Supress PHP HTML5 tag warnings
			libxml_use_internal_errors( true );
			$dom->loadHTML( $body );
			$tr = $dom->getElementsByTagName( 'tr' );
			$column_text = array();
			$x = 1;

			foreach ( $tr as $row ) {

				$columns = $row->getElementsByTagName( 'td' );

				foreach ( $columns as $column ) {

					$column_text[ $x ] = trim( str_replace( '*', '', $column->textContent ) );

					$x++;

				}
			}

			$column_text = array_chunk( $column_text, 7 );

			unset( $column_text[3] );

			$php_version_info = array();

			$y = 0;

			foreach ( $column_text as $php_info ) {

				$php_version_info[ $php_info[0] ] = array(
					'released'        => strtotime( $php_info[1] ),
					'supported_until' => strtotime( $php_info[3] ),
					'security_until'  => strtotime( $php_info[5] ),
				);

				$y++;
			}


		return $php_version_info;
	}

}

$versioner = new PHPVersioner();
