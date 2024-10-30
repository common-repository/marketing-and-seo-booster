<?php
/*
Additional post settings in topbar

*/

add_action( 'admin_init', 'masb_front_deactivate_kc_pro_template', 999 );

function masb_front_deactivate_kc_pro_template() {
	global $kc_pro;
	if ( ! empty( $kc_pro->action ) && $kc_pro->action == 'live-editor' && file_exists( plugin_dir_path( __FILE__ ) . 'front_edit_kc.templates.php' ) ) {
		if ( class_exists( 'RevSliderFront' ) ) {
			if ( method_exists( 'RevSliderFront', 'onAddScripts' ) ) {
				RevSliderFront::onAddScripts();
			} elseif ( method_exists( 'RevSliderFront', 'add_actions' ) ) {
				RevSliderFront::add_actions();
			}
		}
		remove_action( 'kc_after_admin_footer', array( &$kc_pro, 'after_admin_footer' ), 0 );
		add_action( 'kc_after_admin_footer', 'masb_front_edit_template', 0 );
	}
}

function masb_front_edit_template() {
	$screens = array(
		'post'      => array( 'id' => 'layout_settings', 'args' => SL_Marketing_and_SEO_Booster::$blog_keys, ),
		'page'      => array( 'id' => 'layout_settings', 'args' => SL_Marketing_and_SEO_Booster::$keys, ),
		'portfolio' => array( 'id' => 'layout_settings', 'args' => SL_Marketing_and_SEO_Booster::$keys, ),
		'product'   => array( 'id' => 'layout_settings', 'args' => SL_Marketing_and_SEO_Booster::$shop_keys, ),
	);

	include plugin_dir_path( __FILE__ ) . 'front_edit_kc.templates.php';

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	$styles = '';

	wp_add_inline_style( 'ssc_admin_style', $styles );

	wp_localize_script( 'masb_admin_js',
		'masb_ajax',
		array(
			'nonce'     => wp_create_nonce( 'masb_ajax_nonce' ),
			'forbidden' => esc_html__( 'Error: secure session is invalid. Reload and try again',
				'marketing-and-seo-booster' ),
			'loading'   => esc_html__( 'Saving page options', 'marketing-and-seo-booster' ),
		)
	);

}

