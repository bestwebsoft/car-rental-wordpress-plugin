<?php
/**
 * Template Name: Choose car
 *
 * @subpackage Car Rental
 * @since      Car Rental 1.0.0
 */

global $crrntl_options, $wpdb, $crrntl_currency, $crrntl_filepath;

$crrntl_error = '';

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

foreach ( array( 'crrntl_location', 'crrntl_select_carclass' ) as $one_search_data ) {
	if ( isset( $_POST[$one_search_data] ) ) {
		$_SESSION[$one_search_data] = $_POST[$one_search_data];
	}
}

/* Write the date value as a integer. Check whether the client has the ability to choose the time. */
$date_format = crrntl_get_date_format();

$crrntl_time_from = ( ! empty( $crrntl_options['time_selecting']) && isset( $_POST['crrntl_time_from'] ) )  ? $_POST['crrntl_time_from'] : $crrntl_options['time_from'];
if ( isset( $_POST['crrntl_date_from'] ) && ! empty( $crrntl_time_from ) ) {
	if ( crrntl_check_date_format( $_POST['crrntl_date_from'] ) ) {
		if ( crrntl_get_date_int( $_POST['crrntl_date_from'], $crrntl_time_from ) > time() ) {
			$_SESSION['crrntl_date_from'] = crrntl_get_date_int( $_POST['crrntl_date_from'], $crrntl_time_from );
		} else {
			$crrntl_error .= __( 'Please choose correct Pick Up datetime', 'car-rental' ) . '<br />';
		}
	} else {
		$crrntl_error .= __( 'Please choose correct Pick Up datetime in format ', 'car-rental' ) . '"' . $date_format . '"' . '<br />';
	}
}

$crrntl_time_to = ( ! empty( $crrntl_options['time_selecting'] ) && isset( $_POST['crrntl_time_to'] ) ) ? $_POST['crrntl_time_to'] : $crrntl_options['time_from'];
if ( isset( $_SESSION['crrntl_date_from'] ) && isset( $_POST['crrntl_date_to'] ) && ! empty( $crrntl_time_to ) ) {
	if ( crrntl_check_date_format( $_POST['crrntl_date_to'] ) ) {
		if ( crrntl_get_date_int( $_POST['crrntl_date_to'], $crrntl_time_to ) > $_SESSION['crrntl_date_from'] &&
			crrntl_get_date_int( $_POST['crrntl_date_to'], $crrntl_time_to ) > time() ) {
			$_SESSION['crrntl_date_to'] = crrntl_get_date_int( $_POST['crrntl_date_to'], $crrntl_time_to );
		} else {
			$crrntl_error .= __( 'Please choose correct Drop Off datetime', 'car-rental' ) . '<br />';
		}
	} else {
		$crrntl_error .= __( 'Please choose correct Drop Off datetime in format ', 'car-rental' ) . '"' . $date_format . '"' . '<br />';
	}
}

if ( ! empty( $_GET ) ) {
	if ( empty( $_SESSION['crrntl_date_from'] ) || empty( $_SESSION['crrntl_date_to'] ) ) {
		$crrntl_error .= __( 'Please choose datetime', 'car-rental' ) . '<br />';
	}
}

$required_data_exists = 1;
if ( empty( $_SESSION['crrntl_date_from'] ) || empty( $_SESSION['crrntl_date_to'] ) ) {
	$required_data_exists = 0;
}
if ( isset( $_POST['crrntl_search_submit'] ) && isset( $_POST['crrntl_checkbox_location'] ) ) {
	$_SESSION['crrntl_checkbox_location'] = $_POST['crrntl_checkbox_location'];
	$_SESSION['crrntl_return_location']   = $_POST['crrntl_return_location'];
} elseif ( isset( $_POST['crrntl_search_submit'] ) && ! isset( $_POST['crrntl_checkbox_location'] ) ) {
	$_SESSION['crrntl_checkbox_location'] = $_SESSION['crrntl_return_location'] = '';
}

