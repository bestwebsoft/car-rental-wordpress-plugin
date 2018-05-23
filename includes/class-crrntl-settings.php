<?php
/**
 * Displays the content on the plugin settings page
 */

require_once( dirname( dirname( __FILE__ ) ) . '/bws_menu/class-bws-settings.php' );

if ( ! class_exists( 'Crrntl_Settings_Tabs' ) ) {
	class Crrntl_Settings_Tabs extends Bws_Settings_Tabs {
		public $pages, $statuses, $related_plugins;

		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $wpdb, $wp_version, $crrntl_options, $crrntl_plugin_info, $crrntl_BWS_demo_data;

			$tabs = array(
				'settings' 			=> array( 'label' => __( 'Settings', 'car-rental' ) ),
				'misc' 					=> array( 'label' => __( 'Misc', 'car-rental' ) ),
				'custom_code' 	=> array( 'label' => __( 'Custom Code', 'car-rental' ) ),
				'import-export' => array( 'label' => __( 'Import / Export', 'car-rental' ) ),
				'license'				=> array( 'label' => __( 'License Key', 'car-rental' ) ),
			);

			parent::__construct( array(
				'plugin_basename'			=> $plugin_basename,
				'plugins_info'				=> $crrntl_plugin_info,
				'prefix'							=> 'crrntl',
				'default_options'			=> crrntl_get_options_default(),
				'options'							=> $crrntl_options,
				'tabs'								=> $tabs,
				'wp_slug'							=> 'car-rental',
				'demo_data'						=> $crrntl_BWS_demo_data,
				'pro_page' 						=> "edit.php?post_type={$crrntl_options['post_type_name']}&amp;page=car-rental-pro-settings",
				'bws_license_plugin'	=> 'car-rental-pro/car-rental-pro.php',
				'link_key' 						=> '664b00b8cd82b35c4f9b2a4838de35ff',
				'link_pn' 						=> '576'
			) );

			$this->bws_hide_pro_option_exist = false;

			$this->pages = array(
				'car_page' => array(
					'filename'		=> 'page-choose-car.php',
					'pagename'		=> __( 'Choose car', 'car-rental' ),
					'description'	=> __( 'Cars', 'car-rental' )
				),
				'extra_page' => array(
					'filename'		=> 'page-choose-extras.php',
					'pagename'		=> __( 'Choose extras', 'car-rental' ),
					'description'	=> __( 'Extras', 'car-rental' )
				),
				'review_page' => array(
					'filename'		=> 'page-review-book.php',
					'pagename'		=> __( 'Review & Book', 'car-rental' ),
					'description'	=> __( 'Review & Book', 'car-rental' )
				)
			);

			$this->statuses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}crrntl_statuses", ARRAY_A );
			$this->related_plugins = array(
				'captcha' => array(
					'name'					=> 'Captcha Pro by BestWebSoft',
					'short_name'		=> 'Captcha',
					'download_link'	=> 'https://bestwebsoft.com/products/wordpress/plugins/captcha/?k=0cb1f30e297633615b78123d2a0866aa&amp;pn=' . $this->link_pn . '&amp;v=' . $this->plugins_info["Version"] . '&amp;wp_v=' . $wp_version,
					'status'				=> crrntl_get_related_plugin_status( 'captcha' )
				),
				'recaptcha' => array(
					'name'				=> 'Google Captcha (reCAPTCHA) by BestWebSoft',
					'short_name'		=> 'Google Captcha',
					'download_link'		=> 'https://bestwebsoft.com/products/wordpress/plugins/google-captcha/?k=6b0f3eab7392eac42ca57facd180fd24&amp;pn=' . $this->link_pn . '&v=' . $this->plugins_info["Version"] . '&amp;wp_v=' . $wp_version,
					'status'			=> crrntl_get_related_plugin_status( 'recaptcha' )
				),
			);

			add_action( get_parent_class( $this ) . '_display_custom_messages', array( $this, 'display_custom_messages' ) );
			add_action( get_parent_class( $this ) . '_additional_misc_options_affected', array( $this, 'additional_misc_options_affected' ) );
			add_action( get_parent_class( $this ) . '_additional_import_export_options', array( $this, 'additional_import_export_options' ) );
		}

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function save_options() {
			global $wpdb, $crrntl_settings_page_link;

			$message = $notice = $error = '';

			/* Settings Tab */
			$this->options['custom_currency']							= sanitize_text_field( $_POST['crrntl_custom_currency'] );
			if ( isset( $_POST['crrntl_currency'] ) && 'custom' == $_POST['crrntl_currency'] ) {
				if ( ! empty( $this->options['custom_currency'] ) ) {
					$this->options['currency_custom_display']			= 1;
				} else {
					$this->options['currency_custom_display']			= 0;
					$error .= __( 'Please enter the correct value for custom currency field.', 'car-rental' ) . '<br />';
				}
			} else {
				$this->options['currency_custom_display']				= 0;
				$this->options['currency_unicode']						= intval( $_POST['crrntl_currency'] );
			}
			$this->options['currency_position']							= isset( $_POST['crrntl_currency_position'] ) && in_array( $_POST['crrntl_currency_position'], array( 'before', 'after' ) ) ? $_POST['crrntl_currency_position'] : 'before';

			$this->options['custom_unit_consumption']					= sanitize_text_field( $_POST['crrntl_custom_unit_consumption'] );
			if ( isset( $_POST['crrntl_unit_consumption'] ) && 'custom' == $_POST['crrntl_unit_consumption'] ) {
				if ( ! empty( $this->options['custom_unit_consumption'] ) ) {
					$this->options['unit_consumption_custom_display']	= 1;
				} else {
					$this->options['unit_consumption_custom_display']	= 0;
					$error .= __( 'Please enter the correct value for custom unit in the consumption field.', 'car-rental' ) . '<br />';
				}
			} else {
				$this->options['unit_consumption']						= sanitize_text_field( $_POST['crrntl_unit_consumption'] );
				$this->options['unit_consumption_custom_display']		= 0;
			}

			$this->options['per_page']							= ! empty( $_POST['crrntl_per_page'] ) ? intval( $_POST['crrntl_per_page'] ) : get_option( 'posts_per_page' );
			$this->options['car_page_id']						= intval( $_POST['crrntl_car_page_id'] );
			$this->options['min_from']                          = ( preg_match( "/^\d{1,2}:\d{1,2}$/", $_POST['crrntl_min_from'] ) ) ? $_POST['crrntl_min_from'] : '00:00';
			$this->options['max_to']                            = ( preg_match( "/^\d{1,2}:\d{1,2}$/", $_POST['crrntl_max_to'] ) ) ? $_POST['crrntl_max_to'] : '23:30';
			$this->options['extra_page_id']						= intval( $_POST['crrntl_extra_page_id'] );
			$this->options['review_page_id']					= intval( $_POST['crrntl_review_page_id'] );
			$this->options['time_selecting']					= isset( $_POST['crrntl_time_selecting'] ) ? 1 : 0;
			$this->options['time_from']							= sanitize_text_field( $_POST['crrntl_time_from'] );
			$this->options['rent_per']							= in_array( $_POST['crrntl_rent_per'], array( 'hour', 'day' ) ) ? $_POST['crrntl_rent_per'] : 'day';
			$this->options['return_location_selecting']			= isset( $_POST['crrntl_return_location_selecting'] ) ? 1 : 0;
			$this->options['maps_key']							= sanitize_text_field( trim( $_POST['crrntl_maps_key'] ) );
			$this->options['min_age']							= ! empty( $_POST['crrntl_min_age'] ) ? intval( $_POST['crrntl_min_age'] ) : 16;
			$this->options['datepicker_type']					= isset( $_POST['crrntl_datepicker_type'] ) &&
																 in_array( $_POST['crrntl_datepicker_type'], array( 'yy-mm-dd', 'yy-dd-mm', 'mm-dd-yy', 'dd-mm-yy', 'custom' ) ) ? $_POST['crrntl_datepicker_type'] : 'yy-mm-dd';
			$this->options['datepicker_custom_format']			= ! empty( $_POST['crrntl_datepicker_custom_format'] ) ? sanitize_text_field( $_POST['crrntl_datepicker_custom_format'] ) : 'yy-mm-dd';

			$this->options['send_email_sa']						= isset( $_POST['crrntl_send_email_sa'] ) ? 1 : 0;
			$this->options['send_email_customer']				= isset( $_POST['crrntl_send_email_customer'] ) ? 1 : 0;
			$this->options['send_email_custom']					= isset( $_POST['crrntl_send_email_custom'] ) ? 1 : 0;
			
			$this->options['gdpr']								= isset( $_POST['crrntl_gdpr'] ) ? 1 : 0;
			$this->options['gdpr_link']							= sanitize_text_field( $_POST['crrntl_gdpr_link'] );
			$this->options['gdpr_text']							= sanitize_text_field( $_POST['crrntl_gdpr_text'] );
			$this->options['gdpr_cb_name']						= sanitize_text_field( $_POST['crrntl_gdpr_cb_name'] );

			$list = array();
			if ( ! empty( $_POST['custom_email_area'] ) ) {
				$custom_email = explode( ",", $_POST['custom_email_area'] );
				foreach ( $custom_email as $email ) {
					$email = trim( $email );
					if ( is_email( $email ) ) {
						$list[] = $email;
					}
				}
			}
			$this->options['custom_email_list'] = $list;

			if ( empty( $this->options['custom_email_list'] ) ) {
				$this->options['send_email_custom'] = 0;
			}

			if ( empty( $this->options['send_email_sa'] ) && empty( $this->options['send_email_customer'] ) && empty( $this->options['send_email_custom'] ) ) {
				$this->options['send_email_sa'] = 1;
				$this->options['send_email_customer'] = 1;
			}

			if ( isset( $_POST['crrntl_send_email_custom'] ) && empty( $list ) ) {
				$notice .= sprintf(
					'<p><strong>%1$s:</strong> %2$s</p>',
					__( 'Important', 'car-rental' ),
					__( 'you should add at least one email for custom email list.', 'car-rental' )
				);
			}

			if ( 'custom' == $this->options['datepicker_type'] && isset( $_POST['crrntl_datepicker_custom_format'] ) ) {
				if ( strpos( $_POST['crrntl_datepicker_custom_format'], 'M' ) !== false ) {
					$notice .= sprintf(
						'<p><strong>%1$s:</strong> "%2$s" %3$s</p>',
						__( 'Important', 'car-rental' ),
						sanitize_text_field( $_POST["crrntl_datepicker_custom_format"] ),
						__( 'date format is not supported.', 'car-rental' )
					);
				}
			}

			/* Updating Captcha and reCAPTCHA options and status on form submit */
			foreach ( $this->related_plugins as $plugin_slug => $plugin_data ) {
				if ( ! empty( $plugin_data['status']['active'] ) && 'outdated' != $plugin_data['status']['active'] ) {
					$is_enabled = isset( $_POST["crrntl_enable_{$plugin_slug}"] ) ? true : false;
					$this->related_plugins[ $plugin_slug ]['status']['enabled'] = $is_enabled;
					if ( 'captcha' == $plugin_slug && get_option( 'cptch_options' ) ) {
						$cptch_options = get_option( 'cptch_options' );
						if ( isset( $cptch_options['forms']['bws_carrental'] ) ) {
							$cptch_options['forms']['bws_carrental']['enable'] = $is_enabled;
						} else {
							$cptch_options['forms']['bws_carrental'] = array(
								'use_general'			=> true,
								'enable'				=> $is_enabled,
								'hide_from_registered'	=> false,
								'enable_time_limit'		=> 120
							);
						}
						update_option( 'cptch_options', $cptch_options );
					} elseif ( 'recaptcha' == $plugin_slug && get_option( 'gglcptch_options' )  ) {
						$gglcptch_options = get_option( 'gglcptch_options' );
						$gglcptch_options['carrental_form'] = $is_enabled ? 1 : 0;
						update_option( 'gglcptch_options', $gglcptch_options );
					}
				}
			}

			/**
			 * rewriting post types name with unique one from default options
			 * since 4.4.4
			 */
			if ( ! empty( $_POST['crrntl_rename_post_type'] ) ) {
				$wpdb->update(
					$wpdb->prefix . 'posts',
					array(
						'post_type'	=> $this->default_options['post_type_name']
					),
					array(
						'post_type'	=> $this->options['post_type_name']
					)
				);

				$this->options['post_type_name'] = $this->default_options['post_type_name'];
			}

			if ( isset( $_POST['crrntl_status'] ) ) {
				$statuses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}crrntl_statuses", OBJECT_K );
				$_POST['crrntl_status'] = array_unique( $_POST['crrntl_status'] );
				foreach ( $_POST['crrntl_status'] as $key => $status ) {
					$status = sanitize_text_field( $status );
					if ( isset( $statuses[ $key ] ) && ! empty( $status ) && $statuses[ $key ]->status_name != $status && ! preg_match( '/^[\s]+$/', $status ) ) {
						$error_status = $wpdb->update( $wpdb->prefix . 'crrntl_statuses',
							array( 'status_name' => $status ),
							array( 'status_id' => $key ),
							array( '%s' ),
							array( '%d' )
						);
					} elseif ( ! isset( $statuses[ $key ] ) && ! empty( $status ) && ! preg_match( '/^[\s]+$/', $status ) ) {
						$error_status = $wpdb->insert( $wpdb->prefix . 'crrntl_statuses',
							array( 'status_name' => $status ),
							array( '%s' )
						);
					}
				}
				if ( isset( $_POST['crrntl_delete_status'] ) ) {
					foreach ( $_POST['crrntl_delete_status'] as $status_to_delete ) {
						$used_status = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(`order_id`) FROM `{$wpdb->prefix}crrntl_orders` WHERE `status_id` = %d", $status_to_delete ) );
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
				$this->statuses = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}crrntl_statuses", ARRAY_A );
			}

			update_option( 'crrntl_options', $this->options );
			$message = __( "Settings saved.", 'car-rental' );

			foreach ( $this->pages as $page_slug => $page ) {
				$selected_page_id = ! empty( $this->options[ $page_slug . '_id' ] ) ? $this->options[ $page_slug . '_id' ] : 0;
				if ( empty( $selected_page_id ) ) {
					$notice .= sprintf(
						'<p><strong>%1$s:</strong> %2$s - <a href="' . $crrntl_settings_page_link . '">%3$s</a></p>',
						__( 'Important', 'car-rental' ),
						sprintf( __( 'for the correct plugin work, please choose the page for "%s"', 'car-rental' ), $page['description'] ),
						__( 'edit', 'car-rental' )
					);
				}
			}
			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Settings tab
		 */
		public function tab_settings() {
			global $wpdb;
			$currencies = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}crrntl_currency", ARRAY_A );
			if ( isset( $_REQUEST['bws_install_demo_confirm'] ) ) {
				$this->options = get_option( 'crrntl_options' );
			} ?>

			<h3 class="bws_tab_label"><?php _e( 'Settings', 'car-rental' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table crrntl-settings-form">
				<tr valign="top">
					<th scope="row"><?php _e( 'Pages', 'car-rental' ); ?></th>
					<td>
						<?php foreach ( $this->pages as $page_slug => $page ) {
							$selected_page_id = ! empty( $this->options[ $page_slug . '_id' ] ) ? $this->options[ $page_slug . '_id' ] : 0; ?>
								<div>
									<label class="crrntl-pages-settings-labels" for="crrntl_<?php echo $page_slug; ?>_id">
										<span><?php echo $page['description']; ?></span>&emsp;
									</label>
									<?php wp_dropdown_pages( array(
										'name'				=> 'crrntl_' . $page_slug . '_id',
										'echo'				=> 1,
										'show_option_none'	=> __( '&mdash; Select &mdash;' ),
										'option_none_value'	=> '0',
										'selected'			=> $selected_page_id,
									) ); ?>
								</div>
						<?php } ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="crrntl_currency"><?php _e( 'Currency', 'car-rental' ); ?></label></th>
					<td>
						<select name="crrntl_currency" id="crrntl_currency">
							<?php printf(
								'<option value="custom" %1$s>%2$s</option>',
								selected( $this->options['currency_custom_display'], 1, false ),
								__( 'Custom currency', 'car-rental' )
							); ?>
							<?php foreach ( $currencies as $currency ) {
								printf(
									'<option value="%1$s" %2$s>%3$s (%4$s - %5$s)</option>',
									$currency['currency_id'],
									selected(
										( 1 != $this->options['currency_custom_display'] && $currency['currency_id'] == $this->options['currency_unicode'] ),
										true,
										false
									),
									$currency['currency_unicode'],
									$currency['country_currency'],
									$currency['currency_code']
								);
							} ?>
						</select><br />
						<label for="crrntl_custom_currency">
							<input type="text" id="crrntl_custom_currency" name="crrntl_custom_currency" value="<?php echo $this->options['custom_currency']; ?>" />
							<noscript>
								<br /><span class="bws_info"><?php _e( 'Custom currency, for example', 'car-rental' ); ?> $</span>
							</noscript>
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Currency Position', 'car-rental' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="crrntl_currency_position" value="before" <?php checked( $this->options['currency_position'], 'before' ); ?> />&nbsp;<?php _e( 'Before numerals', 'car-rental' ); ?>
							</label><br />
							<label><input type="radio" name="crrntl_currency_position" value="after" <?php checked( $this->options['currency_position'], 'after' ); ?> />&nbsp;<?php _e( 'After numerals', 'car-rental' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="crrntl_unit_consumption"><?php _e( 'Consumption Unit', 'car-rental' ); ?></label>
					</th>
					<td>
						<?php $consumption_units = array( __( 'l/100km', 'car-rental' ), __( 'km/l', 'car-rental' ) ); ?>
						<select class="crrntl_unit_select" name="crrntl_unit_consumption" id="crrntl_unit_consumption">
							<?php printf(
								'<option value="custom" %1$s>%2$s</option>',
								selected( 1 == $this->options['unit_consumption_custom_display'], true, false ),
								__( 'Custom consumption unit', 'car-rental' )
							); ?>
							<?php foreach ( $consumption_units as $units ) {
								printf(
									'<option value="%1$s" %2$s>%1$s</option>',
									$units,
									selected(
										( 1 != $this->options['unit_consumption_custom_display'] && $units == $this->options['unit_consumption'] ),
										true,
										false
									)
								);
							} ?>
						</select><br />
						<label for="crrntl_custom_unit_consumption">
							<input type="text" id="crrntl_custom_unit_consumption" name="crrntl_custom_unit_consumption" value="<?php echo $this->options['custom_unit_consumption']; ?>" />
							<noscript>
								<br /><span class="bws_info"><?php _e( 'Custom Consumption Unit', 'car-rental' ); ?></span>
							</noscript>
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="crrntl-time-selecting"><?php _e( 'Pick Up & Drop Off Time', 'car-rental' ); ?></label></th>
					<td>
						<input type="checkbox" <?php if ( ! empty( $this->options['time_selecting'] ) ) echo 'checked="checked"'; ?> value="1" name="crrntl_time_selecting" id="crrntl-time-selecting" class="bws_option_affect" data-affect-hide="#crrntl_time_from" data-affect-show=".crrntl-work-hours" />&nbsp;
						<label for="crrntl-time-selecting"><span class="bws_info"><?php _e( 'Enable to display Pick Up & Drop Off Time option.', 'car-rental' ); ?></span></label><br />
						<select id="crrntl_time_from" name="crrntl_time_from">
							<?php for ( $i = 00; $i <= 23; $i ++ ) { ?>
								<option value="<?php echo $i; ?>:00" <?php selected( ( $i . ':00' ) == $this->options['time_from'] ); ?>><?php echo $i; ?>:00</option>
								<option value="<?php echo $i; ?>:30" <?php selected( ( $i . ':30' ) == $this->options['time_from'] ); ?>><?php echo $i; ?>:30</option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr class="crrntl-work-hours" valign="top">
					<th scope="row"><label for="crrntl-time-selecting"><?php _e( 'Working Hours', 'car-rental' ); ?></label></th>
					<td>
						<select name="crrntl_min_from">
							<?php for ( $i = 00; $i <= 23; $i ++ ) { ?>
								<option value="<?php echo $i; ?>:00" <?php selected( ( $i . ':00' ) == $this->options['min_from'] ); ?>><?php echo $i; ?>:00</option>
								<option value="<?php echo $i; ?>:30" <?php selected( ( $i . ':30' ) == $this->options['min_from'] ); ?>><?php echo $i; ?>:30</option>
							<?php } ?>
						</select>
						<span> - </span>
						<select name="crrntl_max_to">
							<?php for ( $i = 00; $i <= 23; $i ++ ) { ?>
								<option value="<?php echo $i; ?>:00" <?php selected( ( $i . ':00' ) == $this->options['max_to'] ); ?>><?php echo $i; ?>:00</option>
								<option value="<?php echo $i; ?>:30" <?php selected( ( $i . ':30' ) == $this->options['max_to'] ); ?>><?php echo $i; ?>:30</option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="crrntl-return-location-selecting"><?php _e( 'Return Location', 'car-rental' ); ?></label></th>
					<td>
						<input type="checkbox" <?php if ( ! empty( $this->options['return_location_selecting'] ) ) echo 'checked="checked"'; ?> value="1" name="crrntl_return_location_selecting" id="crrntl-return-location-selecting" />&nbsp;
						<label for="crrntl-return-location-selecting"><span class="bws_info"><?php _e( 'Enable to display Return Location option.', 'car-rental' ); ?></span></label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Rent per', 'car-rental' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="crrntl_rent_per" value="hour" <?php checked( $this->options['rent_per'], 'hour' ); ?> />&nbsp;<?php _e( 'Hour', 'car-rental' ); ?></label><br/>
							<label><input type="radio" name="crrntl_rent_per" value="day" <?php checked( $this->options['rent_per'], 'day' ); ?> />&nbsp;<?php _e( 'Day', 'car-rental' ); ?></label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="crrntl_maps_key"><?php _e( 'Google Maps API Key', 'car-rental' ); ?></label>
					</th>
					<td>
						<input id="crrntl_maps_key" type="text" value="<?php echo ( ! empty( $this->options['maps_key'] ) ) ? $this->options['maps_key'] : ''; ?>" name="crrntl_maps_key" /><br>
						<span class="bws_info">
							<?php printf(
								__( "If you include a key in your request it will allow you to monitor your application's API usage in the %s.", 'car-rental' ),
								sprintf(
									'<a href="https://console.developers.google.com/" target="_blank">%s</a>',
									__( 'Google API Console', 'car-rental' )
								)
							); ?><br />
							<?php _e( "Don't have an API key?", 'car-rental' ); ?>
							<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank"><?php _e( 'Get it now!', 'car-rental' ); ?></a>
						</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="crrntl_per_page"><?php _e( 'Search Pages Show at Most', 'car-rental' ); ?></label>
					</th>
					<td>
						<input type="number" min="1" max="50" step="1" id="crrntl_per_page" name="crrntl_per_page" value="<?php echo $this->options['per_page']; ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Set the Order Status List', 'car-rental' ); ?>
					</th>
					<td class="crrntl-status-list" valign="middle">
						<?php foreach ( $this->statuses as $one_status ) { ?>
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
				<tr valign="top">
					<th scope="row">
						<label for="crrntl_min_age"><?php _e( 'Minimum Age', 'car-rental' ); ?></label>
					</th>
					<td>
						<input id="crrntl_min_age" type="number" min="0" max="100" name="crrntl_min_age" value="<?php echo $this->options['min_age']; ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Date Format', 'car-rental' ); ?></th>
					<td>
						<fieldset>
							<?php foreach ( array( 'yy-mm-dd', 'yy-dd-mm', 'mm-dd-yy', 'dd-mm-yy' ) as $format ) { ?>
								<label><input type="radio" class="bws_option_affect" data-affect-hide="#check_custom_datepicker_format" name="crrntl_datepicker_type" value="<?php echo $format ?>" <?php checked( $this->options['datepicker_type'], $format ); ?>/>&nbsp;<?php echo $format ?></label><br/>
							<?php } ?>
							<label><input type="radio" class="bws_option_affect" data-affect-show="#check_custom_datepicker_format" name="crrntl_datepicker_type" value="custom" <?php checked( $this->options['datepicker_type'], 'custom' ); ?> /> <?php _e( 'Custom', 'car-rental' ); ?></label><br/>
							<label id="check_custom_datepicker_format">
								<input type="text" id="custom_datepicker_format" name="crrntl_datepicker_custom_format" value="<?php echo $this->options['datepicker_custom_format']; ?>"/><br/>
								<span class="bws_info"><?php _e( 'You can make your own date format.', 'car-rental' ); ?></span>
								<a target="_blank" href="http://api.jqueryui.com/datepicker/#utility-formatDate"><?php _e( 'Learn more', 'car-rental' ); ?></a>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Recipient of the Order Notification', 'car-rental' );?></th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="crrntl_send_email_sa" value="1" <?php checked( ! empty( $this->options['send_email_sa'] ) ); ?> />
								<span><?php _e( 'Administrator', 'car-rental' ); ?></span>
							</label><br/>
							<label>
								<input type="checkbox" name="crrntl_send_email_customer" value="1" <?php checked( ! empty( $this->options['send_email_customer'] ) ); ?> />
								<span><?php _e( 'Customer', 'car-rental' ); ?></span>
							</label><br/>
							<label>
								<input type="checkbox" name="crrntl_send_email_custom" value="1" <?php checked( ! empty( $this->options['send_email_custom'] ) ); ?> class="bws_option_affect" data-affect-show="#custom_email_list"/>
								<span><?php _e( 'Custom email list', 'car-rental' ); ?></span></br>
								<label for="custom_email_area" id="custom_email_list">
									<textarea id="custom_email_area" name="custom_email_area" cols="30" rows="3"><?php echo implode( ', ', $this->options['custom_email_list'] ); ?></textarea>
									<span class="bws_info"><?php _e( 'You can enter more than one address. For example: ', 'car-rental' ) ?>example@example.com, example1@example.com</span>
								</label>
							</label>
						</fieldset>
					</td>
				</tr>
				<?php /* Display Captcha and reCAPTCHA settings */
				foreach ( $this->related_plugins as $plugin_slug => $plugin_data ) { ?>
					<tr valign="top">
						<th scope="row">
							<label for="<?php echo "crrntl-enable-{$plugin_slug}"; ?>">
								<?php printf(
									__( 'Add %s', 'car-rental' ),
									$plugin_data['short_name']
								); ?>
							</label>
						</th>
						<td>
							<label for="crrntl-enable-<?php echo $plugin_slug; ?>">
								<input type="checkbox"
									name="crrntl_enable_<?php echo $plugin_slug; ?>"
									id="<?php echo "crrntl-enable-{$plugin_slug}"; ?>"
									<?php checked( $plugin_data['status']['enabled'] );
									disabled(
										! $plugin_data['status']['installed'] ||
										! $plugin_data['status']['active'] ||
										'outdated' == $plugin_data['status']['active']
									); ?>
									value="1" >&nbsp;
								<span class="bws_info">
									<?php if ( ! $plugin_data['status']['installed'] ) {
										printf(
											'<a href="%1$s" target="_blank">%2$s</a> %3$s.',
											$plugin_data['download_link'],
											__( 'Download', 'car-rental' ),
											$plugin_data['name']
										);
									} elseif ( ! $plugin_data['status']['active'] ) {
										printf(
											'<a href="%1$s" target="_blank">%2$s</a> %3$s.',
											network_admin_url( 'plugins.php' ),
											__( 'Activate', 'car-rental' ),
											$plugin_data['name']
										);
									} else {
										if ( 'outdated' != $plugin_data['status']['active'] ) {
											printf(
												__( 'Enable to use %s for Review & Book form.', 'car-rental' ),
												$plugin_data['name']
											);
										} else {
											printf(
												__( 'Your %s plugin is outdated. Please update it to the latest version.', 'car-rental' ),
												$plugin_data['name']
											);
										}
									} ?>
								</span>
							</label>
						</td>
					</tr>
				<?php } ?>
				<tr>
					<th>
						<label for="crrntl_gdpr"><?php _e( 'GDPR Compliance', 'car-rental' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="crrntl_gdpr" name="crrntl_gdpr" value="1" <?php checked( '1', $this->options['gdpr'] ); ?> />
						<div id="crrntl_gdpr_link_options" >
							<label class="crrntl_privacy_policy_text" >
								<?php _e( 'Checkbox label', 'car-rental' ); ?>
								<input type="text" id="crrntl_gdpr_cb_name" size="29" name="crrntl_gdpr_cb_name" value="<?php echo $this->options['gdpr_cb_name']; ?>"/>
							</label>
							<label class="crrntl_privacy_policy_text" >
								<?php _e( "Link to Privacy Policy Page", 'car-rental' ); ?>
								<input type="url" id="crrntl_gdpr_link" name="crrntl_gdpr_link" value="<?php echo $this->options['gdpr_link']; ?>" />
							</label>
							<label class="crrntl_privacy_policy_text" >
								<?php _e( "Text for Privacy Policy Link", 'car-rental' ); ?>
								<input type="text" id="crrntl_gdpr_text" name="crrntl_gdpr_text" value="<?php echo $this->options['gdpr_text']; ?>" />
							</label>
						</div>
					</td>
				</tr>
			</table>
		<?php }

		public function display_custom_messages( $save_results ) { ?>
			<noscript>
				<div class="error below-h2">
					<p><strong><?php _e( 'Please enable JavaScript in your browser for fully functional work of the plugin.', 'car-rental' ); ?></strong></p>
				</div>
			</noscript>
		<?php }

		/**
		 * Display custom options on the 'misc' tab
		 * @access public
		 */
		public function additional_misc_options_affected() {
			if ( $this->options['post_type_name'] != $this->default_options['post_type_name'] ) { ?>
				<tr valign="top">
					<th scope="row"><label for="crrntl_rename_post_type"><?php _e( 'Car Rental Post Type', 'car-rental' ); ?></label></th>
					<td>
						<label><input type="checkbox" name="crrntl_rename_post_type" id="crrntl_rename_post_type" value="1" />&nbsp;<span class="bws_info"><?php _e( 'Enable to avoid conflicts with other plugins installed. All Cars created earlier will stay unchanged. However, after enabling we recommend to check settings of other plugins where "cars" post type is used.', 'car-rental' ); ?></span></label>
					</td>
				</tr>
			<?php }
		}

		public function additional_import_export_options() { ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Demo Data', 'car-rental' ); ?></th>
					<td>
						<?php $this->demo_data->bws_show_demo_button( __( 'Install demo-data to create demo-cars with images and details, demo-extras with images and details, manufacturers, vehicles types and car classes.', 'car-rental' ) ); ?>
					</td>
				</tr>
			</table>
		<?php }
	}
}
