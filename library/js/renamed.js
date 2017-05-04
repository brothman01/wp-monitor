jQuery(document).ready(function() {

		// alert('aaa');
		 var time;

		 var total_message =
		 'Plugin Updates: ' + wpm_data.plugin_updates + '\n' +
		 'Theme Updates: ' + wpm_data.theme_updates + '\n' +
		 'WordPress Updates: ' + wpm_data.wordpress_updates + '\n' +
		 'PHP: ' + wpm_data.php_updates;

		 var grade_message =
		 'Plugin Updates: ' + ( wpm_data.total_plugins - wpm_data.plugin_updates ) + ' / ' + wpm_data.total_plugins + ' up to date' + '\n' +
		 'Theme Updates: ' + ( wpm_data.total_themes - wpm_data.theme_updates ) + ' / ' + wpm_data.total_themes + ' up to date' + '\n' +
		 'WordPress Updates: ' + wpm_data.wordpress_updates + ' updates' + '\n' +
		 'PHP Updates: ' + wpm_data.php_updates + '\n' +
		 'SSL: ' + wpm_data.ssl;

		 for (time = 0; time < 25; time++) {
	//
	 	setTimeout(function() {

		var number = Math.floor(Math.random() * 100) + 1;

		jQuery('#total_counter').text(number);

		jQuery('#grade_counter').text(number);

	}, (time * 50) );
	//
	}

		setTimeout(function(){

			jQuery('#total_counter').text( wpm_data.total );

			jQuery('#grade_counter').text( wpm_data.grade );



			jQuery('#total_breakdown_link').html('<a href="#">Why? (Hover)</a>');

			jQuery('#total_breakdown_link').attr('title', total_message);


			jQuery('#grade_breakdown_link').html('<a href="#" style="text-align: center;">Why? (Hover)</a>');

			jQuery('#grade_breakdown_link').attr('title', grade_message);


		}, ((time + 1) * 50) );

		setTimeout(function(){

			if ( wpm_data.wordpress === 0 ) {

				jQuery('#wordpress_light').css( 'background', '#01FC27' );

				jQuery('#wpm_wordpress_message').text( 'Up To Date' );

			} else {

				jQuery('#wordpress_light').css( 'background', '#FF0000' );

				jQuery('#wpm_wordpress_message').text( 'Update Now!' );

			}

			if ( wpm_data.ssl == 'Off' ) {

				jQuery('#ssl_light').css( 'background', '#FF0000' );

				jQuery('#wpm_ssl_message').html( 'SSL Inactive <br /> <a href="http://www.wp-monitor.net/2017/04/04/why-use-ssl/">Why Use SSL?</a>' );

			} else {

				jQuery('#ssl_light').css( 'background', '#01FC27' );

				jQuery('#wpm_ssl_message').text( 'SSL Active' );

			}

		}, 1500);

	} );
