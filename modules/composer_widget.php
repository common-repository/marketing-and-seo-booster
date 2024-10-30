<?php
/*
 Show the content of a custom post of the type 'composer_block' ( it can be made with King Composer Plugin ) in a widget or with a shortcode.
 Version: 1.2

*/

// Launch the plugin.
function masb_custom_post_widget_plugin_init() {
	add_action( 'widgets_init', 'masb_custom_post_widget_load_widgets' );
}

add_action( 'after_setup_theme', 'masb_custom_post_widget_plugin_init', 20 );

// Loads the widgets packaged with the plugin.
function masb_custom_post_widget_load_widgets() {
	// Add featured image support
	if ( function_exists( 'add_theme_support' ) ) {
		add_theme_support( 'post-thumbnails' );
	}

// First create the widget for the admin panel
	class custom_post_widget extends WP_Widget {
		function __construct() {
			$widget_ops = array(
				'classname'   => 'widget_custom_post_widget',
				'description' => esc_html__( 'Displays Composer Widget post content in a widget',
					'marketing-and-seo-booster' ),
			);
			parent::__construct( 'custom_post_widget',
				esc_html__( 'Composer Content Block', 'marketing-and-seo-booster' ),
				$widget_ops );
		}

		public function add_shortcodes_custom_css( $id = null ) {
			if ( ! empty( $id ) ) {
				$out                   = '';
				$shortcodes_custom_css = get_post_meta( $id, '_wpb_shortcodes_custom_css', true );
				if ( ! empty( $shortcodes_custom_css ) ) {
					$shortcodes_custom_css = strip_tags( $shortcodes_custom_css );
					$out                   .= '<style type="text/css" data-type="vc_shortcodes-custom-css">';
					$out                   .= $shortcodes_custom_css;
					$out                   .= '</style>';
				}

				return $out;
			}
		}

		public function form( $instance ) {
			$custom_post_id = ''; // Initialize the variable
			if ( isset( $instance['custom_post_id'] ) ) {
				$custom_post_id = esc_attr( $instance['custom_post_id'] );
			};
			$show_custom_post_title = isset( $instance['show_custom_post_title'] ) ? $instance['show_custom_post_title'] : true;
			?>

            <p>
                <label for="<?php echo $this->get_field_id( 'custom_post_id' ); ?>"> <?php echo esc_html__( 'Composer Widgets to Display:',
						'marketing-and-seo-booster' ) ?>
                    <select class="widefat 1" id="<?php echo $this->get_field_id( 'custom_post_id' ); ?>"
                            name="<?php echo $this->get_field_name( 'custom_post_id' ); ?>">
						<?php
						if ( class_exists( 'Atiframebuilder_Config' ) ) {
							$content_blocks = Atiframebuilder_Config::get_composer_block_array( 'sidebar' );
						} elseif ( class_exists( 'Secret_Lab_Config' ) ) {
							$content_blocks = Secret_Lab_Config::get_composer_block_array( 'sidebar' );
						} elseif ( function_exists( 'atiframebuilder_get_composer_block_array' ) ) {
							$content_blocks = atiframebuilder_get_composer_block_array( 'sidebar' );
						} elseif ( function_exists( 'atiframe_get_composer_block_array' ) ) {
							$content_blocks = atiframe_get_composer_block_array( 'sidebar' );
						} else {
							$content_blocks = array();
						}
						if ( ! empty( $content_blocks ) ) {
							foreach ( $content_blocks as $content_block_id => $content_block_title ) :
								echo '<option value="' . $content_block_id . '"';
								if ( $custom_post_id == $content_block_id ) {
									echo ' selected';
									$widgetExtraTitle = $content_block_title;
								};
								echo '>' . $content_block_title . '</option>';
							endforeach;
						} else {
							echo '<option value="">' . esc_html__( 'No composer widgets available',
									'marketing-and-seo-booster' ) . '</option>';
						};
						?>
                    </select>
                </label>
            </p>

            <input type="hidden" id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>"
                   value="<?php if ( ! empty( $widgetExtraTitle ) ) {
				       echo $widgetExtraTitle;
			       } ?>"/>

            <p>
				<?php
				echo '<a href="post.php?post=' . $custom_post_id . '&action=edit">' . esc_html__( 'Edit Composer Widget',
						'marketing-and-seo-booster' ) . '</a>';
				?>
            </p>

            <p>
                <input class="checkbox"
                       type="checkbox" <?php checked( (bool) isset( $instance['show_custom_post_title'] ), true ); ?>
                       id="<?php echo $this->get_field_id( 'show_custom_post_title' ); ?>"
                       name="<?php echo $this->get_field_name( 'show_custom_post_title' ); ?>"/>
                <label for="<?php echo $this->get_field_id( 'show_custom_post_title' ); ?>"><?php echo esc_html__( 'Show Post Title',
						'marketing-and-seo-booster' ) ?></label>
            </p>
			<?php
		}

		public function update( $new_instance, $old_instance ) {
			$instance                           = $old_instance;
			$instance['custom_post_id']         = strip_tags( $new_instance['custom_post_id'] );
			$instance['show_custom_post_title'] = $new_instance['show_custom_post_title'];

			return $instance;
		}

		// Display the content block content in the widget area
		public function widget( $args, $instance ) {
			global $wp_scripts, $wp_styles;

			if ( ! in_array( 'ultimate-script', $wp_scripts->queue ) ) {
				$wp_scripts->queue[] = 'ultimate-script';
			}
			if ( ! in_array( 'ultimate-style-min', $wp_styles->queue ) ) {
				$wp_styles->queue[] = 'ultimate-style-min';
			}

			extract( $args );
			$custom_post_id = ( $instance['custom_post_id'] != '' ) ? esc_attr( $instance['custom_post_id'] ) : esc_html__( 'Find',
				'marketing-and-seo-booster' );
			// Add support for WPML Plugin.
			if ( function_exists( 'icl_object_id' ) ) {
				$custom_post_id = icl_object_id( $custom_post_id, 'composer_widget', true );
			}
			// Variables from the widget settings.
			$show_custom_post_title = isset( $instance['show_custom_post_title'] ) ? $instance['show_custom_post_title'] : false;
			$content_post           = get_post( $custom_post_id );
			$post_status            = get_post_status( $custom_post_id );
			if ( isset( $content_post ) && isset( $content_post->post_content_filtered ) && ! empty( $content_post->post_content_filtered ) && function_exists( 'kc_do_shortcode' ) ) {
				$content = kc_do_shortcode( $content_post->post_content_filtered );
			} elseif ( isset( $content_post ) && isset( $content_post->post_content ) && ! empty( $content_post->post_content ) ) {
				$content = $content_post->post_content;
			} else {
				$content = '';
			}

			if ( $post_status == 'publish' ) {
				// Display custom widget frontend
				if ( $located = locate_template( 'custom-post-widget.php' ) ) {
					require $located;

					return;
				}
				echo $before_widget;
				if ( $show_custom_post_title ) {
					echo $before_title . apply_filters( 'widget_title',
							$content_post->post_title ) . $after_title; // This is the line that displays the title (only if show title is set)
				}

				echo do_shortcode( $content ); // This is where the actual content of the custom post is being displayed
				echo $this->add_shortcodes_custom_css( $custom_post_id );
				echo $after_widget;
			}
		}

		public function get_composer_block_array( $type = 'sidebar' ) {
			global $wpdb;
			$composer_block_array = array();
			if ( 'header' == $type || 'footer' == $type ) {
				$composer_block_array[ - 1 ] = 'None';
			}
			$composer_block_type = 'composer_block_' . $type;
			$query_arr           = $wpdb->get_results( $wpdb->prepare( "SELECT pm.`post_id`, wps.`post_title` FROM $wpdb->postmeta pm INNER JOIN $wpdb->posts wps ON pm.post_id=wps.ID WHERE pm.meta_key = 'composer_block_type' AND pm.meta_value = '%s' AND wps.post_status = 'publish'",
				$composer_block_type ) );
			if ( is_array( $query_arr ) ) {
				foreach ( $query_arr as $composer_block_data ) {
					$composer_block_array[ $composer_block_data->post_id ] = $composer_block_data->post_title;
				}
			}

			return $composer_block_array;
		}
	}

