<?php
/**
 * Functions for displaying Slider on Home page
 * @subpackage Car Rental
 * @since      Car Rental 1.0.0
 */

/**
 * Include necessary css-files
 * @return void
 */
if ( ! function_exists( 'crrntl_slider_styles' ) ) {
	function crrntl_slider_styles() {
		global $crrntl_plugin_info, $crrntl_is_carrental_template;
		if ( empty( $crrntl_is_carrental_template ) ) {
			wp_enqueue_style( 'crrntl-slider-style', plugins_url( 'css/slider.css', dirname( __FILE__ ) ), array(), $crrntl_plugin_info['Version'] );
			wp_enqueue_style( 'crrntl-animate-style', plugins_url( 'css/animate.css', dirname( __FILE__ ) ), array(), $crrntl_plugin_info['Version'] );
			wp_enqueue_style( 'crrntl-owl-carousel-style', plugins_url( 'css/owl.carousel.css', dirname( __FILE__ ) ), array(), $crrntl_plugin_info['Version'] );
			wp_enqueue_style( 'crrntl-owl-theme-style', plugins_url( 'css/owl.theme.default.css', dirname( __FILE__ ) ), array(), $crrntl_plugin_info['Version'] );
		}
	}
}

/**
 * Include necessary javascript
 * @return void
 */
if ( ! function_exists( 'crrntl_slider_scripts' ) ) {
	function crrntl_slider_scripts() {
		global $crrntl_is_main_page, $crrntl_plugin_info, $crrntl_is_carrental_template;
		if (
			is_home() ||
			is_front_page() ||
			is_page_template( 'page-homev1.php' ) ||
			is_page_template( 'page-homev2.php' ) ||
			is_page_template( 'page-homev3.php' ) ||
			( ! empty( $crrntl_is_main_page ) && empty( $crrntl_is_carrental_template ) )
		) {
			wp_enqueue_script( 'crrntl-owl-carousel-script', plugins_url( 'js/owl.carousel.js', dirname( __FILE__ ) ), array( 'jquery' ), $crrntl_plugin_info['Version'] );
		}
	}
}

