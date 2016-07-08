<?php
/**
 * Display Admin panel
 *
 * @subpackage Car Rental
 * @since      Car Rental 1.0.0
 */

if ( ! function_exists( 'crrntl_settings_page' ) ) {
	function crrntl_settings_page() {
		global $wpdb, $title, $crrntl_options, $crrntl_filenames, $crrntl_slider_options, $crrntl_option_defaults, $crrntl_plugin_info;
		if ( empty( $crrntl_options ) ) {
			$crrntl_options = get_option( 'crrntl_options' );
		}
		if ( ! get_option( 'crrntl_slider_options' ) ) {
			if ( wp_get_theme() == 'Renty' ) {
				$slider_options = get_option( 'renty_slider_options' );
				add_option( 'crrntl_slider_options', $slider_options );
			} else {
				add_option( 'crrntl_slider_options' );
			}
		}
		$crrntl_slider_options = get_option( 'crrntl_slider_options' );

		$plugin_basename = 'car-rental/car-rental.php';
		require_once( dirname( __FILE__ ) . '/demo-data/class-bws-demo-data.php' );
		$args                  = array(
			'plugin_basename' => $plugin_basename,
			'plugin_prefix'   => 'crrntl_',
			'plugin_name'     => 'Car Rental',
			'plugin_page'     => 'car-rental-settings',
			'demo_folder'     => dirname( __FILE__ ) . '/demo-data/',
		);
		if ( ! isset( $crrntl_options['display_demo_notice'] ) ) {
			$crrntl_options['display_demo_notice'] = 1;
		}
		$crrntl_bws_demo_data = new Crrntl_Demo_Data( $args );
		$crrntl_bws_demo_data->bws_handle_demo_notice( $crrntl_options['display_demo_notice'] );
		$error = $message = $crrntl_select_pages = $crrntl_notice = '';
		$i = 0;

		$currencies = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}crrntl_currency", ARRAY_A );

		if ( isset( $_POST['crrntl_form_submit'] ) && check_admin_referer( $plugin_basename, 'crrntl_nonce_name' ) ) {
			$crrntl_options['currency_custom_display']         = $_POST['crrntl_currency_custom_display'];
			$crrntl_options['currency_unicode']                = $_POST['crrntl_currency'];
			$crrntl_options['custom_currency']                 = sanitize_text_field( $_POST['crrntl_custom_currency'] );
			$crrntl_options['currency_position']               = $_POST['crrntl_currency_position'];
			$crrntl_options['unit_consumption_custom_display'] = $_POST['crrntl_unit_consumption_custom_display'];
			$crrntl_options['unit_consumption']                = $_POST['crrntl_unit_consumption'];
			$crrntl_options['custom_unit_consumption']         = sanitize_text_field( $_POST['crrntl_custom_unit_consumption'] );
			$crrntl_options['per_page']                        = ( ! empty( $_POST['crrntl_per_page'] ) ) ? $_POST['crrntl_per_page'] : get_option( 'posts_per_page' );
			$crrntl_options['car_page_id']                     = $_POST['crrntl_car_page_id'];
			$crrntl_options['extra_page_id']                   = $_POST['crrntl_extra_page_id'];
			$crrntl_options['review_page_id']                  = $_POST['crrntl_review_page_id'];
			$crrntl_options['maps_key'] = ( ! empty( $_POST['crrntl_maps_key'] ) ) ? sanitize_text_field( $_POST['crrntl_maps_key'] ) : '';

			if ( 1 == $crrntl_options['currency_custom_display'] && empty( $crrntl_options['custom_currency'] ) ) {
				$crrntl_options['currency_custom_display'] = 0;
				$error .= __( 'Please, enter the correct value for custom currency field. Settings not saved.', 'car-rental' ) . '<br />';
			} else {
				update_option( 'crrntl_options', $crrntl_options );
				$message .= __( 'Settings saved.', 'car-rental' ) . '<br />';
			}
			if ( isset( $_POST['crrntl_status'] ) ) {
				$crrntl_statuses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}crrntl_statuses", OBJECT_K );
				$_POST['crrntl_status'] = array_unique( $_POST['crrntl_status'] );
				foreach ( $_POST['crrntl_status'] as $key => $status ) {
					$status = sanitize_text_field( $status );
					if ( isset( $crrntl_statuses[ $key ] ) && ! empty( $status ) && $crrntl_statuses[ $key ]->status_name != $status && ! preg_match( '/^[\s]+$/', $status ) ) {
						$error_status = $wpdb->update( $wpdb->prefix . 'crrntl_statuses',
							array( 'status_name' => $status ),
							array( 'status_id' => $key ),
							array( '%s' ),
							array( '%d' )
						);
					} elseif ( ! isset( $crrntl_statuses[ $key ] ) && ! empty( $status ) && ! preg_match( '/^[\s]+$/', $status ) ) {
						$error_status = $wpdb->insert( $wpdb->prefix . 'crrntl_statuses',
							array( 'status_name' => $status ),
							array( '%s' )
						);
					}
				}
				if ( isset( $_POST['crrntl_delete_status'] ) ) {
					foreach ( $_POST['crrntl_delete_status'] as $status_to_delete ) {
						$used_status = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(order_id) FROM `{$wpdb->prefix}crrntl_orders` WHERE `status_id` = %d", $status_to_delete ) );
						if ( ! empty( $used_status ) ) {
							$old_status = $wpdb->get_var( $wpdb->prepare( "SELECT `status_name` FROM `{$wpdb->prefix}crrntl_statuses` WHERE `status_id` = %d", $status_to_delete ) );
							$error .= sprintf( __( 'Warning! The status "%1$s" can not be removed, because it is used in the %2$s orders.', 'car-rental' ), $old_status, $used_status ) . '<br />';
						} else {
							$error_delete = $wpdb->delete( 
								$wpdb->prefix . 'crrntl_statuses', 
								array( 'status_id' => $status_to_delete )
							);
						}
					}
				}
				if ( ( isset( $error_status ) && false === $error_status ) || ( isset( $error_delete ) && false === $error_delete ) ) {
					$error .= __( 'An error occurred during the updating status list.', 'car-rental' ) . '<br />';
				}
			}
		}
		$crrntl_statuses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}crrntl_statuses", ARRAY_A );

		if ( isset( $_POST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
			$crrntl_options = $crrntl_option_defaults;
			update_option( 'crrntl_options', $crrntl_options );
			update_option( 'crrntl_slider_options', array() );
			$message .= __( 'All plugin settings were restored to default.', 'car-rental' );
		}

		$result = $crrntl_bws_demo_data->bws_handle_demo_data();
		if ( ! empty( $result ) && is_array( $result ) ) {
			$error .= $result['error'];
			$message .= $result['done'];
			if ( ! empty( $result['done'] ) && ! empty( $result['options'] ) ) {
				$crrntl_options = $result['options'];
			}
		}

		$crrntl_page_description = array(
			__( 'for choosing a Car', 'car-rental' ),
			__( 'for choosing Extras', 'car-rental' ),
			__( 'for Review & Book', 'car-rental' ),
		);
		$page_template_name        = array(
			'Choose car',
			'Choose extras',
			'Review & Book',
		);	

		if ( empty( $crrntl_options['maps_key'] ) && ! isset( $_GET['tab'] ) )
			$error .= __( 'Please, enter the correct value for Google Maps Key field.', 'car-rental' ) . '<br />';

		foreach ( $crrntl_filenames as $page_name => $page_file ) {
			$selected_page_id = ! empty( $crrntl_options[ $page_name . '_id' ] ) ? $crrntl_options[ $page_name . '_id' ] : 0;
			if ( empty( $selected_page_id ) ) {
				$crrntl_notice .= '<p><strong>' . __( 'Important', 'car-rental' ) . ':</strong> ' . sprintf( __( 'for the correct plugin work, please choose the page %s', 'car-rental' ), $crrntl_page_description[ $i ] ) . ' - <a href="admin.php?page=car-rental-settings">' . __( 'edit', 'car-rental' ) . '</a></p>';
			} elseif ( get_post_meta( $selected_page_id, '_wp_page_template', true ) != $page_file ) {
				$crrntl_notice .= '<p><strong>' . __( 'Important', 'car-rental' ) . ':</strong> ' . sprintf( __( 'for the correct plugin work, please choose the %2$s template for the %1$s page', 'car-rental' ), '<strong>"' . get_post_field( 'post_title', $selected_page_id, 'raw' ) . '"</strong>', '<strong>"' . $page_template_name[ $i ] . '"</strong>' ) . ' - <a href="' . get_edit_post_link( $selected_page_id, '' ) . '">' . __( 'edit', 'car-rental' ) . '</a></p>';
			}
			$crrntl_select_pages .= '<label for="crrntl_' . $page_name . '_id' . '">' .
			                          $crrntl_page_description[ $i ] . ': ' .
			                          wp_dropdown_pages( array(
				                          'name'              => 'crrntl_' . $page_name . '_id',
				                          'echo'              => 0,
				                          'show_option_none'  => __( '&mdash; Select &mdash;' ),
				                          'option_none_value' => '0',
				                          'selected'          => $selected_page_id,
			                          ) ) .
			                          '</label><br />';
			$i ++;
		} 

		/* GO PRO */
		if ( isset( $_GET['tab'] ) && 'go_pro' == $_GET['tab'] ) {
			$go_pro_result = bws_go_pro_tab_check( $plugin_basename, 'crrntl_options' );
			if ( ! empty( $go_pro_result['error'] ) )
				$error = $go_pro_result['error'];
			elseif ( ! empty( $go_pro_result['message'] ) )
				$message = $go_pro_result['message'];
		} ?>
		<div class="wrap">
			<h1><?php echo $title; ?></h1>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['tab'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=car-rental-settings"><?php _e( 'Settings', 'car-rental' ); ?></a>
				<a class="nav-tab<?php if ( isset( $_GET['tab'] ) && 'slider' == $_GET['tab'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=car-rental-settings&amp;tab=slider"><?php _e( 'Slider', 'car-rental' ); ?></a>
				<a class="nav-tab<?php if ( isset( $_GET['tab'] ) && 'custom_code' == $_GET['tab'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=car-rental-settings&amp;tab=custom_code"><?php _e( 'Custom code', 'car-rental' ); ?></a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['tab'] ) && 'go_pro' == $_GET['tab'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=car-rental-settings&amp;tab=go_pro"><?php _e( 'Go PRO', 'car-rental' ); ?></a>
			</h2>			
			<?php if ( isset( $_POST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
				bws_form_restore_default_confirm( $plugin_basename );
			} elseif ( isset( $_POST['bws_handle_demo'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
				$crrntl_bws_demo_data->bws_demo_confirm();
			} else {
				/* Display form on the setting page */
				if ( ! empty( $crrntl_notice ) ) {
					echo '<div class="notice notice-warning below-h2"><p>' . $crrntl_notice . '</p></div>';
				} ?>
				<div id="crrntl_settings_message" class="updated fade below-h2" <?php if ( empty( $message ) ) echo 'style="display:none"'; ?>>
					<p><strong><?php echo $message; ?></strong></p>
				</div>
				<div class="error below-h2" <?php if ( empty( $error ) ) echo 'style="display:none"'; ?>>
					<p><strong><?php echo $error; ?></strong></p>
				</div>
				<?php bws_show_settings_notice(); ?>
				<div id="crrntl_settings_notice" class="updated fade below-h2" style="display:none;">
					<p>
						<strong><?php _e( 'Notice', 'car-rental' ); ?>:</strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'car-rental' ); ?>
					</p>
				</div>
				<?php if ( ! isset( $_GET['tab'] ) ) { ?>
					<form id="crrntl_settings_form" class="bws_form" method="post" action="admin.php?page=car-rental-settings">
						<table class="form-table">
							<tr valign="top">
								<th scope="row"><?php _e( 'Pages', 'car-rental' ); ?>:</th>
								<td>
									<?php echo $crrntl_select_pages; ?>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="crrntl_currency"><?php _e( 'Currency', 'car-rental' ); ?></label></th>
								<td>
									<label><input type="radio" name="crrntl_currency_custom_display" id="crrntl_currency_custom_display_false" value="0" <?php checked( $crrntl_options['currency_custom_display'], 0 ); ?> /></label>
									<select name="crrntl_currency" id="crrntl_currency">
										<?php foreach ( $currencies as $currency ) { ?>
											<option value="<?php echo $currency['currency_id']; ?>" <?php selected( $currency['currency_id'] == $crrntl_options['currency_unicode'] ); ?>><?php echo $currency['currency_unicode'] . ' (' . $currency['country_currency'] . ' - ' . $currency['currency_code'] . ')'; ?></option>
										<?php } ?>
									</select><br />
									<label><input type="radio" name="crrntl_currency_custom_display" id="crrntl_currency_custom_display_true" value="1" <?php checked( $crrntl_options['currency_custom_display'], 1 ); ?> /></label>
									<input type="text" id="crrntl_custom_currency" name="crrntl_custom_currency" value="<?php echo $crrntl_options['custom_currency']; ?>" />
									<span class="bws_info"><label for="crrntl_custom_currency"><?php _e( 'Custom currency, for example', 'car-rental' ); ?> $</label></span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Currency Position', 'car-rental' ); ?></th>
								<td>
									<fieldset>
										<label for="crrntl_currency_position_before"><input type="radio" id="crrntl_currency_position_before" name="crrntl_currency_position" value="before" <?php checked( $crrntl_options['currency_position'], 'before' ); ?> /> <?php _e( 'before numerals', 'car-rental' ); ?>
										</label><br />
										<label for="crrntl_currency_position_after"><input type="radio" id="crrntl_currency_position_after" name="crrntl_currency_position" value="after" <?php checked( $crrntl_options['currency_position'], 'after' ); ?> /> <?php _e( 'after numerals', 'car-rental' ); ?>
										</label>
									</fieldset>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="crrntl_unit_consumption"><?php _e( 'Unit of consumption', 'car-rental' ); ?></label>
								</th>
								<td>
									<label><input type="radio" name="crrntl_unit_consumption_custom_display" id="crrntl_unit_consumption_custom_display_false" value="0" <?php checked( $crrntl_options['unit_consumption_custom_display'], 0 ); ?> /></label>
									<select name="crrntl_unit_consumption" id="crrntl_unit_consumption">
										<option value="<?php _e( 'l/100km', 'car-rental' ); ?>" <?php selected( __( 'l/100km', 'car-rental' ) == $crrntl_options['unit_consumption'] ); ?>><?php _e( 'l/100km', 'car-rental' ); ?></option>
										<option value="<?php _e( 'km/l', 'car-rental' ); ?>" <?php selected( __( 'km/l', 'car-rental' ) == $crrntl_options['unit_consumption'] ); ?>><?php _e( 'km/l', 'car-rental' ); ?></option>
									</select><br />
									<label><input type="radio" name="crrntl_unit_consumption_custom_display" id="crrntl_unit_consumption_custom_display_true" value="1" <?php checked( $crrntl_options['unit_consumption_custom_display'], 1 ); ?> /></label>
									<label><input type="text" id="crrntl_custom_unit_consumption" name="crrntl_custom_unit_consumption" value="<?php echo $crrntl_options['custom_unit_consumption']; ?>" />
										<span class="bws_info"><?php _e( 'Custom unit of consumption', 'car-rental' ); ?></span></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="crrntl_maps_key"><?php _e( 'Google Maps Key', 'car-rental' ); ?></label>
								</th>
								<td>
									<input id="crrntl_maps_key" type="text" value="<?php echo ( ! empty( $crrntl_options['maps_key'] ) ) ? $crrntl_options['maps_key'] : ''; ?>" name="crrntl_maps_key" />
									<span class="bws_info">
										<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank"><?php _e( 'How to get a Key', 'car-rental' ); ?></a>
									</span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="crrntl_per_page"><?php _e( 'Search pages show at most', 'car-rental' ); ?></label>
								</th>
								<td>
									<input type="number" min="1" step="1" id="crrntl_per_page" name="crrntl_per_page" value="<?php echo $crrntl_options['per_page']; ?>" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="crrntl_statuses"><?php _e( 'Set the order status list', 'car-rental' ); ?></label>
								</th>
								<td class="crrntl-status-list" valign="middle">
									<?php foreach ( $crrntl_statuses as $one_status ) { ?>
									<div class="crrntl-status-item">
										<input type="text" name="crrntl_status[<?php echo $one_status['status_id']; ?>]" value="<?php echo $one_status['status_name']; ?>" title="" />
										<?php if ( 1 != $one_status['status_id'] ) { ?>
											<label class="crrntl-delete-status-label">
												<input id="crrntl-delete-status<?php echo $one_status['status_id']; ?>" type="checkbox" name="crrntl_delete_status[]" value="<?php echo $one_status['status_id']; ?>" />
												<?php _e( 'delete', 'car-rental' ); ?>
											</label>
											<label for="crrntl-delete-status<?php echo $one_status['status_id']; ?>">
												<span class="crrntl-delete-status dashicons dashicons-dismiss"></span>
											</label>
										<?php } ?>
									</div>
									<?php } ?>
									<div class="crrntl-status-item">
										<input class="bws_no_bind_notice" type="text" name="crrntl_status[]" value="" placeholder="<?php _e( 'Enter new status', 'car-rental' ); ?>" />
										<label>
											<span class="crrntl-add-status dashicons dashicons-plus"></span>
											<span style="display: none;" class="crrntl-delete-status-live dashicons dashicons-dismiss"></span>
										</label>
									</div>
								</td>
							</tr>
						</table>
						<input type="hidden" name="crrntl_form_submit" value="submit" />
						<div class="submit crrntl-submit-bottom-button">
							<input type="submit" id="bws-submit-button" class="button-primary" value="<?php _e( 'Save Changes', 'car-rental' ); ?>" />
						</div>
						<?php wp_nonce_field( $plugin_basename, 'crrntl_nonce_name' ); ?>
						<div class="clear"></div>
					</form>
					<?php bws_form_restore_default_settings( $plugin_basename );
					$crrntl_bws_demo_data->bws_show_demo_button( __( 'If you install the demo-data, will be created demo-cars with images and details, demo-extras with images and details, manufacturers, vehicles types and car classes.', 'car-rental' ) );
				} elseif ( 'slider' == $_GET['tab'] ) {
					crrntl_slider_settings();
				} elseif ( 'go_pro' == $_GET['tab'] ) { 
					bws_go_pro_tab_show( false, $crrntl_plugin_info, $plugin_basename, 'car-rental-settings', 'car-rental-pro-settings', 'car-rental-pro/car-rental-pro.php', 'car-rental', '664b00b8cd82b35c4f9b2a4838de35ff', '576', isset( $go_pro_result['pro_plugin_is_activated'] ) ); 
				} else {
					bws_custom_code_tab();
				}
			}
			bws_plugin_reviews_block( $crrntl_plugin_info['Name'], 'car-rental' ); ?>
		</div>
	<?php }
}
