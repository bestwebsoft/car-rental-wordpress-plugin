<?php
/**
 * Template Name: Review & Book
 *
 * @subpackage Car Rental
 * @since      Car Rental 1.0.0
 */

global $crrntl_options, $wpdb, $crrntl_currency, $crrntl_selected_prod_id;

$crrntl_error = $message = $confirm_invalid = $personal_info_error = '';

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

$captcha_status = crrntl_get_related_plugin_status( 'captcha' );
$recaptcha_status = crrntl_get_related_plugin_status( 'recaptcha' );

if ( $crrntl_logged_in ) {
	$crrntl_current_user  = wp_get_current_user();

	$_SESSION['crrntl_user_id']    = $crrntl_current_user->ID;
	if ( ! isset( $_POST['crrntl_first_name'] ) && isset( $crrntl_current_user->user_firstname ) ) {
		$_SESSION['crrntl_first_name'] = $crrntl_current_user->user_firstname;
	}
	if ( ! isset( $_POST['crrntl_last_name'] ) && isset( $crrntl_current_user->user_lastname ) ) {
		$_SESSION['crrntl_last_name']  = $crrntl_current_user->user_lastname;
	}
	if ( ! isset( $_POST['crrntl_user_age'] ) && isset( $crrntl_current_user->user_age ) ) {
		$_SESSION['crrntl_user_age']   = $crrntl_current_user->user_age;
	}
	if ( ! isset( $_POST['crrntl_user_phone'] ) && isset( $crrntl_current_user->user_phone ) ) {
		$_SESSION['crrntl_user_phone'] = $crrntl_current_user->user_phone;
	}
}

if ( empty( $crrntl_selected_prod_id ) ) {
	$crrntl_error .= sprintf(
		'<a href="%1$s">%2$s</a><br />',
		( ! empty( $crrntl_options['car_page_id'] ) ) ? get_permalink( $crrntl_options['car_page_id'] ) : '',
		__( 'Please choose a Car', 'car-rental' )
	);
}

if ( empty( $_SESSION['crrntl_return_location'] ) ) {
	$_SESSION['crrntl_return_location'] = ! empty( $_SESSION['crrntl_location'] ) ? $_SESSION['crrntl_location'] : '';
}