if ( ! function_exists( 'crrntl_slider_settings' ) ) {
	function crrntl_slider_settings() {
		global $title, $crrntl_options, $crrntl_slide_data, $crrntl_slider_options;
		$message = $error = '';

		$theme = wp_get_theme();

		if ( ! get_option( 'crrntl_slider_options' ) ) {
			if ( $theme['Name'] == 'Renty' ) {
				$slider_options = get_option( 'renty_slider_options' );
				add_option( 'crrntl_slider_options', $slider_options );
			} else {
				add_option( 'crrntl_slider_options' );
			}
		}
		$crrntl_slider_options = get_option( 'crrntl_slider_options' );
		if (is_string ($crrntl_slider_options) ) {
			$crrntl_slider_options = explode(" ", $crrntl_slider_options);
		}


		$action_query = add_query_arg( array(
				'post_type'		=> $crrntl_options['post_type_name'],
				'page'			=> 'car-rental-slider-settings'
			),
			get_admin_url( null, 'edit.php' )
		); ?>
		<div class="wrap crrntl-wrap">
			<h1><?php echo $title; ?></h1>
			<?php /* handle request from action links */
			if ( isset( $_GET['action'] ) ) {
				switch ( $_GET['action'] ) {
					case 'add_slide':
						$crrntl_slide_data['image'] = $crrntl_slide_data['title'] = $crrntl_slide_data['description'] = $crrntl_slide_data['link'] = '';
						break;
					case 'edit_slide':
						if ( isset( $crrntl_slider_options[ $_GET['slide_id'] ] ) ) {
							$crrntl_slide_data['image']       = $crrntl_slider_options[ $_GET['slide_id'] ]['image'];
							$crrntl_slide_data['title']       = $crrntl_slider_options[ $_GET['slide_id'] ]['title'];
							$crrntl_slide_data['description'] = $crrntl_slider_options[ $_GET['slide_id'] ]['description'];
							$crrntl_slide_data['link']        = $crrntl_slider_options[ $_GET['slide_id'] ]['link'];
						}
						/* update slide data */
						if ( isset( $_POST['crrntl_save_slide'] ) && isset( $crrntl_slider_options[ $_GET['slide_id'] ] ) && check_admin_referer( plugin_basename( __FILE__ ), 'crrntl_nonce_name' ) ) {
							if ( ! empty( $_POST['crrntl_slide_url'] ) ) {
								$crrntl_slide_data['image']                 = esc_url_raw( $_POST['crrntl_slide_url'] );
								$crrntl_slide_data['title']                 = ( isset( $_POST['crrntl_slide_title'] ) ) ? sanitize_text_field( $_POST['crrntl_slide_title'] ) : $crrntl_slide_data['title'];
								$crrntl_slide_data['description']           = ( isset( $_POST['crrntl_slide_desc'] ) ) ? sanitize_text_field( $_POST['crrntl_slide_desc'] ) : $crrntl_slide_data['description'];
								$crrntl_slide_data['link']                  = ( isset( $_POST['crrntl_slide_link'] ) ) ? esc_url_raw( $_POST['crrntl_slide_link'] ) : $crrntl_slide_data['link'];
								$crrntl_slider_options[ $_GET['slide_id'] ] = $crrntl_slide_data;
								update_option( 'crrntl_slider_options', $crrntl_slider_options );
								$message .= __( 'Slide settings saved.', 'car-rental' );
							} elseif ( empty( $_POST['crrntl_slide_url'] ) ) {
								$error .= __( 'Please choose new image for slide. Settings not saved.', 'car-rental' );
							}
						}
						/* add new slide data */
						if ( isset( $_POST['crrntl_save_slide'] ) && ! isset( $crrntl_slider_options[ $_GET['slide_id'] ] ) && check_admin_referer( plugin_basename( __FILE__ ), 'crrntl_nonce_name' ) ) {
							if ( ! empty( $_POST['crrntl_slide_url'] ) ) {
								$crrntl_slide_data['image']       = esc_url_raw( $_POST['crrntl_slide_url'] );
								$crrntl_slide_data['title']       = isset( $_POST['crrntl_slide_title'] ) ? sanitize_text_field( $_POST['crrntl_slide_title'] ) : '';
								$crrntl_slide_data['description'] = isset( $_POST['crrntl_slide_desc'] ) ? sanitize_text_field( $_POST['crrntl_slide_desc'] ) : '';
								$crrntl_slide_data['link']        = isset( $_POST['crrntl_slide_link'] ) ? esc_url_raw( $_POST['crrntl_slide_link'] ) : '';
								$crrntl_slider_options []         = $crrntl_slide_data;
								update_option( 'crrntl_slider_options', $crrntl_slider_options );
								$message .= __( 'Slide was successfully added.', 'car-rental' );
							} elseif ( empty( $_POST['crrntl_slide_url'] ) ) {
								$error .= __( 'Please choose image for slide. Settings not saved.', 'car-rental' );
							}
						}
						break;
					case 'delete_slide':
						$crrntl_slide_data['image'] = $crrntl_slide_data['title'] = $crrntl_slide_data['description'] = $crrntl_slide_data['link'] = '';
						if ( isset( $crrntl_slider_options[ $_GET['slide_id'] ] ) ) {
							unset( $crrntl_slider_options[ $_GET['slide_id'] ] );
							$message .= __( 'Slide was successfully deleted', 'car-rental' );
						}
						update_option( 'crrntl_slider_options', $crrntl_slider_options );
						break;
					case 'delete_slide_admin':
						if ( isset( $crrntl_slider_options[ $_GET['slide_id'] ] ) ) {
							unset( $crrntl_slider_options[ $_GET['slide_id'] ] );
							$message .= __( 'Slide was successfully deleted', 'car-rental' );
						}
						update_option( 'crrntl_slider_options', $crrntl_slider_options );
						break;
					default:
						break;
				}
				if ( wp_get_theme() == 'Renty' ) {
					$crrntl_theme_slider_options = get_option( 'renty_slider_options' );
					if ( false === $crrntl_slider_options && false !== $crrntl_theme_slider_options ) {
						delete_option( 'renty_slider_options' );
					} elseif ( false !== $crrntl_slider_options ) {
						update_option( 'renty_slider_options', $crrntl_slider_options );
					}
				}
			}
			$new_slide_id = ( ! empty( $crrntl_slider_options ) ) ? max( array_keys( $crrntl_slider_options ) ) + 1 : 0; ?>
			<noscript>
				<div class="error below-h2">
					<p><strong><?php _e( 'Please enable JavaScript in your browser for fully functional work of the plugin.', 'car-rental' ); ?></strong></p>
				</div>
			</noscript>
			<?php $crrntl_current_theme = wp_get_theme();
			if ( 'Renty' != $crrntl_current_theme ) { ?>
				<div class="notice notice-info below-h2">
					<p><?php _e( 'If you want to display the slider on your homepage, paste the following strings into the template source code', 'car-rental' ); ?>: <b>&lt;?php do_action( "crrntl_display_slider" ); ?&gt;</b></p>
					<p><?php _e( 'If you want to display the slider on any page (e.g. that uses some custom template), paste the following strings into the source code of the corresponding template', 'car-rental' ); ?>: <b>&lt;?php do_action( "crrntl_display_slider_custom" ); ?&gt;</b></p>
				</div>
			<?php } ?>
			<div class="error below-h2" <?php echo ( empty( $error ) ) ? 'style="display:none"' : ''; ?>>
				<p><strong><?php echo $error; ?></strong></p>
			</div><!-- .error.below-h2 -->
			<div class="updated fade below-h2" <?php echo ( empty( $message ) ) ? 'style="display:none"' : ''; ?>>
				<p><strong><?php echo $message; ?></strong></p>
			</div><!-- .updated.fade below-h2 -->

			<?php if ( ! isset( $_GET['action'] ) || 'delete_slide_admin' == $_GET['action'] ) { ?>
				<form id="crrntl-settings-slider-form" method="post" action="<?php echo $action_query; ?>">
					<div id="crrntl-slides-list" class="crrntl-slider-settings">
						<h3 class="crrntl-header">
							<?php _e( 'List of Slides', 'car-rental' ); ?>
						</h3><!-- .crrntl-header -->
						<div id="crrntl-slider-list" class="crrntl-slider owl-carousel">
							<?php if ( ! empty( $crrntl_slider_options ) ) {
								foreach ( $crrntl_slider_options as $id => $option ) { ?>
									<div class="crrntl-slider-item">
										<a class="crrntl-slider-item-delete" href="<?php echo add_query_arg( array( 'action' => 'delete_slide_admin', 'slide_id' => $id ), $action_query ); ?>" title="<?php _e( 'Delete Slide', 'car-rental' ); ?>"></a>
										<span>
											<strong>
												<i><?php echo ( ! empty( $option['title'] ) ) ? $option['title'] : __( '- This slide has no title -', 'car-rental' ); ?></i>
											</strong>
										</span>
										<div class="crrntl-slider-item-edit">
											<a href="<?php echo add_query_arg( array( 'action' => 'edit_slide', 'slide_id' => $id ), $action_query ); ?>">
												<span><?php _e( 'Edit', 'car-rental' ); ?></span>
											</a>
											<?php if ( ! empty( $option['image'] ) ) { ?>
												<img class="crrntl-uploaded-image" src="<?php echo $option['image']; ?>" alt="<?php echo 'Slide ' . ( $id + 1 ); ?>">
											<?php } ?>
										</div>
									</div><!-- .crrntl-slider-item -->
								<?php }
							} ?>
							<div class="crrntl-slider-item crrntl-add-slide">
								<a href="<?php echo add_query_arg( array( 'action' => 'add_slide', 'slide_id' => $new_slide_id ), $action_query ); ?>">
									<span><?php _e( 'Add Slide', 'car-rental' ); ?></span>
								</a>
							</div>
						</div><!-- #crrntl-slider-list -->
					</div><!-- #crrntl-slides-list .crrntl-slider-settings -->
				</form><!-- #crrntl-settings-slider-form -->
			<?php } else { ?>
				<ul class="subsubsub">
					<?php  if ( 'edit_slide' == $_GET['action'] ) { ?>
						<li>
							<a id="crrntl-add-slide-link" href="<?php echo add_query_arg( array( 'action' => 'add_slide', 'slide_id' => $new_slide_id ), $action_query ); ?>"><?php echo __( 'Add New Slide', 'car-rental' ); ?></a> |
						</li>
						<li>
							<a id="crrntl-delete-slide-link" href="<?php echo add_query_arg( array( 'action' => 'delete_slide', 'slide_id' => $_GET['slide_id'] ), $action_query ); ?>"><?php echo __( 'Delete Slide', 'car-rental' ); ?></a> |
						</li>
					<?php } ?>
					<li>
						<a id="crrntl-back-to-slide-link" href="<?php echo $action_query; ?>"><?php _e( 'Back to Slider Settings', 'car-rental' ); ?></a>
					</li>
				</ul><!-- .subsubsub -->
				<div class="clear"></div>
				<form method="post" action="<?php echo add_query_arg( array( 'action' => 'edit_slide', 'slide_id' => ( 'edit_slide' == $_GET['action'] ) ? $_GET['slide_id'] : $new_slide_id ), $action_query ); ?>" class="crrntl-edit-slide-form">
					<div class="crrntl-setting-item">
						<table id="crrntl-slide-image" class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row"><?php _e( 'Image', 'car-rental' ); ?></th>
									<td>
										<?php if ( 'edit_slide' == $_GET['action'] && ! empty( $crrntl_slide_data['image'] ) ) { ?>
											<img class="crrntl-uploaded-image" src="<?php echo $crrntl_slide_data['image']; ?>" alt="<?php _e( 'Slide Image', 'car-rental' ); ?>">
											<p id="crrntl-no-image" class="crrntl-hidden"><?php _e( 'No image chosen', 'car-rental' ); ?></p>
										<?php } else { ?>
											<p id="crrntl-no-image"><?php _e( 'No image chosen', 'car-rental' ); ?></p>
										<?php } ?>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="crrntl-slider-image"><?php _e( 'Image URL', 'car-rental' ); ?></label>
									</th>
									<td>
										<input id="crrntl-slider-image" class="crrntl-image-url" type="url" name="crrntl_slide_url" value="<?php echo $crrntl_slide_data['image']; ?>" placeholder="http://example.com" /><br />
										<input class="button-secondary crrntl-upload-image" type="button" value="<?php echo ( 'add_slide' == $_GET['action'] ) ? __( 'Upload Image', 'car-rental' ) : __( 'Change Image', 'car-rental' ); ?>">
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="crrntl-slide-title"><?php _e( 'Slide Title', 'car-rental' ); ?></label>
									</th>
									<td>
										<input id="crrntl-slide-title" type="text" name="crrntl_slide_title" value="<?php echo $crrntl_slide_data['title']; ?>" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="crrntl-slide-description"><?php _e( 'Slide Description', 'car-rental' ); ?></label>
									</th>
									<td>
										<textarea id="crrntl-slide-description" name="crrntl_slide_desc"><?php echo $crrntl_slide_data['description']; ?></textarea>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="crrntl-slide-link"><?php _e( 'Slide Link', 'car-rental' ); ?></label>
									</th>
									<td>
										<input id="crrntl-slide-link" type="url" name="crrntl_slide_link" value="<?php echo $crrntl_slide_data['link']; ?>" placeholder="http://example.com" />
									</td>
								</tr>
							</tbody>
						</table><!-- #crrntl-slide-image .form-table -->
					</div><!-- .crrntl-setting-item -->
					<input type="hidden" name="crrntl_save_slide" value="submit">
					<?php wp_nonce_field( plugin_basename( __FILE__ ), 'crrntl_nonce_name' );
					if ( 'add_slide' == $_GET['action'] || 'delete_slide' == $_GET['action'] ) { ?>
						<div class="submit">
							<input class="button-primary crrntl-save-slide" type="submit" value="<?php _e( 'Add Slide', 'car-rental' ); ?>">
						</div><!-- .submit -->
					<?php } elseif ( 'edit_slide' == $_GET['action'] ) { ?>
						<div class="submit">
							<input class="button-primary crrntl-save-slide" type="submit" value="<?php _e( 'Save Changes', 'car-rental' ); ?>">
						</div><!-- .submit -->
					<?php } ?>
				</form><!-- .crrntl-edit-slide-form -->
			<?php }
			bws_plugin_reviews_block( 'Car Rental', 'car-rental' ); ?>
		</div><!-- .wrap -->
	<?php }
}