function masb_front_page_options( $post, $screen_keys, $echo = true ) {

	global $secretlab;

	$settings   = json_decode( get_post_meta( $post->ID, 'layout_settings', true ), true );
	$native_set = $settings;
	$home       = get_template_directory_uri();
	$sidebars   = SL_Marketing_and_SEO_Booster::get_sidebars();

	if ( $post->post_type == 'post' ) {
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

	$out = '<div id="secretlab-f-post-layout-settings"><div class="masb-f-ajax-loader" style="display: none;"></div>';

	$out .= '<div id="manage_type_selector" class="slmenu-tabs">';
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

	$out .= '<input id="sealed" class="hidden" name="sealed" type="text" value="1" >';

	$out .= '<ul>
                <li class="active" data-page="0">' . esc_html__( 'Design', 'marketing-and-seo-booster' ) . '</li>
                <li data-page="1" class="">' . esc_html__( 'Background', 'marketing-and-seo-booster' ) . '</li>
                <li data-page="1" class="">' . esc_html__( 'Sidebar', 'marketing-and-seo-booster' ) . '</li>';
	if ( class_exists( 'MASB_SEO' ) ) {
		$out .= '<li data-page="1" class="">' . esc_html__( 'SEO', 'marketing-and-seo-booster' ) . '</li>';
	}
	$out .= '</ul>';

	$out .= '<div id="secretlab-post-layout-settings-box" ' . 'class="slmenu-tab-box owindow row ' . $visible_settings_box . /*$b_class.*/
	        '"' . ' n="' . $post->ID . '">';

	$cols  = $screen_keys['args'];
	$tab_1 = $tab_2 = $tab_3 = $tab_4 = '';
	foreach ( $cols as $key => $description ) {

		if ( ! isset( $settings[ $key ] ) ) {
			if ( isset( $secretlab[ $key ] ) ) {
				$setting = $secretlab[ $key ];
			}
		} else {
			$setting = $settings[ $key ];
		}

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
				$tab_1 .= '<div id="' . $key . '-block" class="custom_settings_box">';
				$tab_1 .= '<div class="ssc_label"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
				$tab_1 .= '<select id="' . $key . '" class="custom_setting_input header_widget" name="' . $key . $b_hidden . '" >';
				if ( isset( $native_set[ $key ] ) ) {
					if ( count( $header_widget ) >= 1 ) {
						$header_widget = array(
							                 0 => esc_html__( 'Use Global Widget',
								                 'marketing-and-seo-booster' ),
						                 ) + $header_widget;
					}
					foreach ( $header_widget as $id => $name ) {
						if ( $id == $setting ) {
							$selected = ' selected ';
						} else {
							$selected = ' ';
						}
						$tab_1 .= '<option ' . $selected . ' value="' . $id . '">' . $name . '</option>';
					}
				} else {
					$tab_1 .= '<option selected value=0>' . esc_html__( 'Use Global Widget',
							'marketing-and-seo-booster' ) . '</option>';
					foreach ( $header_widget as $id => $name ) {
						$tab_1 .= '<option value="' . $id . '">' . $name . '</option>';
					}
				}
				$tab_1 .= '</select>';
				switch ( $setting ) {
					case 0:
						$edit_link = '<a id="masb-f-edit-header-widget-link" href="' . admin_url( '?page=kingcomposer&kc_action=live-editor&id=' . $secretlab[ $key ] ) . '" target="_blank">' . esc_html__( 'Live Edit Header',
								'marketing-and-seo-booster' ) . '</a>';
						break;
					case - 1:
						$edit_link = '';
						break;
					default:
						$edit_link = '<a id="masb-f-edit-header-widget-link" href="' . admin_url( '?page=kingcomposer&kc_action=live-editor&id=' . $setting ) . '" target="_blank">' . esc_html__( 'Live Edit Header',
								'marketing-and-seo-booster' ) . '</a>';
				}
				$tab_1 .= '<div id="masb-f-edit-header-widget-block" class="masb-edit-link">' . $edit_link . '</div>';
				$tab_1 .= '</div>';
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
				$tab_1 .= '<div id="' . $key . '" class="custom_settings_box">';
				$tab_1 .= '<div class="ssc_label"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
				$tab_1 .= '<select id="' . $key . '" class="custom_setting_input footer_widget" name=' . $key . $b_hidden . ' >';
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
						$tab_1 .= '<option ' . $selected . ' value="' . $id . '">' . $name . '</option>';
					}
				} else {
					$tab_1 .= '<option selected value=0>' . esc_html__( 'Use Global Widget',
							'marketing-and-seo-booster' ) . '</option>';
					foreach ( $footer_widget as $id => $name ) {
						$tab_1 .= '<option value="' . $id . '">' . $name . '</option>';
					}
				}
				$tab_1 .= '</select>';
				switch ( $setting ) {
					case 0:
						$edit_link = '<a id="masb-f-edit-footer-widget-link" href="' . admin_url( '?page=kingcomposer&kc_action=live-editor&id=' . $secretlab[ $key ] ) . '">' . esc_html__( 'Live Edit Footer',
								'marketing-and-seo-booster' ) . '</a>';
						break;
					case - 1:
						$edit_link = '';
						break;
					default:
						$edit_link = '<a id="masb-f-edit-footer-widget-link" href="' . admin_url( '?page=kingcomposer&kc_action=live-editor&id=' . $setting ) . '">' . esc_html__( 'Live Edit Footer',
								'marketing-and-seo-booster' ) . '</a>';
				}
				$tab_1 .= '<div id="masb-f-edit-footer-widget-block" class="masb-edit-link">' . $edit_link . '</div>';
				$tab_1 .= '</div>';
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
			$tab_1   .= '<div id="' . $key . '" class="custom_settings_box' . $visible . '">';
			$tab_1   .= '<div class="ssc_label"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
			$tab_1   .= '<select id="' . $key . '_select" class="custom_setting_input" name="' . $key . esc_attr( $fake . $r_hidden ) . '" >';
			$tab_1   .= '<option value="1" ' . selected( $setting, 1, false ) . '>' . esc_html__( 'Slider',
					'marketing-and-seo-booster' ) . '</option>';
			$tab_1   .= '<option value="2" ' . selected( $setting, 2, false ) . '>' . esc_html__( 'Static Image',
					'marketing-and-seo-booster' ) . '</option>';
			$tab_1   .= '</select>';
			$tab_1   .= '</div>';

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
			$tab_1 .= '<div id="' . $key . '" class="header_image custom_settings_box' . $visible . '">';
			$tab_1 .= '<div class="ssc_label"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
			$tab_1 .= '<div id="' . $key . '_selector">';
			$tab_1 .= '<input type="text" class="custom_setting_input header_img" name="' . $key . '" value="' . $img . '">';
			$tab_1 .= '<div class="header_img_preview"><img src="' . $img . '"></div>';
			$tab_1 .= '<a id="' . $key . '_loader" class="ssc_btn header_img upload" href="#masb-front-header-image-change">' . esc_html__( 'Upload Header Image for this Post',
					'marketing-and-seo-booster' ) . '</a>';
			$tab_1 .= '<a id="' . $key . '_remover" class="ssc_btn img-remove" href="#masb-front-header-image-remove">' . esc_html__( 'Remove Header Image',
					'marketing-and-seo-booster' ) . '</a>';
			$tab_1 .= '</div>';
			$tab_1 .= '</div>';

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
			$tab_1   .= '<div id="' . $key . '" class="custom_settings_box' . $visible . '">';
			$tab_1   .= '<div class="ssc_label"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
			$tab_1   .= '<select id="' . $key . '_select" class="custom_setting_input slider_select" name="' . $key . esc_attr( $fake . $b_hidden ) . '" >';
			$sliders = SL_Marketing_and_SEO_Booster::get_sliders_array();
			if ( isset( $native_set[ $key ] ) ) {
				if ( count( $sliders ) > 1 ) {
					$sliders = SL_Marketing_and_SEO_Booster::array_insert( $sliders,
						array( 'default' => 'Use Global Slider' ),
						0 );
				}
				foreach ( $sliders as $alias => $name ) {
					$selected = ( (string) $setting == (string) $alias ) ? 'selected' : '';
					$tab_1    .= '<option value="' . esc_attr( $alias ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
				}
			} else {
				$tab_1 .= '<option value="default" selected>' . esc_html__( 'Use Global Slider',
						'marketing-and-seo-booster' ) . '</option>';
				foreach ( $sliders as $alias => $name ) {
					$tab_1 .= ' "<option value="' . esc_attr( $alias ) . '">' . esc_html( $name ) . '</option>';
				}
			}
			$tab_1 .= '</select>';
			$tab_1 .= '</div>';

		}

		if ( preg_match( '/(blog|shop|design)-layout/', $key ) == 1 ) {

			$tab_2 .= SL_Marketing_and_SEO_Booster::img_selector( $key,
				array( $home . '/images/framework/wide.gif', $home . '/images/framework/boxed.gif' ),
				$setting,
				$screen_keys['args'],
				$b_hidden,
				false );
		}
		if ( preg_match( '/boxed-background/', $key ) == 1 ) {

			$tab_2 .= '<div id="' . $key . '" class="custom_settings_box' . $visible . ' boxed-background">';
			$tab_2 .= '<div class="ssc_box_label"><span>' . esc_html__( 'Boxed Background',
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
							$tab_2 .= '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box ' . $field_id . '">';
							$tab_2 .= '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
							$tab_2 .= '<input id="' . $key . '-' . $field_id . '_input" class="custom_setting_input slnm-color-rgba wp-color-picker boxed-background-color ' . $field_id . '_input" name="' . esc_attr( $key . '[' . $field_id . ']' . $b_hidden ) . '" value="' . esc_attr( $color ) . '" data-alpha="true" >';
							$tab_2 .= '<input type="hidden" id="' . $key . '-' . $field_id . '_input_default" class="' . $field_id . '_input_default" name="' . esc_attr( $key . '[' . $field_id . $b_hidden ) . '_default]" value="' . esc_attr( $secretlab[ $key ][ $field_id ] ) . '" data-alpha="true" >';
							$tab_2 .= '</div>';
							break;

						case 'background-image':
							$tab_2 .= '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box ' . $field_id . '">';
							$tab_2 .= '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
							$tab_2 .= '<div id="' . $field_id . '_uploader">';
							$tab_2 .= '<div id="' . $field_id . '_upload_box">';
							if ( empty( $setting[ $field_id ] ) && ! empty( $setting['media'] ) && is_array( $setting['media'] ) && ! empty( $setting['media']['id'] ) ) {
								$att_url              = wp_get_attachment_image_src( $setting['media']['id'] );
								$setting[ $field_id ] = array( 'url' => $att_url[0], 'id' => $setting['media']['id'] );
							} elseif ( ! empty( $setting[ $field_id ] ) && is_array( $setting[ $field_id ] ) ) {
							} elseif ( ! empty( $setting[ $field_id ] ) && ! empty( $setting['media'] ) && is_array( $setting['media'] ) && ! empty( $setting['media']['id'] ) ) {
								$setting[ $field_id ] = array(
									'url' => $setting[ $field_id ],
									'id'  => $setting['media']['id'],
								);
							} elseif ( ! empty( $setting[ $field_id ] ) && ! empty( $setting['media'] ) && ! is_array( $setting['media'] ) ) {
								$setting[ $field_id ] = array( 'url' => $setting[ $field_id ], 'id' => '' );
							} else {
								$setting[ $field_id ] = array( 'url' => '', 'id' => '' );
							}
							if ( ! isset( $setting ) || $setting[ $field_id ]['url'] == '' || $setting[ $field_id ]['url'] == 'none' ) {
								$visible_el = 'hidden';
							} else {
								$visible_el = 'visible';
							}
							if ( $setting[ $field_id ]['url'] !== 'none' ) {
								$tab_2 .= '<img id="' . $key . '-' . $field_id . '_holder" class="' . $visible_el . ' ' . $field_id . '_holder" img_n="' . $setting[ $field_id ]['id'] . '" src="' . esc_url( $setting[ $field_id ]['url'] ) . '" width="200px" height="200px">';
							}
							$tab_2 .= '</div>';
							$tab_2 .= '<div id="boxed_upload_link_box">';
							$tab_2 .= '<a href="#masb-front-boxed-image-change" class="ssc_btn ' . $field_id . '_change_img" id="' . $key . '-' . $field_id . '_change_img">' . esc_html__( 'Add/change',
									'marketing-and-seo-booster' ) . '</a>';
							$tab_2 .= '<a href="#masb-front-boxed-image-remove" class="' . $visible_el . ' img-remove ssc_btn ' . $field_id . '_remove_img" id="' . $key . '-' . $field_id . '_remove_img">' . esc_html__( 'Remove',
									'marketing-and-seo-booster' ) . '</a>';
							$tab_2 .= '</div>';
							$tab_2 .= '</div>';
							$tab_2 .= '<input id="' . $key . '-' . $field_id . '_input" class="custom_setting_input hidden ' . $field_id . '_input" type="text" value="' . esc_attr( $setting[ $field_id ]['url'] ) . '" name="' . esc_attr( $key . '[' . $field_id . '][url]' . $b_hidden ) . '" >';
							$tab_2 .= '<input id="hiden-' . $key . '-' . $field_id . '_input" class="custom_setting_input hidden ' . $field_id . '_input" type="hidden" value="' . esc_attr( $setting[ $field_id ]['id'] ) . '" name="' . esc_attr( $key . '[' . $field_id . '][id]' . $b_hidden ) . '" >';
							$tab_2 .= '</div>';
							break;

						case 'background-repeat':
							$visible = ' visible';
							$fake    = '';
							$tab_2   .= '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box bg-setting' . $visible . '">';
							$tab_2   .= '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
							$tab_2   .= '<select id="' . $key . '-' . $field_id . '_select" class="custom_setting_input" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
							$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '' ;
							$tab_2 .= masb_f_select_options_render( $background_repeat_settings, $correct );
							$tab_2 .= '</select>';
							$tab_2 .= '</div>';
							break;

						case 'background-size':

							$visible = ' visible';
							$fake    = '';
							$tab_2   .= '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box bg-setting' . $visible . '">';
							$tab_2   .= '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
							$tab_2   .= '<select id="' . $key . '-' . $field_id . '_select" class="custom_setting_input" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
							$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '' ;
							$tab_2 .= masb_f_select_options_render( $background_size_settings, $correct );
							$tab_2 .= '</select>';
							$tab_2 .= '</div>';
							break;

						case 'background-attachment':

							$visible = ' visible';
							$fake    = '';
							$tab_2   .= '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box bg-setting' . $visible . '">';
							$tab_2   .= '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
							$tab_2   .= '<select id="' . $key . '-' . $field_id . '_select" class="custom_setting_input" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
							$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '' ;
							$tab_2 .= masb_f_select_options_render( $background_attachment_settings, $correct );
							$tab_2 .= '</select>';
							$tab_2 .= '</div>';
							break;

						case 'background-position':

							$visible = ' visible';
							$fake    = '';
							$tab_2   .= '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box bg-setting' . $visible . '">';
							$tab_2   .= '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
							$tab_2   .= '<select id="' . $key . '-' . $field_id . '_select" class="custom_setting_input" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
							$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '' ;
							$tab_2 .= masb_f_select_options_render( $background_position_settings, $correct );
							$tab_2 .= '</select>';
							$tab_2 .= '</div>';
							break;

					} // End switch

				}

			}

			$tab_2 .= '</div>';

		}

		if ( preg_match( '/content-background/', $key ) == 1 ) {

			$tab_2 .= '<div id="' . $key . '" class="custom_settings_box' . $visible . ' content-background">';
			$tab_2 .= '<div class="ssc_box_label"><span>' . esc_html__( 'Content Background',
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
							$tab_2 .= '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box ' . $field_id . '">';
							$tab_2 .= '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
							$tab_2 .= '<input id="' . $key . '-' . $field_id . '_input" class="custom_setting_input slnm-color-rgba wp-color-picker content-background-color ' . $field_id . '_input" name="' . esc_attr( $key . '[' . $field_id . ']' . $b_hidden ) . '" value="' . esc_attr( $color ) . '" data-alpha="true" >';
							$tab_2 .= '</div>';
							break;

						case 'background-image':
							$tab_2 .= '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box' . $visible . ' ' . $field_id . '">';
							$tab_2 .= '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
							$tab_2 .= '<div id="' . $field_id . '_uploader">';
							$tab_2 .= '<div id="' . $field_id . '_upload_box">';
							if ( empty( $setting[ $field_id ] ) && ! empty( $setting['media'] ) && is_array( $setting['media'] ) && ! empty( $setting['media']['id'] ) ) {
								$att_url              = wp_get_attachment_image_src( $setting['media']['id'] );
								$setting[ $field_id ] = array( 'url' => $att_url[0], 'id' => $setting['media']['id'] );
							} elseif ( ! empty( $setting[ $field_id ] ) && is_array( $setting[ $field_id ] ) ) {
							} elseif ( ! empty( $setting[ $field_id ] ) && ! empty( $setting['media'] ) && is_array( $setting['media'] ) && ! empty( $setting['media']['id'] ) ) {
								$setting[ $field_id ] = array(
									'url' => $setting[ $field_id ],
									'id'  => $setting['media']['id'],
								);
							} elseif ( ! empty( $setting[ $field_id ] ) && ! empty( $setting['media'] ) && ! is_array( $setting['media'] ) ) {
								$setting[ $field_id ] = array( 'url' => $setting[ $field_id ], 'id' => '' );
							} else {
								$setting[ $field_id ] = array( 'url' => '', 'id' => '' );
							}
							if ( ! isset( $setting ) || $setting[ $field_id ]['url'] == '' || $setting[ $field_id ]['url'] == 'none' ) {
								$visible_el = 'hidden';
							} else {
								$visible_el = 'visible';
							}
							if ( $setting[ $field_id ]['url'] !== 'none' ) {
								$tab_2 .= '<img id="' . $key . '-' . $field_id . '_holder" class="' . $visible_el . ' ' . $field_id . '_holder" img_n="' . $setting[ $field_id ]['id'] . '" src="' . esc_url( $setting[ $field_id ]['url'] ) . '" width="200px" height="200px">';
							}
							$tab_2 .= '</div>';
							$tab_2 .= '<div id="boxed_upload_link_box">';
							$tab_2 .= '<a href="#masb-front-content-image-change" class="ssc_btn ' . $field_id . '_change_img" id="' . $key . '-' . $field_id . '_change_img">' . esc_html__( 'Add/change',
									'marketing-and-seo-booster' ) . '</a>';
							$tab_2 .= '<a href="#masb-front-content-image-remove" class="' . $visible_el . ' img-remove ssc_btn ' . $field_id . '_remove_img" id="' . $key . '-' . $field_id . '_remove_img">' . esc_html__( 'Remove',
									'marketing-and-seo-booster' ) . '</a>';
							$tab_2 .= '</div>';
							$tab_2 .= '</div>';
							$tab_2 .= '<input id="' . $key . '-' . $field_id . '_input" class="custom_setting_input hidden ' . $field_id . '_input" type="text" value="' . esc_attr( $setting['background-image']['url'] ) . '" name="' . esc_attr( $key . '[' . $field_id . '][url]' . $b_hidden ) . '" >';
							$tab_2 .= '<input id="hiden-' . $key . '-' . $field_id . '_input" class="custom_setting_input hidden ' . $field_id . '_input" type="hidden" value="' . esc_attr( $setting[ $field_id ]['id'] ) . '" name="' . esc_attr( $key . '[' . $field_id . '][id]' . $b_hidden ) . '" >';
							$tab_2 .= '</div>';
							break;

						case 'background-repeat':
							$visible = ' visible';
							$fake    = '';
							$tab_2   .= '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box bg-setting' . $visible . '">';
							$tab_2   .= '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
							$tab_2   .= '<select id="' . $key . '-' . $field_id . '_select" class="custom_setting_input" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
							$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '' ;
							$tab_2 .= masb_f_select_options_render( $background_repeat_settings, $correct );
							$tab_2 .= '</select>';
							$tab_2 .= '</div>';
							break;

						case 'background-size':

							$visible = ' visible';
							$fake    = '';
							$tab_2   .= '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box bg-setting' . $visible . '">';
							$tab_2   .= '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
							$tab_2   .= '<select id="' . $key . '-' . $field_id . '_select" class="custom_setting_input" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
							$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '' ;
							$tab_2 .= masb_f_select_options_render( $background_size_settings, $correct );

							$tab_2 .= '</select>';
							$tab_2 .= '</div>';
							break;

						case 'background-attachment':

							$visible = ' visible';
							$fake    = '';
							$tab_2   .= '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box bg-setting' . $visible . '">';
							$tab_2   .= '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
							$tab_2   .= '<select id="' . $key . '-' . $field_id . '_select" class="custom_setting_input" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
							$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '' ;
							$tab_2 .= masb_f_select_options_render( $background_attachment_settings, $correct );

							$tab_2 .= '</select>';
							$tab_2 .= '</div>';
							break;

						case 'background-position':

							$visible = ' visible';
							$fake    = '';
							$tab_2   .= '<div id="' . $key . '-' . $field_id . '" class="custom_settings_box bg-setting' . $visible . '">';
							$tab_2   .= '<div class="key_subheader"><span>' . $screen_keys['args'][ $key ][ $field_id ] . '</span></div>';
							$tab_2   .= '<select id="' . $key . '-' . $field_id . '_select" class="custom_setting_input" name="' . $key . '[' . $field_id . ']' . esc_attr( $fake . $r_hidden ) . '" >';
							$correct = isset( $setting[ $field_id ] ) ? $setting[ $field_id ] : '' ;
							$tab_2 .= masb_f_select_options_render( $background_position_settings, $correct );

							$tab_2 .= '</select>';
							$tab_2 .= '</div>';
							break;

					} // End switch

				}

			}

			$tab_2 .= '</div>';

		}

		if ( preg_match( '/sidebar-layout/', $key ) == 1 ) {
			$tab_3 .= SL_Marketing_and_SEO_Booster::img_selector( $key,
				array(
					$home . '/images/framework/nosidebar.gif',
					$home . '/images/framework/2sidebars.gif',
					$home . '/images/framework/leftsidebar.gif',
					$home . '/images/framework/rightsidebar.gif',
				),
				$setting,
				$screen_keys['args'],
				$b_hidden,
				false );
			if ( $setting == 1 ) {
				$show_blog_columns = ' visible';
			} else {
				$show_blog_columns = ' hidden';
			}
		}

		if ( ! empty( $sidebars ) && preg_match( '/left_sidebar_widgets/', $key ) == 1 ) {
			$tab_3 .= '<div id="' . $key . '" class="custom_settings_box">';
			$tab_3 .= '<div class="ssc_label"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
			$tab_3 .= '<select id="' . $key . '" class="custom_setting_input regular_widget" name=' . $key . $b_hidden . ' >';
			foreach ( $sidebars as $id => $name ) {
				if ( $id == $setting ) {
					$selected = ' selected ';
				} else {
					$selected = ' ';
				}
				$tab_3 .= '<option ' . $selected . ' value="' . $id . '">' . $name . '</option>';
			}
			$tab_3 .= '</select>';
			$tab_3 .= '</div>';
		}
		if ( ! empty( $sidebars ) && preg_match( '/right_sidebar_widgets/', $key ) == 1 ) {
			$tab_3 .= '<div id="' . $key . '" class="custom_settings_box">';
			$tab_3 .= '<div class="ssc_label"><span>' . $screen_keys['args'][ $key ] . '</span></div>';
			$tab_3 .= '<select id="' . $key . '" class="custom_setting_input regular_widget" name=' . esc_attr( $key . $b_hidden ) . ' >';
			foreach ( $sidebars as $id => $name ) {
				if ( $id == $setting ) {
					$selected = ' selected ';
				} else {
					$selected = ' ';
				}
				$tab_3 .= '<option ' . $selected . ' value="' . $id . '">' . $name . '</option>';
			}
			$tab_3 .= '</select>';
			$tab_3 .= '</div>';

		}

	}


	$out .= '<div class="col-xs-12">' . $tab_1 . '</div>';
	$out .= '<div class="col-xs-12">' . $tab_2 . '</div>';
	$out .= '<div class="col-xs-12">' . $tab_3 . '</div>';
	if ( class_exists( 'MASB_SEO' ) ) {
		$MASB_SEO = MASB_SEO::get_instance();
		ob_start();
		$MASB_SEO->get_seo_admin()->meta_box( $post );
		$tab_4 = ob_get_contents();
		ob_end_clean();
		$out .= '<div class="col-xs-12">' . $tab_4 . '</div>';
	}

	$out .= '</div>
		</div>';
	$out .= '<div id="subtitle">' . '<a id="manage_type_selector_input" class="ssc_btn" href="#masb-front-reset-settings">' . esc_html__( 'Reset Settings',
			'marketing-and-seo-booster' ) . '</a>' . '</div>';
	$out .= '<button type="button" id="masb-f-save-page-settings" class="ssc_btn ">' . esc_html__( 'Save Page settings',
			'marketing-and-seo-booster' ) . '</button>
	</div>'; // secretlab-post-layout-settings div

	if ( $echo ) {
		echo $out;

		return;
	}

	return $out;

}