get_header(); ?>
	<div class="main-content">
		<div class="content-area">
			<div class="site-content">
				<div id="crrntl-progress-bar">
					<div id="crrntl-progress-bar-steps">
						<div class="crrntl-progress-bar-step crrntl-done">
							<div class="crrntl-step-number">1</div>
							<div class="crrntl-step-name"><?php _e( 'Create request', 'car-rental' ); ?></div>
						</div><!-- .crrntl-progress-bar-step -->
						<div class="crrntl-progress-bar-step crrntl-current">
							<div class="crrntl-step-number">2</div>
							<div class="crrntl-step-name"><?php _e( 'Choose a car', 'car-rental' ); ?></div>
						</div><!-- .crrntl-progress-bar-step -->
						<div class="crrntl-progress-bar-step">
							<div class="crrntl-step-number">3</div>
							<div class="crrntl-step-name"><?php _e( 'Choose extras', 'car-rental' ); ?></div>
						</div><!-- .crrntl-progress-bar-step -->
						<div class="crrntl-progress-bar-step crrntl-last">
							<div class="crrntl-step-number">4</div>
							<div class="crrntl-step-name"><?php _e( 'Review &amp; Book', 'car-rental' ); ?></div>
						</div><!-- .crrntl-progress-bar-step -->
					</div><!-- #crrntl-progress-bar-steps -->
					<div class="clear"></div>
				</div><!-- #crrntl-progress-bar -->
				<div class="crrntl-with-form-search">
					<?php load_template( $crrntl_filepath . 'car-search-form.php' ); ?>
					<div class="crrntl-content-area crrntl-wrapper">
						<?php if ( ! empty( $crrntl_error ) ) { ?>
							<div class="crrntl-site-content">
								<article class="crrntl-review clearfix">
									<div class="crrntl-choose-car-message">
										<span>
											<?php echo $crrntl_error; ?>
										</span>
									</div>
								</article>
							</div>
						<?php } ?>
						<main id="content" class="crrntl-site-content">
							<?php /* WP_Query arguments */
							if ( get_query_var( 'paged' ) ) {
								$paged = get_query_var( 'paged' );
							} elseif ( get_query_var( 'page' ) ) {
								$paged = get_query_var( 'page' );
							} else {
								$paged = 1;
							}
							$price_filter = $pass_filter = $manufacturer_filter = $vehicle_type_filter = $location_filter = $car_class_filter = '';
							/* Location filter data */
							if ( ! empty( $_SESSION['crrntl_location'] ) ) {
								$location_filter = array(
									'key'   => 'car_location',
									'value' => $_SESSION['crrntl_location'],
								);
							}
							/* Car Class filter data */
							if ( ! empty( $_SESSION['crrntl_select_carclass'] ) ) {
								$car_class_filter = array(
									'taxonomy' => 'car_class',
									'field'    => 'id',
									'terms'    => $_SESSION['crrntl_select_carclass'],
								);
							}
							/* Price filter data */
							if ( isset( $_GET['crrntl_price_min'] ) && isset( $_GET['crrntl_price_max'] ) ) {
								$price_filter = array(
									'relation' => 'OR',
									array(
										'key'     => 'car_price',
										'value'   => array( $_GET['crrntl_price_min'], $_GET['crrntl_price_max'] ),
										'type'    => 'DECIMAL',
										'compare' => 'BETWEEN'
									),
									array(
										'key'     => 'car_price',
										'value'   => 'on_request',
										'compare' => '='
									)
								);
							}
							/* Passengers filter data */
							if ( isset( $_GET['crrntl_pass_min'] ) && isset( $_GET['crrntl_pass_max'] ) ) {
								$pass_filter = array(
									'key'     => 'car_passengers',
									'value'   => array( $_GET['crrntl_pass_min'], $_GET['crrntl_pass_max'] ),
									'type'    => 'numeric',
									'compare' => 'BETWEEN',
								);
							}
							/* Manufacturers filter data */
							if ( ! empty( $_GET['crrntl_manufacturer'] ) ) {
								$manufacturer_filter = array(
									'taxonomy' => 'manufacturer',
									'field'    => 'id',
									'terms'    => $_GET['crrntl_manufacturer'],
								);
							}
							/* Vehicle types filter data */
							if ( ! empty( $_GET['crrntl_vehicle_type'] ) ) {
								$vehicle_type_filter = array(
									'taxonomy' => 'vehicle_type',
									'field'    => 'id',
									'terms'    => $_GET['crrntl_vehicle_type'],
								);
							}
							$args = array(
								'post_type'      => array( $crrntl_options['post_type_name'] ),
								'post_status'    => 'publish',
								'posts_per_page' => $crrntl_options['per_page'],
								'paged'          => $paged,
								'tax_query'      => array(
									'relation' => 'AND',
									$car_class_filter,
									$manufacturer_filter,
									$vehicle_type_filter
								),
								'meta_query'     => array(
									'relation' => 'AND',
									$location_filter,
									$price_filter,
									$pass_filter
								),
							);
							/* Sort by Price or Car Class */
							if ( ! empty( $_GET['sortby'] ) ) {
								if ( 'price' == $_GET['sortby'] ) {
									$crrntl_sortby = array(
										'meta_key' => 'car_price',
										'orderby'  => 'meta_value_num',
										'order'    => 'ASC',
									);
									$args            = array_merge( $args, $crrntl_sortby );
								} elseif ( 'name' == $_GET['sortby'] ) {
									$crrntl_sortby = array(
										'orderby' => 'title',
										'order'   => 'ASC',
									);
									$args            = array_merge( $args, $crrntl_sortby );
								}
							}

							/* The Query */
							$crrntl_query = new WP_Query( $args );

							if ( $crrntl_query->have_posts() ) { ?>
								<header>
									<div class="crrntl-result-title">
										<div>
											<img src="<?php echo $crrntl_plugin_directory . '/images/list.png'; ?>" alt="" />
											<?php echo __( 'Results', 'car-rental' ) . ' <span>( ' . $crrntl_query->post_count . '&nbsp;' . __( 'from', 'car-rental' ) . '&nbsp;' . $crrntl_query->found_posts . ' )</span>'; ?>
										</div>
										<div class="crrntl-widget-title-sort">
											<span class="crrntl-sorting"><?php _e( 'Sort by', 'car-rental' ); ?>: </span>
											<a href="<?php echo esc_url( add_query_arg( 'sortby', 'price' ) ); ?>" title="" <?php echo ( isset( $_GET['sortby'] ) && 'price' == $_GET['sortby'] ) ? 'class="crrntl-current"' : ''; ?>><?php _e( 'Price', 'car-rental' ); ?></a> |
											<a href="<?php echo esc_url( add_query_arg( 'sortby', 'name' ) ); ?>" title="" <?php echo ( isset( $_GET['sortby'] ) && 'name' == $_GET['sortby'] ) ? 'class="crrntl-current"' : ''; ?>><?php _e( 'Name', 'car-rental' ); ?></a>
										</div><!-- .crrntl-widget-title-sort -->
										<div class="clear"></div>
									</div><!-- .crrntl-result-title -->
								</header>

								<?php while ( $crrntl_query->have_posts() ) :
									$crrntl_query->the_post();
									$car_location_id			= get_post_meta( $post->ID, 'car_location', true );
									$car_location_formatted		= $wpdb->get_var( "SELECT `formatted_address` FROM `{$wpdb->prefix}crrntl_locations` WHERE loc_id='{$car_location_id}'" );
									$car_info					= get_post_meta( $post->ID, 'car_info', true );
									$car_passengers				= get_post_meta( $post->ID, 'car_passengers', true );
									$car_class					= get_the_terms( $post->ID, 'car_class' );
									$car_price					= get_post_meta( $post->ID, 'car_price', true );

									$car_price_display = ( 'on_request' == $car_price ) ? __( 'Price on request', 'car-rental' ) : number_format_i18n( $car_price, 2 );
									$car_price_display = '<span class="crrntl-car-price">' . $car_price_display . '</span>';

									if ( 'on_request' != $car_price && ! empty( $crrntl_currency_position ) ) {
										if ( 'before' == $crrntl_currency_position ) {
											$car_price_display = $crrntl_currency . ' ' . $car_price_display;
										} else {
											$car_price_display .= ' ' . $crrntl_currency;
										}
									} ?>
									<article class="crrntl-product clearfix">
										<div class="crrntl-product-wrap">
											<?php if ( has_post_thumbnail() ) { ?>
												<div class="crrntl-product-img">
													<?php the_post_thumbnail( 'crrntl_product_image' ); ?>
												</div><!-- .crrntl-product-img -->
											<?php } ?>
											<div class="crrntl-product-info">
												<div class="crrntl-product-title">
													<h3>
														<?php echo $post->post_title;
														if ( ! empty( $car_class ) ) { ?>
															<span> |
																<?php echo array_shift( $car_class )->name; ?>
															</span>
														<?php } ?>
													</h3>
												</div><!-- .crrntl-product-title -->
												<?php if ( empty( $_POST['crrntl_location'] ) && ! empty( $car_location_formatted ) ) { ?>
													<div class="crrntl-product-location">
														<p>
															<i class="dashicons dashicons-location"></i> <span><?php echo $car_location_formatted; ?></span>
														</p>
													</div><!-- .crrntl-product-location -->
												<?php } ?>
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
															<?php $luggage = array();
															if ( ! empty( $car_info['luggage_large'] ) ) {
																$luggage_large    = $car_info['luggage_large'];
																$luggage['large'] = sprintf( _n( '%s large suitcase', '%s large suitcases', $luggage_large, 'car-rental' ), $luggage_large );
															}
															if ( ! empty( $car_info['luggage_small'] ) ) {
																$luggage_small    = $car_info['luggage_small'];
																$luggage['small'] = sprintf( _n( '%s small suitcase', '%s small suitcases', $luggage_small, 'car-rental' ), $luggage_small );
															}
															echo implode( ', ', $luggage ); ?>
														</p>
													<?php }
													if ( ! empty( $car_info['transmission'] ) && 0 != $car_info['transmission'] ) { ?>
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
												<?php if ( ! empty( $post->post_content ) ) { ?>
													<div class="crrntl-product-details">
														<div class="crrntl-view-details">[+] <?php _e( 'View car details', 'car-rental' ); ?></div>
														<div class="crrntl-close-details">[-] <?php _e( 'Close car details', 'car-rental' ); ?></div>
														<div class="crrntl-details-more">
															<?php echo $post->post_content; ?>
														</div>
													</div><!-- .crrntl-product-details -->
												<?php } ?>
											</div><!-- .crrntl-product-info -->
										</div><!-- .crrntl-product-wrap -->
										<div class="crrntl-product-price">
											<p><?php echo $car_price_display; ?></p>
											<form method="post" action="<?php echo ( ! empty( $crrntl_options['extra_page_id'] ) ) ? get_permalink( $crrntl_options['extra_page_id'] ) : ''; ?>">
												<?php if ( ! empty( $required_data_exists ) ) { ?>
													<input class="crrntl-select-car crrntl-continue-button crrntl-blue-button" type="submit" value="<?php _e( 'Select', 'car-rental' ); ?>" />
												<?php } else { ?>
													<input class="crrntl-select-car crrntl-continue-button" type="button" value="<?php _e( 'Select', 'car-rental' ); ?>" />
												<?php } ?>
												<input type="hidden" name="crrntl_selected_product" value="<?php echo $post->ID; ?>" />
												<input type="hidden" name="crrntl_location" value="<?php echo $car_location_id; ?>" />
											</form>
										</div><!-- .crrntl-product-price -->
									</article><!-- .crrntl-product clearfix -->

								<?php endwhile;
							} else {
								/* no posts found */ ?>
								<article class="crrntl-review clearfix">
									<div>
										<p><?php _e( 'No cars available.', 'car-rental' ); ?></p>
									</div>
								</article>
							<?php } ?>
							<div class="clear"></div>
						</main><!-- #content -->
						<div class="clear"></div>
						<?php crrntl_paginate( $crrntl_query->max_num_pages ); /* post navigation */

						/* Restore original Post Data */
						wp_reset_postdata(); ?>
					</div><!-- .crrntl-content-area -->
					<aside class="sidebars-area crrntl-sidebar-info">
						<?php the_widget( 'Car_Rental_Filters_Widget' );
						dynamic_sidebar( 'sidebar-choose-car' ); ?>
					</aside><!-- .sidebars-area .crrntl-sidebar-info -->
					<div class="clear"></div>
				</div><!-- .crrntl-with-form-search -->
			</div><!-- .site-content -->
			<div class="clear"></div>
		</div><!-- .content-area -->
		<div class="clear"></div>
	</div><!-- .main-content -->
<?php get_footer();