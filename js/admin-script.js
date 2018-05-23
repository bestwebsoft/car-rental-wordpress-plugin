( function( $ ) {
	$( document ).ready( function() {
		$( '#crrntl_gdpr' ).on( 'change', function() {
			if( $( this).is( ':checked' ) ) {
				$( '#crrntl_gdpr_link_options' ).show();
			} else {
				$( '#crrntl_gdpr_link_options' ).hide();
			}
		} ).trigger( 'change' );

		$( '#crrntl-js-location, #crrntl-map' ).show();

		if ( $.fn.sortable ) {
			$( '#crrntl_gallery.crrntl-gallery' ).sortable();
		}

		var width = 0;
		$( window ).on( 'resize', function() {
			if ( width != $( this ).width() ) {
				width = $( this ).width();
				var input_width = $( '#crrntl-map' ).width() - 175;
				$( '#crrntl-pac-input-js' ).css( { 'max-width' : input_width, 'width' : input_width } );
			}
		} ).trigger( 'resize' );

		$( '#crrntl-choose-car-location-js' ).on( 'change', function() {
			if ( 'new' == $( this ).val() ) {
				$( '#crrntl-pac-input-js' ).focus();
			}
		} );
		$( '#crrntl-pac-input-js' ).keydown( function( event ) {
			if ( event.keyCode == 13 && $( '#crrntl-pac-input-js' ).is( ':focus' ) ) {
				event.preventDefault();
				return false;
			}
		} );

		/**
		 * Add datepicker script
		 */
		var pickUpDate = $( '.crrntl-pick-up .datepicker' ),
			dropOffDate = $( '.crrntl-drop-off .datepicker' );
		pickUpDate.datepicker( {
			dateFormat: 'yy-mm-dd',
			minDate:    0
		} );
		dropOffDate.datepicker( {
			dateFormat: 'yy-mm-dd'
		} );
		pickUpDate.on( 'change', function() {
			var minDate = pickUpDate.val();
			dropOffDate.datepicker( 'option', 'minDate', minDate );
		} );

		/**
		 * Adding and removing orders in status list
		 */
		$( '.crrntl-delete-status-label' ).hide();
		$( '.crrntl-status-list' ).on( 'click', '.crrntl-add-status, .crrntl-delete-status, .crrntl-delete-status-live', function( e ) {
			var statusClassList = e.target.classList;
			if ( statusClassList.contains( 'crrntl-add-status' ) ) {
				if ( $.trim( $( this ).closest( '.crrntl-status-item' ).find( 'input' ).val() ) != '' ) {
					var addStatus = '<div class="crrntl-status-item">' +
													'<input type="text" name="crrntl_status[]" value="" placeholder="' + crrntlScriptVars[ 'crrntl_add_new_status' ] + '" /> ' +
													'<label>' +
													'<span class="crrntl-add-status dashicons dashicons-plus"></span>' +
													'<span style="display: none;" class="crrntl-delete-status-live dashicons dashicons-dismiss"></span>' +
													'</label>' +
													'</div>';
					$( this ).closest( '.crrntl-status-item' ).find( '.crrntl-delete-status' ).show();
					$( this ).closest( '.crrntl-status-item' ).find( '.crrntl-delete-status-live' ).show();
					$( this ).closest( '.crrntl-status-item' ).after( addStatus );
					$( this ).remove();
				}
			} else if ( statusClassList.contains( 'crrntl-delete-status' ) ) {
				$( this ).closest( '.crrntl-status-item' ).hide();
			} else if ( statusClassList.contains( 'crrntl-delete-status-live' ) ) {
				$( this ).closest( '.crrntl-status-item' ).remove();
			}
		} );

		/**
		 * include WordPress media uploader for slider and terms images
		 */
		var imageUrl, imagePreview, button, file_frame, imageId,
				wp_media_post_id = wp.media.model.settings.post.id, /* Store the old id */
				set_to_post_id   = 0; /* Set this */

		$( '.crrntl-upload-image' ).on( 'click', function( event ) {
			button       = $( this );
			imageUrl     = $( this ).parent().find( 'input.crrntl-image-url' );
			imageId      = $( this ).parent().find( 'input.crrntl-image-id' );
			imagePreview = $( '#crrntl-no-image' ).parent();

			event.preventDefault();

			/* If the media frame already exists, reopen it. */
			if ( file_frame ) {
				/* Set the post ID to what we want */
				/* file_frame.uploader.uploader.param( 'post_id', set_to_post_id ); */
				/* Open frame */
				file_frame.open();
				return;
			} else {
				/* Set the wp.media post id so the uploader grabs the ID we want when initialized */
				wp.media.model.settings.post.id = set_to_post_id;
			}

			/* Create the media frame. */
			file_frame = wp.media.frames.file_frame = wp.media( {
				title:    $( this ).data( 'uploader_title' ),
				library:  {
					type: 'image'
				},
				button:   {
					text: $( this ).data( 'uploader_button_text' )
				},
				multiple: false  /* Set to true to allow multiple files to be selected */
			} );

			/* When an image is selected, run a callback. */
			file_frame.on( 'select', function() {
				/* We set multiple to false so only get one image from the uploader */
				var attachment = file_frame.state().get( 'selection' ).first().toJSON();
				if ( ! attachment.mime.match( '^image/' ) ) {
					alert( crrntlScriptVars[ 'errorInsertImage' ] );
					return false;
				}

				/* Do something with attachment.id and/or attachment.url here */
				button.val( crrntlScriptVars['changeImageLabel'] );
				imageUrl.val( attachment.url ).trigger( 'change' );
				imageId.val( attachment.id );
				if ( ! ( imagePreview.find( '.crrntl-uploaded-image' ).length > 0 ) ) {
					imagePreview.prepend( '<img class="crrntl-uploaded-image" src="' + attachment.sizes.thumbnail.url + '" alt="' + attachment.url + '"/><div class="clear"></div>' );
					$( '.crrntl-remove-image' ).show();
					$( '#crrntl-no-image' ).hide();
				} else {
					imagePreview.find( '.crrntl-uploaded-image' ).attr( {
						src: attachment.sizes.thumbnail.url,
						alt: attachment.url
					} );
				}

				/* Restore the main post ID */
				wp.media.model.settings.post.id = wp_media_post_id;
			} );

			/* Finally, open the modal */
			file_frame.open();
		} );

		/* Removing terms image */
		$( document ).on( 'click', '.crrntl-remove-image', function( event ) {
			button       = $( this ).parent().find( '.crrntl-upload-image' );
			imageId      = $( this ).parent().find( 'input.crrntl-image-id' );
			imagePreview = $( this ).parent();

			event.preventDefault();
			button.val( crrntlScriptVars['addImageLabel'] );
			$( this ).hide();
			imagePreview.find( '.crrntl-uploaded-image' ).remove();
			$( '#crrntl-no-image' ).show();
			imageId.val( '' );
		} );

		/* Clear thumbnail field after extra is added */
		$( document ).ajaxComplete( function( event, request, options ) {
			if (
				request && 4 === request.readyState && 200 === request.status && options.data &&
				0 <= options.data.indexOf( 'action=add-tag' ) &&
				0 <= options.data.indexOf( 'screen=edit-extra' )
			) {
				var res = wpAjax.parseAjaxResponse( request.responseXML, 'ajax-response' );
				if ( ! res || res.errors ) {
					return;
				}
				$( '.crrntl-remove-image' ).click();
				return;
			}
		} );

		/* Validate car price input field */
		$( '#crrntl-price' ).on( 'input', function() {
			if ( 'on_request' != $( 'input[name="crrntl_price_type"]:checked' ).val() ) {
				var input    = $( this );
				var re       = /^\d{1,9}(\.\d{2})?$/;
				var is_price = re.test( input.val() );
				if ( is_price ) {
					input.removeClass( 'crrntl-confirm-invalid' );
				} else {
					input.addClass( 'crrntl-confirm-invalid' );
				}
			}
		} );

		/* Validate extra price input field */
		$( '#crrntl-extra-price' ).on( 'input', function() {
			var input    = $( this );
			var re       = /^\d{1,9}(\.\d{2})?$/;
			var is_price = re.test( input.val() );
			if ( is_price ) {
				input.removeClass( 'crrntl-confirm-invalid' );
			} else {
				input.addClass( 'crrntl-confirm-invalid' );
			}
		} );

		/* hide/show custom currency field */
		$( 'select[name="crrntl_currency"]' ).on( 'change', function() {
			if ( 'custom' == $( this ).val() ) {
				$( 'label[for="crrntl_custom_currency"]' ).show();
			} else {
				$( 'label[for="crrntl_custom_currency"]' ).hide();
			}
		} ).trigger( 'change' );

		/* hide/show custom unit consumption field */
		$( 'select[name="crrntl_unit_consumption"]' ).on( 'change', function() {
			if ( 'custom' == $( this ).val() ) {
				$( 'label[for="crrntl_custom_unit_consumption"]' ).show();
			} else {
				$( 'label[for="crrntl_custom_unit_consumption"]' ).hide();
			}
		} ).trigger( 'change' );

		$( '#crrntl-pac-input-js' ).on( 'input change', function() {
			$( '#crrntl-choose-car-location-js' ).val( 'new' );
		} );

		/* Show/hide quantity of extra on the 'Edit order' page */
		$( '.crrntl-extra-item input[type="checkbox"]' ).on( 'change', function() {
			var checkbox = $( this ),
				extraQuantity = checkbox.closest( '.crrntl-extra-item' ).find( '.crrntl-extra-quantity' );
			if ( checkbox.is( ':checked' ) ) {
				extraQuantity.show();
			} else {
				extraQuantity.hide();
			}
		} ).trigger( 'change' );
	} );
} )( jQuery );