function masb_front_composer_block_options( $post, $echo = true ) {
	$out = '<div id="manage_type_selector" class="">';
	$out .= '<div id="secretlab-post-layout-settings-box" ' . 'class="owindow row "' . ' n="' . $post->ID . '">';
	$out .= '<div class="col-xs-12 inside">';
	ob_start();
	masb_meta_box( $post );
	$out .= ob_get_contents();
	ob_end_clean();
	$out .= '</div>';
	$out .= '</div>
		</div><button type="button" id="masb-f-save-composer-box-settings" class="ssc_btn ">' . esc_html__( 'Save Page settings',
			'marketing-and-seo-booster' ) . '</button>';
	if ( $echo ) {
		echo $out;

		return;
	}

	return $out;
}

if ( wp_doing_ajax() ) {
	add_action( 'wp_ajax_masb_get_header_widget', 'masb_get_header_widget' );
	add_action( 'wp_ajax_masb_get_footer_widget', 'masb_get_footer_widget' );
	add_action( 'wp_ajax_masb_get_slider', 'masb_get_slider' );
	add_action( 'wp_ajax_masb_get_sidebar', 'masb_get_sidebar' );
	add_action( 'wp_ajax_masb_f_save_page_settings', 'masb_f_save_page_settings' );
	add_action( 'wp_ajax_masb_f_save_composer_block_settings', 'masb_f_save_composer_block_settings' );
}

