<?php
/**
 * Implements metadata functionality for taxonomy terms, including for tags and categories
 * From WordPress 4.4 these functions was added to the core as add_term_meta(), delete_term_meta(), get_term_meta(), update_term_meta()
 *
 * @package    WordPress
 * @subpackage Car Rental 
 * @since      Car Rental 1.0.0
 */

/**
 * Use functions in WordPress
 * crrntl_add_term_meta() crrntl_delete_term_meta() crrntl_get_term_meta() crrntl_update_term_meta()
 */

/* install table in $wpdb */
global $wpdb;
$wpdb->termmeta = "{$wpdb->prefix}termmeta";

/**
 * Function for create table of the taxonomies metadata, use in register_activation_hook()
 * register_activation_hook( __FILE__, 'crrntl_create_termmeta_table');
 */
if ( ! function_exists( 'crrntl_create_termmeta_table' ) ) {
	function crrntl_create_termmeta_table() {
		global $wpdb;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		/**
		 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
		 * As of 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
		 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
		 */
		$max_index_length = 191;

		$tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}termmeta'" );
		if ( ! count( $tables ) ) {
			$wpdb->query( "CREATE TABLE {$wpdb->prefix}termmeta (
			meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			term_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			meta_key VARCHAR(255) DEFAULT NULL,
			meta_value LONGTEXT,
			PRIMARY KEY ( meta_id ),
			KEY term_id ( term_id ),
			KEY meta_key ( meta_key( {$max_index_length} ) )
			) {$charset_collate};" );
		}
	}
}

/*
** Taxonomy meta functions
*/

/**
 * Add meta data field to a term.
 *
 * @param int    $term_id    Post ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value.
 * @param bool   $unique     Optional, default is false. Whether the same key should not be added.
 *
 * @return bool False for failure. True for success.
 */
if ( ! function_exists( 'crrntl_add_term_meta' ) ) {
	function crrntl_add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {
		return add_metadata( 'term', $term_id, $meta_key, $meta_value, $unique );
	}
}

/**
 * Update term meta field based on term ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and term ID.
 *
 * If the meta field for the term does not exist, it will be added.
 *
 * @param int    $term_id    Term ID.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 *
 * @return bool False on failure, true if success.
 */
if ( ! function_exists( 'crrntl_update_term_meta' ) ) {
	function crrntl_update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
		return update_metadata( 'term', $term_id, $meta_key, $meta_value, $prev_value );
	}
}

/**
 * Retrieve term meta field for a term.
 *
 * @param int    $term_id  Term ID.
 * @param string $meta_key The meta key to retrieve.
 * @param bool   $single   Whether to return a single value.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
 *  is true.
 */
if ( ! function_exists( 'crrntl_get_term_meta' ) ) {
	function crrntl_get_term_meta( $term_id, $meta_key = '', $single = false ) {
		return get_metadata( 'term', $term_id, $meta_key, $single );
	}
}

/**
 * Remove metadata matching criteria from a term.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @param int    $term_id    term ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value.
 *
 * @return bool  False for failure. True for success.
 */
if ( ! function_exists( 'crrntl_delete_term_meta' ) ) {
	function crrntl_delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {
		return delete_metadata( 'term', $term_id, $meta_key, $meta_value );
	}
}

/**
 * Delete all metafields of the term when this term is deleting
 * do_action('delete_term', $term, $tt_id, $taxonomy, $deleted_term );
 */
if ( ! function_exists( 'crrntl_delete_termmeta_on_delete_term' ) ) {
	function crrntl_delete_termmeta_on_delete_term( $term_id ) {
		global $wpdb;
		$wpdb->delete( $wpdb->termmeta, array( 'term_id' => $term_id ), array( '%d' ) );
	}
}

add_action( 'delete_term', 'crrntl_delete_termmeta_on_delete_term' );