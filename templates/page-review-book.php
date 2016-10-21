<?php
/**
 * Template Name: Review & Book
 *
 * @subpackage Car Rental 
 * @since      Car Rental 1.0.0
 */

get_header(); ?>
	<div class="main-content">
		<div class="content-area">
			<div class="site-content">
				<?php if ( ! function_exists( 'is_plugin_active' ) )
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

				/*if the plugin Car Rental active */
				if ( is_plugin_active( 'car-rental/car-rental.php' ) ) {
					global $crrntl_options, $wpdb, $crrntl_currency, $crrntl_selected_prod_id;

					$crrntl_error = $message = $confirm_invalid = '';					

					if ( empty( $crrntl_options ) )
						$crrntl_options = get_option( 'crrntl_options' );

					if ( empty( $crrntl_options['custom_currency'] ) || 0 == $crrntl_options['currency_custom_display'] ) {
						$crrntl_currency = $wpdb->get_var( "SELECT currency_unicode FROM {$wpdb->prefix}crrntl_currency WHERE currency_id = {$crrntl_options['currency_unicode']}" );
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

					$crrntl_logged_in = is_user_logged_in();

					if ( $crrntl_logged_in ) {
						$crrntl_current_user  = wp_get_current_user();

						$_SESSION['crrntl_user_id']    = $crrntl_current_user->ID;
						if ( ! isset( $_POST['crrntl_first_name'] ) && isset( $crrntl_current_user->user_firstname ) )
							$_SESSION['crrntl_first_name'] = $crrntl_current_user->user_firstname;
						if ( ! isset( $_POST['crrntl_last_name'] ) && isset( $crrntl_current_user->user_lastname ) )
							$_SESSION['crrntl_last_name']  = $crrntl_current_user->user_lastname;
						if ( ! isset( $_POST['crrntl_user_age'] ) && isset( $crrntl_current_user->user_age ) )
							$_SESSION['crrntl_user_age']   = $crrntl_current_user->user_age;
						if ( ! isset( $_POST['crrntl_user_phone'] ) && isset( $crrntl_current_user->user_phone ) )
							$_SESSION['crrntl_user_phone'] = $crrntl_current_user->user_phone;						
					}

					if ( isset( $_POST['crrntl_form_save_order'] ) && wp_verify_nonce( $_POST['crrntl_nonce_name'], plugin_basename( __FILE__ ) ) ) {						

						if ( empty( $_SESSION['crrntl_selected_product_id'] ) ) {
							$crrntl_error .= __( 'Please, choose a Car', 'car-rental' ) . '<br />';
						}
						if ( empty( $_SESSION['crrntl_location'] ) ) {
							$crrntl_error .= __( 'Please, choose location', 'car-rental' ) . '<br />';
						}
						if ( empty( $_SESSION['crrntl_return_location'] ) ) {
							$_SESSION['crrntl_return_location'] = ! empty( $_SESSION['crrntl_location'] ) ? $_SESSION['crrntl_location'] : '';
						}
						if ( empty( $_SESSION['crrntl_date_from'] ) || empty( $_SESSION['crrntl_time_from'] ) ) {
							if ( empty( $_SESSION['crrntl_date_from'] ) ) {
								$crrntl_error .= __( 'Please, choose Pick-Up date', 'car-rental' ) . '<br />';
							}
							if ( empty( $_SESSION['crrntl_time_from'] ) ) {
								$crrntl_error .= __( 'Please, choose Pick-Up time', 'car-rental' ) . '<br />';
							}
						} else {
							$date_from = strtotime( $_SESSION['crrntl_date_from'] . ' ' . $_SESSION['crrntl_time_from'] );
							if ( crrntl_check_date_format( $_SESSION['crrntl_date_from'] ) === false ) {
								$crrntl_error .= __( 'Please, choose correct Pick-Up datetime in format "YYYY-MM-DD"', 'car-rental' ) . '<br />';
							} elseif ( $date_from <= time() ) {
								$crrntl_error .= __( 'Please, choose correct Pick-Up datetime', 'car-rental' ) . '<br />';
							}
						}
						if ( empty( $_SESSION['crrntl_date_to'] ) || empty( $_SESSION['crrntl_time_to'] ) ) {
							if ( empty( $_SESSION['crrntl_date_to'] ) ) {
								$crrntl_error .= __( 'Please, choose Drop-Off date', 'car-rental' ) . '<br />';
							}
							if ( empty( $_SESSION['crrntl_time_to'] ) ) {
								$crrntl_error .= __( 'Please, choose Drop-Off time', 'car-rental' ) . '<br />';
							}
						} else {
							$date_to = strtotime( $_SESSION['crrntl_date_to'] . ' ' . $_SESSION['crrntl_time_to'] );
							if ( crrntl_check_date_format( $_SESSION['crrntl_date_to'] ) === false ) {
								$crrntl_error .= __( 'Please, choose correct Drop-Off datetime in format "YYYY-MM-DD"', 'car-rental' ) . '<br />';
							} elseif ( ( isset( $date_from ) && $date_to <= $date_from ) || $date_to <= time() ) {
								$crrntl_error .= __( 'Please, choose correct Drop-Off datetime', 'car-rental' ) . '<br />';
							}
						}
						if ( empty( $_SESSION['crrntl_total'] ) ) {
							$crrntl_error .= __( 'Error in total calculate', 'car-rental' ) . '<br />';
						}

						$userdata = array();
						$userdata['first_name'] = $_SESSION['crrntl_first_name'] = ( isset( $_POST['crrntl_first_name'] ) ) ? sanitize_text_field( $_POST['crrntl_first_name'] ) : '';
						$userdata['last_name'] = $_SESSION['crrntl_last_name'] = ( isset( $_POST['crrntl_last_name'] ) ) ? sanitize_text_field( $_POST['crrntl_last_name'] ) : '';
						$userdata['user_age']   = $_SESSION['crrntl_user_age'] = ( isset( $_POST['crrntl_user_age'] ) ) ? $_POST['crrntl_user_age'] : '';
						$userdata['user_phone'] = $_SESSION['crrntl_user_phone'] = ( isset( $_POST['crrntl_user_phone'] ) ) ? sanitize_text_field( $_POST['crrntl_user_phone'] ) : '';
						
						if ( ! $crrntl_logged_in ) {
							if ( ! is_email( $_POST['crrntl_user_email'] ) ) {
								$crrntl_error .= __( 'Please, enter correct e-mail address.', 'car-rental' ) . '<br />';
							} elseif ( $_POST['crrntl_user_email'] != $_POST['crrntl_confirm_user_email'] ) {
								$crrntl_error .= __( 'Please, confirm your e-mail address.', 'car-rental' ) . '<br />';
								$confirm_invalid = ' class="crrntl-confirm-invalid"';
							}

							if ( email_exists( $_POST['crrntl_user_email'] ) ) {
								$user                          = get_user_by( 'email', $_POST['crrntl_user_email'] );
								$_SESSION['crrntl_user_email'] = $user->user_email;
								$_SESSION['crrntl_user_id']    = $user->ID;
							} else {
								$_SESSION['crrntl_user_email'] = ( isset( $_POST['crrntl_user_email'] ) ) ? sanitize_text_field( $_POST['crrntl_user_email'] ) : '';

								if ( empty( $crrntl_error ) ) {
									if ( has_filter( 'sbscrbr_checkbox_check' ) && ! empty( $_POST['sbscrbr_checkbox_subscribe'] ) ) {
										$sbscrbr_check = apply_filters( 'sbscrbr_checkbox_check', array(
											'email' => $_SESSION['crrntl_user_email'],
										) );
										if ( isset( $sbscrbr_check['response'] ) ) {
											echo $sbscrbr_check['response']['message'];
										}
									} else {
										$random_password              = wp_generate_password( 20, false );
										$_SESSION['crrntl_user_id'] = wp_create_user( $_SESSION['crrntl_user_email'], $random_password, $_SESSION['crrntl_user_email'] );

										wp_new_user_notification( $_SESSION['crrntl_user_id'], $random_password, 'both' );
									}
								}
							}
						}

						if ( ! empty( $userdata ) && ! empty( $_SESSION['crrntl_user_id'] ) ) {
							foreach ( $userdata as $userdata_key => $userdata_value ) {
								update_user_meta( $_SESSION['crrntl_user_id'], $userdata_key, $userdata_value );
							}
						} elseif ( empty( $_SESSION['crrntl_user_id'] ) && empty( $crrntl_error ) ) {
							$crrntl_error .= __( 'Error in user create', 'car-rental' ) . '<br />';
						}

						if ( empty( $crrntl_error ) && empty( $_SESSION['crrntl_save_order'] ) ) {
							$crrntl_order_info = crrntl_save_reservation();
							if ( ! empty( $crrntl_order_info['error'] ) ) {
								$crrntl_error .= $crrntl_order_info['error'];
							} elseif ( ! empty( $crrntl_order_info['success'] ) ) {
								$message .= $crrntl_order_info['success'];
							}
						}
					} ?>
					<div id="crrntl-progress-bar">
						<div id="crrntl-progress-bar-steps">
							<a href="<?php echo home_url(); ?>">
								<div class="crrntl-progress-bar-step crrntl-done">
									<div class="crrntl-step-number">1</div>
									<div class="crrntl-step-name"><?php _e( 'Create request', 'car-rental' ); ?></div>
								</div><!-- .crrntl-progress-bar-step -->
							</a>
							<a href="<?php echo ( ! empty( $crrntl_options['car_page_id'] ) ) ? get_permalink( $crrntl_options['car_page_id'] ) : ''; ?>">
								<div class="crrntl-progress-bar-step crrntl-done">
									<div class="crrntl-step-number">2</div>
									<div class="crrntl-step-name"><?php _e( 'Choose a car', 'car-rental' ); ?></div>
								</div><!-- .crrntl-progress-bar-step -->
							</a>
							<a href="<?php echo ( ! empty( $crrntl_options['extra_page_id'] ) ) ? get_permalink( $crrntl_options['extra_page_id'] ) : ''; ?>">
								<div class="crrntl-progress-bar-step crrntl-done">
									<div class="crrntl-step-number">3</div>
									<div class="crrntl-step-name"><?php _e( 'Choose extras', 'car-rental' ); ?></div>
								</div><!-- .crrntl-progress-bar-step -->
							</a>
							<div class="crrntl-progress-bar-step crrntl-last crrntl-current">
								<div class="crrntl-step-number">4</div>
								<div class="crrntl-step-name"><?php _e( 'Review &amp; Book', 'car-rental' ); ?></div>
							</div><!-- .crrntl-progress-bar-step -->
						</div><!-- #crrntl-progress-bar-steps -->
						<div class="clear"></div>
					</div><!-- #crrntl-progress-bar -->
					<div class="crrntl-without-form-search">
						<div class="crrntl-content-area">
							<form id="crrntl-user-info" method="post" action="">
								<main id="content" class="crrntl-site-content">
									<header>
										<div class="crrntl-result-title">
											<div>
												<img src="<?php echo $crrntl_plugin_directory . '/images/list.png'; ?>" alt="" />
												<?php _e( 'Complete reservation form', 'car-rental' ); ?>
											</div>
											<div class="clear"></div>
										</div><!-- .crrntl-result-title -->
									</header>
									<?php while ( have_posts() ) : the_post(); ?>
										<article class="crrntl-review clearfix">
											<?php if ( ! empty( $post->post_content ) ) { ?>
												<h4><?php _e( 'Note', 'car-rental' ); ?></h4>
												<div>
													<?php echo $post->post_content; ?>
												</div><!-- .crrntl-product-details -->
											<?php }
											if ( ! empty( $message ) ) {
												echo '<div class="crrntl-notice-message">' . $message . '</div>';
												$_SESSION['crrntl_save_order'] = true;
											} else { ?>
	
												<h4><?php _e( 'Personal Information', 'car-rental' ); ?></h4>	
												<div>
													<?php if ( ! empty( $crrntl_error ) ) {
														echo '<div class="crrntl-error-message">' . $crrntl_error . '</div>';
													} ?>
													<div class="crrntl-form-element">
														<div><?php _e( 'First name', 'car-rental' ); ?></div>
														<input type="text" value="<?php echo ( ! empty( $_SESSION['crrntl_first_name'] ) ) ? $_SESSION['crrntl_first_name'] : ''; ?>" placeholder="<?php _e( 'Enter your first name', 'car-rental' ); ?>" name="crrntl_first_name" required="required" />
													</div>
													<div class="crrntl-form-element">
														<div><?php _e( 'Last name', 'car-rental' ); ?></div>
														<input type="text" value="<?php echo ( ! empty( $_SESSION['crrntl_last_name'] ) ) ? $_SESSION['crrntl_last_name'] : ''; ?>" placeholder="<?php _e( 'Enter your last name', 'car-rental' ); ?>" name="crrntl_last_name" required="required" />
													</div>
													<div class="crrntl-form-element">
														<div><?php _e( 'Age', 'car-rental' ); ?></div>
														<div class="crrntl-user-age">
															<select name="crrntl_user_age" title="<?php _e( 'Choose your age', 'car-rental' ); ?>" required="required">
																<?php for ( $i = 16; $i <= 100; $i ++ ) {
																	if ( ! empty( $_SESSION['crrntl_user_age'] ) && $i == $_SESSION['crrntl_user_age'] ) {
																		echo '<option value="' . $i . '" selected="selected">' . $i . '</option>';
																	} else {
																		echo '<option value="' . $i . '">' . $i . '</option>';
																	}
																} ?>
															</select>
														</div>
													</div>
													<div class="clear"></div>
													<?php if ( ! $crrntl_logged_in ) { ?>
														<div class="crrntl-form-element">
															<div><?php _e( 'Email address', 'car-rental' ); ?></div>
															<input type="email" value="<?php echo ( ! empty( $_SESSION['crrntl_user_email'] ) ) ? $_SESSION['crrntl_user_email'] : ''; ?>" placeholder="<?php _e( 'Enter your email address', 'car-rental' ); ?>" name="crrntl_user_email" required="required" />
														</div>
														<div class="crrntl-form-element">
															<div><?php _e( 'Confirm email address', 'car-rental' ); ?></div>
															<input type="email" value="<?php echo ( ! empty( $_SESSION['crrntl_confirm_user_email'] ) ) ? $_SESSION['crrntl_confirm_user_email'] : ''; ?>" placeholder="<?php _e( 'Confirm your email address', 'car-rental' ); ?>" name="crrntl_confirm_user_email" required="required"<?php echo $confirm_invalid; ?> />
														</div>
														<div class="clear"></div>
													<?php } ?>
													<div class="crrntl-form-element">
														<div><?php _e( 'Phone number', 'car-rental' ); ?></div>
														<input type="tel" value="<?php echo ( ! empty( $_SESSION['crrntl_user_phone'] ) ) ? $_SESSION['crrntl_user_phone'] : ''; ?>" placeholder="<?php _e( 'Enter your phone number', 'car-rental' ); ?>" name="crrntl_user_phone" required="required" />
													</div>
													<div class="clear"></div>
													<div class="crrntl-form-element">
														<?php if ( has_filter( 'sbscrbr_checkbox_add' ) ) {
															$sbscrbr_checkbox = apply_filters( 'sbscrbr_checkbox_add', false );
															if ( isset( $sbscrbr_checkbox['content'] ) ) {
																echo $sbscrbr_checkbox['content'];
															}
														} ?>
													</div>
													<div class="clear"></div>
												</div>
											<?php $_SESSION['crrntl_save_order'] = false;
											} ?>
										</article><!-- .crrntl-extra -->
						<?php endwhile; ?>
									<div class="clear"></div>
								</main><!-- #content -->
								<div class="crrntl-next-page">
									<?php if ( empty( $message ) ) { ?>
										<input class="crrntl-continue-button crrntl-blue-button crrntl-checkout-button" type="submit" value="<?php _e( 'Book Now', 'car-rental' ); ?>">
										<?php if ( ! $crrntl_logged_in ) { ?>
											<input name="crrntl_form_save_order" type="hidden" value="1">
										<?php } else { ?>
											<input name="crrntl_form_save_order" type="hidden" value="0">
										<?php }
										wp_nonce_field( plugin_basename( __FILE__ ), 'crrntl_nonce_name' );
									} ?>
								</div>
								<div class="clear"></div>
							</form>
							<div class="clear"></div>
						</div><!-- .crrntl-content-area -->
						<aside class="sidebars-area crrntl-sidebar-info">
							<?php the_widget( 'Car_Rental_Order_Info_Widget' );
							dynamic_sidebar( 'sidebar-review' ); ?>
						</aside><!-- .sidebars-area .crrntl-sidebar-info -->
						<div class="clear"></div>
					</div><!-- .crrntl-with-form-search -->
				<?php } else { ?>
					<div>
						<p><?php _e( 'Plugin "Car Rental" is not activated', 'car-rental' ); ?></p>
					</div>
				<?php } ?>
			</div><!-- .site-content -->
		</div><!-- .content-area -->
	</div><!-- .main-content -->
<?php get_footer();
