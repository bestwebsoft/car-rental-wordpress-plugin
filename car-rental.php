<?php
/*
Plugin Name: Car Rental by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/
Description: A convenient plugin that adds Car Rental functionality.
Author: BestWebSoft
Text Domain: car-rental
Domain Path: /languages
Version: 1.0.0
Author URI: http://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2016  BestWebSoft  ( http://support.bestwebsoft.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Load Admin panel files
 */
global $crrntl_filenames, $crrntl_filepath, $crrntl_themepath;
$crrntl_filepath    = dirname( __FILE__ ) . '/templates/';
$crrntl_themepath   = get_stylesheet_directory() . '/';
$crrntl_filenames = array(
	'car_page'    => 'page-choose-car.php',
	'extra_page'  => 'page-choose-extras.php',
	'review_page' => 'page-review-book.php',
);

require_once( dirname( __FILE__ ) . '/includes/admin-panel.php' );
require_once( dirname( __FILE__ ) . '/includes/orders-table.php' );
require_once( dirname( __FILE__ ) . '/includes/taxonomy-metadata.php' );
require_once( dirname( __FILE__ ) . '/includes/slider-template.php' );

/**
 * Function for adding menu and submenu
 */
if ( ! function_exists( 'crrntl_admin_menu' ) ) {
	function crrntl_admin_menu() {
		bws_general_menu();

		add_submenu_page( 'bws_plugins', __( 'Car Rental Settings', 'car-rental' ), 'Car Rental', 'manage_options', 'car-rental-settings', 'crrntl_settings_page' );
		crrntl_add_menu_items();
	}
}

