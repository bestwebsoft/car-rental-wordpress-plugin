<?php /* If uninstall is not called from WordPress, exit */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( ! function_exists( 'crrntl_plugin_delete' ) ) {
	function crrntl_plugin_delete() {
		global $wpdb, $crrntl_options;

		if ( empty( $crrntl_options ) ) {
			$crrntl_options = get_option( 'crrntl_options' );
		}

		/* Deactivating plugin if it is active */
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if ( is_plugin_active( 'car-rental/car-rental.php' ) ) {
			deactivate_plugins( 'car-rental/car-rental.php' );
		}

		$all_plugins = get_plugins();
		if ( ! array_key_exists( 'car-rental-pro/car-rental-pro.php', $all_plugins ) ) {
			crrntl_remove_data();

			/* Delete plugin's tables */
			$wpdb->query( "DROP TABLE IF EXISTS
				{$wpdb->prefix}crrntl_currency,
				{$wpdb->prefix}crrntl_extras_order,
				{$wpdb->prefix}crrntl_locations,
				{$wpdb->prefix}crrntl_orders,
				{$wpdb->prefix}crrntl_statuses;"
			);

			/* Delete posts of the custom post-types */
			$customs = get_posts( array(
				'post_type'   => $crrntl_options['post_type_name'],
				'numberposts' => - 1,
			) );
			if ( count( $customs ) > 0 ) {
				foreach ( $customs as $custom ) {
					/* Delete's each post. */
					wp_delete_post( $custom->ID, true );
				}
			}

			/* Delete terms of the custom taxonomies */
			$taxonomies = "'manufacturer', 'vehicle_type', 'car_class', 'extra'";
			$query = "SELECT DISTINCT t.term_id, tax.taxonomy
				FROM {$wpdb->prefix}terms AS t
					LEFT JOIN {$wpdb->prefix}term_taxonomy AS tax ON tax.term_id = t.term_id
				WHERE tax.taxonomy IN ( {$taxonomies} );";

			$terms = $wpdb->get_results( $query, ARRAY_A );
			if ( count( $terms ) > 0 ) {
				foreach ( $terms as $term ) {
					wp_delete_term( $term['term_id'], $term['taxonomy'] );
				}
			}

			/* Delete plugin's options */
			delete_option( 'crrntl_slider_options' );
			delete_option( 'crrntl_demo_options' );
			delete_option( 'crrntl_options' );
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

if ( ! function_exists( 'crrntl_remove_data' ) ) {
	function crrntl_remove_data() {
		global $crrntl_options;

		if ( empty( $crrntl_options ) ) {
			$crrntl_options = get_option( 'crrntl_options' );
		}

		$crrntl_pagenames = array(
			'BWS Choose Car',
			'BWS Choose Extras',
			'BWS Review & Book',
		);
		foreach ( $crrntl_pagenames as $page_name ) {
			$page_to_delete = get_page_by_title( $page_name );
			if ( ! empty( $page_to_delete ) ) {
				wp_delete_post( $page_to_delete->ID, true );
			}
		}
		require_once( dirname( __FILE__ ) . '/includes/demo-data/class-bws-demo-data.php' );
		$args = array(
			'plugin_basename' => plugin_basename( __FILE__ ),
			'plugin_prefix'   => 'crrntl_',
			'plugin_name'     => 'Car Rental',
			'plugin_page'     => 'car-rental-settings',
			'demo_folder'     => dirname( __FILE__ ) . '/includes/demo-data/',
		);
		if ( ! isset( $crrntl_options['display_demo_notice'] ) ) {
			$crrntl_options['display_demo_notice'] = 1;
		}
		$crrntl_BWS_demo_data = new Crrntl_Demo_Data( $args );
		$crrntl_BWS_demo_data->bws_remove_demo_data( false );
		unset( $crrntl_BWS_demo_data );
	}
}

global $wpdb;
/* check if it is a network activation - if so, run the uninstall function for each blog id */
if ( is_multisite() ) {
	$old_blog = get_current_blog_id();
	/* Get all blog ids */
	$blogids = $wpdb->get_col( "SELECT `blog_id` FROM {$wpdb->blogs};" );
	rsort( $blogids );
	foreach ( $blogids as $blog_id ) {
		switch_to_blog( $blog_id );
		crrntl_plugin_delete();
	}
	switch_to_blog( $old_blog );

	return;
} else {
	crrntl_plugin_delete();
}