// Create the Content Block custom post type
	function masb_post_type_init() {
		$labels = array(
			'name'               => esc_html__( 'Composer Block', 'post type general name', 'marketing-and-seo-booster' ),
			'singular_name'      => esc_html__( 'Composer Block', 'post type singular name', 'marketing-and-seo-booster' ),
			'plural_name'        => esc_html__( 'Composer Block', 'post type plural name', 'marketing-and-seo-booster' ),
			'add_new'            => esc_html__( 'Add a Composer Block', 'block', 'marketing-and-seo-booster' ),
			'add_new_item'       => esc_html__( 'Add a New Composer Block', 'marketing-and-seo-booster' ),
			'edit_item'          => esc_html__( 'Edit Composer Block', 'marketing-and-seo-booster' ),
			'new_item'           => esc_html__( 'New Composer Block', 'marketing-and-seo-booster' ),
			'view_item'          => esc_html__( 'View Composer Block', 'marketing-and-seo-booster' ),
			'search_items'       => esc_html__( 'Search Composer Block', 'marketing-and-seo-booster' ),
			'not_found'          => esc_html__( 'No Composer Block Found', 'marketing-and-seo-booster' ),
			'not_found_in_trash' => esc_html__( 'No Composer Block found in Trash', 'marketing-and-seo-booster' ),
		);
		//$content_block_public = false; // added to make this a filterable option
		$content_block_public = current_user_can( 'manage_options' ) ? true : false;
		$options              = array(
			'labels'              => $labels,
			'public'              => apply_filters( 'composer_widget_post_type', $content_block_public ),
			'publicly_queryable'  => $content_block_public,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'query_var'           => true,
			'rewrite'             => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'menu_icon'           => 'dashicons-screenoptions',
			'supports'            => array( 'title', 'editor', 'revisions', 'author' ),
		);
		register_post_type( 'composer_widget', $options );
	}

	add_action( 'init', 'masb_post_type_init' );

	function composer_widgets_messages( $messages ) {
	    $revision = filter_input( INPUT_COOKIE, 'revision', FILTER_VALIDATE_INT );
		$messages['content_block'] = array(
			0  => '',
			1  => current_user_can( 'edit_theme_options' ) ? sprintf( esc_html__( 'Composer Content Block updated. %sManage Widgets%s',
				'marketing-and-seo-booster' ),
				'<a href="' . esc_url( 'widgets.php' ) . '">',
				'</a>' ) : esc_html__( 'Composer Content Block updated.', 'marketing-and-seo-booster' ),
			2  => esc_html__( 'Custom field updated.', 'marketing-and-seo-booster' ),
			3  => esc_html__( 'Custom field deleted.', 'marketing-and-seo-booster' ),
			4  => esc_html__( 'Composer Widget updated.', 'marketing-and-seo-booster' ),
			5  => is_int( $revision ) ? sprintf( esc_html__( 'Composer Widget restored to revision from %s',
				'marketing-and-seo-booster' ),
				wp_post_revision_title( $revision, false ) ) : false,
			6  => current_user_can( 'edit_theme_options' ) ? sprintf( esc_html__( 'Composer Widget published. %sManage Widgets%s',
				'marketing-and-seo-booster' ),
				'<a href="' . esc_url( 'widgets.php' ) . '">',
				'</a>' ) : esc_html__( 'Composer Widget published.', 'marketing-and-seo-booster' ),
			7  => esc_html__( 'Block saved.', 'marketing-and-seo-booster' ),
			8  => current_user_can( 'edit_theme_options' ) ? sprintf( esc_html__( 'Composer Widget submitted. %sManage Widgets%s',
				'marketing-and-seo-booster' ),
				'<a href="' . esc_url( 'widgets.php' ) . '">',
				'</a>' ) : esc_html__( 'Composer Widget submitted.', 'marketing-and-seo-booster' ),
			9  => sprintf( esc_html__( 'Composer widget scheduled for: %1$s.', 'marketing-and-seo-booster' ),
				'<strong>' . date_i18n( esc_html__( 'M j, Y @ G:i', 'marketing-and-seo-booster' ),
					strtotime( isset( $post->post_date ) ? $post->post_date : 'now' ) ) . '</strong>' ),
			10 => current_user_can( 'edit_theme_options' ) ? sprintf( esc_html__( 'Composer Widget draft updated. %sManage Widgets%s',
				'marketing-and-seo-booster' ),
				'<a href="' . esc_url( 'widgets.php' ) . '">',
				'</a>' ) : sprintf( esc_html__( 'Composer Widget draft updated.', 'marketing-and-seo-booster' ),
				esc_url( 'widgets.php' ) ),
		);

		return $messages;
	}

	add_filter( 'post_updated_messages', 'composer_widgets_messages' );