if ( ! function_exists( 'crrntl_plugin_loaded' ) ) {
	function crrntl_plugin_loaded() {
		load_plugin_textdomain( 'car-rental', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
}

if ( ! function_exists( 'crrntl_init' ) ) {
	function crrntl_init() {
		global $crrntl_plugin_info;

		if ( ! session_id() ) {
			session_start();
		}

		/* Trigger our function that registers the custom post type */
		crrntl_setup_post_types();

		add_image_size( 'crrntl_product_image', 161, 9999 );
		add_image_size( 'crrntl_product_image_widget', 105, 9999 );

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $crrntl_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$crrntl_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version */
		bws_wp_version_check( plugin_basename( __FILE__ ), $crrntl_plugin_info, '3.8' );

		/* Call register settings function */
		if ( ! is_admin() || ( isset( $_REQUEST['page'] ) && 'car-rental-settings' == $_REQUEST['page'] ) ) {
			crrntl_settings();
			crrntl_get_pages_link();
		}
	}
}

if ( ! function_exists( 'crrntl_admin_init' ) ) {
	function crrntl_admin_init() {
		global $bws_plugin_info, $crrntl_plugin_info;

		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array( 'id' => '576', 'version' => $crrntl_plugin_info['Version'] );
		}		
	}
}

if ( ! function_exists( 'crrntl_get_pages_link' ) ) {
	function crrntl_get_pages_link() {
		global $crrntl_filenames, $crrntl_options;
		if ( empty( $crrntl_options ) ) {
			$crrntl_options = get_option( 'crrntl_options' );
		}
		$crrntl_options_old = $crrntl_options;
		$i = 0;
		foreach ( $crrntl_filenames as $page_name => $page_file ) {
			if ( ! isset( $crrntl_options[ $page_name . '_id' ] ) || get_post( $crrntl_options[ $page_name . '_id' ] ) == null ) {
				$args = array(
					'post_type'  => 'page',
					'fields'     => 'ids',
					'nopaging'   => true,
					'meta_key'   => '_wp_page_template',
					'meta_value' => $page_file,
				);
				$page = get_posts( $args );

				if ( ! empty( $page[0] ) ) {
					$crrntl_options[ $page_name . '_id' ] = $page[0];
				} else {
					$crrntl_page_title                    = array(
						'BWS Choose Car',
						'BWS Choose Extras',
						'BWS Review & Book',
					);
					$post_data                              = array(
						'post_title'     => $crrntl_page_title[ $i ],
						'post_content'   => '',
						'post_name'      => '',
						'post_type'      => 'page',
						'post_status'    => 'publish',
						'comment_status' => 'closed',
						'ping_status'    => 'closed',
					);
					$crrntl_options[ $page_name . '_id' ] = wp_insert_post( $post_data );
					update_post_meta( $crrntl_options[ $page_name . '_id' ], '_wp_page_template', $page_file );
				}
			}
			$i ++;
		}
		if ( $crrntl_options != $crrntl_options_old ) {
			update_option( 'crrntl_options', $crrntl_options );
		}
	}
}

if ( ! function_exists( 'crrntl_widget_init' ) ) {
	function crrntl_widget_init() {
		/* load widgets files */
		$crrntl_widgets_link = plugin_dir_path( __FILE__ ) . 'includes/';
		require_once( $crrntl_widgets_link . 'widget-filter.php' );
		require_once( $crrntl_widgets_link . 'widget-order-info.php' );
		register_sidebar( array(
			'name'          => __( 'Sidebar on Car page', 'car-rental' ),
			'id'            => 'sidebar-choose-car',
			'description'   => __( 'Sidebar is displayed on the Choose Car page.', 'car-rental' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
		) );
		register_sidebar( array(
			'name'          => __( 'Sidebar on Extras page', 'car-rental' ),
			'id'            => 'sidebar-choose-extras',
			'description'   => __( 'Sidebar is displayed on the Choose Extras page.', 'car-rental' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
		) );
		register_sidebar( array(
			'name'          => __( 'Sidebar on Review page', 'car-rental' ),
			'id'            => 'sidebar-review',
			'description'   => __( 'Sidebar is displayed on the Review page.', 'car-rental' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
		) );
	}
}

if ( ! function_exists( 'crrntl_settings' ) ) {
	function crrntl_settings() {
		global $crrntl_options, $crrntl_option_defaults, $crrntl_plugin_info;
		$crrntl_db_version = '1.0';

		$crrntl_option_defaults = array(
			'plugin_db_version'               => $crrntl_db_version,
			'plugin_option_version'           => $crrntl_plugin_info['Version'],
			'theme_banner'					  => 1,
			'currency_custom_display'         => 0,
			'currency_unicode'                => 109,
			'custom_currency'                 => '',
			'currency_position'               => 'before',
			'unit_consumption_custom_display' => 0,
			'unit_consumption'                => __( 'l/100km', 'car-rental' ),
			'custom_unit_consumption'         => '',
			'per_page'                        => get_option( 'posts_per_page' ),
			'display_demo_notice'             => 1,
			'suggest_feature_banner'          => 1,
			'cflag'						  	  => '0A',
			'eflag'						  	  => '03'
		);

		/* Install the option defaults */
		if ( ! get_option( 'crrntl_options' ) )
			add_option( 'crrntl_options', $crrntl_option_defaults );

		$crrntl_options = get_option( 'crrntl_options' );

		if ( ! isset( $crrntl_options['plugin_option_version'] ) || $crrntl_options['plugin_option_version'] != $crrntl_plugin_info['Version'] ) {
			$crrntl_options = array_merge( $crrntl_option_defaults, $crrntl_options );
			$crrntl_options['plugin_option_version'] = $crrntl_plugin_info['Version'];
			$update_option = true;
			crrntl_plugin_templates_install();
		}

		if ( ! isset( $crrntl_options['plugin_db_version'] ) || $crrntl_options['plugin_db_version'] != $crrntl_db_version ) {
			crrntl_install();
			$crrntl_options['plugin_db_version'] = $crrntl_db_version;
			$update_option = true;
		}
		if ( isset( $update_option ) )
			update_option( 'crrntl_options', $crrntl_options );
	}
}

/**
 * Copy template files.
 */
if ( ! function_exists( 'crrntl_plugin_templates_install' ) ) {
	function crrntl_plugin_templates_install() {
		global $crrntl_filenames, $crrntl_filepath;
		$crrntl_themepath = get_stylesheet_directory() . '/';
		foreach ( $crrntl_filenames as $filename ) {
			if ( ! file_exists( $crrntl_themepath . $filename ) ) {
				$handle   = @fopen( $crrntl_filepath . $filename, 'r' );
				$contents = @fread( $handle, filesize( $crrntl_filepath . $filename ) );
				@fclose( $handle );
				if ( ! ( $handle = @fopen( $crrntl_themepath . $filename, 'w' ) ) ) {
					return false;
				}
				@fwrite( $handle, $contents );
				@fclose( $handle );
				@chmod( $crrntl_themepath . $filename, octdec( 644 ) );
			} else {
				$handle   = @fopen( $crrntl_themepath . $filename, 'r' );
				$contents = @fread( $handle, filesize( $crrntl_themepath . $filename ) );
				@fclose( $handle );
				if ( ! ( $handle = @fopen( $crrntl_themepath . $filename . '.bak', 'w' ) ) ) {
					return false;
				}
				@fwrite( $handle, $contents );
				@fclose( $handle );

				$handle   = @fopen( $crrntl_filepath . $filename, 'r' );
				$contents = @fread( $handle, filesize( $crrntl_filepath . $filename ) );
				@fclose( $handle );
				if ( ! ( $handle = @fopen( $crrntl_themepath . $filename, 'w' ) ) ) {
					return false;
				}
				@fwrite( $handle, $contents );
				@fclose( $handle );
				@chmod( $crrntl_themepath . $filename, octdec( 644 ) );
			}
		}
	}
}

if ( ! function_exists( 'crrntl_after_switch_theme' ) ) {
	function crrntl_after_switch_theme() {
		global $crrntl_filenames, $crrntl_themepath;
		$file_exists_flag = true;
		foreach ( $crrntl_filenames as $filename ) {
			if ( ! file_exists( $crrntl_themepath . $filename ) ) {
				$file_exists_flag = false;
			}
		}
		if ( ! $file_exists_flag ) {
			crrntl_plugin_templates_install();
		}
	}
}

if ( ! function_exists( 'crrntl_install' ) ) {
	function crrntl_install() {
		global $wpdb;
		load_plugin_textdomain( 'car-rental', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		$charset_collate = ! empty( $wpdb->charset ) ? "DEFAULT CHARACTER SET {$wpdb->charset}" : 'DEFAULT CHARACTER SET utf8';
		$charset_collate .= ! empty( $wpdb->collate ) ? " COLLATE {$wpdb->collate}" : ' COLLATE utf8_general_ci';

		$table_name = $wpdb->prefix . 'crrntl_currency';

		$sql = "CREATE TABLE {$table_name} (
			currency_id      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			country_currency CHAR(50)         NOT NULL,
			currency_code    CHAR(3)          NOT NULL,
			currency_hex     CHAR(20)         NOT NULL,
			currency_unicode CHAR(30)         NOT NULL,
			PRIMARY KEY  (currency_id)
		) ENGINE=InnoDB {$charset_collate}";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$wpdb->query( "INSERT INTO {$table_name} (currency_id, country_currency, currency_code, currency_hex, currency_unicode) VALUES
		(1, 'Albania Lek', 'ALL', '4c, 65, 6b', '&#76;&#101;&#107;'),
		(2, 'Afghanistan Afghani', 'AFN', '60b', '&#1547;'),
		(3, 'Argentina Peso', 'ARS', '24', '&#36;'),
		(4, 'Aruba Guilder', 'AWG', '192', '&#402;'),
		(5, 'Australia Dollar', 'AUD', '24', '&#36;'),
		(6, 'Azerbaijan New Manat', 'AZN', '43c, 430, 43d', '&#1084;&#1072;&#1085;'),
		(7, 'Bahamas Dollar', 'BSD', '24', '&#36;'),
		(8, 'Barbados Dollar', 'BBD', '24', '&#36;'),
		(9, 'Belarus Ruble', 'BYR', '70, 2e', '&#112;&#46;'),
		(10, 'Belize Dollar', 'BZD', '42, 5a, 24', '&#66;&#90;&#36;'),
		(11, 'Bermuda Dollar', 'BMD', '24', '&#36;'),
		(12, 'Bolivia Boliviano', 'BOB', '24, 62', '&#36;&#98;'),
		(13, 'Bosnia and Herzegovina Convertible Marka', 'BAM', '4b, 4d', '&#75;&#77;'),
		(14, 'Botswana Pula', 'BWP', '50', '&#80;'),
		(15, 'Bulgaria Lev', 'BGN', '43b, 432', '&#1083;&#1074;'),
		(16, 'Brazil Real', 'BRL', '52, 24', '&#82;&#36;'),
		(17, 'Brunei Darussalam Dollar', 'BND', '24', '&#36;'),
		(18, 'Cambodia Riel', 'KHR', '17db', '&#6107;'),
		(19, 'Canada Dollar', 'CAD', '24', '&#36;'),
		(20, 'Cayman Islands Dollar', 'KYD', '24', '&#36;'),
		(21, 'Chile Peso', 'CLP', '24', '&#36;'),
		(22, 'China Yuan Renminbi', 'CNY', 'a5', '&#165;'),
		(23, 'Colombia Peso', 'COP', '24', '&#36;'),
		(24, 'Costa Rica Colon', 'CRC', '20a1', '&#8353;'),
		(25, 'Croatia Kuna', 'HRK', '6b, 6e', '&#107;&#110;'),
		(26, 'Cuba Peso', 'CUP', '20b1', '&#8369;'),
		(27, 'Czech Republic Koruna', 'CZK', '4b, 10d', '&#75;&#269;'),
		(28, 'Denmark Krone', 'DKK', '6b, 72', '&#107;&#114;'),
		(29, 'Dominican Republic Peso', 'DOP', '52, 44, 24', '&#82;&#68;&#36;'),
		(30, 'East Caribbean Dollar', 'XCD', '24', '&#36;'),
		(31, 'Egypt Pound', 'EGP', 'a3', '&#163;'),
		(32, 'El Salvador Colon', 'SVC', '24', '&#36;'),
		(33, 'Estonia Kroon', 'EEK', '6b, 72', '&#107;&#114;'),
		(34, 'Euro Member Countries', 'EUR', '20ac', '&#8364;'),
		(35, 'Falkland Islands (Malvinas) Pound', 'FKP', 'a3', '&#163;'),
		(36, 'Fiji Dollar', 'FJD', '24', '&#36;'),
		(37, 'Ghana Cedi', 'GHC', 'a2', '&#162;'),
		(38, 'Gibraltar Pound', 'GIP', 'a3', '&#163;'),
		(39, 'Guatemala Quetzal', 'GTQ', '51', '&#81;'),
		(40, 'Guernsey Pound', 'GGP', 'a3', '&#163;'),
		(41, 'Guyana Dollar', 'GYD', '24', '&#36;'),
		(42, 'Honduras Lempira', 'HNL', '4c', '&#76;'),
		(43, 'Hong Kong Dollar', 'HKD', '24', '&#36;'),
		(44, 'Hungary Forint', 'HUF', '46, 74', '&#70;&#116;'),
		(45, 'Iceland Krona', 'ISK', '6b, 72', '&#107;&#114;'),
		(46, 'India Rupee', 'INR', '', ''),
		(47, 'Indonesia Rupiah', 'IDR', '52, 70', '&#82;&#112;'),
		(48, 'Iran Rial', 'IRR', 'fdfc', '&#65020;'),
		(49, 'Isle of Man Pound', 'IMP', 'a3', '&#163;'),
		(50, 'Israel Shekel', 'ILS', '20aa', '&#8362;'),
		(51, 'Jamaica Dollar', 'JMD', '4a, 24', '&#74;&#36;'),
		(52, 'Japan Yen', 'JPY', 'a5', '&#165;'),
		(53, 'Jersey Pound', 'JEP', 'a3', '&#163;'),
		(54, 'Kazakhstan Tenge', 'KZT', '43b, 432', '&#1083;&#1074;'),
		(55, 'Korea (North) Won', 'KPW', '20a9', '&#8361;'),
		(56, 'Korea (South) Won', 'KRW', '20a9', '&#8361;'),
		(57, 'Kyrgyzstan Som', 'KGS', '43b, 432', '&#1083;&#1074;'),
		(58, 'Laos Kip', 'LAK', '20ad', '&#8365;'),
		(59, 'Latvia Lat', 'LVL', '4c, 73', '&#76;&#115;'),
		(60, 'Lebanon Pound', 'LBP', 'a3', '&#163;'),
		(61, 'Liberia Dollar', 'LRD', '24', '&#36;'),
		(62, 'Lithuania Litas', 'LTL', '4c, 74', '&#76;&#116;'),
		(63, 'Macedonia Denar', 'MKD', '434, 435, 43d', '&#1076;&#1077;&#1085;'),
		(64, 'Malaysia Ringgit', 'MYR', '52, 4d', '&#82;&#77;'),
		(65, 'Mauritius Rupee', 'MUR', '20a8', '&#8360;'),
		(66, 'Mexico Peso', 'MXN', '24', '&#36;'),
		(67, 'Mongolia Tughrik', 'MNT', '20ae', '&#8366;'),
		(68, 'Mozambique Metical', 'MZN', '4d, 54', '&#77;&#84;'),
		(69, 'Namibia Dollar', 'NAD', '24', '&#36;'),
		(70, 'Nepal Rupee', 'NPR', '20a8', '&#8360;'),
		(71, 'Netherlands Antilles Guilder', 'ANG', '192', '&#402;'),
		(72, 'New Zealand Dollar', 'NZD', '24', '&#36;'),
		(73, 'Nicaragua Cordoba', 'NIO', '43, 24', '&#67;&#36;'),
		(74, 'Nigeria Naira', 'NGN', '20a6', '&#8358;'),
		(75, 'Korea (North) Won', 'KPW', '20a9', '&#8361;'),
		(76, 'Norway Krone', 'NOK', '6b, 72', '&#107;&#114;'),
		(77, 'Oman Rial', 'OMR', 'fdfc', '&#65020;'),
		(78, 'Pakistan Rupee', 'PKR', '20a8', '&#8360;'),
		(79, 'Panama Balboa', 'PAB', '42, 2f, 2e', '&#66;&#47;&#46;'),
		(80, 'Paraguay Guarani', 'PYG', '47, 73', '&#71;&#115;'),
		(81, 'Peru Nuevo Sol', 'PEN', '53, 2f, 2e', '&#83;&#47;&#46;'),
		(82, 'Philippines Peso', 'PHP', '20b1', '&#8369;'),
		(83, 'Poland Zloty', 'PLN', '7a, 142', '&#122;&#322;'),
		(84, 'Qatar Riyal', 'QAR', 'fdfc', '&#65020;'),
		(85, 'Romania New Leu', 'RON', '6c, 65, 69', '&#108;&#101;&#105;'),
		(86, 'Russia Ruble', 'RUB', '440, 443, 431', '&#1088;&#1091;&#1073;'),
		(87, 'Saint Helena Pound', 'SHP', 'a3', '&#163;'),
		(88, 'Saudi Arabia Riyal', 'SAR', 'fdfc', '&#65020;'),
		(89, 'Serbia Dinar', 'RSD', '414, 438, 43d, 2e', '&#1044;&#1080;&#1085;&#46;'),
		(90, 'Seychelles Rupee', 'SCR', '20a8', '&#8360;'),
		(91, 'Singapore Dollar', 'SGD', '24', '&#36;'),
		(92, 'Solomon Islands Dollar', 'SBD', '24', '&#36;'),
		(93, 'Somalia Shilling', 'SOS', '53', '&#83;'),
		(94, 'South Africa Rand', 'ZAR', '52', '&#82;'),
		(95, 'Korea (South) Won', 'KRW', '20a9', '&#8361;'),
		(96, 'Sri Lanka Rupee', 'LKR', '20a8', '&#8360;'),
		(97, 'Sweden Krona', 'SEK', '6b, 72', '&#107;&#114;'),
		(98, 'Switzerland Franc', 'CHF', '43, 48, 46', '&#67;&#72;&#70;'),
		(99, 'Suriname Dollar', 'SRD', '24', '&#36;'),
		(100, 'Syria Pound', 'SYP', 'a3', '&#163;'),
		(101, 'Taiwan New Dollar', 'TWD', '4e, 54, 24', '&#78;&#84;&#36;'),
		(102, 'Thailand Baht', 'THB', 'e3f', '&#3647;'),
		(103, 'Trinidad and Tobago Dollar', 'TTD', '54, 54, 24', '&#84;&#84;&#36;'),
		(104, 'Turkey Lira', 'TRY', '', ''),
		(105, 'Turkey Lira', 'TRL', '20a4', '&#8356;'),
		(106, 'Tuvalu Dollar', 'TVD', '24', '&#36;'),
		(107, 'Ukraine Hryvnia', 'UAH', '20b4', '&#8372;'),
		(108, 'United Kingdom Pound', 'GBP', 'a3', '&#163;'),
		(109, 'United States Dollar', 'USD', '24', '&#36;'),
		(110, 'Uruguay Peso', 'UYU', '24, 55', '&#36;&#85;'),
		(111, 'Uzbekistan Som', 'UZS', '43b, 432', '&#1083;&#1074;'),
		(112, 'Venezuela Bolivar', 'VEF', '42, 73', '&#66;&#115;'),
		(113, 'Viet Nam Dong', 'VND', '20ab', '&#8363;'),
		(114, 'Yemen Rial', 'YER', 'fdfc', '&#65020;'),
		(115, 'Zimbabwe Dollar', 'ZWD', '5a, 24', '&#90;&#36;')
		ON DUPLICATE KEY UPDATE
		country_currency = VALUES(country_currency),
		currency_code    = VALUES(currency_code),
		currency_hex     = VALUES(currency_hex),
		currency_unicode = VALUES(currency_unicode);" );

		$table_name = $wpdb->prefix . 'crrntl_orders';

		$sql = "CREATE TABLE {$table_name} (
			order_id        BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			car_id          BIGINT(20) UNSIGNED NOT NULL,
			pickup_loc_id   BIGINT(20) UNSIGNED NOT NULL,
			dropoff_loc_id  BIGINT(20) UNSIGNED NOT NULL,
			pickup_date     DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
			dropoff_date    DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
			user_id         BIGINT(20) UNSIGNED NOT NULL,
			total           DECIMAL(20, 2)      NOT NULL,
			status_id       INT(11),
			PRIMARY KEY  (order_id)
		) ENGINE=InnoDB {$charset_collate}";
		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'crrntl_locations';

		$sql = "CREATE TABLE {$table_name} (
			loc_id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			place_id          TEXT                NOT NULL,
			formatted_address TEXT                NOT NULL,
			PRIMARY KEY  (loc_id)
		) ENGINE=InnoDB {$charset_collate}";
		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'crrntl_extras_order';

		$sql = "CREATE TABLE {$table_name} (
			id             BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id       BIGINT(20) UNSIGNED NOT NULL,
			extra_id       BIGINT(20) UNSIGNED NOT NULL,
			extra_quantity INT(11)             NOT NULL DEFAULT '1',
			PRIMARY KEY  (id)
		) ENGINE=InnoDB {$charset_collate}";
		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'crrntl_statuses';

		$sql = "CREATE TABLE {$table_name} (
			status_id   INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			status_name TEXT,
			PRIMARY KEY  (status_id)
		) ENGINE=InnoDB {$charset_collate}";
		dbDelta( $sql );

		$statuses = array(
			__( 'Reserved', 'car-rental' ),
			__( 'Free', 'car-rental' ),
			__( 'In use', 'car-rental' ),
		);

		$wpdb->query( "INSERT INTO {$table_name} (status_id, status_name) VALUES
		(1, '{$statuses[0]}'),
		(2, '{$statuses[1]}'),
		(3, '{$statuses[2]}')
		ON DUPLICATE KEY UPDATE
		status_name = status_name;" );
	}
}

/**
 * Function for register new post_types and taxonomies
 */
if ( ! function_exists( 'crrntl_setup_post_types' ) ) {
	function crrntl_setup_post_types() {
		
		/* Register "Cars" custom post type */
		$labels = array(
			'name'                  => _x( 'Cars', 'post type general name', 'car-rental' ),
			'singular_name'         => _x( 'Car', 'post type singular name', 'car-rental' ),
			'menu_name'             => _x( 'Cars', 'admin menu', 'car-rental' ),
			'name_admin_bar'        => _x( 'Car', 'add new on admin bar', 'car-rental' ),
			'all_items'             => __( 'All Cars', 'car-rental' ),
			'add_new'               => _x( 'Add New', 'car', 'car-rental' ),
			'add_new_item'          => __( 'Add New Car', 'car-rental' ),
			'edit_item'             => __( 'Edit Car', 'car-rental' ),
			'new_item'              => __( 'New Car', 'car-rental' ),
			'view_item'             => __( 'View Car', 'car-rental' ),
			'search_items'          => __( 'Search Cars', 'car-rental' ),
			'not_found'             => __( 'No Cars found.', 'car-rental' ),
			'not_found_in_trash'    => __( 'No Cars found in Trash.', 'car-rental' ),
			'filter_items_list'     => __( 'Filter Cars list', 'car-rental' ),
			'items_list_navigation' => __( 'Cars list navigation', 'car-rental' ),
			'items_list'            => __( 'Cars list', 'car-rental' ),
		);

		$args = apply_filters( 'crrntl_args_filter', array(
			'labels'          => $labels,
			'public'          => true,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-sos',
			'rewrite'         => true,
			'capability_type' => 'post',			
			'has_archive'     => true,
			'hierarchical'    => false,
			'menu_position'   => 58,
			'supports'        => array(
				'title',
				'editor',
				'thumbnail'
			)
		), 'cars' );
		register_post_type( 'cars', $args );

		/* Register "Manufacturers" custom taxonomy */
		$labels = array(
			'name'                       => _x( 'Manufacturers', 'taxonomy general name', 'car-rental' ),
			'singular_name'              => _x( 'Manufacturer', 'taxonomy singular name', 'car-rental' ),
			'search_items'               => __( 'Search Manufacturers', 'car-rental' ),
			'popular_items'              => __( 'Popular Manufacturers', 'car-rental' ),
			'all_items'                  => __( 'All Manufacturers', 'car-rental' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Manufacturer', 'car-rental' ),
			'update_item'                => __( 'Update Manufacturer', 'car-rental' ),
			'add_new_item'               => __( 'Add New Manufacturer', 'car-rental' ),
			'view_item'                  => __( 'View Manufacturer', 'car-rental' ),
			'new_item_name'              => __( 'New Manufacturer Name', 'car-rental' ),
			'separate_items_with_commas' => __( 'Separate Manufacturers with commas', 'car-rental' ),
			'add_or_remove_items'        => __( 'Add or remove Manufacturers', 'car-rental' ),
			'choose_from_most_used'      => __( 'Choose from the most used Manufacturers', 'car-rental' ),
			'not_found'                  => __( 'No Manufacturers found.', 'car-rental' ),
			'menu_name'                  => __( 'Manufacturers', 'car-rental' ),
			'items_list_navigation'      => __( 'Manufacturers list navigation', 'car-rental' ),
			'items_list'                 => __( 'Manufacturers list', 'car-rental' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_tagcloud'     => false,
			'show_admin_column' => true,
			'meta_box_cb'       => 'crrntl_tax_metabox',
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'manufacturer' ),
		);

		register_taxonomy( 'manufacturer', array( 'cars' ), $args );

		/* Register "Vehicle Type" custom taxonomy */
		$labels = array(
			'name'                       => _x( 'Vehicle Types', 'taxonomy general name', 'car-rental' ),
			'singular_name'              => _x( 'Vehicle Type', 'taxonomy singular name', 'car-rental' ),
			'search_items'               => __( 'Search Vehicle Types', 'car-rental' ),
			'popular_items'              => __( 'Popular Vehicle Types', 'car-rental' ),
			'all_items'                  => __( 'All Vehicle Types', 'car-rental' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Vehicle Type', 'car-rental' ),
			'update_item'                => __( 'Update Vehicle Type', 'car-rental' ),
			'add_new_item'               => __( 'Add New Vehicle Type', 'car-rental' ),
			'view_item'                  => __( 'View Vehicle Type', 'car-rental' ),
			'new_item_name'              => __( 'New Vehicle Type Name', 'car-rental' ),
			'separate_items_with_commas' => __( 'Separate Vehicle Types with commas', 'car-rental' ),
			'add_or_remove_items'        => __( 'Add or remove Vehicle Types', 'car-rental' ),
			'choose_from_most_used'      => __( 'Choose from the most used Vehicle Types', 'car-rental' ),
			'not_found'                  => __( 'No Vehicle Types found.', 'car-rental' ),
			'menu_name'                  => __( 'Vehicle Types', 'car-rental' ),
			'items_list_navigation'      => __( 'Vehicle Types list navigation', 'car-rental' ),
			'items_list'                 => __( 'Vehicle Types list', 'car-rental' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_tagcloud'     => false,
			'show_admin_column' => true,
			'meta_box_cb'       => 'crrntl_tax_metabox',
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'vehicle_type' ),
		);

		register_taxonomy( 'vehicle_type', array( 'cars' ), $args );

		/* Register "Car Class" custom taxonomy */
		$labels = array(
			'name'                       => _x( 'Car Classes', 'taxonomy general name', 'car-rental' ),
			'singular_name'              => _x( 'Car Class', 'taxonomy singular name', 'car-rental' ),
			'search_items'               => __( 'Search Car Classes', 'car-rental' ),
			'popular_items'              => __( 'Popular Car Classes', 'car-rental' ),
			'all_items'                  => __( 'All Car Classes', 'car-rental' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Car Class', 'car-rental' ),
			'update_item'                => __( 'Update Car Class', 'car-rental' ),
			'add_new_item'               => __( 'Add New Car Class', 'car-rental' ),
			'view_item'                  => __( 'View Car Class', 'car-rental' ),
			'new_item_name'              => __( 'New Car Class Name', 'car-rental' ),
			'separate_items_with_commas' => __( 'Separate Car Classes with commas', 'car-rental' ),
			'add_or_remove_items'        => __( 'Add or remove Car Classes', 'car-rental' ),
			'choose_from_most_used'      => __( 'Choose from the most used Car Classes', 'car-rental' ),
			'not_found'                  => __( 'No Car Classes found.', 'car-rental' ),
			'menu_name'                  => __( 'Car Classes', 'car-rental' ),
			'items_list_navigation'      => __( 'Car Classes list navigation', 'car-rental' ),
			'items_list'                 => __( 'Car Classes list', 'car-rental' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_tagcloud'     => false,
			'show_admin_column' => true,
			'meta_box_cb'       => 'crrntl_tax_metabox',
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'car_class' ),
		);

		register_taxonomy( 'car_class', array( 'cars' ), $args );

		/* Register "Extra" custom taxonomy */
		$labels = array(
			'name'                       => _x( 'Extras', 'taxonomy general name', 'car-rental' ),
			'singular_name'              => _x( 'Extra', 'taxonomy singular name', 'car-rental' ),
			'search_items'               => __( 'Search Extras', 'car-rental' ),
			'popular_items'              => __( 'Popular Extras', 'car-rental' ),
			'all_items'                  => __( 'All Extras', 'car-rental' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Extra', 'car-rental' ),
			'update_item'                => __( 'Update Extra', 'car-rental' ),
			'add_new_item'               => __( 'Add New Extra', 'car-rental' ),
			'view_item'                  => __( 'View Extra', 'car-rental' ),
			'new_item_name'              => __( 'New Extra Name', 'car-rental' ),
			'separate_items_with_commas' => __( 'Separate Extras with commas', 'car-rental' ),
			'add_or_remove_items'        => __( 'Add or remove Extras', 'car-rental' ),
			'choose_from_most_used'      => __( 'Choose from the most used Extras', 'car-rental' ),
			'not_found'                  => __( 'No Extras found.', 'car-rental' ),
			'menu_name'                  => __( 'Extras', 'car-rental' ),
			'items_list_navigation'      => __( 'Extras list navigation', 'car-rental' ),
			'items_list'                 => __( 'Extras list', 'car-rental' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_tagcloud'     => false,
			'show_admin_column' => true,
			'meta_box_cb'       => 'crrntl_extras_meta_box',
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'extra' )
		);

		register_taxonomy( 'extra', array( 'cars' ), $args );
	}
}

/**
 * Callback function for taxonomy meta boxes
 *
 * A simple callback function for 'meta_box_cb' argument
 * inside register_taxonomy() that replaces the regular
 * checkboxes with a plain dropdown list
 *
 * @param  [type] $post [description]
 * @param  [type] $box  [description]
 */
if ( ! function_exists( 'crrntl_tax_metabox' ) ) {
	function crrntl_tax_metabox( $post, $box ) {
		if ( isset( $box['args'] ) && is_array( $box['args'] ) ) {
			$args     = $box['args'];
			$taxonomy = $args['taxonomy']; ?>
			<div id="taxonomy-<?php echo $taxonomy; ?>">
				<?php $name = 'tax_input[' . $taxonomy . ']';
				/* Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks. */
				echo "<input type='hidden' name='{$name}[]' value='0' />";

				$term_obj = wp_get_object_terms( $post->ID, $taxonomy ); /* _log($term_obj[0]->term_id) */

				wp_dropdown_categories( array(
					'taxonomy'         => $taxonomy,
					'hide_empty'       => 0,
					'name'             => $name . '[]',
					'selected'         => isset( $term_obj[0] ) ? $term_obj[0]->term_id : -1,
					'orderby'          => 'name',
					'hierarchical'     => 0,
					'show_option_none' => '&mdash;',
					'class'            => 'widefat',
				) ); ?>
			</div>
		<?php }
	}
}

if ( ! function_exists( 'crrntl_extras_meta_box' ) ) {
	function crrntl_extras_meta_box( $post, $box ) {
		$args = ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) ) ? array() : $box['args'];
		$tax_name = esc_attr( $args['taxonomy'] ); ?>
		<div id="taxonomy-<?php echo $tax_name; ?>" class="categorydiv">
			<div id="<?php echo $tax_name; ?>-all" class="tabs-panel">
				<?php
				$name = 'tax_input[' . $tax_name . ']';
				/* Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks. */
				echo "<input type='hidden' name='{$name}[]' value='0' />"; ?>
				<ul id="<?php echo $tax_name; ?>checklist" data-wp-lists="list:<?php echo $tax_name; ?>" class="categorychecklist form-no-clear">
					<?php wp_terms_checklist( $post->ID, array( 'taxonomy' => $tax_name, 'popular_cats' => false ) ); ?>
				</ul>
			</div>
		</div>
	<?php }
}

/**
 * Adds a box to the main column on the post-type Cars edit screens.
 */
if ( ! function_exists( 'crrntl_add_custom_box' ) ) {
	function crrntl_add_custom_box() {
		add_meta_box( 'car-info-metabox', __( "Car's Info", 'car-rental' ), 'crrntl_car_info_metabox', 'cars', 'normal', 'high' );
	}
}

/**
 * Prints the box content.
 *
 * @param WP_Post $post The object for the current post/page.
 */
if ( ! function_exists( 'crrntl_car_info_metabox' ) ) {
	function crrntl_car_info_metabox( $post ) {
		global $crrntl_options, $wpdb, $crrntl_currency;
		if ( empty( $crrntl_options ) ) {
			$crrntl_options = get_option( 'crrntl_options' );
		}
		if ( empty( $crrntl_options['custom_currency'] ) || 0 == $crrntl_options['currency_custom_display'] ) {
			$crrntl_currency = $wpdb->get_var( $wpdb->prepare( "SELECT `currency_unicode` FROM `{$wpdb->prefix}crrntl_currency` WHERE `currency_id` = %d", $crrntl_options['currency_unicode'] ) );
			if ( empty( $crrntl_currency ) ) {
				$crrntl_currency = '&#36;';
			}
		} else {
			$crrntl_currency = $crrntl_options['custom_currency'];
		}

		$unit_consumption = ( empty( $crrntl_options['custom_unit_consumption'] ) || 0 == $crrntl_options['unit_consumption_custom_display'] ) ? $crrntl_options['unit_consumption'] : $crrntl_options['custom_unit_consumption'];

		/*
		* Use get_post_meta() to retrieve an existing value
		* from the database and use the value for the form.
		*/
		$car_info       	= get_post_meta( $post->ID, 'car_info', true );
		$car_passengers 	= get_post_meta( $post->ID, 'car_passengers', true );
		$car_price      	= get_post_meta( $post->ID, 'car_price', true );
		$car_location_id   	= get_post_meta( $post->ID, 'car_location', true );
		$crrntl_location_list = $wpdb->get_results( "SELECT `loc_id`, `formatted_address` FROM `{$wpdb->prefix}crrntl_locations` ORDER BY `formatted_address`", ARRAY_A );

		/* Add a nonce field so we can check for it later. */
		wp_nonce_field( plugin_basename( __FILE__ ), 'crrntl_noncename' );
		/* Forms for custom metabox */ ?>
		<table class="form-table crrntl-car-info">
			<tbody>
				<tr>
					<th>
						<?php _e( 'Location', 'car-rental' ); ?>:
					</th>
					<td>
						<fieldset>
							<label>
								<input id="crrntl-add-new-location-false" name="crrntl_add_new_location" type="radio" value="0" <?php checked( ! empty( $car_location_id ) ); ?> />
								<select id="crrntl-choose-car-location" class="crrntl-location-select" name="crrntl_location_id" title="<?php _e( 'Choose location', 'car-rental' ); ?>">
									<option value=""><?php _e( 'Choose location', 'car-rental' ); ?></option>
									<?php foreach ( $crrntl_location_list as $one_location ) { ?>
										<option value="<?php echo $one_location['loc_id']; ?>" <?php selected( ! empty( $car_location_id ) && ( $one_location['loc_id'] ) == $car_location_id ); ?>><?php echo $one_location['formatted_address']; ?></option>
									<?php } ?>
								</select>
								<span class="bws_info"><?php _e( 'Choose location from list', 'car-rental' ); ?></span>
							</label>
							<br />
							<label>
								<noscript>
									<input type="hidden" name="crrntl_noscript" value="1" />
								</noscript>
								<input id="crrntl-add-new-location-true" name="crrntl_add_new_location" type="radio" value="1" <?php checked( empty( $car_location_id ) ); ?> />
								<input type="hidden" id="crrntl-location" name="crrntl_location" size="40" value="" />
								<input id="crrntl-pac-input" class="crrntl-controls" name="crrntl_formatted_location" type="text" value="" placeholder="<?php _e( 'Enter a location', 'car-rental' ); ?>">
								<span class="bws_info"><?php _e( 'Add new location', 'car-rental' ); ?></span>
							</label>
							<div id="crrntl-map"></div>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th>
						<label for="crrntl-passengers"><?php _e( 'Number of passengers', 'car-rental' ); ?>:</label>
					</th>
					<td>
						<input type="number" id="crrntl-passengers" name="crrntl_passengers" value="<?php echo ( ! empty( $car_passengers ) ) ? $car_passengers : ''; ?>" min="0" max="999" />
						<span class="bws_info">(<?php _e( 'max.', 'car-rental' ); ?>: 999)</span>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Luggage Quantity', 'car-rental' ); ?>:</th>
					<td>
						<label><input type="number" id="crrntl-luggage-large" name="crrntl_luggage_large" value="<?php echo ( ! empty( $car_info['luggage_large'] ) ) ? $car_info['luggage_large'] : ''; ?>" min="0" max="999" /> <?php _e( 'large suitcases', 'car-rental' ); ?></label>
						<span class="bws_info">(<?php _e( 'max.', 'car-rental' ); ?>: 999)</span>
						<br />
						<label><input type="number" id="crrntl-luggage-small" name="crrntl_luggage_small" value="<?php echo ( ! empty( $car_info['luggage_small'] ) ) ? $car_info['luggage_small'] : ''; ?>" min="0" max="999" /> <?php _e( 'small suitcases', 'car-rental' ); ?></label>
						<span class="bws_info">(<?php _e( 'max.', 'car-rental' ); ?>: 999)</span>
					</td>
				</tr>
				<tr>
					<th>
						<label for="crrntl-transmission"><?php _e( 'Transmission', 'car-rental' ); ?>:</label>
					</th>
					<td>
						<select id="crrntl-transmission" name="crrntl_transmission">
							<option value="0" <?php selected( empty( $car_info['transmission'] ) || 0 == $car_info['transmission'] ); ?>><?php _e( 'Unknown', 'car-rental' ); ?></option>
							<option value="1" <?php selected( ! empty( $car_info['transmission'] ) && 1 == $car_info['transmission'] ); ?>><?php _e( 'Automatic', 'car-rental' ); ?></option>
							<option value="2" <?php selected( ! empty( $car_info['transmission'] ) && 2 == $car_info['transmission'] ); ?>><?php _e( 'Manual', 'car-rental' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th>
						<label for="crrntl-condition"><?php _e( 'Air Conditioning', 'car-rental' ); ?>:</label>
					</th>
					<td>
						<input type="checkbox" id="crrntl-condition" name="crrntl_condition" value="1" <?php checked( ! empty( $car_info['condition'] ) && 1 == $car_info['condition'] ); ?> />
					</td>
				</tr>
				<tr>
					<th>
						<label for="crrntl-consumption"><?php _e( 'Average consumption', 'car-rental' ); ?>:</label>
					</th>
					<td>
						<input type="number" id="crrntl-consumption" name="crrntl_consumption" value="<?php echo ( ! empty( $car_info['consumption'] ) ) ? $car_info['consumption'] : ''; ?>" min="0" max="999" /> <?php echo $unit_consumption; ?>
						<span class="bws_info">(<?php _e( 'max.', 'car-rental' ); ?>: 999)</span>
					</td>
				</tr>
				<tr>
					<th>
						<label for="crrntl-price"><?php _e( 'Price per hour', 'car-rental' ); ?> (<?php echo $crrntl_currency; ?>):</label>
					</th>
					<td>
						<input type="text" pattern="^\d{1,9}(\.\d{2})?$" id="crrntl-price" name="crrntl_price" size="10" value="<?php echo ( ! empty( $car_price ) ) ? $car_price : ''; ?>" />
						<span class="crrntl-info"><?php _e( 'for example', 'car-rental' ); ?>: 258.00</span>
						<span class="bws_info">(<?php _e( 'max.', 'car-rental' ); ?>: 999999999.99)</span>
					</td>
				</tr>
			</tbody>
		</table>
	<?php }
}

/**
 * When the post is saved, save custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
if ( ! function_exists( 'crrntl_save_postdata' ) ) {
	function crrntl_save_postdata( $post_id ) {
		global $wpdb;
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */
		/* If this is an autosave, our form has not been submitted, so we don't want to do anything. */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		/* Check the user's permissions. */
		if ( empty( $_POST['post_type'] ) || 'cars' != $_POST['post_type'] || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		/* Verify that the nonce is valid. */
		if ( ! wp_verify_nonce( $_POST['crrntl_noncename'], plugin_basename( __FILE__ ) ) ) {
			return;
		}

		/* Sanitize user input. */
		$car_new_info                  = array();
		$car_new_info['transmission']  = sanitize_text_field( $_POST['crrntl_transmission'] );
		$car_new_info['luggage_large'] = sanitize_text_field( $_POST['crrntl_luggage_large'] );
		$car_new_info['luggage_small'] = sanitize_text_field( $_POST['crrntl_luggage_small'] );
		$car_new_info['condition']     = sanitize_text_field( $_POST['crrntl_condition'] );
		$car_new_info['consumption']   = sanitize_text_field( $_POST['crrntl_consumption'] );

		$car_new_passengers = sanitize_text_field( $_POST['crrntl_passengers'] );
		$car_new_price      = ( isset( $_POST['crrntl_price'] ) && preg_match( '/^\d{1,9}(\.\d{2})?$/', $_POST['crrntl_price'] ) ) ? $_POST['crrntl_price'] : 0;

		if ( isset( $_POST['crrntl_add_new_location'] ) && '0' == $_POST['crrntl_add_new_location'] ) {
			$loc_id = ( ! empty( $_POST['crrntl_location_id'] ) ) ? sanitize_text_field( $_POST['crrntl_location_id'] ) : '';
			update_post_meta( $post_id, 'car_location', $loc_id );
		} else {
			if ( isset( $_POST['crrntl_noscript'] ) && ! empty( $_POST['crrntl_formatted_location'] ) ) {
				$crrntl_new_loc = sanitize_text_field( $_POST['crrntl_formatted_location'] );
				$loc_id = $wpdb->get_var( $wpdb->prepare( "SELECT `loc_id` FROM `{$wpdb->prefix}crrntl_locations` WHERE `formatted_address` = %s", $crrntl_new_loc ) );
				if ( null == $loc_id ) {
					$wpdb->insert(
						$wpdb->prefix . 'crrntl_locations',
						array(
							'place_id'          => '',
							'formatted_address' => wp_unslash( $crrntl_new_loc ),
						),
						array( '%s', '%s' )
					);
					$loc_id = $wpdb->insert_id;
				}
				update_post_meta( $post_id, 'car_location', $loc_id );
			} elseif ( ! isset( $_POST['crrntl_noscript'] ) ) {
				$crrntl_new_place_id 	= ( isset( $_POST['crrntl_location'] ) ) ? sanitize_text_field( $_POST['crrntl_location'] ) : '';
				$crrntl_new_loc 		= ( isset( $_POST['crrntl_formatted_location'] ) ) ? sanitize_text_field( $_POST['crrntl_formatted_location'] ) : '';
				if ( ! empty( $crrntl_new_place_id ) || ! empty( $crrntl_new_loc ) ) {
					$query = '';
					if ( ! empty( $crrntl_new_loc ) )
						$query .= "`formatted_address` = '$crrntl_new_loc'";

					if ( ! empty( $crrntl_new_place_id ) )
						$query .= empty( $query ) ? "`place_id` = '$crrntl_new_place_id'" : " OR `place_id` = '$crrntl_new_place_id'";

					$loc_id = $wpdb->get_var( "SELECT `loc_id` FROM {$wpdb->prefix}crrntl_locations WHERE {$query};" );
					if ( null == $loc_id ) {
						$wpdb->insert(
							$wpdb->prefix . 'crrntl_locations',
							array(
								'place_id'          => $crrntl_new_place_id,
								'formatted_address' => wp_unslash( $crrntl_new_loc ),
							),
							array( '%s', '%s' )
						);
						$loc_id = $wpdb->insert_id;
					}
					update_post_meta( $post_id, 'car_location', $loc_id );
				}
			}
		}

		/* Update the meta field in the database. */
		update_post_meta( $post_id, 'car_info', $car_new_info );
		update_post_meta( $post_id, 'car_passengers', $car_new_passengers );
		update_post_meta( $post_id, 'car_price', $car_new_price );
	}
}

/**
 * Adds a meta fields to "Extras" taxonomy.
 */
if ( ! function_exists( 'crrntl_extra_add_form_fields' ) ) {
	function crrntl_extra_add_form_fields() {
		global $crrntl_options, $wpdb, $crrntl_currency;
		if ( empty( $crrntl_options ) ) {
			$crrntl_options = get_option( 'crrntl_options' );
		}
		if ( empty( $crrntl_options['custom_currency'] ) || 0 == $crrntl_options['currency_custom_display'] ) {
			$crrntl_currency = $wpdb->get_var( $wpdb->prepare( "SELECT `currency_unicode` FROM `{$wpdb->prefix}crrntl_currency` WHERE `currency_id` = %d;", $crrntl_options['currency_unicode'] ) );
			if ( empty( $crrntl_currency ) ) {
				$crrntl_currency = '&#36;';
			}
		} else {
			$crrntl_currency = $crrntl_options['custom_currency'];
		} ?>
		<div class="form-field">
			<label for="crrntl-extra-details"><?php _e( 'More details', 'car-rental' ); ?></label>
			<textarea name="crrntl_extra_details" id="crrntl-extra-details"></textarea>
			<p class="description"><?php _e( 'More about this extra', 'car-rental' ); ?></p>
		</div>
		<div class="form-field">
			<label for="crrntl-extra-rpice"><?php _e( 'Price per hour', 'car-rental' ); ?> (<?php echo $crrntl_currency; ?>)</label>
			<input type="text" pattern="^\d{1,9}(\.\d{2})?$" name="crrntl_extra_price" id="crrntl-extra-rpice" />
			<p class="description"><?php _e( 'The price for one unit. For example', 'car-rental' ); ?>: 258.00 <span class="bws_info">(<?php _e( 'max.', 'car-rental' ); ?>: 999999999.99)</span></p>
		</div>
		<div>
			<label><?php _e( 'Ability to choose a quantity', 'car-rental' ); ?></label>
			<label><input type="checkbox" name="crrntl_extra_quantity" id="crrntl-extra-quantity-on" value="1" /><?php _e( 'Option is available', 'car-rental' ); ?>
			</label>
			<br />
			<label for="extra_photo"><?php _e( 'Image', 'car-rental' ); ?>:</label>
			<div>
				<p id="crrntl-no-image"><?php _e( 'No image chosen', 'car-rental' ); ?></p>
				<input type="button" class="crrntl-upload-image button" value="<?php _e( 'Add image', 'car-rental' ); ?>" />
				<input type="button" class="crrntl-remove-image button" style="display: none;" value="<?php _e( 'Remove image', 'car-rental' ); ?>" />
				<input type="hidden" name="crrntl_extra_image" class="crrntl-image-id" value="" />
			</div>
			<br />
		</div>
	<?php }
}

if ( ! function_exists( 'crrntl_extra_edit_form_fields' ) ) {
	function crrntl_extra_edit_form_fields( $term ) {
		global $crrntl_options, $wpdb, $crrntl_currency;
		if ( empty( $crrntl_options ) ) {
			$crrntl_options = get_option( 'crrntl_options' );
		}
		if ( empty( $crrntl_options['custom_currency'] ) || 0 == $crrntl_options['currency_custom_display'] ) {
			$crrntl_currency = $wpdb->get_var( $wpdb->prepare( "SELECT `currency_unicode` FROM `{$wpdb->prefix}crrntl_currency` WHERE `currency_id` = %d;", $crrntl_options['currency_unicode'] ) );;
			if ( empty( $crrntl_currency ) ) {
				$crrntl_currency = '&#36;';
			}
		} else {
			$crrntl_currency = $crrntl_options['custom_currency'];
		}

		/* Get current metadata of the term */
		$extra_metadata = crrntl_get_term_meta( $term->term_id );

		/* Display meta field on the edit term page */ ?>
		<tr class="form-field">
			<th valign="top" scope="row">
				<label for="crrntl-extra-details"><?php _e( 'More details', 'car-rental' ); ?></label>
			</th>
			<td>
				<textarea name="crrntl_extra_details" id="crrntl-extra-details"><?php echo ( ! empty( $extra_metadata['extra_details'][0] ) ) ? $extra_metadata['extra_details'][0] : ''; ?></textarea>
				<p class="description"><?php _e( 'More about this extra', 'car-rental' ); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top" scope="row">
				<label for="crrntl-extra-rpice"><?php _e( 'Price per hour', 'car-rental' ); ?> (<?php echo $crrntl_currency; ?>)</label>
			</th>
			<td>
				<input type="text" pattern="^\d{1,9}(\.\d{2})?$" name="crrntl_extra_price" id="crrntl-extra-rpice" value="<?php echo ( ! empty( $extra_metadata['extra_price'][0] ) ) ? $extra_metadata['extra_price'][0] : ''; ?>" />
				<p class="description"><?php _e( 'The price for one unit. For example', 'car-rental' ); ?>: 258.00 <span class="bws_info">(<?php _e( 'max.', 'car-rental' ); ?>: 999999999.99)</span></p>
			</td>
		</tr>
		<tr class="form-field">
			<th valign="top" scope="row">
				<label><?php _e( 'Ability to choose a quantity', 'car-rental' ); ?></label>
			</th>
			<td>
				<label><input type="checkbox" name="crrntl_extra_quantity" id="crrntl-extra-quantity-on" value="1" <?php checked( ! empty( $extra_metadata['extra_quantity'][0] ) && 1 == $extra_metadata['extra_quantity'][0] ); ?> /><?php _e( 'Option is available', 'car-rental' ); ?></label>
			</td>
		</tr>
		<tr valign="top">
			<th valign="top" scope="row"><?php _e( 'Image', 'car-rental' ); ?>:</th>
			<td>
				<?php if ( ! empty( $extra_metadata['extra_image'][0] ) ) {
					$image_attributes = wp_get_attachment_image_src( $extra_metadata['extra_image'][0], 'thumbnail' ); ?>
					<img class="crrntl-uploaded-image" src="<?php echo $image_attributes[0]; ?>" alt="<?php _e( 'Extras Image', 'car-rental' ); ?>">
					<div class="clear"></div>
					<p id="crrntl-no-image" style="display: none;"><?php _e( 'No image chosen', 'car-rental' ); ?></p>
					<input type="button" class="crrntl-upload-image button" value="<?php _e( 'Change image', 'car-rental' ); ?>" />
					<input type="button" class="crrntl-remove-image button" value="<?php _e( 'Remove image', 'car-rental' ); ?>" />
				<?php } else { ?>
					<p id="crrntl-no-image"><?php _e( 'No image chosen', 'car-rental' ); ?></p>
					<input type="button" class="crrntl-upload-image button" value="<?php _e( 'Add image', 'car-rental' ); ?>" />
					<input type="button" class="crrntl-remove-image button" style="display: none;" value="<?php _e( 'Remove image', 'car-rental' ); ?>" />
				<?php } ?>
				<input type="hidden" name="crrntl_extra_image" class="crrntl-image-id" value="<?php echo ! empty( $extra_metadata['extra_image'][0] ) ? $extra_metadata['extra_image'][0] : ''; ?>" />
			</td>
		</tr>

	<?php }
}

if ( ! function_exists( 'crrntl_save_extra' ) ) {
	function crrntl_save_extra( $term_id ) {
		if ( isset( $_POST ) ) {
			/* Get data from terms meta fields */
			$extra_details  = ! empty( $_POST['crrntl_extra_details'] ) ? sanitize_text_field( $_POST['crrntl_extra_details'] ) : '';
			$extra_price    = ( isset( $_POST['crrntl_extra_price'] ) && preg_match( '/^\d{1,9}(\.\d{2})?$/', $_POST['crrntl_extra_price'] ) ) ? $_POST['crrntl_extra_price'] : 0;
			$extra_quantity = ! empty( $_POST['crrntl_extra_quantity'] ) ? $_POST['crrntl_extra_quantity'] : 0;
			$extra_image    = isset( $_POST['crrntl_extra_image'] ) ? $_POST['crrntl_extra_image'] : '';

			/* Save data from terms meta fields to table 'termmeta' */
			crrntl_update_term_meta( $term_id, 'extra_details', $extra_details );
			crrntl_update_term_meta( $term_id, 'extra_price', $extra_price );
			crrntl_update_term_meta( $term_id, 'extra_quantity', $extra_quantity );
			crrntl_update_term_meta( $term_id, 'extra_image', $extra_image );
		}
	}
}

/**
 * Function for plugin activate
 */
if ( ! function_exists( 'crrntl_plugin_activate' ) ) {
	function crrntl_plugin_activate( $networkwide ) {
		global $wpdb;
		/* Activation function for network */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			/* check if it is a network activation - if so, run the activation function for each blog id */
			if ( $networkwide ) {
				$old_blog = get_current_blog_id();
				/* Get all blog ids */
				$blogids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					crrntl_plugin_activation();
				}
				switch_to_blog( $old_blog );

				return;
			}
		}
		crrntl_plugin_activation();
	}
}

/* Activation function for new blog in network */
if ( ! function_exists( 'crrntl_new_blog' ) ) {
	function crrntl_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		global $wpdb;
		if ( is_plugin_active_for_network( 'car-rental/car-rental.php' ) ) {
			$old_blog = $wpdb->blogid;
			switch_to_blog( $blog_id );
			crrntl_plugin_activation();
			switch_to_blog( $old_blog );
		}
	}
}

