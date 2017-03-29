
	document.addEventListener( "DOMContentLoaded", function( event ) {

		var php_action_field = document.getElementById("php_action_field");


		setTimeout(function(){

				if (wpm_data_php.state == "Up To Date") {

					php_action_field.style.background = "#00CB25";

				} else {

					php_action_field.style.background = "#FF0000";

				}

				php_action_field.value = wpm_data_php.state;

		}, 1000);

	} );