// Add the ability to display the content block in a reqular post using a shortcode
	function custom_post_widget_shortcode( $atts ) {
		extract( shortcode_atts( array(
			'id'        => '',
			'slug'      => '',
			'class'     => 'content_block',
			'title'     => 'no',
			'title_tag' => 'h3',
		),
			$atts ) );

		if ( $slug ) {
			$block = get_page_by_path( $slug, OBJECT, 'composer_widget' );
			if ( $block ) {
				$id = $block->ID;
			}
		}

		$content = "";

		if ( $id != "" ) {
			$args = array(
				'post__in'  => array( $id ),
				'post_type' => 'composer_widget',
			);

			$content_post = get_posts( $args );

			foreach ( $content_post as $post ) :
				$content .= '<div class="' . esc_attr( $class ) . '" id="custom_post_widget-' . $id . '">';
				if ( $title === 'yes' ) {
					$content .= '<' . esc_attr( $title_tag ) . '>' . $post->post_title . '</' . esc_attr( $title_tag ) . '>';
				}

				$content .= $post->post_content;

				$content .= '</div>';
			endforeach;
		}

		return $content;
	}

	add_shortcode( 'composer_widget', 'custom_post_widget_shortcode' );

	register_widget( 'custom_post_widget' );
}

function masb_filter_composer_widget_init() {
	$composer_widget_public = true;

	return $composer_widget_public;
}