if ( ! function_exists( 'crrntl_plugin_activation' ) ) {
	function crrntl_plugin_activation() {
		/* Trigger our function that registers the custom post type */
		crrntl_setup_post_types();
		crrntl_install();
		crrntl_plugin_templates_install();
		crrntl_create_termmeta_table();

		/* Clear the permalinks after the post type has been registered */
		flush_rewrite_rules();
	}
}

if ( ! function_exists( 'crrntl_enqueue_scripts' ) ) {
	function crrntl_enqueue_scripts() {
		$locale = crrntl_get_locale_datepicker();
		wp_enqueue_style( 'crrntl-stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
		wp_enqueue_script( 'jquery-ui-datepicker-1', plugins_url( 'js/datepicker.js', __FILE__ ), array( 'jquery' ) );
		if ( ! empty( $locale ) ) {
			wp_enqueue_script( 'jquery-ui-datepicker-i18n', 'https://cdn.rawgit.com/jquery/jquery-ui/master/ui/i18n/datepicker-' . $locale . '.js', array( 'jquery', 'jquery-ui-datepicker-1' ) );
		}

		wp_enqueue_script( 'crrntl-script', plugins_url( 'js/script.js', __FILE__ ), array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-slider',
			'jquery-ui-datepicker-1',
		) );
		wp_enqueue_style( 'jquery-ui-css', plugins_url( 'css/jquery-ui.css', __FILE__ ) );
		wp_enqueue_style( 'crrntl-style-jquery-slider', plugins_url( 'css/jquery.slider.css', __FILE__ ) );
		$script_vars = array(
			'crrntl_choose_location' => __( 'Please, choose location', 'car-rental' ),
		);
		wp_localize_script( 'crrntl-script', 'crrntlScriptVars', $script_vars );
	}
}

