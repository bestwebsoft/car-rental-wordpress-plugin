<?php
/**
 * Template Name: Car Search Form
 *
 * @subpackage Car Rental
 * @since      Car Rental 1.0.0
 */
global $wpdb, $crrntl_plugin_directory, $crrntl_options, $crrntl_is_main_page, $crrntl_is_carrental_template;
if ( empty( $crrntl_options ) ) {
	$crrntl_options = get_option( 'crrntl_options' );
}
$crrntl_location_list = $wpdb->get_results( "SELECT `loc_id`, `formatted_address` FROM `{$wpdb->prefix}crrntl_locations` WHERE status='active' ORDER BY `formatted_address`", ARRAY_A );
$action_link = ( ! empty( $crrntl_options['car_page_id'] ) ) ? get_permalink( $crrntl_options['car_page_id'] ) : '';
$extras_page_templ = false;
$disabled_form = '';
if ( empty( $crrntl_plugin_directory ) ) {
	$crrntl_plugin_directory  = plugins_url( 'car-rental' );
}

if ( ! empty( $crrntl_is_carrental_template ) ) {
	$home_page_templ = false;
} else {
	$home_page_templ = true;
}

if ( is_page_template( 'page-choose-extras.php' ) ) {
	$extras_page_templ = true;
	$disabled_form = ' crrntl-disabled-form';
}

$date_format = crrntl_get_date_format();
$pattern = "/\w{1,}/";
$date_format_new = preg_replace_callback( $pattern, 'crrntl_convert_jsformat_tophp', $date_format );

if ( ! empty( $_POST['crrntl_location'] ) ) {
	$_SESSION['crrntl_location'] = $_POST['crrntl_location'];
}

$crrntl_date_from = ( empty( $_SESSION['crrntl_date_from'] ) || ! is_int( $_SESSION['crrntl_date_from'] ) ) ? ( strtotime( '+1day ' . $crrntl_options['min_from'] ) ) : $_SESSION['crrntl_date_from'];
$crrntl_date_to = ( empty( $_SESSION['crrntl_date_to'] ) || ! is_int( $_SESSION['crrntl_date_to'] ) ) ? ( strtotime( '+2days ' . $crrntl_options['max_to'] ) ) : $_SESSION['crrntl_date_to'];

