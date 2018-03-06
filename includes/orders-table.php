<?php
/**
 * Display Table of Orders via WP_List_Table
 *
 * @subpackage Car Rental
 * @since      Car Rental 1.0.0
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
if ( ! class_exists( 'Crrntl_List_Table' ) ) {
	class Crrntl_List_Table extends WP_List_Table {

		/* conctructor */
		function __construct() {
			parent::__construct( array(
				'singular'	=> __( 'order', 'car-rental' ), /* singular name of the listed records */
				'plural'	=> __( 'orders', 'car-rental' ), /* plural name of the listed records */
				'ajax'		=> true, /* does this table support ajax? */
			) );
		}

		function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'order_id':
				case 'car_id':
				case 'extras':
				case 'pickup_loc_id':
				case 'dropoff_loc_id':
				case 'pickup_date':
				case 'dropoff_date':
				case 'status_id':
					return $item[ $column_name ];
				case 'total':
					return ( NULL == $item[ $column_name ] ) ? __( 'On request', 'car-rental' ) : $item[ $column_name ];
				case 'user_id':
					$userdata = get_userdata( $item['user_id'] );
					$phone = isset( $userdata->user_phone ) ? '<br/>' . $userdata->user_phone : '';
					$email = isset( $userdata->user_email ) ? '<br/>' . $userdata->user_email : '';
					return $item['user_name'] . $email . $phone;
				default:
					return print_r( $item, true ); /* Show the whole array for troubleshooting purposes */
			}
		}

		/* function for columns */
		function get_columns() {
			$columns = array(
				'cb'             => '<input type="checkbox" />',
				'order_id'       => __( 'Order ID', 'car-rental' ),
				'car_id'         => __( 'Car', 'car-rental' ),
				'extras'         => __( 'Extras', 'car-rental' ),
				'pickup_loc_id'  => __( 'Location from', 'car-rental' ),
				'dropoff_loc_id' => __( 'Location to', 'car-rental' ),
				'pickup_date'    => __( 'Date from', 'car-rental' ),
				'dropoff_date'   => __( 'Date to', 'car-rental' ),
				'user_id'        => __( 'Client', 'car-rental' ),
				'total'          => __( 'Total', 'car-rental' ),
				'status_id'      => __( 'Status', 'car-rental' ),
			);
			return $columns;
		}

		/* function for column cb */
		function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="crrntl_order_id[]" value="%s" />', $item['order_id'] );
		}

		function column_order_id( $item ) {
			$actions = array(
				'edit' => '<a href="' . esc_url( wp_nonce_url( '?page=orders&action=edit&crrntl_order_id=' . $item['order_id'], 'crrntl-action' ) ) . '">' . __( 'Edit', 'car-rental' ) . '</a>',
				'delete' => '<a href="' . esc_url( wp_nonce_url( '?page=orders&action=delete&crrntl_order_id=' . $item['order_id'], 'crrntl-action' ) ) . '">' . __( 'Delete', 'car-rental' ) . '</a>',
			);
			return sprintf( '%1$s %2$s', $item['order_id'], $this->row_actions( $actions ) );
		}

		/* function for bulk actions */
		function get_bulk_actions() {
			$actions = array(
				'delete' => __( 'Delete', 'car-rental' ),
			);
			return $actions;
		}

		function get_sortable_columns() {
			$sortable_columns = array(
				'order_id'     => array(
					'order_id',
					false,
				),
				'car_id'   => array(
					'car_id',
					false,
				),
				'pickup_loc_id' => array(
					'pickup_loc_id',
					false,
				),
				'dropoff_loc_id' => array(
					'dropoff_loc_id',
					false,
				),
				'pickup_date' => array(
					'pickup_date',
					false,
				),
				'dropoff_date' => array(
					'dropoff_date',
					false,
				),
				'user_id' => array(
					'user_id',
					false,
				),
				'total' => array(
					'total',
					false,
				),
				'status_id' => array(
					'status_id',
					false,
				)
			);
			return $sortable_columns;
		}

		function single_row( $item ) {
			static $crrntl_count_row = 1;
			$crrntl_classes = '';
			if ( ( $crrntl_count_row % 2 ) ) {
				$crrntl_classes = 'alternate';
			}
			$row_class = ' class="' . $crrntl_classes . '"';
			echo '<tr' . $row_class . '>';
			$this->single_row_columns( $item );
			echo '</tr>';
			$crrntl_count_row ++;
		}

		function extra_tablenav( $which ) {
			global $wpdb;
			if ( 'top' == $which ) {
				$cars = $wpdb->get_results( "SELECT DISTINCT
						ro.car_id  AS car_id,
						post_title AS car_name
					FROM {$wpdb->prefix}crrntl_orders AS ro
						LEFT JOIN {$wpdb->prefix}posts AS po ON po.ID = ro.car_id
					ORDER BY car_name ASC", ARRAY_A );

				$users = $wpdb->get_results( "SELECT DISTINCT
						ro.user_id AS user_id,
						CONCAT(um1.meta_value, ' ', um2.meta_value ) AS user_name
					FROM {$wpdb->prefix}crrntl_orders AS ro
						LEFT JOIN {$wpdb->usermeta} AS um1 ON (um1.user_id = ro.user_id AND um1.meta_key = 'first_name')
						LEFT JOIN {$wpdb->usermeta} AS um2 ON (um2.user_id = ro.user_id AND um2.meta_key = 'last_name')
					ORDER BY user_name ASC", ARRAY_A );

				$statuses = $wpdb->get_results( "SELECT DISTINCT
						ro.status_id  AS status_id,
						rs.status_name AS status_name
					FROM {$wpdb->prefix}crrntl_orders AS ro
						LEFT JOIN {$wpdb->prefix}crrntl_statuses AS rs ON rs.status_id = ro.status_id
					ORDER BY status_name ASC", ARRAY_A ); ?>
				<div class="alignleft actions">
					<?php if ( $cars ) { ?>
						<select name="crrntl_car_filter" class="crrntl-filter-car">
							<option value="0"><?php _e( 'All Cars', 'car-rental' ); ?></option>
							<?php foreach ( $cars as $car ) { ?>
									<option value="<?php echo $car['car_id']; ?>" <?php selected( isset( $_GET['crrntl_car_filter'] ) && $_GET['crrntl_car_filter'] == $car['car_id'] ); ?>><?php echo $car['car_name'] ?></option>
							<?php } ?>
						</select>
					<?php }
					if ( $users ) { ?>
						<select name="crrntl_user_filter" class="crrntl-filter-user">
							<option value="0"><?php _e( 'All Customers', 'car-rental' ); ?></option>
							<?php foreach ( $users as $user ) { ?>
								<option value="<?php echo $user['user_id']; ?>" <?php selected( isset( $_GET['crrntl_user_filter'] ) && $_GET['crrntl_user_filter'] == $user['user_id'] ); ?>><?php echo $user['user_name'] ?></option>
							<?php } ?>
						</select>
					<?php }
					if ( $statuses ) { ?>
						<select name="crrntl_status_filter" class="crrntl-filter-status">
							<option value="0"><?php _e( 'All Statuses', 'car-rental' ); ?></option>
							<?php foreach ( $statuses as $status ) { ?>
								<option value="<?php echo $status['status_id']; ?>" <?php selected( isset( $_GET['crrntl_status_filter'] ) && $_GET['crrntl_status_filter'] == $status['status_id'] ); ?>><?php echo $status['status_name'] ?></option>
							<?php } ?>
						</select>
					<?php } ?>
					<input id="crrntl-filter-submit" class="button" value="<?php _e( 'Filter', 'car-rental' ); ?>" type="submit" name="crrntl_filter_action" />
				</div>
			<?php }
		}

		function display_tablenav( $which ) {
			if ( 'top' === $which ) {
				wp_nonce_field( 'crrntl-action', '_wpnonce', 0 );
			} ?>
			<div class="tablenav <?php echo esc_attr( $which ); ?>">
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
				<?php $this->extra_tablenav( $which );
				$this->pagination( $which ); ?>
			</div>
		<?php }

		/* function for prepairing items */
		function prepare_items() {
			global $wpdb;
			$this->_column_headers = $this->get_column_info();
			$action                = $this->current_action();
			$per_page              = $this->get_items_per_page( 'orders_per_page', 10 );
			$current_page          = $this->get_pagenum();
			$total_items           = intval( $wpdb->get_var( "SELECT COUNT(order_id) FROM {$wpdb->prefix}crrntl_orders" ) );
			if ( $total_items > 0 ) {
				$this->set_pagination_args( array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				) );
				$from_row              = ( $current_page - 1 ) * $per_page;
				$this->items           = crrntl_table_data( $from_row, $per_page );
			}
		}
	}
}