if ( ! function_exists( 'crrntl_admin_enqueue_scripts' ) ) {
	function crrntl_admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'bws-plugins_page_car-rental-settings' == $screen->id || 'cars' == $screen->id || 'edit-cars' == $screen->id || 'edit-manufacturer' == $screen->id || 'edit-vehicle_type' == $screen->id || 'edit-car_class' == $screen->id || 'edit-extra' == $screen->id || 'toplevel_page_orders' == $screen->id ) {
			global $crrntl_options;
			if ( empty( $crrntl_options ) ) {
				$crrntl_options = get_option( 'crrntl_options' );
			}
			$locale = crrntl_get_locale_datepicker();
			wp_enqueue_style( 'crrntl-admin-stylesheet', plugins_url( 'css/admin-styles.css', __FILE__ ) );

			wp_enqueue_script( 'jquery-ui-datepicker-admin', plugins_url( 'js/datepicker.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_script( 'crrntl-admin-script', plugins_url( 'js/admin-script.js', __FILE__ ), array(
				'jquery',
				'jquery-ui-sortable',
				'jquery-ui-datepicker-admin',
			) );
			if ( isset( $_GET['page'] ) && 'orders' == $_GET['page'] && isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
				if ( ! empty( $locale ) ) {
					wp_enqueue_script( 'jquery-ui-datepicker-i18n-admin', 'https://cdn.rawgit.com/jquery/jquery-ui/master/ui/i18n/datepicker-' . $locale . '.js', array( 'jquery', 'jquery-ui-datepicker-admin' ) );
				}
				wp_enqueue_style( 'jquery-ui-css', plugins_url( 'css/jquery-ui.css', __FILE__ ) );
			}
			if ( 'cars' == $screen->post_type && 'cars' == $screen->id ) {
				$crrntl_api_key = ( ! empty( $crrntl_options['maps_key'] ) ) ? $crrntl_options['maps_key'] : '';
				$crrntl_api = sprintf( 'https://maps.googleapis.com/maps/api/js?v=3&key=%s&libraries=places&signed_in=true&callback=initMap&language=%s', $crrntl_api_key, $locale );
				wp_enqueue_script( 'crrntl-google-autocomplete', plugins_url( 'js/google-autocomplete.js', __FILE__ ) );
				wp_enqueue_script( 'crrntl-google-api', $crrntl_api, '', '', true );
			}

			wp_enqueue_media();

			$script_vars = array(
				'crrntl_delete_image'   => __( 'Delete', 'car-rental' ),
				'crrntl_add_new_status' => __( 'Enter new status', 'car-rental' ),
				'chooseFile'              => __( 'Choose file', 'car-rental' ),
				'notSelected'             => __( 'No file chosen', 'car-rental' ),
				'addImageLabel'           => __( 'Add image', 'car-rental' ),
				'changeImageLabel'        => __( 'Change image', 'car-rental' ),
				'errorInsertImage'        => __( 'Warning: You can add only image', 'car-rental' ),
			);
			wp_localize_script( 'crrntl-admin-script', 'crrntlScriptVars', $script_vars );

			if ( isset( $_GET['page'] ) && 'car-rental-settings' == $_GET['page'] && isset( $_GET['tab'] ) && 'custom_code' == $_GET['tab'] ) {
				bws_plugins_include_codemirror();
			}
		}
	}
}

