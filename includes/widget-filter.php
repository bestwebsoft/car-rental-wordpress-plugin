<?php
/**
 * Widget for displaying Cars filters
 *
 * @subpackage Car Rental
 * @since      Car Rental 1.0.0
 */

if ( ! class_exists( 'Car_Rental_Filters_Widget' ) ) {
	class Car_Rental_Filters_Widget extends WP_Widget {

		/**
		 * Constructor.
		 *
		 * @since Car Rental 1.0.0
		 *
		 * @return Car_Rental_Filters_Widget
		 */
		public function __construct() {
			parent::__construct(
				'car-rental-filter',
				__( 'Car Rental filters', 'car-rental' ),
				array( 'description' => __( 'Widget for Car filters displaying.', 'car-rental' ) )
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
			global $wpdb, $crrntl_currency, $crrntl_options;
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
			$action_link            = ( ! empty( $crrntl_options['car_page_id'] ) ) ? get_permalink( $crrntl_options['car_page_id'] ) : '';
			$crrntl_manufacturers = get_terms( 'manufacturer' );
			$crrntl_vehicle_types = get_terms( 'vehicle_type' );

			$crrntl_min_max_pass  = $wpdb->get_results(
				"SELECT MIN( cast( meta_value AS UNSIGNED ) ) AS min_pass,
				MAX( cast( meta_value AS UNSIGNED ) ) AS max_pass
				FROM {$wpdb->postmeta} AS pm, {$wpdb->posts} AS po WHERE pm.post_id = po.ID AND po.post_status = 'publish' AND po.post_type = '{$crrntl_options['post_type_name']}' AND pm.meta_key = 'car_passengers'" );
			$crrntl_min_pass      = ( isset( $_GET['crrntl_pass_min'] ) ) ? $_GET['crrntl_pass_min'] : $crrntl_min_max_pass[0]->min_pass;
			$crrntl_max_pass      = ( isset( $_GET['crrntl_pass_max'] ) ) ? $_GET['crrntl_pass_max'] : $crrntl_min_max_pass[0]->max_pass;

			echo $args['before_widget'] . $args['before_title']; ?>
			<img class="widget-title-img" src="<?php echo plugins_url( 'car-rental/images/filter-results.png' ); ?>">
			<?php echo __( 'Filter', 'car-rental' ) . $args['after_title']; ?>
			<form action="<?php echo $action_link; ?>" method="get" id="crrntl-filter-form">
				<?php $isset_price = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->postmeta} AS pm, {$wpdb->posts} AS po WHERE pm.post_id = po.ID AND po.post_status = 'publish' AND po.post_type = '{$crrntl_options['post_type_name']}' AND pm.meta_key = 'car_price' AND pm.meta_value != 'on_request'" );
				if ( ! empty( $isset_price ) ) {
					$crrntl_min_max_price = $wpdb->get_results(
						"SELECT MIN( cast( meta_value AS DECIMAL(10, 2) ) ) AS min_price,
						MAX( cast( meta_value AS DECIMAL(10, 2) ) ) AS max_price
						FROM {$wpdb->postmeta} AS pm, {$wpdb->posts} AS po WHERE pm.post_id = po.ID AND po.post_status = 'publish' AND po.post_type = '{$crrntl_options['post_type_name']}' AND pm.meta_key = 'car_price'" );
					$crrntl_min_price     = ( isset( $_GET['crrntl_price_min'] ) ) ? $_GET['crrntl_price_min'] : floor( $crrntl_min_max_price[0]->min_price );
					$crrntl_max_price     = ( isset( $_GET['crrntl_price_max'] ) ) ? $_GET['crrntl_price_max'] : ceil( $crrntl_min_max_price[0]->max_price ); ?>
					<h4 class="clearfix"><?php _e( 'Price range', 'car-rental' ); ?>
						<span class="crrntl-select-clear">
							<a class="crrntl-reset-price" href="#"><?php _e( 'Reset', 'car-rental' ); ?></a>
						</span>
					</h4>
					<div class="crrntl-widget-content-range">
						<label><?php _e( 'from', 'car-rental' ); ?>: <input type="number" id="crrntl-price-min" name="crrntl_price_min" value="<?php echo $crrntl_min_price; ?>" /></label>
						<label> <?php _e( 'to', 'car-rental' ); ?>: <input type="number" id="crrntl-price-max" name="crrntl_price_max" value="<?php echo $crrntl_max_price; ?>" /></label>
						<div class="crrntl-price-range" data-min="<?php echo floor( $crrntl_min_max_price[0]->min_price ); ?>" data-max="<?php echo ceil( $crrntl_min_max_price[0]->max_price ); ?>"></div>
						<?php if ( ! empty( $crrntl_currency_position ) ) {
							if ( 'before' == $crrntl_currency_position ) { ?>
								<div class="crrntl-slider-result crrntl-price-result-from crrntl-hidden">
									<?php echo $crrntl_currency; ?><span><?php echo $crrntl_min_price; ?></span></div>
								<div class="crrntl-slider-result crrntl-price-result-to crrntl-hidden">
									<?php echo $crrntl_currency; ?><span><?php echo $crrntl_max_price; ?></span></div>
							<?php } else { ?>
								<div class="crrntl-slider-result crrntl-price-result-from crrntl-hidden">
									<span><?php echo $crrntl_min_price; ?></span> <?php echo $crrntl_currency; ?></div>
								<div class="crrntl-slider-result crrntl-price-result-to crrntl-hidden">
									<span><?php echo $crrntl_max_price; ?></span> <?php echo $crrntl_currency; ?></div>
							<?php }
						} else { ?>
							<div class="crrntl-slider-result crrntl-price-result-from crrntl-hidden">
								<span><?php echo $crrntl_min_price; ?></span></div>
							<div class="crrntl-slider-result crrntl-price-result-to crrntl-hidden">
								<span><?php echo $crrntl_max_price; ?></span></div>
						<?php } ?>
					</div><!-- .crrntl-widget-content-range -->
				<?php } ?>
				<h4 class="clearfix"><?php _e( 'Manufacturers', 'car-rental' ); ?>
					<span class="crrntl-select-clear"><a class="crrntl-clear-all" href="#"><?php _e( 'Clear', 'car-rental' ); ?></a> | <a class="crrntl-select-all" href="#"><?php _e( 'Select All', 'car-rental' ); ?></a>
					</span>
				</h4>
				<div class="widget-content crrntl-widget-filter" id="crrntl-manufacturers">
					<?php foreach ( $crrntl_manufacturers as $manufacturer ) {
						$i = '_' . uniqid(); ?>
						<div class="crrntl-filter">
							<input id="crrntl_manufacturers_<?php echo $manufacturer->term_id . $i; ?>" type="checkbox" class="styled" name="crrntl_manufacturer[]" value="<?php echo $manufacturer->term_id; ?>" <?php checked( ! empty( $_GET['crrntl_manufacturer'] ) && in_array( $manufacturer->term_id, $_GET['crrntl_manufacturer'] ) ); ?> />
							<label for="crrntl_manufacturers_<?php echo $manufacturer->term_id . $i; ?>"><?php echo $manufacturer->name; ?></label>
							<div class="crrntl-filter-quantity"><?php echo $manufacturer->count; ?></div>
						</div><!-- .crrntl-filter -->
					<?php } ?>
				</div><!-- .widget-content .crrntl-widget-filter -->
				<h4 class="clearfix"><?php _e( 'Number of seats', 'car-rental' ); ?>
					<span class="crrntl-select-clear">
						<a class="crrntl-reset-pass" href="#"><?php _e( 'Reset', 'car-rental' ); ?></a>
					</span>
				</h4>
				<div class="crrntl-widget-content-range">
					<label><?php _e( 'from', 'car-rental' ); ?>: <input type="number" id="crrntl-pass-min" name="crrntl_pass_min" value="<?php echo $crrntl_min_pass; ?>" /></label>
						<label> <?php _e( 'to', 'car-rental' ); ?>: <input type="number" id="crrntl-pass-max" name="crrntl_pass_max" value="<?php echo $crrntl_max_pass; ?>" /></label>
					<div class="crrntl-pass-range" data-min="<?php echo $crrntl_min_max_pass[0]->min_pass; ?>" data-max="<?php echo $crrntl_min_max_pass[0]->max_pass; ?>"></div>
					<div class="crrntl-slider-result crrntl-pass-result-from crrntl-hidden"><span><?php echo $crrntl_min_pass; ?></span></div>
					<div class="crrntl-slider-result crrntl-pass-result-to crrntl-hidden"><span><?php echo $crrntl_max_pass; ?></span></div>
				</div><!-- .crrntl-widget-content-range -->
				<h4 class="clearfix"><?php _e( 'Vehicle type', 'car-rental' ); ?>
					<span class="crrntl-select-clear">
						<a class="crrntl-clear-all" href="#"><?php _e( 'Clear', 'car-rental' ); ?></a> | <a class="crrntl-select-all" href="#"><?php _e( 'Select All', 'car-rental' ); ?></a>
					</span>
				</h4>
				<div class="widget-content crrntl-widget-filter" id="crrntl-vehicle-type">
					<?php foreach ( $crrntl_vehicle_types as $vehicle_type ) {
						$i = '_' . uniqid(); ?>
						<div class="crrntl-filter">
							<input id="crrntl_type_<?php echo $vehicle_type->term_id . $i; ?>" type="checkbox" class="styled" name="crrntl_vehicle_type[]" value="<?php echo $vehicle_type->term_id; ?>" <?php echo checked( ! empty( $_GET['crrntl_vehicle_type'] ) && in_array( $vehicle_type->term_id, $_GET['crrntl_vehicle_type'] ) ); ?> />
							<label for="crrntl_type_<?php echo $vehicle_type->term_id . $i; ?>"><?php echo $vehicle_type->name; ?></label>

							<div class="crrntl-filter-quantity"><?php echo $vehicle_type->count; ?></div>
						</div><!-- .crrntl-filter -->
					<?php } ?>
				</div><!-- #crrntl-vehicle-type .widget-content .crrntl-widget-filter -->
				<div class="widget-content crrntl-widget-filter">
					<input class="crrntl-orange-button crrntl-filter-form-update" type="submit" value="<?php _e( 'Apply filter', 'car-rental' ); ?>">
				</div><!-- .widget-content .crrntl-widget-filter -->
				<div class="clear"></div>
			</form><!-- #crrntl-filter-form -->

			<?php echo $args['after_widget'];
		}
	}
}
register_widget( 'Car_Rental_Filters_Widget' );
