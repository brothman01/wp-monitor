jQuery(document).ready(function() {

		// alert('aaa');
		 var x;
	//
		 for (x = 0; x < 25; x++) {
	//
	 	setTimeout(function() {

		var number = Math.floor(Math.random() * 100) + 1

		jQuery('#total_counter').text(number);

		jQuery('#grade_counter').text(number);

	 	}, (x * 50) );
	//
	}

		setTimeout(function(){

			var total_conter = jQuery('#total_counter');

			jQuery('#total_counter').text(at_data.total);

			jQuery('#grade_counter').text(at_data.grade);

		}, ((x + 1) * 50) );

	} );