if ( ! function_exists( 'crrntl_table_data' ) ) {
	function crrntl_table_data( $from_row, $per_page ) {
		global $wpdb;
		$order_by = ( ! empty( $_GET['orderby'] ) ) ? esc_sql( $_GET['orderby'] ) : 'order_id';
		$order    = ( ! empty( $_GET['order'] ) ) ? esc_sql( $_GET['order'] ) : 'DESC';

		if ( ! empty( $_GET['s'] ) || ! empty( $_GET['crrntl_car_filter'] ) || ! empty( $_GET['crrntl_user_filter'] ) || ! empty( $_GET['crrntl_status_filter'] ) ) {
			$search_order = 'WHERE ';
		} else {
			$search_order = '';
		}
		$search_orders = array();
		if ( ! empty( $_GET['s'] ) )
			$search_orders[] = 'order_id = ' . intval( $_GET['s'] );
		if ( ! empty( $_GET['crrntl_car_filter'] ) )
			$search_orders[] = 'car_id = ' . intval( $_GET['crrntl_car_filter'] );
		if ( ! empty( $_GET['crrntl_user_filter'] ) )
			$search_orders[] = 'user_id = ' . intval( $_GET['crrntl_user_filter'] );
		if ( ! empty( $_GET['crrntl_status_filter'] ) )
			$search_orders[] = 'status_id = ' . intval( $_GET['crrntl_status_filter'] );
		$search_order .= implode( ' AND ', $search_orders );

		$crrntl_orders_ids = $wpdb->get_col( "SELECT `order_id`
			FROM `{$wpdb->prefix}crrntl_orders` {$search_order}
			ORDER BY {$order_by} {$order}
			LIMIT {$from_row}, {$per_page}" );
		$crrntl_orders_data = array();
		if ( ! empty( $crrntl_orders_ids ) ) {
			$crrntl_orders_ids = implode( ', ', $crrntl_orders_ids );
			$separator = ',<br />';
			$crrntl_orders_data = $wpdb->get_results( "SELECT
				ro.order_id,
				po.post_title AS car_id,
				xtr.opted_xtr AS extras,
				rl1.formatted_address AS pickup_loc_id,
				rl2.formatted_address AS dropoff_loc_id,
				ro.pickup_date,
				ro.dropoff_date,
				ro.user_id,
				CONCAT(um1.meta_value, ' ', um2.meta_value ) AS user_name,
				ro.total,
				rs.status_name AS status_id
				FROM {$wpdb->prefix}crrntl_orders AS ro
					LEFT JOIN {$wpdb->prefix}posts AS po ON po.ID = ro.car_id
					LEFT JOIN ( SELECT GROUP_CONCAT(CONCAT(term.name, ' &times; ', reo.extra_quantity) SEPARATOR '{$separator}' ) AS opted_xtr, reo.order_id AS xtr_order
							 FROM {$wpdb->prefix}terms AS term,
								 {$wpdb->prefix}crrntl_extras_order AS reo
							 WHERE term.term_id = reo.extra_id
							 GROUP BY xtr_order) AS xtr ON xtr.xtr_order = ro.order_id
					LEFT JOIN {$wpdb->prefix}crrntl_locations AS rl1 ON rl1.loc_id = ro.pickup_loc_id
					LEFT JOIN {$wpdb->prefix}crrntl_locations AS rl2 ON rl2.loc_id = ro.dropoff_loc_id
					LEFT JOIN {$wpdb->usermeta} AS um1 ON (um1.user_id = ro.user_id AND um1.meta_key = 'first_name')
					LEFT JOIN {$wpdb->usermeta} AS um2 ON (um2.user_id = ro.user_id AND um2.meta_key = 'last_name')
					LEFT JOIN {$wpdb->prefix}crrntl_statuses AS rs ON rs.status_id = ro.status_id
				WHERE ro.order_id IN ( {$crrntl_orders_ids} )
				ORDER BY {$order_by} {$order}", ARRAY_A );
		}
		return $crrntl_orders_data;
	}
}

if ( ! function_exists( 'crrntl_add_menu_items' ) ) {
	function crrntl_add_menu_items() {
		$hook = add_menu_page(
			__( 'Manage orders page', 'car-rental' ), /* $page_title */
			__( 'Orders', 'car-rental' ), /* $menu_title */
			'activate_plugins', /* $capability */
			'orders', /* $menu_slug */
			'crrntl_orders_list_page', /* $callable_function */
			'', /* $icon_url */
			'58.1' /* $position */
		);
		add_action( "load-$hook", 'crrntl_add_options' );
	}
}

if ( ! function_exists( 'crrntl_add_options' ) ) {
	function crrntl_add_options() {
		global $crrntl_orders_table;
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Orders', 'car-rental' ),
			'default' => 10,
			'option'  => 'orders_per_page',
		);
		add_screen_option( $option, $args );
		$crrntl_orders_table = new Crrntl_List_Table();
	}
}

if ( ! function_exists( 'crrntl_table_set_option' ) ) {
	function crrntl_table_set_option( $status, $option, $value ) {
		return $value;
	}
}

/* function for actions part on table of links tab */
if ( ! function_exists( 'crrntl_actions' ) ) {
	function crrntl_actions( $crrntl_action ) {
		global $wpdb;
		switch ( $crrntl_action ) {
			case 'delete':
				if ( isset( $_GET['crrntl_order_id'] ) && is_array( $_GET['crrntl_order_id'] ) ) {
					$orders_to_delete = esc_sql( implode( ', ', $_GET['crrntl_order_id'] ) );
					$result1 = $wpdb->query( "DELETE FROM `{$wpdb->prefix}crrntl_orders` WHERE `order_id` IN ( {$orders_to_delete} )" );
					$result2 = $wpdb->query( "DELETE FROM `{$wpdb->prefix}crrntl_extras_order` WHERE `order_id` IN ( {$orders_to_delete} )" );
				} else {
					$result1 = $wpdb->delete( $wpdb->prefix . 'crrntl_orders',
						array( 'order_id' => $_GET['crrntl_order_id'] ),
						array( '%d' )
					);
					$result1 = $wpdb->delete( $wpdb->prefix . 'crrntl_extras_order',
						array( 'order_id' => $_GET['crrntl_order_id'] ),
						array( '%d' )
					);
				}
				if ( ( isset( $result1 ) && false === $result1 ) || ( isset( $result2 ) && false === $result2 ) ) {
					$message_value['error'] = __( 'An error occurred during the order deletion.', 'car-rental' );
				} else {
					$message_value['success'] = __( 'The orders have been deleted.', 'car-rental' );
				}
				return $message_value;
				break;
		}
	}
}

if ( ! function_exists( 'crrntl_orders_list_page' ) ) {
	function crrntl_orders_list_page() {
		global $crrntl_orders_table;
		/* Actions for table */
		if (
			! isset( $_GET['crrntl_filter_action'] ) &&
			(
				( isset( $_GET['action'] ) && -1 != $_GET['action'] ) ||
				( isset( $_GET['action2'] ) && -1 != $_GET['action2'] )
			) &&
			! empty( $_GET['crrntl_order_id'] ) &&
			wp_verify_nonce( $_GET['_wpnonce'], 'crrntl-action' )
		) {
			if ( isset( $_GET['action'] ) && -1 != $_GET['action'] ) {
				$message_value = crrntl_actions( $_GET['action'] );
			} elseif ( isset( $_GET['action2'] ) && -1 != $_GET['action2'] ) {
				$message_value = crrntl_actions( $_GET['action2'] );
			}
		}

		if ( isset( $_GET['page'] ) && 'orders' == $_GET['page'] && ( ! isset( $_GET['action'] ) || 'edit' != $_GET['action'] ) ) {
			$crrntl_orders_table = new Crrntl_List_Table();
			$crrntl_orders_table->prepare_items();
			echo '<div class="wrap"><h1>' . __( 'Manage orders page', 'car-rental' ) . '</h1>';
			if ( ! empty( $message_value['error'] ) ) { ?>
				<div class="error fade below-h2">
					<p><strong><?php echo $message_value['error']; ?></strong></p>
				</div>
			<?php }
			if ( ! empty( $message_value['success'] ) ) { ?>
				<div id="crrntl-settings-message" class="updated fade below-h2">
					<p><strong><?php echo $message_value['success']; ?></strong></p>
				</div>
			<?php } ?>
			<form method="get">
				<input type="hidden" name="page" value="orders" />
				<?php $crrntl_orders_table->search_box( __( 'Search', 'car-rental' ), 'order_id' );
				$crrntl_orders_table->display();
				echo '</form></div>';
		} elseif ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] && wp_verify_nonce( $_GET['_wpnonce'], 'crrntl-action' ) ) {
			crrntl_order_edit_page();
		}
	}
}

