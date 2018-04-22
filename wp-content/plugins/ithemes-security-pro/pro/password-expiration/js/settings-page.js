(function ( $, itsec ) {
	$( function () {
		$( document ).on( 'click', '#itsec-password-expiration-force-expiration', function () {
			var $button = $( this );

			$button.prop( 'disabled', true );

			itsec.sendModuleAJAXRequest( 'password-expiration', { method: 'force-expiration' }, function ( response ) {
				$button.removeProp( 'disabled' );
				
				var messages = [];

				var types = { errors: 'error', warnings: 'warning', messages: 'success', infos: 'info' };

				for ( var type in types ) {
					if ( types.hasOwnProperty( type ) ) {
						for ( var i = 0; i < response[type].length; i++ ) {
							messages.push( makeNoticeEl( types[type], response[type][i] ) );
						}
					}
				}

				if ( messages ) {
					$( '#itsec_password_expiration_status' ).append( messages );
				}
			} );
		} );
	} );

	function makeNoticeEl( type, text ) {
		return $( '<div>' )
			.addClass( 'notice notice-alt notice-' + type )
			.append( $( '<p>' ).text( text ) );
	}
})( jQuery, itsecUtil );