<?php

/*
Additional post settings support

*/

if ( preg_match( '/wp-admin\/nav-menus.php|wp-admin\/admin-ajax.php/', $_SERVER['PHP_SELF'] ) ) {
	return;
}

/* add metaboxes to Post, Page and Shop admin pages */
add_action( 'add_meta_boxes', 'masb_add_settings_meta_box' );
remove_action( 'add_meta_boxes', array( 'RevSliderBaseAdmin', 'onAddMetaboxes' ) );

/* function to prevent distortion hierarhical order of output category list at the admin page */
add_filter( 'wp_terms_checklist_args', 'masb_checklist_args' );

add_action( 'load-post-new.php', 'masb_set_blog_screen' );
add_action( 'admin_head-post.php', 'masb_set_blog_screen' );

add_action( 'save_post', 'masb_settings_save' );

function masb_add_settings_meta_box() {
	$screens = array(
		'post'        => SL_Marketing_and_SEO_Booster::$blog_keys,
		'page'        => SL_Marketing_and_SEO_Booster::$keys,
		'portfolio'   => SL_Marketing_and_SEO_Booster::$keys,
		'service'     => SL_Marketing_and_SEO_Booster::$keys,
		'teammate'    => SL_Marketing_and_SEO_Booster::$keys,
		'testimonial' => SL_Marketing_and_SEO_Booster::$keys,
		'product'     => SL_Marketing_and_SEO_Booster::$shop_keys,
	);
	foreach ( $screens as $screen => $screen_keys ) {
		add_meta_box( 'layout_settings',
			esc_html__( 'Page Settings', 'marketing-and-seo-booster' ),
			'masb_settings_proccess',
			$screen,
			'side',
			'low',
			$screen_keys );
	}
}

function masb_checklist_args( $args ) {
	$args['checked_ontop'] = false;

	return $args;
}

function masb_set_blog_screen( $hook ) {
	global $secretlab, $post;

	if ( $post ) {
		if ( $post->post_type == 'page' || $post->post_type != 'post' ) {
			$secretlab['admin_post_new'] = false;

			return;
		}
	}
	if ( preg_match( '/post_type=page/', $_SERVER['REQUEST_URI'] ) ) {
		$secretlab['admin_post_new'] = false;

		return;
	}

	$secretlab['admin_post_new'] = true;
}

/* main functions */

