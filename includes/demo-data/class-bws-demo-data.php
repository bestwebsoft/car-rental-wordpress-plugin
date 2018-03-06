<?php
/**
 * Load demo data
 * @version 1.0.2
 */

if ( ! class_exists( 'Crrntl_Demo_Data' ) ) {
	class Crrntl_Demo_Data {
		private $bws_plugin_basename, $bws_plugin_prefix, $bws_plugin_name, $bws_plugin_page, $bws_demo_folder, $bws_demo_options, $bws_plugin_options, $crrntl_car_notice;

		/**
		 * BWS_Demo_Data constructor.
		 *
		 * @param $args
		 */
		public function __construct( $args ) {
			$this->bws_plugin_basename		= $args['plugin_basename'];
			$this->bws_plugin_prefix		= $args['plugin_prefix'];
			$this->bws_plugin_name			= $args['plugin_name'];
			$this->bws_plugin_page			= $args['plugin_page'];
			$this->bws_demo_folder			= $args['demo_folder'];
			$this->bws_demo_options			= get_option( $this->bws_plugin_prefix . 'demo_options' );
			$this->bws_plugin_options		= get_option( $this->bws_plugin_prefix . 'options' );
		}

		/**
		 * Display "Install demo data" or "Uninstall demo data" buttons
		 *
		 * @param $form_info
		 */
		function bws_show_demo_button( $form_info ) {
			if ( ! ( is_multisite() && is_network_admin() ) ) {
				if ( empty( $this->bws_demo_options ) ) {
					$value        = 'install';
					$button_title = __( 'Install Demo Data', 'car-rental' );
				} else {
					$value        = 'remove';
					$button_title = __( 'Remove Demo Data', 'car-rental' );
					$form_info    = __( 'Delete demo-data and restore old plugin settings.', 'car-rental' );
				}
				if ( empty( $this->bws_demo_options ) && true == $this->crrntl_car_notice ) { ?>
					<button disabled class="button" name="bws_handle_demo" value="<?php echo $value; ?>"><?php echo $button_title; ?></button>
					<div class="bws_info"><?php _e( 'You have reached the limit for Cars.', 'car-rental' ); ?></div>
				<?php } else { ?>
					<button class="button" name="bws_handle_demo" value="<?php echo $value; ?>"><?php echo $button_title; ?></button>
					<div class="bws_info"><?php echo $form_info; ?></div>
				<?php }
			}
		}

		/**
		 * Display page for confirmation action to install demo data
		 * @return void
		 */
		function bws_demo_confirm() {
			if ( 'install' == $_POST['bws_handle_demo'] ) {
				$button_title = __( 'Yes, install demo data', 'car-rental' );
				$label        = __( 'Are you sure you want to install demo data?', 'car-rental' );
			} else {
				$button_title = __( 'Yes, remove demo data', 'car-rental' );
				$label        = __( 'Are you sure you want to remove demo data?', 'car-rental' );
			} ?>
			<div>
				<p><?php echo $label; ?></p>
				<form method="post" action="">
					<p>
						<button class="button button-primary" name="bws_<?php echo $_POST['bws_handle_demo']; ?>_demo_confirm" value="true"><?php echo $button_title; ?></button>
						<button class="button" name="bws_<?php echo $_POST['bws_handle_demo']; ?>_demo_deny" value="true"><?php _e( 'No, go back to the settings page', 'car-rental' ) ?></button>
						<?php wp_nonce_field( $this->bws_plugin_basename, 'bws_settings_nonce_name' ); ?>
					</p>
				</form>
			</div>
		<?php }

		/**
		 * Display confirm for actions with demo data
		 *
		 * @param bool $install_callback
		 * @param bool $remove_callback
		 *
		 * @return array
		 */
		function bws_handle_demo_data( $install_callback = false, $remove_callback = false ) {
			if ( isset( $_POST['bws_install_demo_confirm'] ) && check_admin_referer( $this->bws_plugin_basename, 'bws_settings_nonce_name' ) ) {
				return $this->bws_install_demo_data( $install_callback );
			} elseif ( isset( $_POST['bws_remove_demo_confirm'] ) && check_admin_referer( $this->bws_plugin_basename, 'bws_settings_nonce_name' ) ) {
				return $this->bws_remove_demo_data( $remove_callback );
			} else {
				return false;
			}
		}

		/**
		 * Load demo data
		 *
		 * @param bool|string $callback
		 *
		 * @return array $message   message about the result of the query
		 */
		function bws_install_demo_data( $callback = false ) {
			global $wpdb;
			/* get demo data*/
			$message   = array(
				'error'   => null,
				'done'    => null,
				'options' => null,
			);
			$demo_data = array(
				'posts'                        => null,
				'attachments'                  => null,
				'distant_attachments'          => null,
				'distant_attachments_metadata' => null,
				'terms'                        => null,
				'options'                      => null,
				'slides'                       => null,
			);
			$error     = 0;
			$page_id   = $posttype_post_id = $post_id = '';
			/* get demo data */
			@include_once( $this->bws_demo_folder . 'demo-data.php' );
			$received_demo_data = bws_demo_data_array( $this->bws_plugin_options['post_type_name'] );

			/**
			 * load demo data
			 */
			if ( empty( $received_demo_data ) ) {
				$message['error'] = __( 'Can not get demo data.', 'car-rental' );
			} else {
				$demo_data = array_merge( $demo_data, $received_demo_data );
				/*
				 * check if demo options already loaded
				 */
				if ( ! empty( $this->bws_demo_options ) ) {
					$message['error'] = __( 'Demo data has been already installed.', 'car-rental' );
					return $message;
				}

				/**
				 * load demo options
				 */
				if ( ! empty( $demo_data['options'] ) ) {
					$plugin_options = get_option( $this->bws_plugin_prefix . 'options' );
					/* remember old plugin options */
					if ( ! empty( $plugin_options ) ) {
						$this->bws_demo_options['options'] = $plugin_options;
						update_option( $this->bws_plugin_prefix . 'options', array_merge( $plugin_options, $demo_data['options'] ) );
					}
				}

				/**
				 * load demo slides
				 */
				if ( ! empty( $demo_data['slides'] ) ) {
					if ( ! get_option( 'crrntl_slider_options' ) ) {
						add_option( 'crrntl_slider_options' );
					}
					$current_slider_options = get_option( 'crrntl_slider_options' );
					if ( empty( $current_slider_options ) ) {
						$current_slider_options = array();
					}
					$slider_options = array();
					$this->bws_demo_options['slider_attachments'] = array();
					foreach ( $demo_data['slides'] as $demo_slide ) {
						if ( ! empty( $demo_slide['image'] ) ) {
							$wp_upload_dir           = wp_upload_dir();
							$attachments_folder      = $this->bws_demo_folder . 'images';
							$attachment = $demo_slide['image'];
							$file = $attachments_folder . '/' . $attachment;
							/* insert current attachment */
							/* Check if file is image */
							$file_data = @getimagesize( $file );
							$bws_is_image = ! ( $file_data || in_array( $file_data[2], array( 1, 2, 3 ) ) ) ? false : true;
							if ( $bws_is_image ) {
								$destination   = $wp_upload_dir['path'] . '/' . $this->bws_plugin_prefix . 'demo_' . $attachment; /* path to new file */
								$wp_filetype   = wp_check_filetype( $file, null ); /* Mime-type */

								if ( copy( $file, $destination ) ) { /* if attachment copied */

									$attachment_data = array(
										'post_mime_type' => $wp_filetype['type'],
										'post_title'     => $attachment,
										'post_content'   => '',
										'post_status'    => 'inherit'
									);

									/* insert attschment in to database */
									$attach_id = wp_insert_attachment( $attachment_data, $destination, 0 );
									if ( 0 != $attach_id ) {
										/* remember attachment ID */
										array_unshift( $this->bws_demo_options['slider_attachments'], $attach_id );
										$new_slider = array(
											'image'       => wp_get_attachment_url( $attach_id ),
											'title'       => $demo_slide['title'],
											'description' => $demo_slide['description'],
											'link'        => $demo_slide['link'],
										);
										array_unshift( $slider_options, $new_slider );
										array_unshift( $current_slider_options, $new_slider );

										/* insert attachment metadata */
										$attach_data = wp_generate_attachment_metadata( $attach_id, $destination );
										wp_update_attachment_metadata( $attach_id, $attach_data );
									} else {
										$error ++;
									}
								} else {
									$error ++;
								}
							}
						}
					}
					$current_slider_options = array_values( $current_slider_options );
					update_option( 'crrntl_slider_options', $current_slider_options );
					if ( ! empty( $slider_options ) ) {
						$this->bws_demo_options['slider'] = $slider_options;
					}
				}

				/**
				 * load demo data
				 */
				if ( ! empty( $demo_data['posts'] ) ) {
					$wp_upload_dir      = wp_upload_dir();
					$attachments_folder = $this->bws_demo_folder . 'images';

					/**
					 * load demo terms
					 */
					if ( ! empty( $demo_data['terms'] ) ) {
						foreach ( $demo_data['terms'] as $taxonomy_name => $terms_values_array ) {
							foreach ( $terms_values_array as $term_value_single ) {
								$term_exists = term_exists( $term_value_single['slug'], $taxonomy_name );
								if ( ! $term_exists ) {
									$term_id = wp_insert_term(
										$term_value_single['term'], /* the term. */
										$taxonomy_name, /* the taxonomy. */
										array(
											'slug' => $term_value_single['slug'],
											'description' => isset( $term_value_single['description'] ) ? $term_value_single['description'] : '',
											'parent' => isset( $term_value_single['parent'] ) ? $term_value_single['parent'] : 0,
										)
									);
									if ( is_wp_error( $term_id ) ) {
										$error ++;
										$display_extra_limitation_notice = true;
									} else {
										if ( isset( $term_value_single['meta'] ) && is_array( $term_value_single['meta'] ) && function_exists( 'crrntl_update_term_meta' ) ) {
											foreach ( $term_value_single['meta'] as $meta_key => $meta_value ) {
												if ( 'attachment' == $meta_key && is_array( $meta_value ) ) {
													foreach ( $meta_value as $attach_key => $attach_value ) {
														$wp_upload_dir           = wp_upload_dir();
														$attachments_folder      = $this->bws_demo_folder . 'images';
														$attachment = $attach_value;
														$file = $attachments_folder . '/' . $attachment;
														/* insert current attachment */
														/* Check if file is image */
														$file_data = @getimagesize( $file );
														$bws_is_image = ! ( $file_data || in_array( $file_data[2], array( 1, 2, 3 ) ) ) ? false : true;
														if ( $bws_is_image ) {
															$destination   = $wp_upload_dir['path'] . '/' . $this->bws_plugin_prefix . 'demo_' . $attachment; /* path to new file */
															$wp_filetype   = wp_check_filetype( $file, null ); /* Mime-type */

															if ( copy( $file, $destination ) ) { /* if attachment copied */

																$attachment_data = array(
																	'post_mime_type' => $wp_filetype['type'],
																	'post_title'     => $attachment,
																	'post_content'   => '',
																	'post_status'    => 'inherit'
																);

																/* insert attschment in to database */
																$attach_id = wp_insert_attachment( $attachment_data, $destination, 0 );
																if ( 0 != $attach_id ) {

																	/* insert attachment metadata */
																	$attach_data = wp_generate_attachment_metadata( $attach_id, $destination );
																	wp_update_attachment_metadata( $attach_id, $attach_data );

																	crrntl_update_term_meta( $term_id['term_id'], $attach_key, $attach_id );
																	/* remember attachment ID */
																	$this->bws_demo_options['attachments'][] = $attach_id;
																} else {
																	$error ++;
																}
															} else {
																$error ++;
															}
														}
													}
												} else {
													crrntl_update_term_meta( $term_id['term_id'], $meta_key, $meta_value );
												}
											}
										}
										$term_ids[ $taxonomy_name ][]     = $term_id['term_id'];
										$term_ids_new[ $taxonomy_name ][] = $term_id['term_id'];
									}
								} else {
									$term_ids[ $taxonomy_name ][] = $term_exists['term_id'];
								}
							}
						}
						if ( ! empty( $term_ids_new ) ) {
							$this->bws_demo_options['terms'] = isset( $this->bws_demo_options['terms'] ) ? array_merge( $this->bws_demo_options['terms'], $term_ids_new ) : $term_ids_new;
						}
					}

					/**
					 * load demo posts
					 */
					$this->bws_demo_options['pages'] = array();
					foreach ( $demo_data['posts'] as $demo_post ) {
						if ( preg_match( '/{last_post_id}/', $demo_post['post_content'] ) && ! empty( $post_id ) ) {
							$demo_post['post_content'] = preg_replace( '/{last_post_id}/', $post_id, $demo_post['post_content'] );
						}
						if ( preg_match( '/{template_page}/', $demo_post['post_content'] ) ) {
							if ( empty( $page_id ) && ! empty( $page_template ) ) {
								$page_id = intval( $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key LIKE '_wp_page_template' AND meta_value LIKE {$page_template} LIMIT 1" ) );
							}
							if ( ! empty( $page_id ) ) {
								$demo_post['post_content'] = preg_replace( '/{template_page}/', '<a href="' . get_permalink( $page_id ) . '">' . get_the_title( $page_id ) . '</a>', $demo_post['post_content'] );
							}
						}
						/* insert current post */
						$post_id = wp_insert_post( $demo_post, true );
						if ( 'post' == $demo_post['post_type'] ) {
							$posttype_post_id = $post_id;
						} elseif ( 'page' == $demo_post['post_type'] ) {
							$update = false;
							$templates 	= array(
								'bws-choose-car'		=> 'car_page_id',
								'bws-choose-extras'		=> 'extra_page_id',
								'bws-review-book'		=> 'review_page_id'
							);
							if ( isset( $demo_post['post_name'] ) && isset( $templates[ $demo_post['post_name'] ] ) ) {
								$page_index = $templates[ $demo_post['post_name'] ];
								$this->bws_demo_options['pages'][ $page_index ] = $post_id;
							}
						}

						/* add taxonomy for posttype */
						if ( 'post' != $demo_post['post_type'] && 'page' != $demo_post['post_type'] && ! empty( $term_ids ) ) {
							/* manually set terms */
							if ( ! empty( $demo_post['terms'] ) && is_array( $demo_post['terms'] ) ) {
								foreach ( $demo_post['terms'] as $taxonomy_name => $term_slag_array ) {
									if ( ! empty( $term_slag_array ) && is_array( $term_slag_array ) ) {
										$selected_term = array();
										foreach ( $term_slag_array as $term_slag ) {
											$selected_term_data = get_term_by( 'slug', $term_slag, $taxonomy_name, ARRAY_A );
											if ( ! empty( $selected_term_data ) ) {
												$selected_term[] = intval( $selected_term_data['term_id'] );
											}
										}
										if ( ! empty( $selected_term ) ) {
											if ( ! wp_set_object_terms( $post_id, $selected_term, $taxonomy_name, false ) ) {
												$error ++;
											}
										}
									}
								}
							} else {
								/* random set terms */
								foreach ( $term_ids as $taxonomy_name => $term_id_array ) {
									$selected_term = intval( $term_id_array[ rand( 0, ( count( $term_id_array ) - 1 ) ) ] );
									if ( ! wp_set_object_terms( $post_id, $selected_term, $taxonomy_name, false ) ) {
										$error ++;
									}
								}
							}
						}

						$attach_id = 0;

						if ( is_wp_error( $post_id ) || 0 == $post_id ) {
							$error ++;
						} else {
							/* remember post ID */
							$this->bws_demo_options['posts'][ $post_id ] = get_post_modified_time( 'U', false, $post_id, false );

							/**
							 * load post attachments
							 */
							if ( ! empty( $demo_post['attachment'] ) ) {
								$wp_upload_dir           = wp_upload_dir();
								$attachments_folder      = $this->bws_demo_folder . 'images';
								$attachment =$demo_post['attachment'];
								$file = $attachments_folder . '/' . $attachment;
								/* insert current attachment */
								/* Check if file is image */
								$file_data = @getimagesize( $file );
								$bws_is_image = ! ( $file_data || in_array( $file_data[2], array( 1, 2, 3 ) ) ) ? false : true;
								if ( $bws_is_image ) {
									$destination   = $wp_upload_dir['path'] . '/' . $this->bws_plugin_prefix . 'demo_' . $attachment; /* path to new file */
									$wp_filetype   = wp_check_filetype( $file, null ); /* Mime-type */

									if ( copy( $file, $destination ) ) { /* if attachment copied */

										$attachment_data = array(
											'post_mime_type' => $wp_filetype['type'],
											'post_title'     => $attachment,
											'post_content'   => '',
											'post_status'    => 'inherit'
										);

										/* insert attachment in to database */
										$attach_id = wp_insert_attachment( $attachment_data, $destination, 0 );
										if ( 0 != $attach_id ) {
											/* insert attachment metadata */
											$attach_data = wp_generate_attachment_metadata( $attach_id, $destination );
											wp_update_attachment_metadata( $attach_id, $attach_data );
											/* remember attachment ID */
											$this->bws_demo_options['attachments'][] = $attach_id;
										} else {
											$error ++;
										}
									} else {
										$error ++;
									}
								}
							} elseif ( ! empty( $demo_post['attachments_folder'] ) ) {
								$attachments_list = @scandir( $attachments_folder . '/' . $demo_post['attachments_folder'] );
								if ( 2 < count( $attachments_list ) ) {
									foreach ( $attachments_list as $attachment ) {
										$file = $attachments_folder . '/' . $demo_post['attachments_folder'] . '/' . $attachment;
										/* insert current attachment */
										/* Check if file is image */
										$file_data    = @getimagesize( $file );
										$bws_is_image = ! ( $file_data || in_array( $file_data[2], array( 1, 2, 3 ) ) ) ? false : true;
										if ( $bws_is_image ) {

											$destination = $wp_upload_dir['path'] . '/' . $this->bws_plugin_prefix . 'demo_' . $attachment; /* path to new file */
											$wp_filetype = wp_check_filetype( $file, null ); /* Mime-type */

											if ( copy( $file, $destination ) ) { /* if attachment copied */

												$attachment_data = array(
													'post_mime_type' => $wp_filetype['type'],
													'post_title'     => $attachment,
													'post_content'   => '',
													'post_status'    => 'inherit',
												);

												/* insert attachment in to database */
												$attach_id = wp_insert_attachment( $attachment_data, $destination, $post_id );
												if ( 0 != $attach_id ) {
													/* remember attachment ID */
													$this->bws_demo_options['attachments'][] = $attach_id;

													/* insert attachment metadata */
													$attach_data = wp_generate_attachment_metadata( $attach_id, $destination );
													wp_update_attachment_metadata( $attach_id, $attach_data );
													/* insert additional metadata */
													if ( isset( $demo_data['attachments'][ $attachment ] ) ) {
														foreach ( $demo_data['attachments'][ $attachment ] as $meta_key => $meta_value ) {
															if ( '{get_lorem_ipsum}' == $meta_value ) {
																$meta_value = $this->bws_get_lorem_ipsum();
															}
															add_post_meta( $attach_id, $meta_key, $meta_value );
														}
													}
												} else {
													$error ++;
												}
											} else {
												$error ++;
											}
										}
									}
								}
							}

							/*
							 * load post attachments
							 */
							if ( ! empty( $demo_post['distant_attachments'] ) ) {
								foreach ( $demo_post['distant_attachments'] as $attachment_name ) {
									if ( $data = $demo_data['distant_attachments_metadata'][ $attachment_name ] ) {

										$attachment_data = array(
											'post_mime_type' => $data['mime_type'],
											'post_title'     => $data['title'],
											'post_content'   => '',
											'post_status'    => 'inherit',
										);

										/* insert attachment in to database */
										$attach_id = wp_insert_attachment( $attachment_data, $data['url'], $post_id );
										if ( 0 != $attach_id ) {
											/* remember attachment ID */
											$this->bws_demo_options['distant_attachments'][ $attachment_name ] = $attach_id;

											/* insert attachment metadata */
											$imagesize = @getimagesize( $data['url'] );
											$sizes = ( isset( $data['sizes'] ) ) ? $data['sizes'] : array();
											$attach_data = array(
												'width' 	=> $imagesize[0],
												'height' 	=> $imagesize[1],
												'file' 		=> $data['url'],
												'sizes' 	=> $sizes,
											);

											wp_update_attachment_metadata( $attach_id, $attach_data );

											/* insert additional metadata */
											if ( isset( $demo_data['distant_attachments'][ $attachment_name ] ) ) {
												foreach ( $demo_data['distant_attachments'][ $attachment_name ] as $meta_key => $meta_value ) {
													if ( '{get_lorem_ipsum}' == $meta_value ) {
														$meta_value = $this->bws_get_lorem_ipsum();
													}
													add_post_meta( $attach_id, $meta_key, $meta_value );
												}
											}
										} else {
											$error ++;
										}
									} else {
										$error ++;
									}
								}
							}

							/* insert additional post meta */
							if ( ! empty( $demo_post['post_meta'] ) ) {
								foreach ( $demo_post['post_meta'] as $meta_key => $meta_value ) {
									add_post_meta( $post_id, $meta_key, $meta_value );
								}
							}
							/* set template for post type "page" */
							if ( ! empty( $demo_post['page_template'] ) ) {
								update_post_meta( $post_id, '_wp_page_template', $demo_post['page_template'] );
								$page_id       = $post_id;
								$page_template = $demo_post['page_template'];
							}
							/* last inserted image is thumbnail for post */
							if ( 0 != $attach_id ) {
								update_post_meta( $post_id, '_thumbnail_id', $attach_id );
							}
						}
					}

					/**
					 * Save demo options
					 */
					add_option( $this->bws_plugin_prefix . 'demo_options', $this->bws_demo_options );

					if ( empty( $error ) ) {
						$message['done'] = __( 'Demo data successfully installed.', 'car-rental' );
						if ( ! empty( $posttype_post_id ) ) {
							$message['done'] .= '<br />' . __( 'View post with shortcodes', 'car-rental' ) . ':&nbsp;<a href="' . get_permalink( $posttype_post_id ) . '" target="_blank">' . get_the_title( $posttype_post_id ) . '</a>';
						}
						if ( ! empty( $page_id ) ) {
							$message['done'] .= '<br />' . __( 'View page with examples', 'car-rental' ) . ':&nbsp;<a href="' . get_permalink( $page_id ) . '" target="_blank">' . get_the_title( $page_id ) . '</a>';
						}

						if ( ! empty( $demo_data['options'] ) ) {
							$message['options'] = $demo_data['options'];
						}

						if ( $callback && function_exists( $callback ) ) {
							call_user_func( $callback );
						}
					} else {
						$message['error'] = __( 'Some errors occurred during demo data installation.', 'car-rental' );
						if ( ! empty( $display_extra_limitation_notice ) ) {
							$message['error'] .= '<br />' . __( 'You have reached the limit for Extras.', 'car-rental' );
						}
					}
				} else {
					$message['error'] = __( 'Posts data is missing.', 'car-rental' );
				}
			}
			if ( function_exists( 'crrntl_update_locations' ) ) {
				crrntl_update_locations( $this->bws_plugin_options['post_type_name'] );
			}
			if ( function_exists( 'crrntl_update_pages_id' ) ) {
				crrntl_update_pages_id( true );
			}

			return $message;
		}

		/**
		 * Change url for distant attachments
		 *
		 * @param $url
		 * @param $id
		 *
		 * @return string $url
		 */
		function bws_wp_get_attachment_url( $url, $id ) {
			if ( ! empty( $this->bws_demo_options['distant_attachments'] ) && in_array( $id, $this->bws_demo_options['distant_attachments'] ) ) {
				$url = substr( $url, strpos( $url, 'https://' ) );
			}
			return $url;
		}

		/**
		 * Replace metadata to default for images after saving ( to prevent editing image )
		 *
		 * @param $data
		 * @param $id
		 *
		 * @return array $data
		 */
		function bws_wp_update_attachment_metadata( $data, $id ) {
			if ( ! empty( $data ) && ! empty( $this->bws_demo_options['distant_attachments'] ) && $attachment_name = array_search( $id, $this->bws_demo_options['distant_attachments'] ) ) {
				/* get demo data */
				@include_once( $this->bws_demo_folder . 'demo-data.php' );
				$received_demo_data = bws_demo_data_array( $this->bws_plugin_options['post_type_name'] );

				if ( isset( $received_demo_data['distant_attachments_metadata'][ $attachment_name ] ) ) {

					/* insert attachment metadata */
					$imagesize = @getimagesize( $received_demo_data['distant_attachments_metadata'][ $attachment_name ]['url'] );
					$sizes = ( isset( $received_demo_data['distant_attachments_metadata'][ $attachment_name ]['sizes'] ) ) ? $received_demo_data['distant_attachments_metadata'][ $attachment_name ]['sizes'] : array();
					$data = array(
						'width'  => $imagesize[0],
						'height' => $imagesize[1],
						'file'   => $received_demo_data['distant_attachments_metadata'][ $attachment_name ]['url'],
						'sizes'  => $sizes,
					);
				}
			}
			return $data;
		}

		/**
		 * Change url for distant attachments
		 *
		 * @param      $attr
		 * @param      $attachment
		 * @param bool $size
		 *
		 * @return string $attr
		 */
		function bws_wp_get_attachment_image_attributes( $attr, $attachment, $size = false ) {
			if ( ! empty( $attr['srcset'] ) && ! empty( $this->bws_demo_options['distant_attachments'] ) && in_array( $attachment->ID, $this->bws_demo_options['distant_attachments'] ) ) {
				$srcset = explode( ', ', $attr['srcset'] );
				foreach ( $srcset as $key => $value ) {
					$srcset[ $key ] = substr( $value, strpos( $value, 'https://' ) );
				}
				$attr['srcset'] = implode( ', ', $srcset );
			}
			return $attr;
		}

		/**
		 * Remove demo data
		 *
		 * @param $callback
		 *
		 * @return array $message   message about the result of the query
		 */
		function bws_remove_demo_data( $callback ) {
			$error   = 0;
			$message = array(
				'error'   => null,
				'done'    => null,
				'options' => null,
			);

			if ( empty( $this->bws_demo_options ) ) {
				$message['error'] = __( 'Demo data have already been removed.', 'car-rental' );
			} else {

				/**
				 * Restore plugin options
				 */
				if ( ! empty( $this->bws_demo_options['options'] ) ) {
					$this->bws_demo_options['options']['display_demo_notice'] = 0;
					update_option( $this->bws_plugin_prefix . 'options', $this->bws_demo_options['options'] );
					if ( $callback && function_exists( $callback ) ) {
						call_user_func( $callback );
					}
				}
				$done = $this->bws_delete_demo_option();
				if ( ! $done ) {
					$error ++;
				}

				/**
				 * Delete all slides
				 */
				if ( ! empty( $this->bws_demo_options['slider'] ) ) {
					$current_slider_options = get_option( 'crrntl_slider_options' );
					if ( ! empty( $current_slider_options ) ) {
						foreach ( $this->bws_demo_options['slider'] as $key => $value ) {
							if ( isset( $current_slider_options[ $key ] ) ) {
								$source = $current_slider_options[ $key ];
								$result = array_diff_assoc( $source, $value );
								if ( empty( $result ) ) {
									unset( $current_slider_options[ $key ] );
									if ( isset( $this->bws_demo_options['slider_attachments'][ $key ] ) ) {
										$done = wp_delete_attachment( $this->bws_demo_options['slider_attachments'][ $key ], true );
										if ( ! $done ) {
											$error ++;
										}
									}
								}
							}
						}
						update_option( 'crrntl_slider_options', $current_slider_options );
					}
				}

				/**
				 * Delete all posts
				 */
				if ( ! empty( $this->bws_demo_options['posts'] ) ) {
					foreach ( $this->bws_demo_options['posts'] as $post_id => $last_modified ) {
						/* delete only not modified posts */
						if ( get_post_modified_time( 'U', false, $post_id, false ) == $last_modified ) {
							$done = wp_delete_post( $post_id, true );
							if ( ! $done ) {
								$error ++;
							}
						}
					}
				}

				/**
				 * Delete terms
				 */
				if ( ! empty( $this->bws_demo_options['terms'] ) ) {
					foreach ( $this->bws_demo_options['terms'] as $taxonomy_name => $terms_values_array ) {
						foreach ( $terms_values_array as $term_id ) {
							wp_delete_term( $term_id, $taxonomy_name );
						}
					}
				}

				/**
				 * Delete all attachments
				 */
				if ( ! empty( $this->bws_demo_options['attachments'] ) ) {
					foreach ( $this->bws_demo_options['attachments'] as $post_id ) {
						$done = wp_delete_attachment( $post_id, true );
						if ( ! $done ) {
							$error ++;
						}
					}
				}
				if ( ! empty( $this->bws_demo_options['distant_attachments'] ) ) {
					foreach ( $this->bws_demo_options['distant_attachments'] as $post_id ) {
						$done = wp_delete_attachment( $post_id, true );
						if ( ! $done ) {
							$error ++;
						}
					}
				}

				if ( empty( $error ) ) {
					$message['done']    = __( 'Demo data successfully removed.', 'car-rental' );
					$message['options'] = get_option( $this->bws_plugin_prefix . 'options' );
					$this->bws_demo_options = array();
				} else {
					$message['error'] = __( 'Some errors occurred during demo data removing.', 'car-rental' );
				}
			}
			if ( function_exists( 'crrntl_clear_locations' ) ) {
				crrntl_clear_locations( $this->bws_plugin_options['post_type_name'] );
			}
			if ( function_exists( 'crrntl_update_locations' ) ) {
				crrntl_update_locations( $this->bws_plugin_options['post_type_name'] );
			}

			return $message;
		}

		/**
		 * Delete demo-options
		 * @return boolean
		 */
		function bws_delete_demo_option() {
			$done = delete_option( $this->bws_plugin_prefix . 'demo_options' );

			return $done;
		}

		function bws_handle_demo_notice( $show_demo_notice ) {

			if ( ! empty( $show_demo_notice ) ) {
				global $wp_version;

				if ( isset( $_POST['bws_hide_demo_notice'] ) && check_admin_referer( $this->bws_plugin_basename, 'bws_demo_nonce_name' ) ) {
					$plugin_options                        = get_option( $this->bws_plugin_prefix . 'options' );
					$plugin_options['display_demo_notice'] = 0;
					update_option( $this->bws_plugin_prefix . 'options', $plugin_options );

					return;
				}
				if ( ( ! isset( $_POST['bws_handle_demo'] ) && ! isset( $_POST['bws_install_demo_confirm'] ) ) && empty( $this->bws_demo_options ) ) {
					if ( 4.2 > $wp_version ) {
						$plugin_dir_array = explode( '/', plugin_basename( __FILE__ ) ); ?>
						<style type="text/css">
							#bws_handle_notice_form {
								float: right;
								width: 20px;
								height: 20px;
								margin-bottom: 0;
							}
							.bws_hide_demo_notice {
								width: 100%;
								height: 100%;
								border: none;
								background: url("<?php echo plugins_url( $plugin_dir_array[0] . '/bws_menu/images/close_banner.png' ); ?>") no-repeat center center;
								box-shadow: none;
								position: relative;
								top: -4px;
								cursor: pointer;
							}
							.rtl #bws_handle_notice_form {
								float: left;
							}
						</style>
					<?php } ?>
					<div class="update-nag" style="position: relative;">
						<form id="bws_handle_notice_form" action="" method="post">
							<button class="notice-dismiss bws_hide_demo_notice" title="<?php _e( 'Close notice', 'car-rental' ); ?>"></button>
							<input type="hidden" name="bws_hide_demo_notice" value="hide" />
							<?php wp_nonce_field( $this->bws_plugin_basename, 'bws_demo_nonce_name' ); ?>
						</form>
						<div style="margin: 0 20px;"><a href="<?php echo admin_url( $this->bws_plugin_page ); ?>"><?php _e( 'Install demo data', 'car-rental' ); ?></a>&nbsp;<?php echo __( 'for an acquaintance with the possibilities of the', 'car-rental' ) . '&nbsp;' . $this->bws_plugin_name; ?>.</div>
					</div>
				<?php }
			}
		}

		/**
		 * Generate Lorem Ipsum
		 * @return   string
		 */
		function bws_get_lorem_ipsum() {
			$lorem_ipsum = array(
				'Fusce quis varius quam, non molestie dui. ',
				'Ut eu feugiat eros. Aliquam justo mauris, volutpat eu lacinia et, bibendum non velit. ',
				'Aenean in justo et nunc facilisis varius feugiat quis elit. ',
				'Proin luctus non quam in bibendum. ',
				'Sed finibus, risus eu blandit ullamcorper, sapien urna vulputate ante, quis semper magna nibh vel orci. ',
				'Nullam eu aliquam erat. ',
				'Suspendisse massa est, feugiat nec dolor non, varius finibus massa. ',
				'Sed velit justo, semper ut ante eu, feugiat ultricies velit. ',
				'Ut sed velit ut nisl laoreet malesuada vitae non elit. ',
				'Integer eu sem justo. Nunc sit amet erat tristique, mollis neque et, iaculis purus. ',
				'Vestibulum sit amet varius sapien. Quisque maximus tempor scelerisque. ',
				'Ut eleifend, felis vel rhoncus cursus, purus ipsum consectetur ex, nec elementum mauris ipsum eget quam. ',
				'Integer sem diam, iaculis in arcu vel, pulvinar scelerisque magna. ',
				'Cras rhoncus neque aliquet, molestie justo id, finibus erat. ',
				'Proin eleifend, eros et interdum faucibus, ligula dui accumsan sem, ac tristique dolor erat vel est. ',
				'Etiam ut nulla risus. Aliquam non consequat turpis, id hendrerit magna. Suspendisse potenti. ',
				'Donec fringilla libero ac sapien porta ultricies. ',
				'Donec sapien lacus, blandit vitae fermentum vitae, accumsan ut magna. ',
				'Curabitur maximus lorem lectus, eu porta ipsum fringilla eu. ',
				'Integer vitae justo ultricies, aliquam neque in, venenatis nunc. ',
				'Pellentesque non nulla venenatis, posuere erat id, faucibus leo. ',
				'Nullam fringilla sodales arcu, nec rhoncus lorem fringilla in. ',
				'Quisque consequat lorem vel nisl pharetra iaculis. Donec aliquet interdum tristique. Sed ullamcorper urna odio. ',
				'Nam dictum dictum neque id congue. ',
				'Donec quis quam id turpis condimentum condimentum. ',
				'Morbi tincidunt, nunc nec pellentesque scelerisque, tortor eros efficitur lectus, eget molestie lacus est eu est. ',
				'Morbi non augue a tellus interdum condimentum id ac enim. ',
				'In dictum velit ultricies, dictum est ac, tempus arcu. ',
				'Duis maximus, mi nec pulvinar suscipit, arcu purus vestibulum urna, ',
				'consectetur rutrum mi sapien et massa. Donec faucibus ex vel nibh consequat, ut molestie lacus elementum. ',
				'Interdum et malesuada fames ac ante ipsum primis in faucibus. ',
				'Phasellus quam dolor, convallis vel nulla sed, pretium tristique felis. ',
				'Morbi condimentum nunc vel augue tincidunt, in porttitor metus interdum. Sed nec venenatis elit. ',
				'Donec non urna dui. Maecenas sit amet venenatis eros, sed aliquam metus. ',
				'Nulla venenatis eros ac velit pellentesque, nec semper orci faucibus. ',
				'Etiam sit amet dapibus lacus, non semper erat. ',
				'Donec dolor metus, iaculis nec lacinia a, tristique sed libero. ',
				'Phasellus a quam gravida, tincidunt metus ac, eleifend odio. ',
				'Integer facilisis mauris ut velit gravida ornare. Quisque viverra sagittis lacus, non dapibus turpis iaculis sit amet. ',
				'Vestibulum vehicula pulvinar blandit. ',
				'Praesent sit amet consectetur augue, vitae tincidunt nulla. ',
				'Curabitur metus nibh, molestie vel massa in, egestas dapibus felis. ',
				'Phasellus id erat massa. Aliquam bibendum purus ac ante imperdiet, mattis gravida dui mollis. ',
				'Fusce id purus et mauris condimentum fermentum. ',
				'Fusce tempus et purus ut fringilla. Suspendisse ornare et ligula in gravida. ',
				'Nunc id nunc mauris. Curabitur auctor sodales felis, nec dapibus urna pellentesque et. ',
				'Phasellus quam dolor, convallis vel nulla sed, pretium tristique felis. ',
				'Morbi condimentum nunc vel augue tincidunt, in porttitor metus interdum. ',
				'Sed scelerisque eget mauris et sagittis. ',
				'In eget enim nec arcu malesuada malesuada. ',
				'Nulla eu odio vel nibh elementum vestibulum vel vel magna. ',
			);

			return $lorem_ipsum[ rand( 0, 50 ) ];
		}
	}
}