jQuery(document).ready(function() {


		setTimeout(function(){

			var wordpress_green_light = jQuery('#wordpress_green_light'),
					wordpress_red_light = jQuery('#wordpress_red_light'),
					ssl_green_light = jQuery('#ssl_green_light'),
					ssl_red_light = jQuery('#ssl_red_light');

			var green = '#01FC27',
					red = '#FF0000';

			if ( at_data2.wordpress == 0) {

				wordpress_green_light.css( 'background', green );

			} else {

				wordpress_red_light.css( 'background', red );

			}

			if ( at_data2.ssl == 0 ) {

				ssl_red_light.css( 'background', red );

			} else {

				ssl_green_light.css( 'background', green );

			}

		}, 1500);

	} );