function masb_settings_proccess( $post, $screen_keys ) {

	global $secretlab;

	$settings   = json_decode( get_post_meta( $post->ID, 'layout_settings', true ), true );
	$native_set = $settings;
	$home       = get_template_directory_uri();
	$sidebars   = SL_Marketing_and_SEO_Booster::get_sidebars();

	if ( $post->post_type == 'post' || $secretlab['admin_post_new'] ) {
		$blog_page = true;
	} else {
		$blog_page = false;
	}

	if ( ! $blog_page ) {
		$regular_page = true;
		$r_hidden     = '';
		$r_class      = '';
		$b_hidden     = '';
		$b_class      = 'locked ';
		$s_hidden     = '_locked';
		$s_class      = ' locked';
	} else if ( $blog_page ) {
		$regular_page = false;
		$b_hidden     = '';
		$b_class      = '';
		$r_hidden     = '';
		$r_class      = 'locked ';
		$s_hidden     = '_locked';
		$s_class      = ' locked';
	}

	if ( is_array( $screen_keys['args'] ) ) {

		echo '<div id="secretlab-post-layout-settings">';

		echo '<div id="manage_type_selector" class="custom_settings_box">';
		if ( is_array( $settings ) && count( $settings ) > 0 ) {
			$active               = 'checked';
			$visible_settings_box = 'visible ';
			$box                  = $settings;
			$visible              = '';
		} else {
			$active               = 'checked';
			$visible_settings_box = 'visible ';
			$box                  = $secretlab;
			$visible              = '';
		}

		echo "<div id='subtitle'>" . '<a id="manage_type_selector_input" class="meta_btn" href="#">' . esc_html__( 'Reset Settings',
				'marketing-and-seo-booster' ) . '</a>' . "</div>";
		echo '<input id="sealed" class="hidden" name="sealed" type="text" value="1" >';

		echo '<div id="secretlab-post-layout-settings-box" ' . 'class="' . $visible_settings_box . /*$b_class.*/
		     '"' . ' n=' . $post->ID . '>';
		foreach ( $screen_keys['args'] as $key => $description ) {

			$background_repeat_settings = array(
				'default'   => esc_html__( 'Use Global Settings', 'marketing-and-seo-booster' ),
				'no-repeat' => esc_html__( 'No Repeat', 'marketing-and-seo-booster' ),
				'repeat'    => esc_html__( 'Repeat All', 'marketing-and-seo-booster' ),
				'repeat-x'  => esc_html__( 'Repeat Horizontally', 'marketing-and-seo-booster' ),
				'repeat-y'  => esc_html__( 'Repeat Vertically', 'marketing-and-seo-booster' ),
				'inherit'   => esc_html__( 'Inherit', 'marketing-and-seo-booster' ),
			);

			$background_size_settings = array(
				'default' => esc_html__( 'Use Global Settings', 'marketing-and-seo-booster' ),
				'inherit' => esc_html__( 'Inherit', 'marketing-and-seo-booster' ),
				'cover'   => esc_html__( 'Cover', 'marketing-and-seo-booster' ),
				'contain' => esc_html__( 'Contain', 'marketing-and-seo-booster' ),
			);

			$background_attachment_settings = array(
				'default' => esc_html__( 'Use Global Settings', 'marketing-and-seo-booster' ),
				'fixed'   => esc_html__( 'Fixed', 'marketing-and-seo-booster' ),
				'scroll'  => esc_html__( 'Scroll', 'marketing-and-seo-booster' ),
				'inherit' => esc_html__( 'Inherit', 'marketing-and-seo-booster' ),
			);

			$background_position_settings = array(
				'default'       => esc_html__( 'Use Global Settings', 'marketing-and-seo-booster' ),
				'left top'      => esc_html__( 'Left Top', 'marketing-and-seo-booster' ),
				'left center'   => esc_html__( 'Left center', 'marketing-and-seo-booster' ),
				'left bottom'   => esc_html__( 'Left Bottom', 'marketing-and-seo-booster' ),
				'center top'    => esc_html__( 'Center Top', 'marketing-and-seo-booster' ),
				'center center' => esc_html__( 'Center Center', 'marketing-and-seo-booster' ),
				'center bottom' => esc_html__( 'Center Bottom', 'marketing-and-seo-booster' ),
				'right top'     => esc_html__( 'Right Top', 'marketing-and-seo-booster' ),
				'right center'  => esc_html__( 'Right center', 'marketing-and-seo-booster' ),
				'right bottom'  => esc_html__( 'Right Bottom', 'marketing-and-seo-booster' ),
			);

			if ( ! isset( $settings[ $key ] ) ) {
				if ( isset( $secretlab[ $key ] ) ) {
					$setting = $secretlab[ $key ];
				}
			} else {
				$setting = $settings[ $key ];
			}
			if ( preg_match( '/(blog|shop|design)-layout/', $key ) == 1 ) {
				SL_Marketing_and_SEO_Booster::img_selector( $key,
					array( $home . '/images/framework/wide.gif', $home . '/images/framework/boxed.gif' ),
					$setting,
					$screen_keys['args'],
					$b_hidden );
			}
			if ( preg_match( '/boxed-background/', $key ) == 1 ) {

				echo '<div id="' . $key . '" class="custom_settings_box' . $visible . '">';
				echo '<div class="key_header"><span>' . esc_html__( 'Boxed Background',
						'marketing-and-seo-booster' ) . '</span></div>';

				if ( is_array( $description ) ) {

					foreach ( $description as $field_id => $field_description ) {

						switch ( $field_id ) {

							case 'background-color':
								if ( is_null( $settings ) ) {
									$color = '';
								} else {
									if ( ! empty( $setting ) ) {
										if ( ! is_array( $setting ) ) {
											$color = $setting;
										} else {
											$color = ( isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '' );
										}
									} else {
										$color = '';
									}
								}
								echo '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box ' . $field_id . '">';
								echo '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
								echo '<input id="' . $key . '-' . $field_id . '_input" class="slnm-color-rgba wp-color-picker ' . $field_id . '_input" name="' . esc_attr( $key . '[' . $field_id . ']' . $b_hidden ) . '" value="' . esc_attr( $color ) . '" data-alpha="true" >';
								echo '<input type="hidden" id="' . $key . '-' . $field_id . '_input_default" class="' . $field_id . '_input_default" name="' . esc_attr( $key . '[' . $field_id . $b_hidden ) . '_default]" value="' . esc_attr( $secretlab[ $key ][ $field_id ] ) . '" data-alpha="true" >';
								echo '</div>';
								break;

							case 'background-image':
								echo '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box ' . $field_id . '">';
								echo '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
								echo '<div id="' . $field_id . '_uploader">';
								echo '<div id="' . $field_id . '_upload_box">';
								if ( empty( $setting[ $field_id ] ) && ! empty( $setting['media'] ) && is_array( $setting['media'] ) && ! empty( $setting['media']['id'] ) ) {
									$att_url              = wp_get_attachment_image_src( $setting['media']['id'] );
									$setting[ $field_id ] = array(
										'url' => $att_url[0],
										'id'  => $setting['media']['id'],
									);
								} elseif ( ! empty( $setting[ $field_id ] ) && is_array( $setting[ $field_id ] ) ) {
								} elseif ( ! empty( $setting[ $field_id ] ) && ! empty( $setting['media'] ) && is_array( $setting['media'] ) && ! empty( $setting['media']['id'] ) ) {
									$setting[ $field_id ] = array(
										'url' => $setting[ $field_id ],
										'id'  => $setting['media']['id'],
									);
								} elseif ( ! empty( $setting[ $field_id ] ) && ! empty( $setting['media'] ) && ! is_array( $setting['media'] ) ) {
									$setting[ $field_id ] = array( 'url' => $setting[ $field_id ], 'id' => '' );
								} else {
									$setting              = false === $setting ? [] : $setting;
									$setting[ $field_id ] = array( 'url' => '', 'id' => '' );
								}
								if ( ! isset( $setting ) || $setting[ $field_id ]['url'] == '' || $setting[ $field_id ]['url'] == 'none' ) {
									$visible_el = 'hidden';
								} else {
									$visible_el = 'visible';
								}
								if ( $setting[ $field_id ]['url'] !== 'none' ) {
									echo '<img id="' . $key . '-' . $field_id . '_holder" class="' . $visible_el . ' ' . $field_id . '_holder" img_n="' . $setting[ $field_id ]['id'] . '" src="' . esc_url( $setting[ $field_id ]['url'] ) . '" width="200px" height="200px">';
								}
								echo '</div>';
								echo '<div id="boxed_upload_link_box">';
								echo '<a href="#" class="meta_btn ' . $field_id . '_change_img" id="' . $key . '-' . $field_id . '_change_img">' . esc_html__( 'Add/change',
										'marketing-and-seo-booster' ) . '</a><br>';
								echo '<a href="#" class="' . $visible_el . ' img-remove meta_btn ' . $field_id . '_remove_img" id="' . $key . '-' . $field_id . '_remove_img">' . esc_html__( 'Remove',
										'marketing-and-seo-booster' ) . '</a>';
								echo '</div>';
								echo '</div>';
								echo '<input id="' . $key . '-' . $field_id . '_input" class="hidden ' . $field_id . '_input" type="text" value="' . esc_attr( $setting[ $field_id ]['url'] ) . '" name="' . esc_attr( $key . '[' . $field_id . '][url]' . $b_hidden ) . '" >';
								echo '<input id="hiden-' . $key . '-' . $field_id . '_input" class="hidden ' . $field_id . '_input" type="hidden" value="' . esc_attr( $setting[ $field_id ]['id'] ) . '" name="' . esc_attr( $key . '[' . $field_id . '][id]' . $b_hidden ) . '" >';
								echo '</div>';
								break;

							case 'background-repeat':
								$visible = ' visible';
								$fake    = '';
								echo '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box' . $visible . '">';
								echo '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
								echo '<select id="' . $field_id . '_select" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
								$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '';
								echo masb_select_options_render( $background_repeat_settings, $correct );
								echo '</select>';
								echo '</div>';
								break;

							case 'background-size':

								$visible = ' visible';
								$fake    = '';
								echo '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box' . $visible . '">';
								echo '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
								echo '<select id="' . $field_id . '_select" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
								$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '';
								echo masb_select_options_render( $background_size_settings, $correct );
								echo '</select>';
								echo '</div>';
								break;

							case 'background-attachment':

								$visible = ' visible';
								$fake    = '';
								echo '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box' . $visible . '">';
								echo '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
								echo '<select id="' . $field_id . '_select" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
								$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '';
								echo masb_select_options_render( $background_attachment_settings, $correct );
								echo '</select>';
								echo '</div>';
								break;

							case 'background-position':

								$visible = ' visible';
								$fake    = '';
								echo '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box' . $visible . '">';
								echo '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
								echo '<select id="' . $field_id . '_select" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
								$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '';
								echo masb_select_options_render( $background_position_settings, $correct );
								echo '</select>';
								echo '</div>';
								break;

						} // End switch

					}

				}

				echo '</div>';

			}

			if ( preg_match( '/content-background/', $key ) == 1 ) {

				echo '<div id="' . $key . '" class="custom_settings_box' . $visible . '">';
				echo '<div class="key_header"><span>' . esc_html__( 'Content Background',
						'marketing-and-seo-booster' ) . '</span></div>';

				if ( is_array( $description ) ) {

					foreach ( $description as $field_id => $field_description ) {

						switch ( $field_id ) {

							case 'background-color':
								if ( is_null( $settings ) ) {
									$color = '';
								} else {
									if ( ! empty( $setting ) ) {
										if ( ! is_array( $setting ) ) {
											$color = $setting;
										} else {
											$color = ( isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '' );
										}
									} else {
										$color = '';
									}
								}
								echo '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box ' . $field_id . '">';
								echo '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
								echo '<input id="' . $key . '-' . $field_id . '_input" class="slnm-color-rgba wp-color-picker ' . $field_id . '_input" name="' . esc_attr( $key . '[' . $field_id . ']' . $b_hidden ) . '" value="' . esc_attr( $color ) . '" data-alpha="true" >';
								echo '</div>';
								break;

							case 'background-image':
								echo '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box' . $visible . ' ' . $field_id . '">';
								echo '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
								echo '<div id="' . $field_id . '_uploader">';
								echo '<div id="' . $field_id . '_upload_box">';
								if ( empty( $setting[ $field_id ] ) && ! empty( $setting['media'] ) && is_array( $setting['media'] ) && ! empty( $setting['media']['id'] ) ) {
									$att_url              = wp_get_attachment_image_src( $setting['media']['id'] );
									$setting[ $field_id ] = array(
										'url' => $att_url[0],
										'id'  => $setting['media']['id'],
									);
								} elseif ( ! empty( $setting[ $field_id ] ) && is_array( $setting[ $field_id ] ) ) {
								} elseif ( ! empty( $setting[ $field_id ] ) && ! empty( $setting['media'] ) && is_array( $setting['media'] ) && ! empty( $setting['media']['id'] ) ) {
									$setting[ $field_id ] = array(
										'url' => $setting[ $field_id ],
										'id'  => $setting['media']['id'],
									);
								} elseif ( ! empty( $setting[ $field_id ] ) && ! empty( $setting['media'] ) && ! is_array( $setting['media'] ) ) {
									$setting[ $field_id ] = array( 'url' => $setting[ $field_id ], 'id' => '' );
								} else {
									$setting              = false === $setting ? [] : $setting;
									$setting[ $field_id ] = array( 'url' => '', 'id' => '' );
								}
								if ( ! isset( $setting ) || $setting[ $field_id ]['url'] == '' || $setting[ $field_id ]['url'] == 'none' ) {
									$visible_el = 'hidden';
								} else {
									$visible_el = 'visible';
								}
								if ( $setting[ $field_id ]['url'] !== 'none' ) {
									echo '<img id="' . $key . '-' . $field_id . '_holder" class="' . $visible_el . ' ' . $field_id . '_holder" img_n="' . $setting[ $field_id ]['id'] . '" src="' . esc_url( $setting[ $field_id ]['url'] ) . '" width="200px" height="200px">';
								}
								echo '</div>';
								echo '<div id="boxed_upload_link_box">';
								echo '<a href="#" class="meta_btn ' . $field_id . '_change_img" id="' . $key . '-' . $field_id . '_change_img">' . esc_html__( 'Add/change',
										'marketing-and-seo-booster' ) . '</a><br>';
								echo '<a href="#" class="' . $visible_el . ' img-remove meta_btn ' . $field_id . '_remove_img" id="' . $key . '-' . $field_id . '_remove_img">' . esc_html__( 'Remove',
										'marketing-and-seo-booster' ) . '</a>';
								echo '</div>';
								echo '</div>';
								echo '<input id="' . $key . '-' . $field_id . '_input" class="hidden ' . $field_id . '_input" type="text" value="' . esc_attr( $setting['background-image']['url'] ) . '" name="' . esc_attr( $key . '[' . $field_id . '][url]' . $b_hidden ) . '" >';
								echo '<input id="hiden-' . $key . '-' . $field_id . '_input" class="hidden ' . $field_id . '_input" type="hidden" value="' . esc_attr( $setting[ $field_id ]['id'] ) . '" name="' . esc_attr( $key . '[' . $field_id . '][id]' . $b_hidden ) . '" >';
								echo '</div>';
								break;

							case 'background-repeat':
								$visible = ' visible';
								$fake    = '';
								echo '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box' . $visible . '">';
								echo '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
								echo '<select id="' . $field_id . '_select" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
								$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '';
								echo masb_select_options_render( $background_repeat_settings, $correct );
								echo '</select>';
								echo '</div>';
								break;

							case 'background-size':

								$visible = ' visible';
								$fake    = '';
								echo '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box' . $visible . '">';
								echo '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
								echo '<select id="' . $field_id . '_select" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
								$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '';
								echo masb_select_options_render( $background_size_settings, $correct );
								echo '</select>';
								echo '</div>';
								break;

							case 'background-attachment':

								$visible = ' visible';
								$fake    = '';
								echo '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box' . $visible . '">';
								echo '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
								echo '<select id="' . $field_id . '_select" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
								$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '';
								echo masb_select_options_render( $background_attachment_settings, $correct );
								echo '</select>';
								echo '</div>';
								break;

							case 'background-position':

								$visible = ' visible';
								$fake    = '';
								echo '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box' . $visible . '">';
								echo '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
								echo '<select id="' . $field_id . '_select" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
								$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '';
								echo masb_select_options_render( $background_position_settings, $correct );
								echo '</select>';
								echo '</div>';
								break;

						} // End switch

					}

				}

				echo '</div>';

			}

			if ( preg_match( '/sidebar-layout/', $key ) == 1 ) {
				SL_Marketing_and_SEO_Booster::img_selector( $key,
					array(
						$home . '/images/framework/nosidebar.gif',
						$home . '/images/framework/2sidebars.gif',
						$home . '/images/framework/leftsidebar.gif',
						$home . '/images/framework/rightsidebar.gif',
					),
					$setting,
					$screen_keys['args'],
					$b_hidden );
				if ( $setting == 1 ) {
					$show_blog_columns = ' visible';
				} else {
					$show_blog_columns = ' hidden';
				}
			}
			if ( preg_match( '/header_type/', $key ) == 1 ) {
				$setting = json_decode( get_post_meta( $post->ID, 'layout_settings', true ), true );
				if ( isset( $setting[ $key ] ) && $setting[ $key ] != '' ) {
					$setting = $header_type = $setting[ $key ];
				} else {
					$setting = $header_type = null;
				}
				$visible = ' visible';
				$fake    = '';
				echo '<div id="' . $key . '" class="custom_settings_box' . $visible . '">';
				echo '<div class="key_header"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
				echo '<select id="' . $key . '_select" name="' . $key . esc_attr( $fake . $r_hidden ) . '" >';
				echo '<option value="1" ' . selected( $setting, 1, false ) . '>' . esc_html__( 'Slider',
						'marketing-and-seo-booster' ) . '</option>';
				echo '<option value="2" ' . selected( $setting, 2, false ) . '>' . esc_html__( 'Static Image',
						'marketing-and-seo-booster' ) . '</option>';
				echo '</select>';
				echo '</div>';
			}
			if ( preg_match( '/header_image/', $key ) == 1 ) {
				$setting = json_decode( get_post_meta( $post->ID, 'layout_settings', true ), true );
				if ( $setting && $setting != '' && isset( $setting[ $key ] ) && $setting[ $key ] != '' ) {
					$img     = $setting[ $key ];
					$visible = ' visible';
				} else {
					$img     = '';
					$visible = ' hidden';
				}
				if ( $header_type == "2" ) {
					$header_image = true;
					$visible      = ' visible';
				} else {
					$header_image = false;
					$visible      = ' hidden';
				}
				echo '<div id="' . $key . '" class="header_image custom_settings_box' . $visible . '">';
				echo '<div class="key_header"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
				echo '<div id="' . $key . '_selector">';
				echo '<input type="text" class="header_img" name="' . $key . '" value="' . $img . '">';
				echo '<div class="header_img_preview"><img src="' . $img . '"></div>';
				echo '<a id="' . $key . '_loader" class="meta_btn header_img upload" href="#">' . esc_html__( 'Upload Header Image for this Post',
						'marketing-and-seo-booster' ) . '</a>';
				echo '<a id="' . $key . '_remover" class="meta_btn img-remove" href="#">' . esc_html__( 'Remove Header Image',
						'marketing-and-seo-booster' ) . '</a>';
				echo '</div>';
				echo '</div>';
			}
			if ( ! empty( $sidebars ) && preg_match( '/left_sidebar_widgets/', $key ) == 1 ) {
				echo '<div id="' . $key . '" class="custom_settings_box">';
				echo '<div class="key_header"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
				echo '<select id="' . $key . '" class="regular_widget" name=' . $key . $b_hidden . ' >';
				foreach ( $sidebars as $id => $name ) {
					if ( $id == $setting ) {
						$selected = ' selected ';
					} else {
						$selected = ' ';
					}
					echo '<option ' . $selected . ' value="' . $id . '">' . $name . '</option>';
				}
				echo '</select>';
				echo '</div>';
			}
			if ( ! empty( $sidebars ) && preg_match( '/right_sidebar_widgets/', $key ) == 1 ) {
				echo '<div id="' . $key . '" class="custom_settings_box">';
				echo '<div class="key_header"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
				echo '<select id="' . $key . '" class="regular_widget" name=' . esc_attr( $key . $b_hidden ) . ' >';
				foreach ( $sidebars as $id => $name ) {
					if ( $id == $setting ) {
						$selected = ' selected ';
					} else {
						$selected = ' ';
					}
					echo '<option ' . $selected . ' value="' . $id . '">' . $name . '</option>';
				}
				echo '</select>';
				echo '</div>';
				//echo '</div>';
			}
			if ( 1 === preg_match( '/pick_slider/', $key ) ) {
				if ( $setting == '' ) {
					$setting == '0';
				}
				$visible = ' visible';
				$fake    = '';
				if ( $header_image ) {
					$visible = ' hidden';
				}
				echo '<div id="' . $key . '" class="custom_settings_box' . $visible . '">';
				echo '<div class="key_header"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
				echo '<select id="' . $key . '_select" name="' . $key . esc_attr( $fake . $b_hidden ) . '" >';
				$sliders = SL_Marketing_and_SEO_Booster::get_sliders_array();
				if ( isset( $native_set[ $key ] ) ) {
					if ( count( $sliders ) > 1 ) {
						$sliders = SL_Marketing_and_SEO_Booster::array_insert( $sliders,
							array( 'default' => 'Use Global Slider' ),
							0 );
					}
					foreach ( $sliders as $alias => $name ) {
						$selected = ( (string) $setting == (string) $alias ) ? 'selected' : '';
						echo '<option value="' . esc_attr( $alias ) . '"' . $selected . '>' . esc_html( $name ) . '</option>';
					}
				} else {
					echo '<option value="default" selected>' . esc_html__( 'Use Global Slider',
							'marketing-and-seo-booster' ) . '</option>';
					foreach ( $sliders as $alias => $name ) {
						echo "<option value='" . esc_attr( $alias ) . "'>" . esc_html( $name ) . '</option>';
					}
				}
				echo "</select>";
				echo "</div>";
			}
			if ( class_exists( 'custom_post_widget' ) && preg_match( '/header_widget/', $key ) == 1 ) {
				if ( class_exists( 'Atiframebuilder_Config' ) ) {
					$header_widget = Atiframebuilder_Config::get_composer_block_array( 'header' );
				} elseif ( class_exists( 'Secret_Lab_Config' ) ) {
					$header_widget = Secret_Lab_Config::get_composer_block_array( 'header' );
				} elseif ( function_exists( 'atiframebuilder_get_composer_block_array' ) ) {
					$header_widget = atiframebuilder_get_composer_block_array( 'header' );
				} elseif ( function_exists( 'atiframe_get_composer_block_array' ) ) {
					$header_widget = atiframe_get_composer_block_array( 'header' );
				} else {
					$header_widget = array();
				}
				if ( ! empty( $header_widget ) ) {
					echo '<div id="' . $key . '-block" class="custom_settings_box">';
					echo '<div class="key_header"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
					echo '<select id="' . $key . '" class="header_widget" name="' . $key . $b_hidden . '" >';
					if ( isset( $native_set[ $key ] ) ) {
						if ( count( $header_widget ) >= 1 ) {
							$header_widget = array( 0 => 'Use Global Sidebar' ) + $header_widget;
						}
						foreach ( $header_widget as $id => $name ) {
							if ( $id == $setting ) {
								$selected = ' selected ';
							} else {
								$selected = ' ';
							}
							echo '<option ' . $selected . ' value="' . $id . '">' . $name . '</option>';
						}
					} else {
						echo '<option selected value=0>' . esc_html__( 'Use Global Widget',
								'marketing-and-seo-booster' ) . '</option>';
						foreach ( $header_widget as $id => $name ) {
							echo '<option value="' . $id . '">' . $name . '</option>';
						}
					}
					echo '</select>';
					echo '</div>';
				}
			}
			if ( class_exists( 'custom_post_widget' ) && preg_match( '/footer_widget/', $key ) == 1 ) {
				if ( class_exists( 'Atiframebuilder_Config' ) ) {
					$footer_widget = Atiframebuilder_Config::get_composer_block_array( 'footer' );
				} elseif ( class_exists( 'Secret_Lab_Config' ) ) {
					$footer_widget = Secret_Lab_Config::get_composer_block_array( 'footer' );
				} elseif ( function_exists( 'atiframebuilder_get_composer_block_array' ) ) {
					$footer_widget = atiframebuilder_get_composer_block_array( 'footer' );
				} elseif ( function_exists( 'atiframe_get_composer_block_array' ) ) {
					$footer_widget = atiframe_get_composer_block_array( 'footer' );
				} else {
					$footer_widget = array();
				}
				if ( ! empty( $footer_widget ) ) {
					echo '<div id="' . $key . '" class="custom_settings_box">';
					echo '<div class="key_header"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
					echo '<select id="' . $key . '" class="footer_widget" name=' . $key . $b_hidden . ' >';
					if ( isset( $native_set[ $key ] ) ) {
						if ( count( $footer_widget ) >= 1 ) {
							$footer_widget = array( 0 => 'Use Global Sidebar' ) + $footer_widget;
						}
						foreach ( $footer_widget as $id => $name ) {
							if ( $id == $setting ) {
								$selected = ' selected ';
							} else {
								$selected = ' ';
							}
							echo '<option ' . $selected . ' value="' . $id . '">' . $name . '</option>';
						}
					} else {
						echo '<option selected value=0>' . esc_html__( 'Use Global Widget',
								'marketing-and-seo-booster' ) . '</option>';
						foreach ( $footer_widget as $id => $name ) {
							echo '<option value="' . $id . '">' . $name . '</option>';
						}
					}
					echo '</select>';
					echo '</div>';
				}
			}
		}
		echo '</div>
            </div>
        </div>';
	}

}