/**
 * Edit orders data and save changes
 */
if ( ! function_exists( 'crrntl_order_edit_page' ) ) {
	function crrntl_order_edit_page() {
		global $wpdb, $crrntl_options;
		/* Save orders changes */
		if ( ! empty( $_POST['crrntl_save_orders_changes'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'crrntl_nonce_name' ) ) {
			$extras_update_result = '';
			/* todo: rewrite code to make car change available. Uncomment the code below and provide ajax extras load on car change.
			$new_car_id   = intval( $_POST['crrntl_car_id'] );
			$new_price    = get_post_meta( $new_car_id, 'car_price', true );
			$new_loc      = intval( $_POST['crrntl_pickup_loc'] );
			$crrntl_total = ( $new_price * $diff_time );
			*/
			$date_from   = strtotime( $_POST['crrntl_date_from'] . ' ' . $_POST['crrntl_time_from'] );
			$date_to     = strtotime( $_POST['crrntl_date_to'] . ' ' . $_POST['crrntl_time_to'] );
			$diff_time = ( 'hour' == $crrntl_options['rent_per'] ) ? ceil( ( $date_to - $date_from ) / HOUR_IN_SECONDS ) : ceil( ( $date_to - $date_from ) / DAY_IN_SECONDS );
			/* Save changes to orders table */
			if ( 'on_request' == $_POST['crrntl_car_price'] ) {
				$order_update_result = $wpdb->update( $wpdb->prefix . 'crrntl_orders',
					array(
						/*
						'car_id'         => $new_car_id,
						'pickup_loc_id'  => $new_loc,
						*/
						'dropoff_loc_id' => $_POST['crrntl_return_loc_id'],
						'pickup_date'    => $_POST['crrntl_date_from'] . ' ' . $_POST['crrntl_time_from'],
						'dropoff_date'   => $_POST['crrntl_date_to'] . ' ' . $_POST['crrntl_time_to'],
						'status_id'      => $_POST['crrntl_order_status_id']
					),
					array( 'order_id' => $_GET['crrntl_order_id'] ),
					array(
						/* '%d', '%d', '%d', '%s', '%s', '%f', '%d' */
						'%d', '%s', '%s', '%d' /* @todo: replace with the line above */
					),
					array( '%d' )
				);
			} else {
				$crrntl_total = ( $_POST['crrntl_car_price'] * $diff_time ); /* to remove */
				if ( ! empty( $_POST['crrntl_opted_extras'] ) ) {
					foreach ( $_POST['crrntl_opted_extras'] as $extra_id ) {
						$extra_quantity = isset( $_POST['crrntl_extra_quantity'][ $extra_id ] ) ? $_POST['crrntl_extra_quantity'][ $extra_id ] : 1;
						$crrntl_total = $crrntl_total + ( $_POST[ 'crrntl_price_extra_' . $extra_id ] * $extra_quantity * $diff_time );
					}
				}
				$order_update_result = $wpdb->update( $wpdb->prefix . 'crrntl_orders',
					array(
						/*
						'car_id'         => $new_car_id,
						'pickup_loc_id'  => $new_loc,
						*/
						'dropoff_loc_id' => $_POST['crrntl_return_loc_id'],
						'pickup_date'    => $_POST['crrntl_date_from'] . ' ' . $_POST['crrntl_time_from'],
						'dropoff_date'   => $_POST['crrntl_date_to'] . ' ' . $_POST['crrntl_time_to'],
						'total'          => $crrntl_total,
						'status_id'      => $_POST['crrntl_order_status_id']
					),
					array( 'order_id' => $_GET['crrntl_order_id'] ),
					array(
						/* '%d', '%d', '%d', '%s', '%s', '%f', '%d' */
						'%d', '%s', '%s', '%f', '%d' /* @todo: replace with the line above */
					),
					array( '%d' )
				);
			}
			/* Save changes to extras order table */
			if ( ! empty( $_POST['crrntl_opted_extras'] ) ) {
				$get_extra_orders = $wpdb->get_results( $wpdb->prepare( "SELECT `extra_id`
					FROM `{$wpdb->prefix}crrntl_extras_order`
					WHERE `order_id` = %d", $_GET['crrntl_order_id'] ), ARRAY_A );
				foreach ( $get_extra_orders as $one_extra_order ) {
					if ( is_array( $_POST['crrntl_opted_extras'] ) && ! in_array( $one_extra_order['extra_id'], $_POST['crrntl_opted_extras'] ) ) {
						$extras_update_result = $wpdb->delete( $wpdb->prefix . 'crrntl_extras_order',
							array( 'order_id' => $_GET['crrntl_order_id'], 'extra_id' => $one_extra_order['extra_id'] ),
							array( '%d' )
						);
					}
				}

				foreach ( $_POST['crrntl_opted_extras'] as $extra_id ) {
					$extra_quantity = isset( $_POST['crrntl_extra_quantity'][ $extra_id ] ) ? $_POST['crrntl_extra_quantity'][ $extra_id ] : 1;

					$get_extra_order_id = $wpdb->get_var( $wpdb->prepare( "SELECT `id` FROM `{$wpdb->prefix}crrntl_extras_order` WHERE `order_id` = %d AND `extra_id` = %d", $_GET['crrntl_order_id'], $extra_id ) );

					if ( ! empty( $get_extra_order_id ) ) {
						$extras_update_result = $wpdb->update( $wpdb->prefix . 'crrntl_extras_order',
							array(
								'order_id'       => $_GET['crrntl_order_id'],
								'extra_id'       => $extra_id,
								'extra_quantity' => $extra_quantity,
							),
							array( 'id' => $get_extra_order_id ),
							array( '%d', '%d', '%d' ),
							array( '%d' )
						);
					} else {
						$extras_update_result = $wpdb->insert( $wpdb->prefix . 'crrntl_extras_order',
							array(
								'order_id'       => $_GET['crrntl_order_id'],
								'extra_id'       => $extra_id,
								'extra_quantity' => $extra_quantity,
							),
							array( '%d', '%d', '%d' )
						);
					}
				}
			} else {
				$extras_update_result = $wpdb->delete( $wpdb->prefix . 'crrntl_extras_order',
					array( 'order_id' => $_GET['crrntl_order_id'] ),
					array( '%d' )
				);
			}
			if ( false === $order_update_result || false === $extras_update_result ) {
				$error_message = __( 'An error occurred during the order saving.', 'car-rental' );
			} else {
				$crrntl_message = __( 'All changes have been saved', 'car-rental' );
			}
		}
		$crrntl_order_data  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}crrntl_orders WHERE `order_id` = %d", $_GET['crrntl_order_id'] ), ARRAY_A );
		$crrntl_opted_extras = $wpdb->get_results( $wpdb->prepare( "SELECT extra_id, extra_quantity FROM {$wpdb->prefix}crrntl_extras_order WHERE `order_id` = %d", $_GET['crrntl_order_id'] ), OBJECT_K );
		$crrntl_statuses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}crrntl_statuses", OBJECT_K );
		$crrntl_locations = $wpdb->get_results( "SELECT loc_id, formatted_address FROM {$wpdb->prefix}crrntl_locations", OBJECT_K );

		$userinfo = get_userdata( $crrntl_order_data['user_id'] );
		if ( $userinfo  ) {
			$user_name = "{$userinfo->first_name} {$userinfo->last_name}";
			$user_email = $userinfo->user_email;
			$user_phone = $userinfo->user_phone;
		}
		/*
		$cars = get_posts( array( 'per_page' => -1, 'post_type' => $crrntl_options['post_type_name'], '' ) );
		*/

		$extras = get_the_terms( $crrntl_order_data['car_id'], 'extra' );
		$pickup_date = explode( ' ', $crrntl_order_data['pickup_date'] );
		$dropoff_date = explode( ' ', $crrntl_order_data['dropoff_date'] );
		$car_price = get_post_meta( $crrntl_order_data['car_id'], 'car_price', true ); /* to remove */ ?>
		<div class="wrap">
			<h1><?php echo __( 'Edit order', 'car-rental' ) . ' #' . $_GET['crrntl_order_id']; ?></h1>
			<p><a href="admin.php?page=orders">&larr; <?php _e( 'Return to Manage orders page', 'car-rental' ); ?></a></p>
			<?php if ( ! empty( $error_message ) ) { ?>
				<div class="error fade below-h2"><p><strong><?php echo $error_message; ?></strong></p></div>
			<?php }
			if ( ! empty( $crrntl_message ) ) { ?>
				<div id="crrntl-settings-message" class="updated fade below-h2">
					<p><strong><?php echo $crrntl_message; ?></strong></p>
				</div>
			<?php } ?>
			<form method="post" action="#">
				<table class="form-table crrntl-edit-order">
					<tbody>
						<?php if ( $userinfo ) { ?>
							<tr>
								<th><?php _e( 'Client', 'car-rental' ); ?></th>
								<td>
									<?php echo "<p>{$user_name}</p>
												<p>{$user_email}</p>
												<p>{$user_phone}</p>"; ?>
								</td>
							</tr>
						<?php } else { ?>
							<tr>
								<th><?php _e( 'Client', 'car-rental' ); ?></th>
								<td><?php _e( 'This user doesn\'t exist', 'car-rental' ); ?></td>
							</tr>
						<?php } ?>
						<tr>
							<th><?php _e( 'Car', 'car-rental' ); ?></th>
							<td>
								<?php /*
								<select name="crrntl_car_id">
									<?php foreach ( $cars as $car ) {
										$car_id = $car->ID;
										printf(
											'<option value="%1$s" %2$s>%3$s</option>',
											$car_id,
											selected( $car_id == $crrntl_order_data['car_id'], true, false ),
											( ( $car_id == $crrntl_order_data['car_id'] ) ? '* ' : '' ) . get_the_title( $car_id )
										);
									} ?>
								</select>
								*/
								echo get_the_title( $crrntl_order_data['car_id'] ) . ' (<a href="' . get_edit_post_link( $crrntl_order_data['car_id'] ) . '">' . __( 'Edit Car', 'car-rental' ) . '</a>)'; ?>
								<input type="hidden" name="crrntl_car_price" value="<?php echo $car_price; ?>" /><?php /* to remove */ ?>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Extras', 'car-rental' ); ?>:</th>
							<td>
								<?php if ( ! empty( $extras ) ) { ?>
									<fieldset>
										<table class="crrntl-table-extras">
											<tbody>
												<?php foreach ( $extras as $extra ) {
													$extra_metadata = crrntl_get_term_meta( $extra->term_id, '', true );
													$crrntl_extra_price = $extra_metadata['extra_price'][0]; ?>
													<tr class="crrntl-extra-item">
														<td>
															<input id="crrntl-extra-<?php echo $extra->term_id; ?>" type="checkbox" name="crrntl_opted_extras[]" value="<?php echo $extra->term_id; ?>" <?php checked( isset( $crrntl_opted_extras[ $extra->term_id ] ) ); ?> />
														</td>
														<td>
															<label for="crrntl-extra-<?php echo $extra->term_id; ?>"><?php echo $extra->name; ?></label>
															<input type="hidden" name="crrntl_price_extra_<?php echo $extra->term_id; ?>" value="<?php echo $crrntl_extra_price; ?>" />
														</td>
														<td class="crrntl-extra-quantity">
															<?php if ( '1' == $extra_metadata['extra_quantity'][0] ) { ?>
																<input class="crrntl-product-quantity" name="crrntl_extra_quantity[<?php echo $extra->term_id; ?>]" type="number" min="1" value="<?php echo isset( $crrntl_opted_extras[ $extra->term_id ]->extra_quantity ) ? $crrntl_opted_extras[ $extra->term_id ]->extra_quantity : 1; ?>" title="<?php _e( 'Choose Quantity', 'car-rental' ); ?>" />
																<span> <?php _e( 'pcs.', 'car-rental' ); ?></span>
															<?php } ?>
														</td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</fieldset>
								<?php } else {
									_e( 'There are no available extras for this car.', 'car-rental' );
								} ?>
							</td>
						</tr>
						<tr>
							<th>
								<!-- <label for="crrntl-pickup-loc"> -->
									<?php _e( 'Pick Up Location', 'car-rental' ); ?>
								<!-- </label> -->
							</th>
							<td>
								<?php /* if ( isset( $crrntl_locations[ $crrntl_order_data['pickup_loc_id'] ] ) ) { */ ?>
								<?php if ( isset( $crrntl_locations[ $crrntl_order_data['pickup_loc_id'] ]->formatted_address ) ) { /* to remove */ ?>
									<!-- <select name="crrntl_pickup_loc" id="crrntl-pickup-loc">
										<?php foreach ( $crrntl_locations as $loc_id => $location ) {
											printf(
												'<option value="%1$s" %2$s>%3$s</option>',
												$loc_id,
												selected( $loc_id == $crrntl_order_data['pickup_loc_id'], true, false ),
												( $loc_id == $crrntl_order_data['pickup_loc_id'] ? '* ' : '' ) . $location->formatted_address
											);
										} ?>
									</select> -->
									<?php echo $crrntl_locations[ $crrntl_order_data['pickup_loc_id'] ]->formatted_address; ?>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<th><label for="crrntl-return-loc"><?php _e( 'Return Location', 'car-rental' ); ?></label></th>
							<td>
								<select id="crrntl-return-loc" name="crrntl_return_loc_id">
									<?php foreach ( $crrntl_locations as $crrntl_location ) {
										echo '<option value="' . $crrntl_location->loc_id . '"' . selected( $crrntl_location->loc_id, $crrntl_order_data['dropoff_loc_id'], false ) . '>' . $crrntl_location->formatted_address . '</option>';
									} ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Pick Up Date', 'car-rental' ); ?></th>
							<td class="crrntl-pick-up">
								<input class="datepicker" type="text" value="<?php echo $pickup_date[0]; ?>" name="crrntl_date_from" title="<?php _e( 'Choose Pick Up date', 'car-rental' ); ?>" placeholder="<?php _e( 'YYYY-MM-DD', 'car-rental' ); ?>" />
								<?php if ( ! empty( $crrntl_options['time_selecting'] ) ) { ?>
									<select name="crrntl_time_from" title="<?php _e( 'Choose Pick Up time', 'car-rental' ); ?>">
										<?php for ( $i = 0; $i <= 23; $i ++ ) { ?>
											<option value="<?php echo $i; ?>:00" <?php selected( sprintf( "%02d:00:00", $i ), $pickup_date['1'] ); ?>><?php echo $i; ?>:00</option>
											<option value="<?php echo $i; ?>:30" <?php selected( sprintf( "%02d:30:00", $i ), $pickup_date['1'] ); ?>><?php echo $i; ?>:30</option>
										<?php } ?>
									</select>
								<?php } else {
									echo $pickup_date['1'];
								} ?>
							</td>
						</tr>
						<tr>
							<th><?php _e( 'Drop Off Date', 'car-rental' ); ?></th>
							<td class="crrntl-drop-off">
								<input class="datepicker" type="text" value="<?php echo $dropoff_date[0]; ?>" name="crrntl_date_to" title="<?php _e( 'Choose Drop Off date', 'car-rental' ); ?>" />
								<?php if ( ! empty( $crrntl_options['time_selecting'] ) ) { ?>
									<select name="crrntl_time_to" title="<?php _e( 'Choose Pick Up time', 'car-rental' ); ?>">
										<?php for ( $i = 00; $i <= 23; $i ++ ) { ?>
											<option value="<?php echo $i; ?>:00" <?php selected( sprintf( "%02d:00:00", $i ), $dropoff_date['1'] ); ?>><?php echo $i; ?>:00</option>
											<option value="<?php echo $i; ?>:30" <?php selected( sprintf( "%02d:30:00", $i ), $dropoff_date['1'] ); ?>><?php echo $i; ?>:30</option>
										<?php } ?>
									</select>
								<?php } else {
									echo $dropoff_date['1'];
								} ?>
							</td>
						</tr>
						<tr>
							<th><label for="crrntl-order-status"><?php _e( 'Status', 'car-rental' ); ?></label></th>
							<td>
								<select id="crrntl-order-status" name="crrntl_order_status_id">
									<?php foreach ( $crrntl_statuses as $crrntl_statuse ) {
										echo '<option value="' . $crrntl_statuse->status_id . '"' . selected( $crrntl_statuse->status_id, $crrntl_order_data['status_id'], false ) . '>' . $crrntl_statuse->status_name . '</option>';
									} ?>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="clear"></div>
				<div class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'car-rental' ); ?>" />
					<input type="hidden" name="crrntl_save_orders_changes" value="1" />
					<?php wp_nonce_field( plugin_basename( __FILE__ ), 'crrntl_nonce_name' ); ?>
				</div>
			</form>
		</div>
	<?php }
}