/**
 * Get the locale according to the format available in the jquery ui i18n file list
 *
 * @return string ex: "fr" or "en-GB"
 */
if ( ! function_exists( 'crrntl_get_locale_datepicker' ) ) {
	function crrntl_get_locale_datepicker() {
		//replace _ by - in "en_GB" for example
		$locale = str_replace( '_', '-', get_locale() );
		switch ( $locale ) {
			case 'ar-DZ':
			case 'cy-GB':
			case 'en-AU':
			case 'en-GB':
			case 'en-NZ':
			case 'fr-CH':
			case 'nl-BE':
			case 'pt-BR':
			case 'sr-SR':
			case 'zh-CN':
			case 'zh-HK':
			case 'zh-TW':
				//For all this locale do nothing the file already exist
				break;
			default:
				//for other locale keep the first part of the locale (ex: "fr-FR" -> "fr")
				$length = ( strpos( $locale, '-' ) ) ? strpos( $locale, '-' ) : 2;
				$locale = substr( $locale, 0, $length );
				//English is the default locale
				$locale = ( 'en' == $locale ) ? '' : $locale;
				break;
		}
		return $locale;
	}
}

if ( ! function_exists( 'crrntl_body_class_names' ) ) {
	function crrntl_body_class_names( $classes ) {
		if ( is_page_template( 'page-homev1.php' ) ) {
			$classes[] = 'crrntl-left-slider';
		} elseif ( is_page_template( 'page-homev2.php' ) ) {
			$classes[] = 'crrntl-right-slider';
		} elseif ( is_page_template( 'page-homev3.php' ) || is_front_page() || is_home() ) {
			$classes[] = 'crrntl-center-slider';
		}
		if ( function_exists( 'wp_get_theme' ) ) {
			$current_theme = wp_get_theme();
			$classes[]     = 'crrntl_' . basename( $current_theme->get( 'ThemeURI' ) );
		}
		$classes[] = 'car-rental';

		return $classes;
	}
}