add_filter( 'composer_widget_post_type', 'masb_filter_composer_widget_init' );

add_action( 'vc_before_init', 'masb_composer_widget_setup_vc' );

function masb_composer_widget_setup_vc() {
	if ( function_exists( 'vc_set_default_editor_post_types' ) && function_exists( 'vc_default_editor_post_types' ) ) {
		$list   = vc_default_editor_post_types();
		$list[] = 'composer_widget';
		vc_set_default_editor_post_types( $list );
	}
}


function masb_add_meta_box() {
	add_meta_box(
		'masb_sectionid',
		esc_html__( 'Select Composer Block Type', 'marketing-and-seo-booster' ),
		'masb_meta_box',
		'composer_widget',
		'side'
	);
}

add_action( 'add_meta_boxes', 'masb_add_meta_box' );

function masb_meta_box( $post ) {
	remove_meta_box( 'postimagediv', 'composer_widget', 'side' );
	wp_nonce_field( 'masb_meta_box', 'masb_cw_nonce' );
	$block_type           = get_post_meta( $post->ID, 'composer_block_type', true );
	$composer_block_types = array(
		'composer_block_header'  => esc_html__( 'Header', 'marketing-and-seo-booster' ),
		'composer_block_footer'  => esc_html__( 'Footer', 'marketing-and-seo-booster' ),
		'composer_block_sidebar' => esc_html__( 'Widget', 'marketing-and-seo-booster' ),
	);
	if ( is_array( $composer_block_types ) ) {
		foreach ( $composer_block_types as $composer_block_type => $composer_block_type_title ) {
			$checked = ( $composer_block_type == $block_type ) ? 'checked' : '';
			echo '<p><label><input required name="composer_block_type" type="radio" value="' . $composer_block_type . '" ' . $checked . ' class="composer-block-type-input">' . $composer_block_type_title . '</label>';
			if ( 'composer_block_header' == $composer_block_type ) {
				$header_value       = ( '' === get_post_meta( $post->ID,
						'header_block_type',
						true ) ) ? 'slmm-above' : get_post_meta( $post->ID, 'header_block_type', true );
				$header_block_types = array(
					'slmm-above' => esc_html__( 'Above Slider', 'marketing-and-seo-booster' ),
					'slmm-over'  => esc_html__( 'Over Slider', 'marketing-and-seo-booster' ),
					'slmm-below' => esc_html__( 'Below Slider', 'marketing-and-seo-booster' ),
				);
				if ( is_array( $header_block_types ) ) {
					$display = 'checked' == $checked ? 'block' : 'none';
					echo '<span class="header_block_type_container" style="display:' . $display . '; ">';
					foreach ( $header_block_types as $header_block_type => $header_block_type_title ) {
						$checked = ( $header_block_type == $header_value ) ? 'checked' : '';
						echo '<label><input name="header_block_type" type="radio" value="' . $header_block_type . '" ' . $checked . ' class="composer-block-type-input">' . $header_block_type_title . '</label>';
					}
					echo '</span>';
				}
			}
			echo '</p>';
		}
		echo '<p class="info">' . esc_html__( 'To set sticky menu add id "stickymenu" to row you want to stick.',
				'marketing-and-seo-booster' ) . '</p>';
	}
}

