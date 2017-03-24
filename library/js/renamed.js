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

			jQuery('#total_counter').text( wpm_data_counter.total );

			jQuery('#grade_counter').text( wpm_data_counter.grade );

		}, ((x + 1) * 50) );

	} );