/* paginate */
if ( ! function_exists( 'crrntl_paginate' ) ) {
	function crrntl_paginate( $max_num_pages = 1 ) {
		if ( get_query_var( 'paged' ) ) {
			$current_page = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$current_page = get_query_var( 'page' );
		} else {
			$current_page = 1;
		}
		$big    = 999999; /* unique number for replacement */
		$args   = array(
			'base'      => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
			'format'    => '',
			'current'   => $current_page,
			'total'     => $max_num_pages,
			'show_all'  => false,
			'end_size'  => false,
			'mid_size'  => 1,
			'prev_next' => true,
			'prev_text' => '<img src="' . plugin_dir_url( __FILE__ ) . '/images/pagination-left.png" />',
			'next_text' => '<img src="' . plugin_dir_url( __FILE__ ) . '/images/pagination-right.png" />',
		);
		$result = paginate_links( $args );
		/* remove pagination first page */
		$result = str_replace( '/page/1/', '', $result );
		echo "<div class='crrntl-pagination'>" . $result . "<p class='clear'></p></div>";
	}
}

/**
 * Function for save reservation order
 */
if ( ! function_exists( 'crrntl_save_reservation' ) ) {
	function crrntl_save_reservation() {
		global $wpdb;

		$crrntl_order_info = array(
			'error'   => '',
			'success' => '',
		);

		if ( empty( $_SESSION['crrntl_return_location'] ) ) {
			$_SESSION['crrntl_return_location'] = ! empty( $_SESSION['crrntl_location'] ) ? $_SESSION['crrntl_location'] : '';
		}
		$create_order = $wpdb->insert(
			$wpdb->prefix . 'crrntl_orders',
			array(
				'car_id'         => $_SESSION['crrntl_selected_product_id'],
				'pickup_loc_id'  => $_SESSION['crrntl_location'],
				'dropoff_loc_id' => $_SESSION['crrntl_return_location'],
				'pickup_date'    => $_SESSION['crrntl_date_from'] . ' ' . $_SESSION['crrntl_time_from'],
				'dropoff_date'   => $_SESSION['crrntl_date_to'] . ' ' . $_SESSION['crrntl_time_to'],
				'user_id'        => $_SESSION['crrntl_user_id'],
				'total'          => $_SESSION['crrntl_total'],
				'status_id'      => 1,
			),
			array( '%d', '%d', '%d', '%s', '%s', '%d', '%f', '%d' )
		);
		if ( $create_order ) {
			$order_id = $wpdb->insert_id;

			if ( ! empty( $_SESSION['crrntl_opted_extras'] ) ) {
				foreach ( $_SESSION['crrntl_opted_extras'] as $opted_extra ) {
					if ( isset( $_SESSION['crrntl_extra_quantity'] ) && isset( $_SESSION['crrntl_extra_quantity'][ $opted_extra ] ) ) {
						$wpdb->insert(
							$wpdb->prefix . 'crrntl_extras_order',
							array(
								'order_id'       => $order_id,
								'extra_id'       => $opted_extra,
								'extra_quantity' => $_SESSION['crrntl_extra_quantity'][ $opted_extra ],
							),
							array( '%d', '%d', '%d' )
						);
					} else {
						$wpdb->insert(
							$wpdb->prefix . 'crrntl_extras_order',
							array(
								'order_id' => $order_id,
								'extra_id' => $opted_extra,
							),
							array( '%d', '%d' )
						);
					}
				}
			}

			$crrntl_order_info['success'] = __( 'Thank you for a booking. We will contact you for the order confirmation. Your Order', 'car-rental' ) . ' #' . $order_id;

			/* sending mail with order info */
			$crrntl_options = get_option( 'crrntl_options' );
			if ( empty( $crrntl_options['custom_currency'] ) || 0 == $crrntl_options['currency_custom_display'] ) {
				$crrntl_currency = $wpdb->get_var( $wpdb->prepare( "SELECT `currency_unicode` FROM `{$wpdb->prefix}crrntl_currency` WHERE `currency_id` = %d", $crrntl_options['currency_unicode'] ) );
				if ( empty( $crrntl_currency ) )
					$crrntl_currency = '&#36;';
			} else {
				$crrntl_currency = $crrntl_options['custom_currency'];
			}
			$crrntl_currency_position = $crrntl_options['currency_position'];
			if ( ! empty( $crrntl_currency_position ) ) {
				if ( 'before' == $crrntl_currency_position ) {
					$total_to_mail = $crrntl_currency . number_format_i18n( $_SESSION['crrntl_total'], 2 );
				} else {
					$total_to_mail = number_format_i18n( $_SESSION['crrntl_total'], 2 ) . ' ' . $crrntl_currency;
				}
			} else {
				$total_to_mail = number_format_i18n( $_SESSION['crrntl_total'], 2 );
			}

			$pickuploc_to_mail  = $wpdb->get_var( $wpdb->prepare( "SELECT `formatted_address` FROM `{$wpdb->prefix}crrntl_locations` WHERE `loc_id` = %s", $_SESSION['crrntl_location'] ) );
			$dropoffloc_to_mail = $wpdb->get_var( $wpdb->prepare( "SELECT `formatted_address` FROM `{$wpdb->prefix}crrntl_locations` WHERE `loc_id` = %s", $_SESSION['crrntl_return_location'] ) );

			$extras_to_mail_string = '';
			if ( ! empty( $_SESSION['crrntl_opted_extras'] ) ) {
				$extras_to_mail = array();
				foreach ( $_SESSION['crrntl_opted_extras'] as $one_opted_extra ) {
					$selected_extra = get_term( $one_opted_extra, 'extra' );
					if ( ! empty( $_SESSION['crrntl_extra_quantity'] ) && isset( $_SESSION['crrntl_extra_quantity'][ $one_opted_extra ] ) ) {
						$extras_to_mail[] = $selected_extra->name . ' &times; ' . $_SESSION['crrntl_extra_quantity'][ $one_opted_extra ];
					} else {
						$extras_to_mail[] = $selected_extra->name;
					}
				}
				$extras_to_mail_string = implode( ', ', $extras_to_mail );
			}
			if ( ! empty( $extras_to_mail_string ) ) {
				$extras_to_mail_string = __( 'Extras: ', 'car-rental' ) . $extras_to_mail_string . '<br />';
			}

			$admin_email = ( is_multisite() ) ? get_site_option( 'admin_email' ) : get_option( 'admin_email' );
			$user_mail   = get_userdata( $_SESSION['crrntl_user_id'] )->user_email;

			$subject = get_bloginfo( 'name' ) . ' | ' . __( 'New booking order #', 'car-rental' ) . $order_id . "\n";

			$message = '<p>' . $crrntl_order_info['success'] . '</p>
			<p><strong>' . __( 'Order Info:', 'car-rental' ) . '</strong><br />'
			           . __( 'Car: ', 'car-rental' ) . get_the_title( $_SESSION['crrntl_selected_product_id'] ) . '<br />'
			           . $extras_to_mail_string
			           . __( 'Pick-Up Date: ', 'car-rental' ) . $_SESSION['crrntl_date_from'] . ' ' . $_SESSION['crrntl_time_from'] . '<br />'
			           . __( 'Drop-Off Date: ', 'car-rental' ) . $_SESSION['crrntl_date_to'] . ' ' . $_SESSION['crrntl_time_to'] . '<br />'
			           . __( 'Pick-Up Location: ', 'car-rental' ) . $pickuploc_to_mail . '<br />'
			           . __( 'Drop-Off Location: ', 'car-rental' ) . $dropoffloc_to_mail . '<br />'
			           . __( 'Total: ', 'car-rental' ) . $total_to_mail . '</p>';

			$send_to = array(
				$admin_email,
				$user_mail,
			);

			$headers = 'From: ' . get_bloginfo( 'name' ) . ' <' . $admin_email . '>' . "\r\n";

			add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
			wp_mail( $send_to, $subject, $message, $headers );
			remove_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
		} else {
			$crrntl_order_info['error'] = __( 'An error occured during the creating order.', 'car-rental' );
		}

		return $crrntl_order_info;
	}
}

