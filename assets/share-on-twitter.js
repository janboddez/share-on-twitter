jQuery( document ).ready( function ( $ ) {
	$( '#share-on-twitter .unlink' ).click( function( e ) {
		e.preventDefault();

		if ( ! confirm( share_on_twitter_obj.message ) ) {
			return false;
		}

		var button = $( this );
		var data = {
			'action': 'share_on_twitter_unlink_url',
			'post_id': share_on_twitter_obj.post_id, // Current post ID.
			'share_on_twitter_nonce': $( this ).parent().siblings( '#share_on_twitter_nonce' ).val() // Nonce.
		};

		$.post( ajaxurl, data, function( response ) {
			// On success, remove extra paragraph.
			button.closest( '.description' ).remove();
		} );
	} );
} );
