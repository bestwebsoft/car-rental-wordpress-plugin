( function( $ ) {
	$( document ).ready( function( $ ) {
		$( '.crrntl-next-page input.crrntl-orange-button' ).hide();
		$( '.crrntl-form-edit' ).on( 'click', function( e ) {
			$( '.crrntl-form-update' ).show();
			$( '.crrntl-disabled-form-overlay' ).hide();
			$( this ).hide();
			e.preventDefault();
		} );

		/* Adds datepicker to pick-up/drop-off date block */
		$( function() {
			var pickUp = $( '.crrntl-pick-up .datepicker' ),
					dropOff = $( '.crrntl-drop-off .datepicker' );
			pickUp.datepicker( {
				dateFormat: 'yy-mm-dd',
				minDate:    0
			} );
			dropOff.datepicker( {
				dateFormat: 'yy-mm-dd',
				minDate:    0
			} );
			pickUp.on( 'change', function() {
				var minDate = pickUp.val();
				dropOff.datepicker( 'option', 'minDate', minDate );
			} );
			$( '#crrntl-book-car-content' ).on( 'click', function( event ) {
				if ( $( event.target ).parent().closest( '.ui-datepicker-header' ).length !== 0 || $( event.target ).hasClass( 'datepicker' ) === true ) {
				} else {
					$( '.datepicker' ).datepicker( 'hide', 0 );
				}
			} );
		} );

		/* Display error massage if required data is not chosen */
		$( '.car-rental' ).on( 'click', '.crrntl-form-update, .crrntl-form-continue, .crrntl-filter-form-update, .crrntl-select-car', function( e ) {
			var locValue     = $( '#crrntl-pickup-location' ).val(),
					bookCarContent = $( '#crrntl-book-car-content' ),
					errorMessage = '<div class="crrntl-error-message clearfix" style="padding-bottom: 10px;">' + crrntlScriptVars['crrntl_choose_location'] + '</div>';
			if ( locValue.length == 0 ) {
				if ( bookCarContent.find( '.crrntl-error-message' ).length == 0 ) {
					if ( bookCarContent.find( '.checkbox' ).length > 0 ) {
						bookCarContent.find( '.checkbox' ).before( errorMessage );
					} else if ( bookCarContent.find( '#crrntl-location-checkbox' ).length > 0 ) {
						bookCarContent.find( '#crrntl-location-checkbox' ).before( errorMessage );
					}
				}
				e.preventDefault();
			}
		} );

		/* Show/hide details block */
		$( '.crrntl-product-info' ).find( '.crrntl-details-more' ).hide();
		$( '.crrntl-view-details' ).on( 'click', function() {
			$( this ).hide();
			$( this ).closest( '.crrntl-product-info' ).find( '.crrntl-close-details' ).show();
			$( this ).closest( '.crrntl-product-info' ).find( '.crrntl-details-more' ).show();
		} );
		$( '.crrntl-close-details' ).on( 'click', function() {
			$( this ).hide();
			$( this ).closest( '.crrntl-product-info' ).find( '.crrntl-view-details' ).show();
			$( this ).closest( '.crrntl-product-info' ).find( '.crrntl-details-more' ).hide();
		} );
		$( '.crrntl-close-details, .crrntl-view-details' ).hover( function() {
			$( this ).css( 'color', '#EE7835' );
		}, function() {
			$( this ).css( 'color', '#378EEF' );
		} );

		/* location checkbox - add block "Return location" */
		var checkboxLoc = $( '#crrntl-location-checkbox, #crrntl-location-checkbox-1' );
		if ( checkboxLoc.is( ':checked' ) ) {
			$( '.crrntl-content-form .crrntl-return-location' ).show();
		} else {
			$( '.crrntl-content-form .crrntl-return-location' ).hide();
		}
		checkboxLoc.on( 'change', function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '.crrntl-return-location' ).show();
			} else {
				$( '.crrntl-return-location' ).hide();
			}
		} );
		$( '.crrntl-location-block' ).on( 'click', 'span.checkbox', function() {
			if ( $( this ).next( 'input[type="checkbox"]' ).attr( 'id' ) == 'crrntl-location-checkbox' || $( this ).next( 'input[type="checkbox"]' ).attr( 'id' ) == 'crrntl-location-checkbox-1' ) {
				if ( $( this ).next( 'input[type="checkbox"]' ).is( ':checked' ) ) {
					$( '.crrntl-return-location' ).show();
				} else {
					$( '.crrntl-return-location' ).hide();
				}
			}
		} );

		/* Filter range sliders */
		$( '.widget_car-rental-filter' ).each( function() {
			var element    = $( this ),
					priceRange = element.find( '.crrntl-price-range' ),
					priceMin   = parseInt( priceRange.attr( 'data-min' ) ),
					priceMax   = parseInt( priceRange.attr( 'data-max' ) ),
					passRange  = element.find( '.crrntl-pass-range' ),
					passMin    = parseInt( passRange.attr( 'data-min' ) ),
					passMax    = parseInt( passRange.attr( 'data-max' ) );
			element.find( '.crrntl-slider-result' ).show();
			element.find( '.crrntl-widget-content-range label' ).hide();
			priceRange.slider( {
				min:    priceMin,
				max:    priceMax,
				values: [parseInt( element.find( '#crrntl-price-min' ).val() ), parseInt( element.find( '#crrntl-price-max' ).val() )],
				range:  true,
				slide:  function( event, ui ) {
					element.find( '#crrntl-price-min' ).val( ui.values[0] );
					element.find( '#crrntl-price-max' ).val( ui.values[1] );
					element.find( '.crrntl-price-result-from span' ).text( ui.values[0] );
					element.find( '.crrntl-price-result-to span' ).text( ui.values[1] );
				}
			} );
			passRange.slider( {
				min:    passMin,
				max:    passMax,
				values: [parseInt( element.find( '#crrntl-pass-min' ).val() ), parseInt( element.find( '#crrntl-pass-max' ).val() )],
				range:  true,
				slide:  function( event, ui ) {
					element.find( '#crrntl-pass-min' ).val( ui.values[0] );
					element.find( '#crrntl-pass-max' ).val( ui.values[1] );
					element.find( '.crrntl-pass-result-from span' ).text( ui.values[0] );
					element.find( '.crrntl-pass-result-to span' ).text( ui.values[1] );
				}
			} );
		} );

		/* Select, deselect and reset filters */
		$( '.crrntl-select-clear' ).show();
		$( 'a.crrntl-clear-all' ).on( 'click', function( e ) {
			$( this ).closest( 'h4' ).next().find( 'input:checkbox' ).each( function() {
				if ( $( this ).attr( 'checked', true ) ) {
					$( this ).trigger( 'click' );
				}
			} );
			e.preventDefault();
		} );
		$( 'a.crrntl-select-all' ).on( 'click', function( e ) {
			$( this ).closest( 'h4' ).next().find( 'input:checkbox' ).each( function() {
				if ( $( this ).attr( 'checked', false ) ) {
					$( this ).trigger( 'click' );
				}
			} );
			e.preventDefault();
		} );
		$( 'a.crrntl-reset-price' ).on( 'click', function( e ) {
			var element = $( this ).closest( '#crrntl-filter-form' ),
					priceRange = element.find( '.crrntl-price-range' ),
					min = priceRange.slider( "option", "min" ),
					max = priceRange.slider( "option", "max" );
			priceRange.slider( "option", "values", [min, max] );
			element.find( '#crrntl-price-min' ).val( min );
			element.find( '#crrntl-price-max' ).val( max );
			element.find( '.crrntl-price-result-from span' ).text( min );
			element.find( '.crrntl-price-result-to span' ).text( max );
			e.preventDefault();
		} );
		$( 'a.crrntl-reset-pass' ).on( 'click', function( e ) {
			var element = $( this ).closest( '#crrntl-filter-form' ),
					passRange = element.find( '.crrntl-pass-range' ),
					min = passRange.slider( "option", "min" ),
					max = passRange.slider( "option", "max" );
			passRange.slider( "option", "values", [min, max] );
			element.find( '#crrntl-pass-min' ).val( min );
			element.find( '#crrntl-pass-max' ).val( max );
			element.find( '.crrntl-pass-result-from span' ).text( min );
			element.find( '.crrntl-pass-result-to span' ).text( max );
			e.preventDefault();
		} );

		/* Recalculate totals when extras was changed */
		var curData       = $( '.crrntl-currency-data' ),
				curPos        = curData.attr( 'data-cur-pos' ),
				currency      = curData.attr( 'data-cur' ),
				dec_point     = curData.attr( 'data-dec-point' ),
				thousands_sep = curData.attr( 'data-thous-sep' );
		$( '.crrntl-checkbox-block' ).each( function() {
			$( this ).on( 'click', '.crrntl-extra-checkbox, span.checkbox', function( e ) {
				var element = $( e.target ).closest( '.crrntl-checkbox-block' ).find( '.crrntl-extra-checkbox' ),
						extraId = element.val();

				if ( $( element ).attr( 'checked' ) ) {
					var extraName  = $( element ).closest( '.crrntl-extra' ).find( '.crrntl-product-title' ).find( 'label' ).text(),
							extraPrice = parseFloat( $( element ).closest( '.crrntl-extra' ).find( '.crrntl-extra-price' ).attr( 'data-price' ) ),
							extraString, extraQuantity, extraPriceFormatted;

					if ( $( element ).closest( '.crrntl-extra' ).find( '.crrntl-product-quantity' ).length > 0 ) {
						extraQuantity       = parseInt( $( element ).closest( '.crrntl-extra' ).find( '.crrntl-product-quantity' ).val() );
						extraPrice          = ( extraPrice * extraQuantity);
						extraPriceFormatted = number_format( extraPrice, 2, dec_point, thousands_sep );
						if ( 'before' == curPos ) {
							extraString = '<div class="crrntl-selected-extra-' + extraId + '">' + extraName + ' &times; ' + extraQuantity + ' <p class="crrntl-price">' + currency + '<span data-price="' + extraPrice + '">' + extraPriceFormatted + '</span></p></div>';
						} else {
							if ( 'after' == curPos ) {
								extraString = '<div class="crrntl-selected-extra-' + extraId + '">' + extraName + ' &times; ' + extraQuantity + ' <p class="crrntl-price"><span data-price="' + extraPrice + '">' + extraPriceFormatted + '</span> ' + currency + '</p></div>';
							} else {
								extraString = '<div class="crrntl-selected-extra-' + extraId + '">' + extraName + ' &times; ' + extraQuantity + ' <p class="crrntl-price"><span data-price="' + extraPrice + '">' + extraPriceFormatted + '</span></p></div>';
							}
						}
					} else {
						extraPriceFormatted = number_format( extraPrice, 2, dec_point, thousands_sep );
						if ( 'before' == curPos ) {
							extraString = '<div class="crrntl-selected-extra-' + extraId + '">' + extraName + ' <p class="crrntl-price">' + currency + '<span data-price="' + extraPrice + '">' + extraPriceFormatted + '</span></p></div>';
						} else
							if ( 'after' == curPos ) {
								extraString = '<div class="crrntl-selected-extra-' + extraId + '">' + extraName + ' <p class="crrntl-price"><span data-price="' + extraPrice + '">' + extraPriceFormatted + '</span> ' + currency + '</p></div>';
							} else {
								extraString = '<div class="crrntl-selected-extra-' + extraId + '">' + extraName + ' <p class="crrntl-price"><span data-price="' + extraPrice + '">' + extraPriceFormatted + '</span></p></div>';
							}
					}

					$( '.widget_car-rental-order-info' ).find( '.crrntl-widget-extras-info' ).append( extraString );
				} else {
					$( '.widget_car-rental-order-info' ).find( '.crrntl-selected-extra-' + extraId ).remove();
				}

				var result = parseFloat( $( '.crrntl-subtotal p.crrntl-price span' ).attr( 'data-price' ) ),
						resultFormatted;
				$( '.crrntl-widget-extras-info' ).find( 'p.crrntl-price span' ).each( function() {
					result += parseFloat( $( this ).attr( 'data-price' ) );
				} );
				resultFormatted = number_format( result, 2, dec_point, thousands_sep );
				$( '.crrntl-widget-footer-total p.crrntl-price span' ).text( resultFormatted ).attr( 'data-price', result );
			} );
		} );

		/* Calculate total price for one extra if quantity is available */
		$( '.crrntl-product-quantity' ).each( function() {
			$( this ).on( 'change', function() {
				if ( ! $( this ).closest( '.crrntl-extra' ).find( 'input:checkbox' ).attr( 'checked' ) ) {
					$( this ).closest( '.crrntl-extra' ).find( 'input:checkbox' ).trigger( 'click' );
				}

				var prodQuantity       = parseInt( $( this ).val() ),
						prodPrice = parseFloat( $( this ).closest( '.crrntl-extra' ).find( '.crrntl-extra-price' ).attr( 'data-price' ) ),
						extraId            = $( this ).closest( '.crrntl-extra' ).find( 'input:checkbox' ).val(),
						extraName          = $( this ).closest( '.crrntl-extra' ).find( '.crrntl-product-title' ).find( 'label' ).text(),
						prodPriceFormatted, extraString, result, resultFormatted;

				prodPrice = parseFloat( prodPrice * prodQuantity );
				prodPriceFormatted = number_format( prodPrice, 2, dec_point, thousands_sep );
				$( this ).closest( '.crrntl-extra' ).find( 'p.crrntl-item-price span' ).first().text( prodQuantity );
				$( this ).closest( '.crrntl-extra' ).find( '.crrntl-extra-total' ).text( prodPriceFormatted ).attr( 'data-price', prodPrice );

				if ( 'before' == curPos ) {
					extraString = extraName + ' &times; ' + prodQuantity + ' <p class="crrntl-price">' + currency + '<span data-price="' + prodPrice + '">' + prodPriceFormatted + '</span></p>';
				} else if ( 'after' == curPos ) {
					extraString = extraName + ' &times; ' + prodQuantity + ' <p class="crrntl-price"><span data-price="' + prodPrice + '">' + prodPriceFormatted + '</span> ' + currency + '</p>';
				} else {
					extraString = extraName + ' &times; ' + prodQuantity + ' <p class="crrntl-price"><span data-price="' + prodPrice + '">' + prodPriceFormatted + '</span></p>';
				}
				$( '.crrntl-widget-extras-info' ).find( '.crrntl-selected-extra-' + extraId ).html( extraString );

				result = parseFloat( $( '.crrntl-subtotal p.crrntl-price span' ).attr( 'data-price' ) );
				$( '.crrntl-widget-extras-info' ).find( 'p.crrntl-price span' ).each( function() {
					result += parseFloat( $( this ).attr( 'data-price' ) );
				} );
				resultFormatted = number_format( result, 2, dec_point, thousands_sep );
				$( '.crrntl-widget-footer-total p.crrntl-price span' ).text( resultFormatted ).attr( 'data-price', result );
			} );
		} );

		function number_format( number, decimals, dec_point, thousands_sep ) {
			number         = (number + '').replace( /[^0-9+\-Ee.]/g, '' );
			var n          = !isFinite( +number ) ? 0 : +number,
					prec       = !isFinite( +decimals ) ? 0 : Math.abs( decimals ),
					sep        = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
					dec        = (typeof dec_point === 'undefined') ? '.' : dec_point,
					s          = '',
					toFixedFix = function( n, prec ) {
						var k = Math.pow( 10, prec );
						return '' + (Math.round( n * k ) / k)
										.toFixed( prec );
					};
			/* Fix for IE parseFloat(0.55).toFixed(0) = 0; */
			s = (prec ? toFixedFix( n, prec ) : '' + Math.round( n ))
					.split( '.' );
			if ( s[0].length > 3 ) {
				s[0] = s[0].replace( /\B(?=(?:\d{3})+(?!\d))/g, sep );
			}
			if ( (s[1] || '')
							.length < prec ) {
				s[1] = s[1] || '';
				s[1] += new Array( prec - s[1].length + 1 )
						.join( '0' );
			}
			return s.join( dec );
		}

		/* Slider */
		var sliderContainer = $( '#crrntl-slider-container' );
		if ( sliderContainer.length ) {
			var _SlideshowTransitions = [
				//Fade
				{ $Duration: 1200, $Opacity: 2 }
			];

			var sliderWidth = sliderContainer.width();
			sliderContainer.css( 'width', sliderWidth + 'px' );
			$( '.crrntl-one-slide' ).css( 'width', sliderWidth + 'px' );

			var options = {
				$FillMode:               2,
				$AutoPlay:               true, //[Optional] Whether to auto play, to enable slideshow, this option must be set to true, default value is false
				$SlideDuration:          500, //[Optional] Specifies default duration (swipe) for slide in milliseconds, default value is 500
				$SlideshowOptions:       {
					$Class:            $JssorSlideshowRunner$,
					$Transitions:      _SlideshowTransitions,
					$TransitionsOrder: 1,
					$ShowLink:         true
				},
				$BulletNavigatorOptions: { //[Optional] Options to specify and enable navigator or not
					$Class:        $JssorBulletNavigator$, //[Required] Class to create navigator instance
					$ChanceToShow: 2, //[Required] 0 Never, 1 Mouse Over, 2 Always
					$AutoCenter:   0, //[Optional] Auto center navigator in parent container, 0 None, 1 Horizontal, 2 Vertical, 3 Both, default value is 0
					$Steps:        1, //[Optional] Steps to go for each navigation request, default value is 1
					$Lanes:        1, //[Optional] Specify lanes to arrange items, default value is 1
					$SpacingX:     10, //[Optional] Horizontal space between each item in pixel, default value is 0
					$SpacingY:     10, //[Optional] Vertical space between each item in pixel, default value is 0
					$Orientation:  1 //[Optional] The orientation of the navigator, 1 horizontal, 2 vertical, default value is 1
				}
			};

			var jssor_slider2 = new $JssorSlider$( 'crrntl-slider-container', options );

			setTimeout( function() {
				sliderContainer.find( '.crrntl-one-slide' ).each( function() {
					var slideTitle           = $( this ).find( '.crrntl-slide-title h3' ),
							slideTitleHeight     = parseInt( $( this ).find( '.crrntl-slide-title' ).height(), 10 ),
							slideTitleLineHeight = parseInt( slideTitle.css( 'line-height' ), 10 ),
							slideDesc            = $( this ).find( '.crrntl-slide-description .crrntl-entry-content' ),
							slideDescHeight      = parseInt( $( this ).find( '.crrntl-slide-description' ).height(), 10 ),
							slideDescLineHeight  = parseInt( slideDesc.css( 'line-height' ), 10 );
					if ( ( slideTitleHeight + slideDescHeight ) > 149 ) {
						if ( slideTitleHeight <= 46 ) {
							slideDesc.css( 'height', slideDescLineHeight * 4 );
						} else {
							slideTitle.css( 'height', slideTitleLineHeight * 2 );
							slideDesc.css( 'height', slideDescLineHeight * 2 );
						}
					}
				} )
			}, 500 );

		}
	} );
} )( jQuery );