jQuery(document).ready(function() {


		setTimeout(function(){

			var wordpress_green_light = jQuery('#wordpress_green_light'),
					wordpress_red_light = jQuery('#wordpress_red_light'),
					ssl_green_light = jQuery('#ssl_green_light'),
					ssl_red_light = jQuery('#ssl_red_light');

			var green = '#01FC27',
					red = '#FF0000';

			if (at_data.wordpress === 1) {

				wordpress_green_light.css( 'background', green );

			} else {

				wordpress_red_light.css( 'background', red );

			}

			if (at_data.ssl === 1 ) {

				ssl_green_light.css( 'background', green );

			} else {

				ssl_red_light.css( 'background', red );

			}

		}, 1500);

	} );
