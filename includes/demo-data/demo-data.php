<?php
/**
 * Contents array with demo-data for
 * Car Rental Plugin by BestWebSoft
 */

if ( ! function_exists( 'bws_demo_data_array' ) ) {
	function bws_demo_data_array( $post_type ) {
		global $wpdb;
		/* Locations demo data */
		$car_place_id          = 'ChIJD7fiBh9u5kcRYJSMaMOCCwQ';
		$car_formatted_address = 'Paris, France';
		$loc_id                = $wpdb->get_var( "SELECT loc_id FROM {$wpdb->prefix}crrntl_locations WHERE place_id = '{$car_place_id}'" );
		if ( null == $loc_id ) {
			$wpdb->insert(
				$wpdb->prefix . 'crrntl_locations',
				array(
					'place_id'          => $car_place_id,
					'formatted_address' => $car_formatted_address,
					'status'            => 'active'
				),
				array( '%s', '%s', '%s' )
			);
			$loc_id = $wpdb->insert_id;
		}

		$posts = array(
			/* Posts demo data */
			array(
				'comment_status'	=> 'closed',
				'ping_status'		=> 'closed',
				'post_content'		=> '',
				'post_name'			=> 'bws-choose-car',
				'post_status'		=> 'publish',
				'post_title'		=> 'BWS Choose Car',
				'post_type'			=> 'page',
			),
			array(
				'comment_status'	=> 'closed',
				'ping_status'		=> 'closed',
				'post_content'		=> '',
				'post_name'			=> 'bws-choose-extras',
				'post_status'		=> 'publish',
				'post_title'		=> 'BWS Choose Extras',
				'post_type'			=> 'page',
			),
			array(
				'comment_status'	=> 'closed',
				'ping_status'		=> 'closed',
				'post_content'		=> '',
				'post_name'			=> 'bws-review-book',
				'post_status'		=> 'publish',
				'post_title'		=> 'BWS Review & Book',
				'post_type'			=> 'page',
			),
			array(
				'comment_status'	=> 'closed',
				'ping_status'		=> 'closed',
				'post_content'		=> '<ul>
											<li>6-speaker radio/CD system</li>
											<li>Escaro black fabric</li>
											<li>Hybrid System display</li>
											<li>Vehicle Stability Control</li>
											<li>Hill-start Assist Control</li>
										</ul>',
				'post_name'			=> 'demo-ford-escape',
				'post_status'		=> 'publish',
				'post_title'		=> 'DEMO Ford Escape',
				'post_type'			=> $post_type,
				'terms'				=> array(
					'manufacturer'	=> array(
						'demo-ford'
					),
					'vehicle_type'	=> array(
						'demo-economy'
					),
					'car_class'		=> array(
						'demo-class-a'
					),
					'extra'			=> array(
						'demo-hand-controls',
						'demo-infant-child-seats',
						'demo-neverlost-gps-navigator',
					),
				),
				'post_meta'			=> array(
					'car_info'			=> array(
						'transmission'		=> '1',
						'luggage_large'		=> '3',
						'luggage_small'		=> '2',
						'condition'			=> '1',
						'consumption'		=> '9',
						'doors'				=> 5,
					),
					'car_passengers'	=> 4,
					'car_price'			=> '299.00',
					'car_location'		=> $loc_id,
				),
				'attachment'		=> 'product-3.png',
			),
			array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_content'   => '<ul>
										<li>6-speaker radio/CD system</li>
										<li>Escaro black fabric</li>
										<li>Hybrid System display</li>
										<li>Vehicle Stability Control</li>
										<li>Hill-start Assist Control</li>
									</ul>',
				'post_name'      => 'demo-chevrolet-aveo',
				'post_status'    => 'publish',
				'post_title'     => 'DEMO Chevrolet Aveo',
				'post_type'      => $post_type,
				'terms'      => array(
					'manufacturer' => array(
						'demo-chevrolet'
					),
					'vehicle_type' => array(
						'demo-compact'
					),
					'car_class'    => array(
						'demo-class-b'
					),
					'extra'        => array(
						'demo-hand-controls',
						'demo-infant-child-seats',
						'demo-neverlost-gps-navigator',
					),
				),
				'post_meta'      => array(
					'car_info'       => array(
						'transmission'  => '1',
						'luggage_large' => '1',
						'luggage_small' => '1',
						'condition'     => '1',
						'consumption'   => '13',
						'doors'         => 5,
					),
					'car_passengers' => 3,
					'car_price'      => '360.99',
					'car_location'   => $loc_id,
				),
				'attachment'     => 'product-2.png',
			),
			array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_content'   => '<ul>
										<li>6-speaker radio/CD system</li>
										<li>Escaro black fabric</li>
										<li>Hybrid System display</li>
										<li>Vehicle Stability Control</li>
										<li>Hill-start Assist Control</li>
									</ul>',
				'post_name'      => 'demo-toyota-corolla',
				'post_status'    => 'publish',
				'post_title'     => 'DEMO Toyota Corolla',
				'post_type'      => $post_type,
				'terms'      => array(
					'manufacturer' => array(
						'demo-toyota'
					),
					'vehicle_type' => array(
						'demo-intermediate'
					),
					'car_class'    => array(
						'demo-class-c'
					),
					'extra'        => array(
						'demo-hand-controls',
						'demo-infant-child-seats',
						'demo-neverlost-gps-navigator',
					),
				),
				'post_meta'      => array(
					'car_info'       => array(
						'transmission'  => '1',
						'luggage_large' => '2',
						'luggage_small' => '1',
						'condition'     => '1',
						'consumption'   => '14',
						'doors'         => 5,
					),
					'car_passengers' => 5,
					'car_price'      => '845.25',
					'car_location'   => $loc_id,
				),
				'attachment'     => 'product-1.png',
			),
		);

		/* Terms demo data */
		$terms = array(
			'manufacturer' => array(
				array(
					'term' => 'DEMO Ford',
					'slug' => 'demo-ford',
				),
				array(
					'term' => 'DEMO Chevrolet',
					'slug' => 'demo-chevrolet',
				),
				array(
					'term' => 'DEMO Toyota',
					'slug' => 'demo-toyota',
				),
			),
			'vehicle_type' => array(
				array(
					'term' => 'DEMO Economy',
					'slug' => 'demo-economy',
				),
				array(
					'term' => 'DEMO Compact',
					'slug' => 'demo-compact',
				),
				array(
					'term' => 'DEMO Intermediate',
					'slug' => 'demo-intermediate',
				),
			),
			'car_class'    => array(
				array(
					'term' => 'DEMO Class A',
					'slug' => 'demo-class-a',
				),
				array(
					'term' => 'DEMO Class B',
					'slug' => 'demo-class-b',
				),
				array(
					'term' => 'DEMO Class C',
					'slug' => 'demo-class-c',
				),
			),
			'extra'        => array(
				array(
					'term'        => 'DEMO Hand Controls',
					'description' => 'Left or Right steering wheel controls for the physically challenged.',
					'slug'        => 'demo-hand-controls',
					'meta'        => array(
						'extra_details'  => 'Proin a ipsum neque, sit amet adipiscing est. Donec iaculis erat ut ante ultricies at congue lectus lobortis. Maecenas ac varius felis. Nam sollicitudin dignissim nisl, non pretium urna luctus vitae. Phasellus et dolor ipsum, a vestibulum est. Phasellus eros leo, rutrum ac tempor nec.',
						'extra_price'    => '85.59',
						'extra_quantity' => 0,
						'attachment' => array(
							'extra_image' => 'extras-3.png',
						),
					),
				),
				array(
					'term'        => 'DEMO Infant Child Seats',
					'description' => 'For infants less than one year and under 9kg.',
					'slug'        => 'demo-infant-child-seats',
					'meta'        => array(
						'extra_details'  => 'Proin a ipsum neque, sit amet adipiscing est. Donec iaculis erat ut ante ultricies at congue lectus lobortis. Maecenas ac varius felis. Nam sollicitudin dignissim nisl, non pretium urna luctus vitae. Phasellus et dolor ipsum, a vestibulum est. Phasellus eros leo, rutrum ac tempor nec.',
						'extra_price'    => '100.00',
						'extra_quantity' => 1,
						'attachment' => array(
							'extra_image' => 'extras-2.png',
						),
					),
				),
				array(
					'term'        => 'DEMO NeverLost GPS Navigator',
					'description' => 'Satellite Navigation System provides turn-by-turn directions. If your pickup and return location are not the same you may be charged a surcharge fee',
					'slug'        => 'demo-neverlost-gps-navigator',
					'meta'        => array(
						'extra_details'  => 'Proin a ipsum neque, sit amet adipiscing est. Donec iaculis erat ut ante ultricies at congue lectus lobortis. Maecenas ac varius felis. Nam sollicitudin dignissim nisl, non pretium urna luctus vitae. Phasellus et dolor ipsum, a vestibulum est. Phasellus eros leo, rutrum ac tempor nec.',
						'extra_price'    => '129.99',
						'extra_quantity' => 0,
						'attachment'     => array(
							'extra_image'    => 'extras-1.png',
						),
					),
				),
			),
		);

		$slides = array(
			array(
				'link'        => 'https://bestwebsoft.com/plugin/',
				'description' => 'Nunc in turpis a massa luctus fermentum. Aenean vel lacus massa. Lorem ipsum dolor sit amet, consectetur adipiscing elit. nulla malesuada mauris enim, euismod interdum sem iaculis vel',
				'title'       => 'Donec sodales justo',
				'image'       => 'slide-1.jpg',
			),
			array(
				'link'        => 'https://bestwebsoft.com/services/',
				'description' => 'Vestibulum velit diam, interdum sed nibh quis, blandit ultricies nibh. Curabitur pulvinar euismod lacus in posuere. Proin id urna blandit, commodo massa feugiat, laoreet sapien.',
				'title'       => 'Proin dolor nunc',
				'image'       => 'slide-2.jpg',
			),
			array(
				'link'        => 'https://bestwebsoft.com/contacts/',
				'description' => 'Aenean aliquam purus eget eros tristique, eget rutrum turpis suscipit. Sed non erat ut tellus pretium eleifend id at lorem. Aliquam venenatis justo nulla, ac condimentum felis mattis at. Praesent at erat eu elit malesuada sagittis id eget enim.',
				'title'       => 'Curabitur adipiscing erat',
				'image'       => 'slide-3.jpg',
			),
		);

		return array(
			'posts'  => $posts,
			'terms'  => $terms,
			'slides' => $slides,
		);
	}
}