/**
 * Add action links on plugin page in to Plugin Description block
 *
 * @param   $links array() action links
 * @param   $file  string  relative path to plugin "car-rental/car-rental.php"
 *
 * @return  $links array() action links
 */
if ( ! function_exists( 'crrntl_register_plugin_links' ) ) {
	function crrntl_register_plugin_links( $links, $file ) {
		if ( 'car-rental/car-rental.php' == $file ) {
			if ( ! is_network_admin() ) {
				$links[] = '<a href="admin.php?page=car-rental-settings">' . __( 'Settings', 'car-rental' ) . '</a>';
			}
			$links[] = '<a href="http://bestwebsoft.com/products/car-rental/faq/" target="_blank">' . __( 'FAQ', 'car-rental' ) . '</a>';
			$links[] = '<a href="http://support.bestwebsoft.com">' . __( 'Support', 'car-rental' ) . '</a>';
		}

		return $links;
	}
}

/**
 * Add action links on plugin page in to Plugin Name block
 *
 * @param   $links array() action links
 * @param   $file  string  relative path to plugin "car-rental/car-rental.php"
 *
 * @return  $links array() action links
 */
if ( ! function_exists( 'crrntl_plugin_action_links' ) ) {
	function crrntl_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			if ( 'car-rental/car-rental.php' == $file ) {
				$settings_link = '<a href="admin.php?page=car-rental-settings">' . __( 'Settings', 'car-rental' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

/* add help tab  */
if ( ! function_exists( 'crrntl_add_tabs' ) ) {
	function crrntl_add_tabs() {
		$screen = get_current_screen();
		if ( ( ! empty( $screen->post_type ) && 'cars' == $screen->post_type ) || ( isset( $_GET['page'] ) && ( 'car-rental-settings' == $_GET['page'] || 'orders' == $_GET['page'] ) ) ) {
			$args = array(
				'id'      => 'crrntl',
				'section' => '201886976',
			);
			bws_help_tab( $screen, $args );
		}
	}
}

/**
 * Display notices
 * @return void
 */
if ( ! function_exists( 'crrntl_admin_notices' ) ) {
	function crrntl_admin_notices() {
		global $hook_suffix, $crrntl_plugin_info, $crrntl_options, $crrntl_car_notice, $crrntl_filenames, $crrntl_themepath, $wp_version;

		$post      = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : '';
		$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : get_post_type( $post );

		if ( 'cars' == $post_type || ( isset( $_REQUEST['page'] ) && 'car-rental-settings' == $_REQUEST['page'] ) ) {
			$file_exists_flag = true;
			foreach ( $crrntl_filenames as $filename ) {
				if ( ! file_exists( $crrntl_themepath . $filename ) ) {
					$file_exists_flag = false;
				}
			}
			if ( ! $file_exists_flag ) { ?>
				<div class="error"><p>
					<strong><?php printf( __( "The following files '%s', '%s' and '%s' were not found in the directory of your theme. Please copy them from the directory `%s` to the directory of your theme for the correct work of the Car Rental plugin", 'car-rental' ), 'page-choose-car.php', 'page-choose-extras.php', 'page-review-book.php', '/wp-content/plugins/car-rental/templates/' ); ?></strong>
				</p></div>
			<?php }
			if ( true == $crrntl_car_notice ) { ?>
				<div class="update-nag"><strong><?php printf( __( 'You have reached the limit for Cars. Create unlimited Cars and Extras with %s', 'car-rental' ), '<a href="http://bestwebsoft.com/products/car-rental/?k=664b00b8cd82b35c4f9b2a4838de35ff&pn=576&v=' . $crrntl_plugin_info["Version"] . '&wp_v=' . $wp_version . '" target="_blank">Car Rental Pro</a>' ); ?></strong></div>
			<?php }
		}

		if ( 'plugins.php' == $hook_suffix || ( isset( $_GET['page'] ) && 'car-rental-settings' == $_GET['page'] ) ) {

			if ( 'plugins.php' == $hook_suffix ) {
				if ( ! is_network_admin() ) {

					if ( empty( $crrntl_options ) )
						$crrntl_options = get_option( 'crrntl_options' );

					if ( isset( $crrntl_options['first_install'] ) && strtotime( '-1 week' ) > $crrntl_options['first_install'] )
						bws_plugin_banner( $crrntl_plugin_info, 'crrntl', 'car-rental', '693c590c4eab04459481ef6f1239ba20', '576', 'car-rental' );

					bws_plugin_banner_to_settings( $crrntl_plugin_info, 'crrntl_options', 'car-rental', 'admin.php?page=car-rental-settings', 'post-new.php?post_type=cars', __( 'Car', 'car-rental' ) );
				}
			} else {
				bws_plugin_suggest_feature_banner( $crrntl_plugin_info, 'crrntl_options', 'car-rental' );
			}
		}

		if ( empty( $crrntl_options ) )
			$crrntl_options = get_option( 'crrntl_options' );

		if ( isset( $_POST['crrntl_hide_theme_banner'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'crrntl_nonce_name' ) ) {
			$crrntl_options['theme_banner'] = 0;
			update_option( 'crrntl_options', $crrntl_options );
			return;
		}
		
		if ( 'Renty' != wp_get_theme() && isset( $crrntl_options['theme_banner'] ) && 1 == $crrntl_options['theme_banner'] ) { ?>
			<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
				<div class="bws_banner_on_plugin_page crrntl_theme_notice">
					<div class="text">
						<strong><?php printf( __( 'Your theme does not declare Car Rental plugin support. Please check out our %s theme which has been developed specifically for use with Car Rental plugin.', 'car-rental' ), '<a href="http://bestwebsoft.com/products/renty-car-rental-booking-wordpress-theme/" target="_blank">Renty</a>' ); ?></strong>
					</div>
					<form action="" method="post">
						<button class="notice-dismiss bws_hide_settings_notice" title="<?php _e( 'Close notice', 'bestwebsoft' ); ?>"></button>
						<input type="hidden" name="crrntl_hide_theme_banner" value="hide" />
						<?php wp_nonce_field( plugin_basename( __FILE__ ), 'crrntl_nonce_name' ); ?>
					</form>
				</div>
			</div>
		<?php }
	}
}

/**
 * Function for plugin deactivate
 */
if ( ! function_exists( 'crrntl_deactivation' ) ) {
	function crrntl_deactivation() {
		/* Our post type will be automatically removed, so no need to unregister it */

		/* Clear the permalinks to remove our post type's rules */
		flush_rewrite_rules();
	}
}

/**
 * Function for plugin uninstall
 */
/* Delete plugin blog */
if ( ! function_exists( 'crrntl_delete_blog' ) ) {
	function crrntl_delete_blog( $blog_id ) {
		global $wpdb;
		if ( is_plugin_active_for_network( 'car-rental/car-rental.php' ) ) {
			$old_blog = $wpdb->blogid;
			switch_to_blog( $blog_id );
			crrntl_plugin_delete();
			switch_to_blog( $old_blog );
		}
	}
}

if ( ! function_exists( 'crrntl_plugin_delete' ) ) {
	function crrntl_plugin_delete() {
		global $wpdb, $crrntl_filenames;
		/* Deactivating plugin if it is active */
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'car-rental/car-rental.php' ) ) {
			deactivate_plugins( 'car-rental/car-rental.php' );
		}

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
			'post_type'   => 'cars',
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

		/* Delete plugin's page template from the active theme */
		$crrntl_themepath = get_stylesheet_directory() . '/';

		foreach ( $crrntl_filenames as $filename ) {
			/* Delete templates */
			if ( file_exists( $crrntl_themepath . $filename ) && ! unlink( $crrntl_themepath . $filename ) ) {
				add_action( 'admin_notices', create_function( '', ' return __( "Error delete template file", "car-rental" );' ) );
			}
			if ( file_exists( $crrntl_themepath . $filename . '.bak' ) && ! unlink( $crrntl_themepath . $filename . '.bak' ) ) {
				add_action( 'admin_notices', create_function( '', ' return __( "Error delete template file", "car-rental" );' ) );
			}
		}
		/* Delete plugin's options */
		delete_option( 'crrntl_demo_options' );
		delete_option( 'crrntl_options' );
		delete_option( 'crrntl_slider_options' );
	}
}

if ( ! function_exists( 'crrntl_check_date_format' ) ) {
	function crrntl_check_date_format( $date ) {
		$pattern = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/';
		if ( preg_match( $pattern, $date, $date_part ) ) {
			return checkdate( $date_part[2], $date_part[3], $date_part[1] );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'crrntl_check_args_filter' ) ) {
	function crrntl_check_args_filter( $args, $type ) {
		global $crrntl_options, $wpdb, $crrntl_car_notice;
		if ( empty( $crrntl_options ) )
			$crrntl_options = get_option( 'crrntl_options' );

		if ( ! isset( $crrntl_options['cflag'] ) || $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE `post_type` = %s", $type ) ) >= base_convert( $crrntl_options['cflag'], 16, 10 ) ) {
			$args['capabilities'] = array( 'create_posts' => 'do_not_allow' );
			$args['map_meta_cap'] = true;
			$crrntl_car_notice = true;
		}
		return $args;
	}
}		

if ( ! function_exists( 'crrntl_check_term' ) ) {
	function crrntl_check_term( $term, $taxonomy ) {
		global $crrntl_options, $wpdb;
		if ( $taxonomy == 'extra' ) {			
			if ( empty( $crrntl_options ) )
				$crrntl_options = get_option( 'crrntl_options' );

			if ( ! isset( $crrntl_options['eflag'] ) || $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE `taxonomy` = %s", $taxonomy ) ) >= base_convert( $crrntl_options['eflag'], 16, 10 ) ) {
				return new WP_Error( 'extra_term_error', __( 'You have reached the limit for Extras.', 'car-rental' ) );
			}		
		}
		return $term;
	}
}

/* All hooks */
register_activation_hook( __FILE__, 'crrntl_plugin_activate' );
add_action( 'wpmu_new_blog', 'crrntl_new_blog', 10, 6 );
add_action( 'delete_blog', 'crrntl_delete_blog', 10 );
add_action( 'plugins_loaded', 'crrntl_plugin_loaded' );
add_action( 'init', 'crrntl_init' );
add_action( 'admin_init', 'crrntl_admin_init' );
add_action( 'widgets_init', 'crrntl_widget_init' );
add_action( 'admin_head', 'crrntl_add_tabs' );
add_action( 'admin_menu', 'crrntl_admin_menu' );
add_filter( 'set-screen-option', 'crrntl_table_set_option', 10, 3 );

/* Add meta fields to custom post */
add_action( 'add_meta_boxes', 'crrntl_add_custom_box' );
add_action( 'save_post', 'crrntl_save_postdata' );

/* Add meta fields to custom taxonomies */
add_action( 'extra_add_form_fields', 'crrntl_extra_add_form_fields' );
add_action( 'extra_edit_form_fields', 'crrntl_extra_edit_form_fields' );
add_action( 'create_extra', 'crrntl_save_extra' );
add_action( 'edit_extra', 'crrntl_save_extra' );

/* Add template into the theme after activate new theme */
add_action( 'after_switch_theme', 'crrntl_after_switch_theme', 10, 2 );

add_action( 'wp_enqueue_scripts', 'crrntl_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'crrntl_admin_enqueue_scripts' );
add_filter( 'body_class', 'crrntl_body_class_names' );

add_action( 'crrntl_paginate', 'crrntl_paginate' );

add_filter( 'crrntl_args_filter', 'crrntl_check_args_filter', 10, 2 );
add_filter( 'pre_insert_term', 'crrntl_check_term', 10, 2 );

/* Additional links on the plugin page */
add_filter( 'plugin_row_meta', 'crrntl_register_plugin_links', 10, 2 );
add_filter( 'plugin_action_links', 'crrntl_plugin_action_links', 10, 2 );

add_action( 'admin_notices', 'crrntl_admin_notices' );

/* Deactivate plugin */
register_deactivation_hook( __FILE__, 'crrntl_deactivation' );