function masb_save_postdata( $post_id ) {
	if ( ! isset( $_POST['masb_cw_nonce'] ) ) {
		return $post_id;
	}

	$nonce = $_POST['masb_cw_nonce'];
	if ( ! wp_verify_nonce( $nonce, 'masb_meta_box' ) ) {
		return $post_id;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return $post_id;
	}

	$post_type = filter_input( INPUT_POST, 'post_type' );
	if ( 'composer_widget' != $post_type || ! current_user_can( 'edit_page', $post_id ) ) {
		return $post_id;
	}

	$composer_block_type = filter_input( INPUT_POST, 'composer_block_type', FILTER_SANITIZE_STRING );

	if( empty( $composer_block_type ) ){
		return $post_id;
    }

	update_post_meta( $post_id, 'composer_block_type', $composer_block_type );
	if ( 'composer_block_header' == $composer_block_type ) {
		$header_block_type = filter_input( INPUT_POST, 'header_block_type', FILTER_SANITIZE_STRING );
		if( ! empty( $header_block_type ) ){
			update_post_meta( $post_id, 'header_block_type', $header_block_type );
        }
	} else {
		$header_block_type = '';
		delete_post_meta( $post_id, 'header_block_type' );
	}

}

add_action( 'save_post', 'masb_save_postdata' );

// Add content block information column to overview
function masb_modify_material_table( $column ) {
	$column['composer_block_type'] = esc_html__( 'Composer Block Type', 'marketing-and-seo-booster' );

	return $column;
}

add_filter( 'manage_edit-composer_widget_columns', 'masb_modify_material_table' );

function masb_modify_post_table_row( $column_name, $post_id ) {
	$custom_fields = get_post_custom( $post_id );
	switch ( $column_name ) {
		case 'content_block_information' :
			if ( ! empty( $custom_fields['_content_block_information'][0] ) ) {
				echo $custom_fields['_content_block_information'][0];
			}
			break;
	}
}

add_action( 'manage_posts_custom_column', 'masb_modify_post_table_row', 10, 2 );

function masb_get_content_css( $post ) {

	global $post, $kc;

	$post_css  = '';
	$post_data = get_post_meta( $post->ID, 'kc_data', true );
	$settings  = $kc->settings();

	if ( ! empty( $post_data ) && ! empty( $post_data['css'] ) ) {
		$post_css .= $post_data['css'];
	}

	if ( ! empty( $settings['css_code'] ) ) {
		$post_css .= $settings['css_code'];
	}

	if ( ! empty( $post_data ) && isset( $post_data['max_width'] ) && ! empty( $post_data['max_width'] ) ) {
		$post_css .= '.kc-container{max-width: ' . esc_attr( $post_data['max_width'] ) . ';}';
	} else if ( ! empty( $settings['max_width'] ) && isset( $settings['max_width'] ) && ! empty( $settings['max_width'] ) ) {
		$post_css .= '.kc-container{max-width: ' . esc_attr( $settings['max_width'] ) . ';}';
	}

	$post_css = esc_html( $post_css );
	$post_css = str_replace(
		array(
			"\n",
			"  ",
			": ",
			" {",
			"  ",
			'&gt;',
			'&lt;',
			'&quot;',
			'&#039;',
			"</style>",
			"<style",
			"<script",
			"</script",
		),
		array(
			'',
			' ',
			':',
			'{',
			" ",
			'>',
			'<',
			'"',
			"'",
			"&lt;/style&quot;",
			"&lt;style",
			"&lt;script",
			"&lt;/script",
		),
		$post_css
	);

	$post_css = '<style type="text/css" id="kc-css-general">.kc-off-notice{display: inline-block !important;}' . $post_css . '</style>';

	/*
	*	Start render CSS of all elements
	*/


	$post_css = str_replace(
		array(
			"\n",
			"  ",
			": ",
			" {",
			"  ",
			'&gt;',
			'&lt;',
			'&quot;',
			'&#039;',
			"</style>",
			"<style",
			"<script",
			"</script",
		),
		array(
			'',
			' ',
			':',
			'{',
			" ",
			'>',
			'<',
			'"',
			"'",
			"&lt;/style&quot;",
			"&lt;style",
			"&lt;script",
			"&lt;/script",
		),
		$post_css
	);

	return $post_css;
}