if ( isset( $_POST['crrntl_form_save_order'] ) && wp_verify_nonce( $_POST['crrntl_nonce_name'], plugin_basename( __FILE__ ) ) ) {

	if ( 'outdated' != $captcha_status['active'] && $captcha_status['enabled'] ) {
		/* Checking Captcha answer */
		$cptch_error = apply_filters( 'cptch_verify', true, 'string', 'bws_carrental' );
		if ( true !== $cptch_error ) {
			/* the CAPTCHA answer is wrong or there are some other errors */
			$personal_info_error .= $cptch_error . '<br />';
		}
	}

	if ( 'outdated' != $recaptcha_status['active'] && $recaptcha_status['enabled'] ) {
		/* Checking Google Captcha answer */
		$check_result = apply_filters( 'gglcptch_verify_recaptcha', true, 'string' );
		if ( true !== $check_result ) {
			/* the CAPTCHA answer is wrong or there are some other errors */
			$personal_info_error .= $check_result . '<br />';
		}
	}

	$userdata = array();
	$userdata['first_name'] = $_SESSION['crrntl_first_name'] = ( isset( $_POST['crrntl_first_name'] ) ) ? sanitize_text_field( $_POST['crrntl_first_name'] ) : '';
	$userdata['last_name'] = $_SESSION['crrntl_last_name'] = ( isset( $_POST['crrntl_last_name'] ) ) ? sanitize_text_field( $_POST['crrntl_last_name'] ) : '';
	$userdata['user_age']   = $_SESSION['crrntl_user_age'] = ( isset( $_POST['crrntl_user_age'] ) ) ? $_POST['crrntl_user_age'] : '';
	$userdata['user_phone'] = $_SESSION['crrntl_user_phone'] = ( isset( $_POST['crrntl_user_phone'] ) ) ? sanitize_text_field( $_POST['crrntl_user_phone'] ) : '';

	$car_info = get_post_meta( $_SESSION['crrntl_selected_product_id'], 'car_info', true );
	$min_rent_age = ( isset( $car_info['min_age'] ) ) ? $car_info['min_age'] : $crrntl_options['min_age'];

	if ( isset( $_POST['crrntl_user_age'] ) && $_POST['crrntl_user_age'] < $min_rent_age ) {
		$personal_info_error .= __( 'Inappropriate age to submit the order.', 'car-rental' );
	}

	if ( ! $crrntl_logged_in && empty( $personal_info_error ) ) {
		if ( ! is_email( $_POST['crrntl_user_email'] ) ) {
			$personal_info_error .= __( 'Please enter correct e-mail address.', 'car-rental' ) . '<br />';
		} elseif ( $_POST['crrntl_user_email'] != $_POST['crrntl_confirm_user_email'] ) {
			$personal_info_error .= __( 'Please confirm your e-mail address.', 'car-rental' ) . '<br />';
			$confirm_invalid = ' class="crrntl-confirm-invalid"';
		}

		if ( email_exists( $_POST['crrntl_user_email'] ) ) {
			$user                          = get_user_by( 'email', $_POST['crrntl_user_email'] );
			$_SESSION['crrntl_user_email'] = $user->user_email;
			$_SESSION['crrntl_user_id']    = $user->ID;
		} else {
			$_SESSION['crrntl_user_email'] = ( isset( $_POST['crrntl_user_email'] ) ) ? sanitize_text_field( $_POST['crrntl_user_email'] ) : '';

			if ( empty( $crrntl_error ) ) {
				$random_password              = wp_generate_password( 20, false );
				$_SESSION['crrntl_user_id'] = wp_create_user( $_SESSION['crrntl_user_email'], $random_password, $_SESSION['crrntl_user_email'] );
				wp_new_user_notification( $_SESSION['crrntl_user_id'], null, 'both' );

				if ( has_filter( 'sbscrbr_checkbox_check' ) && ! empty( $_POST['sbscrbr_checkbox_subscribe'] ) ) {
					$sbscrbr_check = apply_filters( 'sbscrbr_checkbox_check', array(
						'email' => $_SESSION['crrntl_user_email'],
					) );
				}
			}
		}
	}

	if ( ! empty( $userdata ) && ! empty( $_SESSION['crrntl_user_id'] ) ) {
		foreach ( $userdata as $userdata_key => $userdata_value ) {
			update_user_meta( $_SESSION['crrntl_user_id'], $userdata_key, $userdata_value );
		}
	} elseif ( empty( $_SESSION['crrntl_user_id'] ) && empty( $personal_info_error ) ) {
		$crrntl_error .= __( 'Error during user creation', 'car-rental' ) . '<br />';
	}

	if ( empty( $crrntl_error ) && empty( $personal_info_error ) && empty( $_SESSION['crrntl_save_order'] ) ) {
		$crrntl_order_info = crrntl_save_reservation();
		$_SESSION['crrntl_save_order'] = true;
		if ( ! empty( $crrntl_order_info['error'] ) ) {
			$crrntl_error .= $crrntl_order_info['error'];
		} elseif ( ! empty( $crrntl_order_info['success'] ) ) {
			$_SESSION['crrntl_success_message'] = $crrntl_order_info['success'];
			wp_redirect( add_query_arg( 'display_order_info', '1' ) );
			exit();
		}
	}
} else {
	unset( $_SESSION['crrntl_save_order'] );
	if ( isset( $_GET['display_order_info'] ) && ! empty( $_SESSION['crrntl_success_message'] ) ) {
		$message = esc_html( $_SESSION['crrntl_success_message'] );
	} else {
		unset( $_SESSION['crrntl_success_message'] );
	}
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
						<a class="crrntl-progress-bar-step crrntl-done" href="<?php echo ( ! empty( $crrntl_options['car_page_id'] ) ) ? get_permalink( $crrntl_options['car_page_id'] ) : ''; ?>">
							<div class="crrntl-step-number">2</div>
							<div class="crrntl-step-name"><?php _e( 'Choose a car', 'car-rental' ); ?></div>
						</a><!-- .crrntl-progress-bar-step -->
						<a class="crrntl-progress-bar-step crrntl-done" href="<?php echo ( ! empty( $crrntl_options['extra_page_id'] ) ) ? get_permalink( $crrntl_options['extra_page_id'] ) : ''; ?>">
							<div class="crrntl-step-number">3</div>
							<div class="crrntl-step-name"><?php _e( 'Choose extras', 'car-rental' ); ?></div>
						</a><!-- .crrntl-progress-bar-step -->
						<div class="crrntl-progress-bar-step crrntl-last crrntl-current">
							<div class="crrntl-step-number">4</div>
							<div class="crrntl-step-name"><?php _e( 'Review &amp; Book', 'car-rental' ); ?></div>
						</div><!-- .crrntl-progress-bar-step -->
					</div><!-- #crrntl-progress-bar-steps -->
					<div class="clear"></div>
				</div><!-- #crrntl-progress-bar -->
				<div class="crrntl-without-form-search">
					<div class="crrntl-content-area crrntl-wrapper">
						<?php if ( ! empty( $crrntl_error ) ) { ?>
							<main id="content" class="crrntl-site-content">
								<article class="crrntl-review clearfix">
									<div class="crrntl-choose-car-message">
										<span>
											<?php echo $crrntl_error; ?>
										</span>
									</div>
								</article>
							</main>
						<?php } else { ?>
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
											if ( ! empty( $message ) ) { ?>
												<div class="crrntl-notice-message">
													<p><?php echo $message; ?></p>
													<a class="crrntl-from-order-page" href="<?php echo get_home_url(); ?>"><?php _e( 'Home Page', 'car-rental' ); ?></a>
													<a class="crrntl-from-order-page" href="<?php echo ( ! empty( $crrntl_options['car_page_id'] ) ) ? get_permalink( $crrntl_options['car_page_id'] ) : '' ?>"><?php _e( 'Choose Car', 'car-rental' ); ?></a>
												</div>
											<?php } else { ?>
												<h4><?php _e( 'Personal Information', 'car-rental' ); ?></h4>
												<div>
													<?php if ( ! empty( $personal_info_error ) ) {
														echo '<div class="crrntl-error-message">' . $personal_info_error . '</div>';
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
																<?php $car_info_book = get_post_meta( $_SESSION['crrntl_selected_product_id'], 'car_info', true );
																$min_rent_age = ( isset( $car_info_book['min_age'] ) ) ? $car_info_book['min_age'] : $crrntl_options['min_age'];
																for ( $i = $min_rent_age; $i <= 100; $i ++ ) {
																	printf(
																		'<option value="%1$s" %2$s>%1$s</option>',
																		$i,
																		selected(
																			(
																				( empty( $_SESSION['crrntl_user_age'] ) && $min_rent_age == $i ) ||
																				( ! empty( $_SESSION['crrntl_user_age'] ) && $i == $_SESSION['crrntl_user_age'] )
																			),
																			true,
																			false
																		)
																	);
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
													<?php /* Todo: implement new field */ ?>
													<!-- <div class="crrntl-form-element crrntl_additional_info">
														<div><?php _e( 'Additional information', 'car-rental' ); ?></div>
														<textarea name="crrntl_additional_info" id="crrntl_additional_info" rows="3"></textarea>
													</div> -->
													<div class="clear"></div>
													<?php if ( has_filter( 'sbscrbr_checkbox_add' ) ) {
														$sbscrbr_checkbox = apply_filters( 'sbscrbr_checkbox_add', false );
														if ( isset( $sbscrbr_checkbox['content'] ) ) { ?>
															<div class="crrntl-form-element">
																<?php echo $sbscrbr_checkbox['content']; ?>
															</div>
															<div class="clear"></div>
														<?php }
													}
													if( ! empty( $crrntl_options['gdpr'] ) ) { ?>
														<div class="crrntl-form-element gdpr-form-element">
															<p class="crrntl-GDPR-wrap">
																<label for="crrntl-GDPR-checkbox">
																	<input id="crrntl-GDPR-checkbox" required type="checkbox" name="crrntl_GDPR" style="vertical-align: middle;"/>
																	<?php echo $crrntl_options['gdpr_cb_name'];
																	if( ! empty( $crrntl_options['gdpr_link'] ) ) { ?>
																		<a style="text-decoration: underline;" target="_blank" href="<?php echo $crrntl_options['gdpr_link']; ?>"><?php echo $crrntl_options['gdpr_text']; ?></a>
																	<?php } else { ?>
																		<span><?php echo $crrntl_options['gdpr_text']; ?></span>
																	<?php } ?>
																</label>
															</p>
														</div>
													<?php }
													if ( 'outdated' != $captcha_status['active'] && $captcha_status['enabled'] ) { ?>
														<div class="crrntl-captcha-field">
															<?php echo apply_filters( 'cptch_display', '', 'bws_carrental' ); ?>
														</div>
														<div class="clear"></div>
													<?php }
													if ( 'outdated' != $recaptcha_status['active'] && $recaptcha_status['enabled'] ) { ?>
														<div class="crrntl-recaptcha-field">
															<?php echo apply_filters( 'gglcptch_display_recaptcha', '', 'carrental_form' ); ?>
														</div>
														<div class="clear"></div>
													<?php } ?>
												</div>
											<?php } ?>
										</article><!-- .crrntl-review -->
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
						<?php } ?>
						<div class="clear"></div>
					</div><!-- .crrntl-content-area -->
					<aside class="sidebars-area crrntl-sidebar-info">
						<?php the_widget( 'Car_Rental_Order_Info_Widget' );
						dynamic_sidebar( 'sidebar-review' ); ?>
					</aside><!-- .sidebars-area .crrntl-sidebar-info -->
					<div class="clear"></div>
				</div><!-- .crrntl-with-form-search -->
			</div><!-- .site-content -->
			<div class="clear"></div>
		</div><!-- .content-area -->
		<div class="clear"></div>
	</div><!-- .main-content -->
<?php get_footer();
if ( isset( $_GET['display_order_info'] ) ) {
	unset(
		$_SESSION['crrntl_date_from'],
		$_SESSION['crrntl_total'],
		$_SESSION['crrntl_opted_extras'],
		$_SESSION['crrntl_extra_quantity'],
		$_SESSION['crrntl_return_location'],
		$_SESSION['crrntl_date_to'],
		$_SESSION['crrntl_time_from'],
		$_SESSION['crrntl_time_to'],
		$_SESSION['crrntl_location'],
		$_SESSION['crrntl_select_carclass'],
		$_SESSION['crrntl_checkbox_location'],
		$_SESSION['crrntl_selected_product_id']
	);
}
