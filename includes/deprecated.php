<?php
/**
 * @deprecated since 1.0.5
 * @todo remove after 30.09.2017
 */
if ( ! function_exists( 'crrntl_remove_old_templates' ) ) {
	function crrntl_remove_old_templates() {
		global $crrntl_options, $wpdb;
		if ( isset( $crrntl_options['plugin_option_version'] ) && version_compare( str_replace( 'pro-', '', $crrntl_options['plugin_option_version'] ), '1.0.5', '<' ) ) {
			$themepath = get_stylesheet_directory() . '/';
			$templates 	= array(
				'car_page'			=> array(
					'filename'			=> 'page-choose-car.php',
					'title'				=> 'BWS Choose Car'
				),
				'extra_page'		=> array(
					'filename'			=> 'page-choose-extras.php',
					'title'				=> 'BWS Choose Extras'
				),
				'review_page'		=> array(
					'filename'			=> 'page-review-book.php',
					'title'				=> 'BWS Review & Book'
				)
			);

			/* get pages with Car Rental templates, set template to default and write down its ID to options */
			foreach ( $templates as $page_slug => $template ) {
				$template_page_id = $wpdb->get_var( "SELECT $wpdb->posts.ID FROM $wpdb->posts, $wpdb->postmeta WHERE meta_key = '_wp_page_template' AND meta_value = '{$template['filename']}' AND ( post_status = 'publish' OR post_status = 'private' ) AND $wpdb->posts.ID = $wpdb->postmeta.post_id" );

				if ( ! empty( $template_page_id ) ) {
					update_post_meta( $template_page_id, '_wp_page_template', 'default' );
				}

				if ( ! isset( $crrntl_options["{$page_slug}_id"] ) || get_post( $crrntl_options[ $page_slug . '_id' ] ) == null ) {
					if ( ! empty( $template_page_id ) ) {
						$crrntl_options["{$page_slug}_id"] = $template_page_id;
					} else {
						$page = get_page_by_title( $template['title'] );
						if ( ! empty( $page ) ) {
							$crrntl_options[ $page_slug . '_id' ] = $page->ID;
						} else {
							/* unset outdated option */
							unset( $crrntl_options[ $page_slug . '_id' ] );
						}
					}
				}

				/* removing old template files and their backups if exist */
				if ( file_exists( $themepath . $template['filename'] ) )
					@unlink( $themepath  . $template['filename'] );
				if ( file_exists( $themepath . $template['filename'] . '.bak' ) )
					@unlink( $themepath  . $template['filename'] . '.bak' );
			}
		}
	}
}