function masb_settings_save( $id ) {
	$settings = array();
	if ( ! isset( $_POST['sealed'] ) || $_POST['sealed'] == 1 ) {
		return;
	}

	$fields = array_merge( SL_Marketing_and_SEO_Booster::$keys, SL_Marketing_and_SEO_Booster::$blog_keys );
	$fields = array_merge( $fields, SL_Marketing_and_SEO_Booster::$shop_keys );

	foreach ( $fields as $key => $description ) {
		if ( isset( $_POST[ $key ] ) ) {

			if ( is_array( $description ) ) {
				foreach ( $description as $field_key => $val ) {
					$settings[ $key ] = filter_input( INPUT_POST, $key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
				}

			} else {
				$settings[ $key ] = filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING );
				if ( $settings[ $key ] == 'on' ) {
					$settings[ $key ] = 1;
				}
			}
		}
	}

	$settings = json_encode( $settings );

	if ( ! empty( $settings ) && $settings != 'null' ) {
		update_post_meta( $id, 'layout_settings', $settings );
	}
}

function masb_select_options_render( $options, $correct ) {
	$opt = '';
	foreach ( $options as $id => $name ) {
		if ( $id == $correct ) {
			$selected = ' selected ';
		} else {
			$selected = ' ';
		}
		$opt .= '<option ' . $selected . ' value="' . $id . '">' . $name . '</option>';
	}

	return $opt;
}