function masb_get_header_widget() {
	check_ajax_referer( 'masb_ajax_nonce', 'nonce' );
	$resp = array( 'status' => 0 );
	$h_id = filter_input( INPUT_POST, 'h_id', FILTER_VALIDATE_INT );
	if ( is_int( $h_id ) ) {
		global $secretlab;
		if ( $h_id == 0 && isset( $secretlab['header_widget'] ) ) {
			$h_id = $secretlab['header_widget'];
		}
		if ( class_exists( 'custom_post_widget' ) ) {
			ob_start();
			the_widget( 'custom_post_widget',
				array( 'custom_post_id' => $h_id, 'apply_content_filters' => true, ),
				array( 'before_widget' => '<div class="header-widget %s">' ) );
			$resp['widget'] = ob_get_contents();
			ob_end_clean();
			$resp['edit_link'] = '<a id="masb-f-edit-header-widget-link" href="' . admin_url( '?page=kingcomposer&kc_action=live-editor&id=' . $h_id ) . '" target="_blank">' . esc_html__( 'Edit The Header',
					'marketing-and-seo-booster' ) . '</a>';
			$resp['status']    = 1;
		} else {
			$resp['message'] = esc_html__( 'Check enabled of "Composer Widget" module, please.',
				'marketing-and-seo-booster' );
		}
	}
	wp_send_json( $resp );
	wp_die();

}

