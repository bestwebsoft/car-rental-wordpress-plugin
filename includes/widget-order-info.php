<?php
/**
 * Widget for displaying Order Info
 *
 * @subpackage Car Rental
 * @since      Car Rental 1.0.0
 */

if ( ! class_exists( 'Car_Rental_Order_Info_Widget' ) ) {
	class Car_Rental_Order_Info_Widget extends WP_Widget {

		/**
		 * Constructor.
		 *
		 * @since Car Rental 1.0.0
		 *
		 * @return Car_Rental_Order_Info_Widget
		 */
		public function __construct() {
			parent::__construct(
				'car-rental-order-info',
				__( 'Car Rental Order Info', 'car-rental' ),
				array( 'description' => __( 'Widget for Order Info displaying.', 'car-rental' ) )
			);
		}

		/**
		 * Display widget content
		 *
		 * @param   array $args
		 * @param   array $instance
		 *
		 * @return void
		 */
		public function widget( $args, $instance ) {
			global $crrntl_options, $wpdb, $crrntl_currency, $crrntl_selected_prod_id, $wp_locale;
			if ( empty( $crrntl_options ) ) {
				$crrntl_options = get_option( 'crrntl_options' );
			}
			if ( empty( $crrntl_options['custom_currency'] ) || empty( $crrntl_options['currency_custom_display'] ) ) {
				$crrntl_currency = $wpdb->get_var( "SELECT currency_unicode FROM {$wpdb->prefix}crrntl_currency WHERE currency_id = {$crrntl_options['currency_unicode']}" );
				if ( empty( $crrntl_currency ) ) {
					$crrntl_currency = '&#36;';
				}
			} else {
				$crrntl_currency = $crrntl_options['custom_currency'];
			}
			$crrntl_currency_position = $crrntl_options['currency_position'];
			if ( empty( $crrntl_options['custom_unit_consumption'] ) || empty( $crrntl_options['unit_consumption_custom_display'] ) ) {
				$unit_consumption = $crrntl_options['unit_consumption'];
			} else {
				$unit_consumption = $crrntl_options['custom_unit_consumption'];
			}
			$crrntl_plugin_directory = plugins_url( 'car-rental' );
			if ( empty( $crrntl_selected_prod_id ) ) {
				if ( ! empty( $_POST['crrntl_selected_product'] ) || ! empty( $_SESSION['crrntl_selected_product_id'] ) ) {
					if ( empty( $_SESSION['crrntl_selected_product_id'] ) ) {
						$crrntl_selected_prod_id = $_SESSION['crrntl_selected_product_id'] = $_POST['crrntl_selected_product'];
					} elseif ( empty( $_POST['crrntl_selected_product'] ) ) {
						$crrntl_selected_prod_id = $_SESSION['crrntl_selected_product_id'];
					} elseif ( $_SESSION['crrntl_selected_product_id'] != $_POST['crrntl_selected_product'] ) {
						$crrntl_selected_prod_id = $_SESSION['crrntl_selected_product_id'] = $_POST['crrntl_selected_product'];
						unset( $_SESSION['crrntl_opted_extras'], $_SESSION['crrntl_extra_quantity'] );
					} else {
						$crrntl_selected_prod_id = $_SESSION['crrntl_selected_product_id'];
					}
				}
			}
			if ( isset( $_POST['crrntl_form_extras_submit'] ) ) {
				if ( isset( $_POST['crrntl_opted_extras'] ) ) {
					$_SESSION['crrntl_opted_extras'] = $_POST['crrntl_opted_extras'];
					if ( isset( $_POST['crrntl_extra_quantity'] ) ) {
						$_SESSION['crrntl_extra_quantity'] = $_POST['crrntl_extra_quantity'];
					}
				} else {
					unset( $_SESSION['crrntl_opted_extras'], $_SESSION['crrntl_extra_quantity'] );
				}
			}
			$crrntl_pickup = '<span class="crrntl-error-message">' . __( 'Please choose Pick Up date', 'car-rental' ) . '</span>';
			$crrntl_dropoff = '<span class="crrntl-error-message">' . __( 'Please choose Drop Off date', 'car-rental' ) . '</span>';
			if ( ! empty( $_SESSION['crrntl_date_from'] ) ) {
				$date_from = $_SESSION['crrntl_date_from'];
				if ( $date_from > time() ) {
					$crrntl_pickup = date_i18n( 'D, d M, Y ', $date_from ) . __( 'at', 'car-rental' ) . date_i18n( ' H:i', $date_from );
					if ( ! empty( $_SESSION['crrntl_date_to'] ) ) {
						$date_to = $_SESSION['crrntl_date_to'];
						if ( $date_to > $date_from ) {
							$crrntl_dropoff = date_i18n( 'D, d M, Y ', $date_to ) . __( 'at', 'car-rental' ) . date_i18n( ' H:i', $date_to );
							if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
								$diff_time = ( 'hour' == $crrntl_options['rent_per'] ) ? ceil( ( $date_to - $date_from ) / HOUR_IN_SECONDS ) : ceil( ( $date_to - $date_from ) / DAY_IN_SECONDS );
							}
						}
					}
				}
			}

			if ( ! empty( $_SESSION['crrntl_location'] ) ) {
				$crrntl_locations = $wpdb->get_var( $wpdb->prepare( "SELECT `formatted_address` FROM {$wpdb->prefix}crrntl_locations WHERE `loc_id` = %d", $_SESSION['crrntl_location'] ) );
				if ( ! empty( $_SESSION['crrntl_return_location'] ) && $_SESSION['crrntl_location'] != $_SESSION['crrntl_return_location'] ) {
					$crrntl_dropoff_loc = $wpdb->get_var( $wpdb->prepare( "SELECT `formatted_address` FROM {$wpdb->prefix}crrntl_locations WHERE `loc_id` = %d", $_SESSION['crrntl_return_location'] ) );
					$crrntl_locations .= ' -<br />' . $crrntl_dropoff_loc;
				}
			}

			echo $args['before_widget'] . $args['before_title']; ?>
			<img class="widget-title-img" src="<?php echo plugins_url( 'car-rental/images/order-info.png' ); ?>">
			<?php echo __( 'Order Info', 'car-rental' ) . $args['after_title']; ?>
			<div id="crrntl-order-info">
				<h4><?php _e( 'Car', 'car-rental' ); ?>
					<span class="crrntl-select-clear">
						<a href="<?php echo ( ! empty( $crrntl_options['car_page_id'] ) ) ? get_permalink( $crrntl_options['car_page_id'] ) : ''; ?>"><?php _e( 'Edit', 'car-rental' ); ?></a>
					</span>
				</h4>
				<?php if ( ! empty( $crrntl_selected_prod_id ) ) {
					$selected_product = get_post( $crrntl_selected_prod_id );
					if ( ! empty( $selected_product ) ) {
						$car_info       = get_post_meta( $crrntl_selected_prod_id, 'car_info', true );
						$car_passengers = get_post_meta( $crrntl_selected_prod_id, 'car_passengers', true );
						$car_price = get_post_meta( $crrntl_selected_prod_id, 'car_price', true );
						$car_class = get_the_terms( $crrntl_selected_prod_id, 'car_class' ); ?>
						<div class="widget-content crrntl-widget-product-info">
							<div class="crrntl-product-img">
								<?php if ( has_post_thumbnail( $crrntl_selected_prod_id ) ) {
									echo get_the_post_thumbnail( $crrntl_selected_prod_id, 'crrntl_product_image_widget' );
								} ?>
							</div><!-- .crrntl-product-img -->
							<div class="crrntl-product-info">
								<div class="crrntl-product-title">
									<div><?php echo $selected_product->post_title; ?></div>
									<?php if ( ! empty( $car_class ) ) { ?>
										<span><?php echo array_shift( $car_class )->name; ?></span>
									<?php } ?>
								</div><!-- .crrntl-product-title -->
								<div class="crrntl-product-features">
									<?php if ( ! empty( $car_passengers ) ) { ?>
										<p>
											<img src="<?php echo $crrntl_plugin_directory . '/images/passengers-icon.png'; ?>" alt="" /> <?php echo sprintf( _n( '%s passenger', '%s passengers', $car_passengers, 'car-rental' ), $car_passengers ); ?>
										</p>
									<?php }
									if ( ! empty( $car_info['doors'] ) ) { ?>
										<p>
											<img src="<?php echo $crrntl_plugin_directory . '/images/doors-icon.png'; ?>" alt="" /> <?php printf( _n( '%s Door', '%s Doors', $car_info['doors'], 'car-rental' ), $car_info['doors'] ); ?>
										</p>
									<?php }
									if ( ! empty( $car_info['luggage_large'] ) || ! empty( $car_info['luggage_small'] ) ) { ?>
										<p>
											<img src="<?php echo $crrntl_plugin_directory . '/images/luggage-icon.png'; ?>" alt="" />
											<?php if ( ! empty( $car_info['luggage_large'] ) ) {
												echo sprintf( _n( '%s large suitcase', '%s large suitcases', $car_info['luggage_large'], 'car-rental' ), $car_info['luggage_large'] ) . ', ';
											}
											if ( ! empty( $car_info['luggage_small'] ) ) {
												echo sprintf( _n( '%s small suitcase', '%s small suitcases', $car_info['luggage_small'], 'car-rental' ), $car_info['luggage_small'] );
											} ?>
										</p>
									<?php }
									if ( ! empty( $car_info['transmission'] ) && '0' != $car_info['transmission'] ) { ?>
										<p>
											<img src="<?php echo $crrntl_plugin_directory . '/images/transmission-icon.png'; ?>" alt="" /> <?php ( 1 == $car_info['transmission'] ) ? _e( 'automatic transmission', 'car-rental' ) : _e( 'manual transmission', 'car-rental' ); ?>
										</p>
									<?php }
									if ( ! empty( $car_info['condition'] ) ) { ?>
										<p>
											<img src="<?php echo $crrntl_plugin_directory . '/images/condition-icon.png'; ?>" alt="" /> <?php _e( 'air conditioning', 'car-rental' ); ?>
										</p>
									<?php }
									if ( ! empty( $car_info['consumption'] ) ) { ?>
										<p>
											<img src="<?php echo $crrntl_plugin_directory . '/images/consumption-icon.png'; ?>" alt="" /> <?php echo $car_info['consumption'] . ' ' . $unit_consumption; ?>
										</p>
									<?php } ?>
								</div><!-- .crrntl-product-features -->
								<?php if ( ! empty( $selected_product->post_content ) ) { ?>
									<div class="crrntl-product-details">
										<div class="crrntl-view-details">[+] <?php _e( 'View car details', 'car-rental' ); ?></div>
										<div class="crrntl-close-details">[-] <?php _e( 'Close car details', 'car-rental' ); ?></div>
										<div class="crrntl-details-more">
											<?php echo $selected_product->post_content; ?>
										</div><!-- .crrntl-details-more -->
									</div><!-- .crrntl-product-details -->
								<?php } ?>
							</div><!-- .crrntl-product-info -->
						</div><!-- .widget-content .crrntl-widget-product-info -->
					<?php }
				} else { ?>
					<div class="widget-content crrntl-widget-product-info">
						<p class="crrntl-error-message"><?php _e( 'Please choose a Car', 'car-rental' ); ?></p>
					</div>
				<?php }
				wp_reset_postdata();
				if ( ! empty( $crrntl_selected_prod_id ) && isset( $car_price ) && 'on_request' != $car_price ) {
					if ( isset( $diff_time ) ) {
						$crrntl_subtotal = $crrntl_total = isset( $car_price ) ? $car_price * $diff_time : 0;
						$crrntl_subtotal = number_format_i18n( $crrntl_subtotal, 2 );
					} else {
						$crrntl_subtotal = $crrntl_total = 0;
					}
					if ( ! empty( $crrntl_currency_position ) ) {
						if ( 'before' == $crrntl_currency_position ) {
							$crrntl_subtotal_display = '<span data-price="' . $crrntl_total . '">' . $crrntl_currency . ' ' . $crrntl_subtotal . '</span>';
						} else {
							$crrntl_subtotal_display = '<span data-price="' . $crrntl_total . '">' . $crrntl_subtotal . ' ' . $crrntl_currency . '</span> ';
						}
					} else {
						$crrntl_subtotal_display = '<span data-price="' . $crrntl_total . '">' . $crrntl_subtotal . '</span>';
					}
				} ?>
				<h4><?php _e( 'Date & Location', 'car-rental' ); ?>
					<span class="crrntl-select-clear">
						<a href="<?php echo ( ! empty( $crrntl_options['car_page_id'] ) ) ? get_permalink( $crrntl_options['car_page_id'] ) : ''; ?>"><?php _e( 'Edit', 'car-rental' ); ?></a>
					</span>
				</h4>
				<div class="widget-content crrntl-widget-time-info" data-time-diff="<?php if ( isset( $diff_time ) ) echo $diff_time; ?>">
					<h4><?php _e( 'Pick Up time', 'car-rental' ); ?></h4>
					<p><?php echo $crrntl_pickup; ?></p>

					<h4><?php _e( 'Return time', 'car-rental' ); ?></h4>
					<p><?php echo $crrntl_dropoff; ?></p>

					<?php if ( ! empty( $crrntl_locations ) ) { ?>
						<h4><?php _e( 'Pickup and Return Location', 'car-rental' ); ?></h4>
						<p><?php echo $crrntl_locations; ?></p>
					<?php } ?>
				</div><!-- .widget-content .crrntl-widget-time-info -->

				<?php if ( isset( $crrntl_subtotal_display ) ) { ?>
					<div class="crrntl-subtotal-content">
						<div class="crrntl-subtotal clearfix">
							<?php _e( 'Subtotal', 'car-rental' ); ?>: <div class="crrntl-price"><?php echo $crrntl_subtotal_display; ?></div>
						</div>
					</div><!-- .crrntl-subtotal-content -->
				<?php } ?>

				<h4 class="crrntl-extras"><?php _e( 'Extras', 'car-rental' ); ?>
					<?php if ( is_page_template( 'page-review-book.php' ) ) { ?>
						<span class="crrntl-select-clear">
							<a href="<?php echo ( ! empty( $crrntl_options['extra_page_id'] ) ) ? get_permalink( $crrntl_options['extra_page_id'] ) : ''; ?>"><?php _e( 'Edit', 'car-rental' ); ?></a>
						</span>
					<?php } ?>
				</h4>
				<div class="widget-content crrntl-widget-extras-info clearfix">
					<?php if ( isset( $_SESSION['crrntl_opted_extras'] ) ) {
						foreach ( $_SESSION['crrntl_opted_extras'] as $selected_extra_id ) {
							$selected_extra          = get_term( $selected_extra_id, 'extra' );
							$selected_extra_metadata = crrntl_get_term_meta( $selected_extra_id, '', true );
							$selected_extra_total    = $selected_extra_metadata['extra_price'][0];
							$selected_extra_name     = $selected_extra->name;
							if ( '1' == $selected_extra_metadata['extra_quantity'][0] ) {
								$selected_extra_quantity = ( ! empty( $_SESSION['crrntl_extra_quantity'][ $selected_extra_id ] ) ? $_SESSION['crrntl_extra_quantity'][ $selected_extra_id ] : 1 );
								$selected_extra_total    = $selected_extra_total * $selected_extra_quantity;
								$selected_extra_name     = $selected_extra->name . ' &times; ' . $selected_extra_quantity;
							}
							if ( isset( $diff_time ) ) {
								$selected_extra_total = $selected_extra_total * $diff_time;
								$crrntl_total = isset( $crrntl_total ) ? $crrntl_total + $selected_extra_total : $selected_extra_total;
							}

							if ( ! empty( $crrntl_currency_position ) ) {
								if ( 'before' == $crrntl_currency_position ) {
									$selected_extra_total_display = '<span data-price="' . $selected_extra_total . '">' . $crrntl_currency . ' ' . number_format_i18n( $selected_extra_total, 2 ) . '</span>';
								} else {
									$selected_extra_total_display = '<span data-price="' . $selected_extra_total . '">' . number_format_i18n( $selected_extra_total, 2 ) . ' ' . $crrntl_currency . '</span>';
								}
							} else {
								$selected_extra_total_display = '<span data-price="' . $selected_extra_total . '">' . number_format_i18n( $selected_extra_total, 2 ) . '</span>';
							} ?>
							<div class="crrntl-selected-extra-<?php echo $selected_extra_id; ?>"><?php echo $selected_extra_name; ?> <div class="crrntl-price"><?php echo $selected_extra_total_display; ?></div></div>
						<?php }
					}
					if ( ! empty( $crrntl_currency_position ) ) { ?>
						<div class="crrntl-currency-data" data-cur="<?php echo $crrntl_currency; ?>" data-cur-pos="<?php echo $crrntl_currency_position; ?>" data-dec-point="<?php echo $wp_locale->number_format['decimal_point']; ?>" data-thous-sep="<?php echo $wp_locale->number_format['thousands_sep']; ?>"></div>
					<?php } else { ?>
						<div class="crrntl-currency-data" data-cur="<?php echo $crrntl_currency; ?>" data-cur-pos="" data-dec-point="<?php echo $wp_locale->number_format['decimal_point']; ?>" data-thous-sep="<?php echo $wp_locale->number_format['thousands_sep']; ?>"></div>
					<?php } ?>
				</div><!-- .widget-content .crrntl-widget-extras-info -->
				<?php if ( isset( $crrntl_subtotal_display ) ) { ?>
					<div class="crrntl-widget-footer-total clearfix" data-time-diff="<?php if ( isset( $diff_time ) ) echo $diff_time; ?>">
						<?php if ( ! empty( $crrntl_currency_position ) ) {
							if ( 'before' == $crrntl_currency_position ) {
								$crrntl_total_display    = '<span data-price="' . $crrntl_total . '">' . $crrntl_currency . ' ' . number_format_i18n( $crrntl_total, 2 ) . '</span>';
							} else {
								$crrntl_total_display    = '<span data-price="' . $crrntl_total . '">' . number_format_i18n( $crrntl_total, 2 ) . ' ' . $crrntl_currency . '</span>';
							}
						} else {
							$crrntl_total_display = '<span data-price="' . $crrntl_total . '">' . number_format_i18n( $crrntl_total, 2 ) . '</span>';
						}
						if ( isset( $crrntl_options['review_page_id'] ) && get_the_ID() == $crrntl_options['review_page_id'] ) {
							$_SESSION['crrntl_total'] = $crrntl_total;
						}
						_e( 'Total', 'car-rental' ); ?>: <p class="crrntl-price"><?php echo $crrntl_total_display; ?></p>
					</div><!-- .crrntl-widget-footer-total -->
				<?php } ?>
				<div class="clear"></div>
			</div><!-- #crrntl-order-info -->
			<?php echo $args['after_widget'];
		}
	}
}
register_widget( 'Car_Rental_Order_Info_Widget' );