<?php
/**
 * Template Name: Choose extras
 *
 * @subpackage Car Rental
 * @since      Car Rental 1.0.0
 */

global $crrntl_options, $wpdb, $crrntl_currency, $crrntl_selected_prod_id, $crrntl_filepath;

if ( empty( $crrntl_options ) ) {
	$crrntl_options = get_option( 'crrntl_options' );
}
if ( empty( $crrntl_options['custom_currency'] ) || empty( $crrntl_options['currency_custom_display'] ) ) {
	$crrntl_currency = $wpdb->get_var( "SELECT `currency_unicode` FROM {$wpdb->prefix}crrntl_currency WHERE `currency_id` = {$crrntl_options['currency_unicode']}" );
	if ( empty( $crrntl_currency ) ) {
		$crrntl_currency = '&#36;';
	}
} else {
	$crrntl_currency = $crrntl_options['custom_currency'];
}
$crrntl_currency_position = $crrntl_options['currency_position'];
$crrntl_plugin_directory  = plugins_url( 'car-rental' );
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
$extras = get_the_terms( $crrntl_selected_prod_id, 'extra' );

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
						<a class="crrntl-progress-bar-step crrntl-done" href="<?php echo ( ! empty( $crrntl_options['car_page_id'] ) ) ? get_permalink( $crrntl_options['car_page_id'] ) : ''; ?>">
							<div class="crrntl-step-number">2</div>
							<div class="crrntl-step-name"><?php _e( 'Choose a car', 'car-rental' ); ?></div>
						</a><!-- .crrntl-progress-bar-step -->
						<div class="crrntl-progress-bar-step crrntl-current">
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
					<div class="clear"></div>
					<div class="crrntl-content-area crrntl-wrapper">
						<?php if ( empty( $crrntl_selected_prod_id ) ) { ?>
							<main id="content" class="crrntl-site-content">
								<article class="crrntl-extra clearfix">
									<div class="crrntl-choose-car-message">
										<span>
											<?php printf(
												'<a href="%1$s">%2$s</a>',
												( ! empty( $crrntl_options['car_page_id'] ) ) ? get_permalink( $crrntl_options['car_page_id'] ) : '',
												__( 'Please choose a Car', 'car-rental' )
											); ?>
										</span>
									</div>
								</article>
							</main>
						<?php } else { ?>
							<form method="post" action="<?php echo ( ! empty( $crrntl_options['review_page_id'] ) ) ? get_permalink( $crrntl_options['review_page_id'] ) : ''; ?>">
								<main id="content" class="crrntl-site-content">
									<header>
										<div class="crrntl-result-title">
											<div>
												<img src="<?php echo $crrntl_plugin_directory . '/images/list.png'; ?>" alt="" />
												<?php _e( 'Available Extras', 'car-rental' ); ?>
											</div>
											<div class="clear"></div>
										</div><!-- .crrntl-result-title -->
									</header>
									<?php if ( ! empty( $extras ) && is_array( $extras ) ) {
										foreach ( $extras as $extra ) {
											$extra_metadata       = crrntl_get_term_meta( $extra->term_id );
											$crrntl_extra_price = $extra_metadata['extra_price'][0]; ?>

											<article class="crrntl-extra clearfix">
												<div class="crrntl-checkbox-block">
													<input id="crrntl-extra-<?php echo $extra->term_id; ?>" type="checkbox" class="crrntl-extra-checkbox styled" name="crrntl_opted_extras[]" value="<?php echo $extra->term_id; ?>" <?php checked( ! empty( $_SESSION['crrntl_opted_extras'] ) && in_array( $extra->term_id, $_SESSION['crrntl_opted_extras'] ) ); ?> />
												</div><!-- .crrntl-checkbox-block -->
												<div class="crrntl-product-wrap">
													<?php if ( ! empty( $extra_metadata['extra_image'][0] ) ) { ?>
														<div class="crrntl-product-img">
															<label for="crrntl-extra-<?php echo $extra->term_id; ?>">
																<?php echo wp_get_attachment_image( $extra_metadata['extra_image'][0], 'crrntl_product_image' ); ?>
															</label>
														</div><!-- .crrntl-product-img -->
													<?php } ?>
													<div class="crrntl-product-info">
														<div class="crrntl-product-title">
															<h3>
																<label for="crrntl-extra-<?php echo $extra->term_id; ?>">
																	<?php echo $extra->name; ?>
																</label>
															</h3>
														</div><!-- .crrntl-product-title -->
														<div class="crrntl-product-features">
															<?php echo $extra->description;
															if ( '1' == $extra_metadata['extra_quantity'][0] ) { ?>
																<input class="crrntl-product-quantity" name="crrntl_extra_quantity[<?php echo $extra->term_id; ?>]" type="number" min="1" value="<?php echo isset( $_SESSION['crrntl_extra_quantity'][ $extra->term_id ] ) ? $_SESSION['crrntl_extra_quantity'][ $extra->term_id ] : 1; ?>" title="<?php _e( 'Choose Quantity', 'car-rental' ); ?>" />
															<?php } ?>
														</div><!-- .crrntl-product-features -->
														<?php if ( ! empty( $extra_metadata['extra_details'][0] ) ) { ?>
															<div class="crrntl-product-details">
																<div class="crrntl-view-details">[+] <?php _e( 'Learn more', 'car-rental' ); ?></div>
																<div class="crrntl-close-details">[-] <?php _e( 'Close details', 'car-rental' ); ?></div>
																<p class="crrntl-details-more">
																	<?php echo $extra_metadata['extra_details'][0]; ?>
																</p><!-- .crrntl-details-more -->
															</div><!-- .crrntl-product-details -->
														<?php } ?>
													</div><!-- .crrntl-product-info -->
												</div><!-- .crrntl-product-wrap -->
												<div class="crrntl-product-price">
													<?php if ( '1' == $extra_metadata['extra_quantity'][0] ) {
														$extra_quantity       = ( ! empty( $_SESSION['crrntl_extra_quantity'][ $extra->term_id ] ) ? $_SESSION['crrntl_extra_quantity'][ $extra->term_id ] : 1 );
														$crrntl_extra_total = $crrntl_extra_price * $extra_quantity;
														if ( ! empty( $crrntl_currency_position ) ) {
															if ( 'before' == $crrntl_currency_position ) {
																$crrntl_extra_total_display = $crrntl_currency . ' <span class="crrntl-extra-total" data-price="' . $crrntl_extra_total . '">' . number_format_i18n( $crrntl_extra_total, 2 ) . '</span>';
																$crrntl_extra_price_display = $crrntl_currency . ' <span class="crrntl-extra-price" data-price="' . $crrntl_extra_price . '">' . number_format_i18n( $crrntl_extra_price, 2 ) . '</span>';
															} else {
																$crrntl_extra_total_display = '<span class="crrntl-extra-total" data-price="' . $crrntl_extra_total . '">' . number_format_i18n( $crrntl_extra_total, 2 ) . '</span> ' . $crrntl_currency;
																$crrntl_extra_price_display = '<span class="crrntl-extra-price" data-price="' . $crrntl_extra_price . '">' . number_format_i18n( $crrntl_extra_price, 2 ) . '</span> ' . $crrntl_currency;
															}
														} else {
															$crrntl_extra_total_display = '<span class="crrntl-extra-total" data-price="' . $crrntl_extra_total . '">' . number_format_i18n( $crrntl_extra_total, 2 ) . '</span>';
															$crrntl_extra_price_display = '<span class="crrntl-extra-price" data-price="' . $crrntl_extra_price . '">' . number_format_i18n( $crrntl_extra_price, 2 ) . '</span>';
														} ?>
														<p><?php echo $crrntl_extra_total_display; ?></p>
														<p class="crrntl-item-price"><?php echo '<span>' . $extra_quantity . '</span> ' . __( 'pcs.', 'car-rental' ) . ' &times; ' . $crrntl_extra_price_display; ?></p>
													<?php } else {
														if ( ! empty( $crrntl_currency_position ) ) {
															if ( 'before' == $crrntl_currency_position ) {
																$crrntl_extra_price_display = $crrntl_currency . ' <span class="crrntl-extra-price" data-price="' . $crrntl_extra_price . '">' . number_format_i18n( $crrntl_extra_price, 2 ) . '</span>';
															} else {
																$crrntl_extra_price_display = '<span class="crrntl-extra-price" data-price="' . $crrntl_extra_price . '">' . number_format_i18n( $crrntl_extra_price, 2 ) . '</span> ' . $crrntl_currency;
															}
														} else {
															$crrntl_extra_price_display = '<span class="crrntl-extra-price" data-price="' . $crrntl_extra_price . '">' . number_format_i18n( $crrntl_extra_price, 2 ) . '</span>';
														} ?>
														<p><?php echo $crrntl_extra_price_display; ?></p>
													<?php } ?>
												</div><!-- .crrntl-product-price -->
											</article><!-- .crrntl-extra -->
										<?php } /* end foreach */
									} else { ?>
										<article class="crrntl-review clearfix">
											<div>
												<p><?php _e( 'There are no available extras for this car.', 'car-rental' ); ?></p>
											</div>
										</article>
									<?php } ?>
									<div class="clear"></div>
								</main><!-- #content -->
								<div class="crrntl-next-page">
									<input class="crrntl-continue-button crrntl-orange-button crrntl-checkout-button" type="submit" formaction="" value="<?php _e( 'Update', 'car-rental' ); ?>" />
									<input class="crrntl-continue-button crrntl-blue-button crrntl-checkout-button" type="submit" value="<?php _e( 'Continue to checkout', 'car-rental' ); ?>" />
									<input type="hidden" name="crrntl_selected_product" value="<?php echo $crrntl_selected_prod_id; ?>" />
									<input name="crrntl_form_extras_submit" type="hidden" value="submit">
								</div><!-- .crrntl-next-page -->
								<div class="clear"></div>
							</form>
						<?php } ?>
						<div class="clear"></div>
					</div><!-- .crrntl-content-area -->
					<aside class="sidebars-area crrntl-sidebar-info">
						<?php the_widget( 'Car_Rental_Order_Info_Widget' );
						dynamic_sidebar( 'sidebar-choose-extras' ); ?>
					</aside><!-- .sidebars-area .crrntl-sidebar-info -->
					<div class="clear"></div>
				</div><!-- .crrntl-with-form-search -->
			</div><!-- .site-content -->
			<div class="clear"></div>
		</div><!-- .content-area -->
		<div class="clear"></div>
	</div><!-- .main-content -->
<?php get_footer();