function masb_get_footer_widget() {
	check_ajax_referer( 'masb_ajax_nonce', 'nonce' );
	$resp = array( 'status' => 0 );
	$f_id = filter_input( INPUT_POST, 'f_id', FILTER_VALIDATE_INT );
	if ( is_int( $f_id ) ) {
		global $secretlab;
		if ( $f_id == 0 && isset( $secretlab['footer_widget'] ) ) {
			$f_id = $secretlab['footer_widget'];
		}
		if ( class_exists( 'custom_post_widget' ) ) {
			ob_start();
			the_widget( 'custom_post_widget',
				array( 'custom_post_id' => $f_id, 'apply_content_filters' => true, ),
				array( 'before_widget' => '<div class="footer-widget %s">' ) );
			$resp['widget'] = ob_get_contents();
			ob_end_clean();
			$resp['edit_link'] = '<a id="masb-f-edit-footer-widget-link" href="' . admin_url( '?page=kingcomposer&kc_action=live-editor&id=' . $f_id ) . '">' . esc_html__( 'Edit The Footer',
					'marketing-and-seo-booster' ) . '</a>';
			$resp['status']    = 1;
		} else {
			$resp['message'] = esc_html__( 'Check enabled of "Composer Widget" module, please.',
				'marketing-and-seo-booster' );
		}
	}
	wp_send_json( $resp );
	wp_die();

}

