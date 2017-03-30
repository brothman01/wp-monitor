jQuery( document ).ready( function() {


	jQuery( 'body' ).on( 'click', '.reveal-ip', function() {

		var ip = jQuery( this ).data( 'ip' );

		jQuery( this ).html( ip );

		jQuery( this ).css( 'color', 'black' );

		jQuery( this ).css( 'text-decoration', 'none' );

	} );

	jQuery( 'body' ).on( 'click', '.reveal-address', function() {

		var address = jQuery( this ).data( 'address' );

		jQuery( this ).html( address );

		jQuery( this ).css( 'color', 'black' );

		jQuery( this ).css( 'text-decoration', 'none' );

	} );

} );
