<?php

class Settings extends AdminTools {

	public static $options;

	public function __construct() {

		self::$options = AdminTools::$options;

		 wp_die( print_r( self::$options ) );

	}

}

$settings = new Settings();