function masb_get_slider() {
	check_ajax_referer( 'masb_ajax_nonce', 'nonce' );
	$resp = array( 'status' => 0 );
	$s_id = filter_input( INPUT_POST, 's_id' );
	if ( ! empty( $s_id ) ) {
		global $secretlab;
		$param = sanitize_key( trim( $s_id ) );
		if ( masb_f_is_existing_slider_name( $param ) ) {
			if ( $param == 'default' ) {
				$param = $secretlab['pick_slider'];
			}
			if ( ! empty( $param ) && preg_match( '/(rev_|lay_)(.+)/', $param, $slider ) ) {
				$type   = $slider[1];
				$slider = $slider[2];
				if ( $type == 'lay_' ) {
					$resp['status'] = 1;
					$resp['html']   = do_shortcode( '[layerslider id="' . $slider . '"]' );
				}
				if ( $type == 'rev_' ) {
					$resp['status'] = 1;
					$resp['html']   = do_shortcode( '[rev_slider alias="' . $slider . '"]' );
				}
			}
		}
	}
	wp_send_json( $resp );
	wp_die();
}

function masb_get_sidebar() {
	check_ajax_referer( 'masb_ajax_nonce', 'nonce' );
	$resp = array( 'status' => 0 );
	$s_id = filter_input( INPUT_POST, 's_id' );
	if ( ! empty( $s_id ) ) {
		$s_id = sanitize_key( trim( $s_id ) );
		if ( masb_f_is_existing_sidebar_name( $s_id ) ) {
			if ( is_active_sidebar( $s_id ) ) {
				ob_start();
				dynamic_sidebar( $s_id );
				$resp['sidebar'] = ob_get_contents();
				ob_end_clean();
			} else {
				$resp['sidebar'] = '';
			}
			$resp['status'] = 1;
		}
	}
	wp_send_json( $resp );
	wp_die();
}

