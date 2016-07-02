function bws_show_settings_notice() {
	(function($) {
		$( '.updated.fade, .error' ).css( 'display', 'none' );
		$( '#bws_save_settings_notice' ).css( 'display', 'block' );
	})(jQuery);
}

(function($) {
	$( document ).ready( function() {
		/**
		 * add notice about changing on the settings page 
		 */
		$( '.bws_form input, .bws_form textarea, .bws_form select' ).bind( "change paste select", function() {
			if ( $( this ).attr( 'type' ) != 'submit' && ! $( this ).hasClass( 'bws_no_bind_notice' ) ) {
				bws_show_settings_notice();
			};
		});
		$( '.bws_save_anchor' ).on( "click", function( event ) {
			event.preventDefault();
			$( '.bws_form #bws-submit-button' ).click();
		});

		if ( typeof CodeMirror == 'function' && $( '#bws_newcontent_css' ).length > 0 ) {
			var editor = CodeMirror.fromTextArea( document.getElementById("bws_newcontent_css"), {
				mode: "css",
				theme: "default",
				styleActiveLine: true,
				matchBrackets: true,
				lineNumbers: true
			});
		}
	});
})(jQuery);