/**
 * Display slider on home page
 * @return void
 */
if ( ! function_exists( 'crrntl_homepage_slider' ) ) {
	function crrntl_homepage_slider() {
		if (
			is_home() ||
			is_front_page() ||
			is_page_template( 'page-homev1.php' ) ||
			is_page_template( 'page-homev2.php' ) ||
			is_page_template( 'page-homev3.php' )
		) {
			crrntl_slider_template();
		}
	}
}

/**
 * Display slider on home page
 * @return void
 */
if ( ! function_exists( 'crrntl_slider_template' ) ) {
	function crrntl_slider_template() {
		global $crrntl_slider_options, $crrntl_filepath, $crrntl_is_main_page, $crrntl_is_carrental_template;

		$crrntl_is_main_page = true;

		$theme = wp_get_theme();

		if ( 'Renty' == $theme['Name'] && version_compare( $theme['Version'], '1.0.5', '>=' ) ) {
			$renty_options = get_option( 'renty_options' );
			$crrntl_slider_options = get_option( 'renty_slider_options' );
			$display_on_mobile = ! empty( $renty_options['mobile_slider_is_enabled'] );
		}

		if ( empty( $crrntl_slider_options ) ) {
			$crrntl_slider_options = get_option( 'crrntl_slider_options' );
		}

		if (
			(
				! isset( $renty_options ) ||
				( isset( $renty_options ) && ! empty( $renty_options['display_slider'] ) && ! empty( $crrntl_slider_options ) )
			) &&
			empty( $crrntl_is_carrental_template )
		) {
			if ( ! wp_is_mobile() || ! empty( $display_on_mobile ) ) { ?>
				<div id="crrntl-slider-container" class="crrntl-slider-container">
					<!-- Slides Container -->
					<div class="crrntl-slider owl-carousel">
						<?php if ( ! empty( $crrntl_slider_options ) && is_array( $crrntl_slider_options ) ) {
							foreach ( $crrntl_slider_options as $crrntl_slide_data ) {
								if ( ! empty( $crrntl_slide_data['image'] ) ) { ?>
									<div class="crrntl-one-slide">
										<img data-u="image" src="<?php echo $crrntl_slide_data['image']; ?>" alt="" />
										<span class="crrntl-slider-overlay"></span>

										<div class="crrntl-slider-post clearfix">
											<?php if ( ! empty( $crrntl_slide_data['title'] ) ) { ?>
												<div class="crrntl-slide-title">
													<h3><?php echo $crrntl_slide_data['title']; ?></h3>
												</div><!-- .crrntl-slide-title -->
											<?php }
											if ( ! empty( $crrntl_slide_data['description'] ) ) { ?>
												<div class="crrntl-slide-description">
													<div class="crrntl-entry-content"><?php echo $crrntl_slide_data['description']; ?></div>
												</div><!-- .crrntl-slide-description -->
											<?php }

											if ( ! empty( $crrntl_slide_data['link'] ) ) { ?>
												<div class="crrntl-entry-meta">
													<a class="crrntl-slider-link" title="<?php echo __( 'Go to', 'car-rental' ) . ' ' . $crrntl_slide_data['title']; ?>" href="<?php echo esc_url( $crrntl_slide_data['link'] ); ?>" target="_blank"><?php _e( 'Learn More', 'car-rental' ); ?></a>
												</div><!-- .crrntl-entry-meta -->
											<?php } ?>
										</div><!-- .crrntl-slider-post -->
									</div><!-- .crrntl-one-slide -->
								<?php }
							}
						} ?>
					</div><!-- .crrntl-slider.owl-carousel -->
					<?php if ( is_page_template( 'page-homev1.php' ) ) { ?>
						<div class="crrntl-front-img">
							<img src="<?php echo plugins_url( 'images/slider-front-img.png', dirname( __FILE__ ) ); ?>">
						</div><!-- .crrntl-front-img -->
					<?php } elseif ( is_page_template( 'page-homev2.php' ) ) { ?>
						<div class="crrntl-front-img">
							<img src="<?php echo plugins_url( 'images/slider-front-img-right.png', dirname( __FILE__ ) ); ?>">
						</div><!-- .crrntl-front-img -->
					<?php } ?>
				</div><!-- .crrntl-slider-container -->
			<?php } ?>
			<div id="crrntl-slider-form-container">
				<?php load_template( $crrntl_filepath . 'car-search-form.php' ); ?>
			</div>
			<div class="clear line-dashed"></div>
		<?php }
	}
}

/*
* Add all hooks
*/
add_action( 'wp_enqueue_scripts', 'crrntl_slider_styles' );
/* display slider on homepage, frontpage, home-v1, home-v2 and home-v3 page templates */
add_action( 'crrntl_display_slider', 'crrntl_homepage_slider' );
/* display slider on any page */
add_action( 'crrntl_display_slider_custom', 'crrntl_slider_template' );
add_action( 'wp_footer', 'crrntl_slider_scripts' );