function masb_f_save_page_settings() {
	check_ajax_referer( 'masb_ajax_nonce', 'nonce' );
	$resp = array( 'status' => 0 );
	$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
	$l_set = filter_input( INPUT_POST, 'l_set' );
	$masb_seo = filter_input( INPUT_POST, 'masb_seo' );
	if ( ! empty( $post_id ) && ! empty( $l_set ) && ! empty( $masb_seo ) ) {
		if ( current_user_can( 'edit_posts', $post_id ) ) {
			$l_set           = is_array( $l_set ) ? stripslashes( json_encode( $l_set ) ) : $l_set;
			$seo             = json_decode( stripslashes( $masb_seo ), true );
			$resp['message'] = esc_html__( 'This data was already saved', 'marketing-and-seo-booster' );
			if ( update_post_meta( $post_id, 'layout_settings', $l_set ) ) {
				$resp['status']  = 1;
				$resp['message'] = esc_html__( 'Page data saved', 'marketing-and-seo-booster' );
			}
			if ( is_array( $seo ) ) {
				foreach ( $seo as $k => $v ) {
					if ( update_post_meta( $post_id, $k, stripslashes( $v ) ) ) {
						$resp['status']  = 1;
						$resp['message'] = esc_html__( 'Page data saved', 'marketing-and-seo-booster' );
					}
				}
			}
		} else {
			$resp['message'] = esc_html__( 'You can\'t edit this page', 'marketing-and-seo-booster' );
		}
	} else {
		$resp['message'] = esc_html__( 'Wrong page settings', 'marketing-and-seo-booster' );
	}
	wp_send_json( $resp );
	wp_die();

}

