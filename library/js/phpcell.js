
jQuery(document).ready(function() {



		setTimeout(function(){

				if (wpm_data_php.state == "Up To Date") {

					jQuery('#php_action_field').css( 'background',  '#00CB25' );

				} else {

					jQuery('#php_action_field').css( 'background', '#FF0000');

				}

					jQuery('#php_action_field').val( wpm_data_php.state + '' );


		}, 1000);

	} );