$crrntl_car_classes = get_terms( 'car_class' ); ?>
<form id="crrntl-slider-form" action="<?php echo $action_link; ?>" class="crrntl-main-form" method="post">
	<?php if ( $home_page_templ ) { ?>
		<div id="crrntl-book-car" class="crrntl-title-form crrntl-current">
			<?php _e( 'Book a Car', 'car-rental' ); ?>
		</div>
	<?php } else { ?>
		<div id="crrntl-book-car" class="crrntl-title-form crrntl-current">
			<?php if ( $extras_page_templ ) { ?>
				<img src="<?php echo $crrntl_plugin_directory . '/images/edit-location.png'; ?>" alt="" />
				<?php _e( 'Edit Locations & Dates', 'car-rental' );
			} else { ?>
				<img src="<?php echo $crrntl_plugin_directory . '/images/search.png'; ?>" alt="" />
				<?php _e( 'Search for a car', 'car-rental' ); ?>:
			<?php } ?>
		</div><!-- #crrntl-book-car .crrntl-title-form .crrntl-current -->
	<?php } ?>
	<div id="crrntl-book-car-content" class="crrntl-content-form">
		<?php echo ( $extras_page_templ && ! isset( $_POST['crrntl_edit_submit'] ) ) ? '<div class="crrntl-disabled-form-overlay"></div>' : ''; ?>
		<div class="crrntl-form-block crrntl-location-block">
			<h4><?php _e( 'Location', 'car-rental' ); ?></h4>
			<select id="crrntl-pickup-location" class="crrntl-location-select" name="crrntl_location" title="<?php _e( 'Choose location', 'car-rental' ); ?>">
				<option value=""><?php _e( 'Any location', 'car-rental' ); ?></option>
				<?php foreach ( $crrntl_location_list as $one_location ) { ?>
					<option value="<?php echo $one_location['loc_id']; ?>" <?php selected( ! empty( $_SESSION['crrntl_location'] ) && ( $one_location['loc_id'] ) == $_SESSION['crrntl_location'] ); ?>><?php echo $one_location['formatted_address']; ?></option>
				<?php } ?>
			</select><!-- #crrntl-pickup-location .crrntl-location-select -->
			<?php if ( ! empty( $crrntl_options['return_location_selecting'] ) ) { ?>
				<input id="crrntl-location-checkbox" type="checkbox" class="styled" name="crrntl_checkbox_location" value="1" <?php checked( ! empty( $_SESSION['crrntl_checkbox_location'] ) ); ?> />
				<label for="crrntl-location-checkbox"> <?php _e( 'Return at different location', 'car-rental' ); ?></label>
				<div class="crrntl-location-block crrntl-return-location">
					<h4><?php _e( 'Return location', 'car-rental' ); ?></h4>
					<div class="clear"></div>
					<select id="crrntl-dropoff-location" class="crrntl-location-select" name="crrntl_return_location" title="<?php _e( 'Choose location', 'car-rental' ); ?>">
						<option value=""><?php _e( 'Choose location', 'car-rental' ); ?></option>
						<?php foreach ( $crrntl_location_list as $one_location ) { ?>
							<option value="<?php echo $one_location['loc_id']; ?>" <?php selected( ! empty( $_SESSION['crrntl_return_location'] ) && ( $one_location['loc_id'] ) == $_SESSION['crrntl_return_location'] ); ?>><?php echo $one_location['formatted_address']; ?></option>
						<?php } ?>
					</select><!-- #crrntl-dropoff-location .crrntl-location-select -->
				</div><!-- .crrntl-form-block .crrntl-location -->
				<div class="clear"></div>
			<?php } ?>
		</div><!-- .crrntl-location-block .crrntl-return-location -->
		<div class="crrntl-form-block crrntl-pick-up">
			<h4><?php _e( 'Pick Up date', 'car-rental' ); ?></h4>
			<input class="datepicker" type="text" value="<?php echo date( $date_format_new, $crrntl_date_from ); ?>" name="crrntl_date_from"  title="<?php _e( 'Choose Pick Up date', 'car-rental' ); ?>" placeholder="<?php echo date( $date_format_new, time() ); ?>" required="required" />
			<?php if ( ! empty( $crrntl_options['time_selecting'] ) ) {
				$i_min = explode( ':', $crrntl_options['min_from'] );
				$i_max = explode( ':', $crrntl_options['max_to'] ); ?>
				<select class="crrntl-time-select" name="crrntl_time_from" title="<?php _e( 'Choose Pick Up time', 'car-rental' ); ?>">
					<?php for ( $i = $i_min[0]; $i <= $i_max[0]; $i ++ ) {
						if ( $i != $i_min[0] || $i_min[1] != '30' ) { ?>
							<option value="<?php echo $i; ?>:00" <?php selected( $i . ':00' == date( "G:i", $crrntl_date_from ) ); ?>><?php echo $i; ?>:00</option>
						<?php }
						if ( $i != $i_max[0] || $i_max[1] != '00' ) { ?>
							<option value="<?php echo $i; ?>:30" <?php selected( $i . ':30' == date( "G:i", $crrntl_date_from ) ); ?>><?php echo $i; ?>:30</option>
						<?php }
					} ?>
				</select><!-- .crrntl-time-select -->
			<?php } ?>
		</div><!-- .crrntl-form-block .crrntl-pick-up -->
		<div class="crrntl-form-block crrntl-drop-off">
			<h4><?php _e( 'Drop Off date', 'car-rental' ); ?></h4>
			<input class="datepicker" type="text" value="<?php echo date( $date_format_new, $crrntl_date_to ); ?>" name="crrntl_date_to"  title="<?php _e( 'Choose Drop Off date', 'car-rental' ); ?>" placeholder="<?php echo date( $date_format_new, time() ); ?>" required="required" />
			<?php if ( ! empty( $crrntl_options['time_selecting'] ) ) { ?>
				<select class="crrntl-time-select" name="crrntl_time_to" title="<?php _e( 'Choose Drop Off time', 'car-rental' ); ?>">
					<?php for ( $i = $i_min[0]; $i <= $i_max[0]; $i ++ ) {
						if ( $i != $i_min[0] || $i_min[1] != '30' ) { ?>
							<option value="<?php echo $i; ?>:00" <?php selected( $i . ':00' == date( "G:i", $crrntl_date_to ) ); ?>><?php echo $i; ?>:00</option>
						<?php } 
						if ( $i != $i_max[0] || $i_max[1] != '00' ) { ?>
							<option value="<?php echo $i; ?>:30" <?php selected( $i . ':30' == date( "G:i", $crrntl_date_to ) ); ?>><?php echo $i; ?>:30</option>
						<?php } 
					} ?>
				</select><!-- .crrntl-time-select -->
			<?php } ?>
		</div><!-- .crrntl-form-block .crrntl-drop-off -->
		<div class="crrntl-form-block crrntl-car-type">
			<h4><?php _e( 'Car Class', 'car-rental' ); ?></h4>
			<div class="crrntl-car-type-select">
				<select name="crrntl_select_carclass" title="<?php _e( 'Choose Car Class', 'car-rental' ); ?>">
					<option value="0"><?php _e( 'Choose Class', 'car-rental' ); ?></option>
					<?php foreach ( $crrntl_car_classes as $one_class ) { ?>
						<option value="<?php echo $one_class->term_id; ?>" <?php selected( ! empty( $_SESSION['crrntl_select_carclass'] ) && $one_class->term_id == $_SESSION['crrntl_select_carclass'] ); ?>><?php echo $one_class->name; ?></option>
					<?php } ?>
				</select>
			</div><!-- .crrntl-car-type-select -->
		</div><!-- .crrntl-form-block .crrntl-car-type -->
		<div class="crrntl-form-block crrntl-form-submit">
			<?php if ( $extras_page_templ && ! isset( $_POST['crrntl_edit_submit'] ) ) { ?>
				<input class="crrntl-orange-button crrntl-form-edit" type="submit" value="<?php _e( 'Edit', 'car-rental' ); ?>" />
				<input class="crrntl-orange-button crrntl-form-update crrntl-hidden" type="submit" value="<?php _e( 'Update', 'car-rental' ); ?>" />
				<input type="hidden" name="crrntl_edit_submit" value="crrntl_edit_submit" />
			<?php } elseif ( $home_page_templ ) { ?>
				<input class="crrntl-orange-button crrntl-form-continue" type="submit" value="<?php _e( 'Continue', 'car-rental' ); ?>" />
			<?php } else { ?>
				<input class="crrntl-orange-button crrntl-form-update" type="submit" value="<?php _e( 'Update', 'car-rental' ); ?>" />
			<?php } ?>
			<input type="hidden" name="crrntl_search_submit" value="crrntl_search_submit" />
		</div><!-- .crrntl-form-block .crrntl-form-submit -->
		<div class="clear"></div>
	</div><!-- #crrntl-book-car-content .crrntl-content-form -->
	<div class="clear"></div>
</form><!-- #crrntl-slider-form .crrntl-main-form -->