function masb_f_save_composer_block_settings() {
	check_ajax_referer( 'masb_ajax_nonce', 'nonce' );
	$resp = array( 'status' => 0 );
	$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
	$w_type = filter_input( INPUT_POST, 'w_type' );
	$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );
	if ( ! empty( $post_id ) && is_numeric( $post_id ) && ! empty( $w_type ) && 'composer_widget' == $post_type ) {
		if ( current_user_can( 'edit_page', $post_id ) ) {
			$w_type              = ! is_array( $w_type ) ? json_decode( stripslashes( $w_type ),
				true ) : $w_type;
			$composer_block_type = $w_type['composer_block_type'];
			update_post_meta( $post_id, 'composer_block_type', $composer_block_type );
			if ( 'composer_block_header' == $composer_block_type ) {
				update_post_meta( $post_id, 'header_block_type', $w_type['header_block_type'] );
			} else {
				delete_post_meta( $post_id, 'header_block_type' );
			}
			$resp['status']  = 1;
			$resp['message'] = esc_html__( 'Composer Block type saved', 'marketing-and-seo-booster' );
		} else {
			$resp['message'] = esc_html__( 'You can\'t edit this page', 'marketing-and-seo-booster' );
		}
	} else {
		$resp['message'] = esc_html__( 'Wrong page settings', 'marketing-and-seo-booster' );
	}

	wp_send_json( $resp );
	wp_die();
}

function masb_f_is_existing_sidebar_name( $name ) {
	$sidebars = SL_Marketing_and_SEO_Booster::get_sidebars();

	return array_key_exists( $name, $sidebars );
}

function masb_f_is_existing_slider_name( $name ) {
	$sliders = SL_Marketing_and_SEO_Booster::get_sliders_array();
	if ( count( $sliders ) > 1 ) {
		$sliders = SL_Marketing_and_SEO_Booster::array_insert( $sliders,
			array( 'default' => 'Use Global Slider' ),
			0 );
	}

	return array_key_exists( $name, $sliders );
}

function masb_f_select_options_render( $options, $correct ) {
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