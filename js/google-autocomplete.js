/**
 * This sample uses the Place Autocomplete widget to allow the user to search
 * for and select a place. The sample then displays an info window containing
 * the place ID and other information about the place that the user has
 * selected.
 */

function initMap() {
	var geocoder   = new google.maps.Geocoder;

	var map = new google.maps.Map( document.getElementById( 'crrntl-map' ), {
		center: { lat: -33.8688, lng: 151.2195 },
		zoom:   17
	} );

	var input = document.getElementById( 'crrntl-pac-input-js' ),
		place_select = document.getElementById( 'crrntl-choose-car-location-js' );

	var autocomplete = new google.maps.places.Autocomplete( input );
	autocomplete.bindTo( 'bounds', map );

	map.controls[google.maps.ControlPosition.TOP_LEFT].push( input );

	var infowindow = new google.maps.InfoWindow();
	var marker     = new google.maps.Marker( {
		map: map
	} );

	if ( document.getElementById( 'crrntl-location' ).value != '' ) {
		geocodePlaceId( geocoder, map, infowindow );
	}

	place_select.addEventListener( 'change', function() {
		var place = this.options[this.selectedIndex].getAttribute('data-place'),
			val = this.options[this.selectedIndex].value;
		if ( '' != place && 'new' != val ) {
			document.getElementById( 'crrntl-location' ).value = place;
			geocodePlaceId( geocoder, map, infowindow );
		}
	} );

	marker.addListener( 'click', function() {
		infowindow.open( map, marker );
	} );

	autocomplete.addListener( 'place_changed', function() {
		infowindow.close();
		var place = autocomplete.getPlace();
		if ( !place.geometry ) {
			return;
		}

		if ( place.geometry.viewport ) {
			map.fitBounds( place.geometry.viewport );
		} else {
			map.setCenter( place.geometry.location );
			map.setZoom( 17 );
		}

		/* Set the position of the marker using the place ID and location. */
		marker.setPlace( {
			placeId:  place.place_id,
			location: place.geometry.location
		} );
		marker.setVisible( true );

		infowindow.setContent( '<div><strong>' + place.name + '</strong><br>' +
				'Place ID: ' + place.place_id + '<br>' +
				place.formatted_address );
		infowindow.open( map, marker );

		document.getElementById( 'crrntl-location' ).value = place.place_id;
	} );

	document.addEventListener( 'DOMContentLoaded', function() {
		google.maps.event.trigger( map, 'resize' );
	} );
}

/* This function is called when the user clicks the UI button requesting a reverse geocode. */
function geocodePlaceId( geocoder, map, infowindow ) {
	var placeId = document.getElementById( 'crrntl-location' ).value;
	geocoder.geocode( { 'placeId': placeId }, function( results, status ) {
		if ( status === google.maps.GeocoderStatus.OK ) {
			if ( results[0] ) {
				map.setZoom( 17 );
				map.setCenter( results[0].geometry.location );
				var marker = new google.maps.Marker( {
					map:      map,
					position: results[0].geometry.location
				} );
				infowindow.setContent( results[0].formatted_address );
				infowindow.open( map, marker );
				document.getElementById( 'crrntl-pac-input-js' ).value = results[0].formatted_address;
			} else {
				window.alert( 'No results found' );
			}
		} else {
			window.alert( 'Geocoder failed due to: ' + status );
		}
	} );
}