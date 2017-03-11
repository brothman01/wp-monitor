<?php

class VisitorTracker extends AdminTools {

	public function __construct() {

		add_action( 'wp_footer', [ $this, 'at_tracker' ] );

	}

	public function at_tracker() {
		//$current_visitor = new Visitor();

		$ip_address = $this->at_get_user_ip();

		echo 'the ip address of the visitor is ' . $ip_address;


	}

	public function at_get_user_ip() {

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {

			$ip = $_SERVER['HTTP_CLIENT_IP'];

		} else if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {

			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

		} else {

			$ip = $_SERVER['REMOTE_ADDR'];

		}

	}



}


$tracker = new VisitorTracker();
