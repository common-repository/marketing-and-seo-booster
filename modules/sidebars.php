<?php
/*
 The module manages Sidebars in WordPress admin panel
v 1.0.1
*/

if ( ! class_exists( 'masb_Widget_Areas' ) ) {

	class masb_Widget_areas {

		public $redux;
		public $widget_areas = array();

		public function __construct( $widget_areas = array() ) {

			add_action( 'load-widgets.php', array( &$this, 'register_custom_widget_areas' ) );
			add_action( 'widgets_init', array( &$this, 'register_custom_widget_areas' ) );
			add_action( 'admin_footer', array( $this, 'new_widget_area_box' ) );
			add_action( 'wp_ajax_masb_delete_widget_area', array( $this, 'masb_delete_custom_widget_area' ) );
			add_action( 'wp_ajax_masb_add_widget_area', array( $this, 'masb_add_custom_widget_area' ) );

		}

		/* Check of the Name */


		public function masb_add_custom_widget_area() {
			if ( ! empty( $_REQUEST['name'] ) ) {
				$response = array();
				$name     = strip_tags( ( stripslashes( $_REQUEST['name'] ) ) );
				$this->get_widget_areas();
				$key = array_search( $name, $this->widget_areas );
				if ( is_numeric( $key ) ) {
					$response['code'] = 0;
					$response['text'] = 'Already exists widget with such name - <b>' . $name . '</b>, set another one';
				} else {
					array_push( $this->widget_areas, $name );
					set_theme_mod( 'sl-widget-areas', array_unique( $this->widget_areas ) );
					$response['code'] = 1;
				}
			} else {
				$response['code'] = 0;
				$response['text'] = 'widget name must be at least 1 symbol length';
			}
			echo json_encode( $response );
			die();
		}


		/* Display a form */

		public function new_widget_area_box() {

			$screen = get_current_screen();

			if ( $screen->id == 'widgets' ) {
				$content = '
                    <div id="sl-add-widget-template">
                                    <div id="sl-add-widget" class="widgets-holder-wrap">
                                        <div id="widget_informer"></div>
                                            <div class="sl-add-widget-template">
                                                <div class="sidebar-name">
                                                    <h3>' . esc_html__( 'Create a Sidebar', 'marketing-and-seo-booster' ) . '<span class="spinner"></span></h3>
                                                </div>
                                            <div class="sidebar-description">
                                            <p>You can add unlimited widget areas for display it on pages through King Composer. </p>
                                            <form id="addWidgetAreaForm" action="" method="post">
                                                <div class="widget-content">
                                                    <input id="sl-add-widget-input" name="sl-add-widget-input" type="text" class="regular-text" title="' . esc_attr__( 'Name',
						'marketing-and-seo-booster' ) . '" placeholder="' . esc_attr__( 'Name', 'marketing-and-seo-booster' ) . '" />
                                                </div>
                                                <div class="widget-control-actions">
                                                    <div class="aligncenter">
                                                        <input class="addWidgetArea-button button-primary" type="submit" value="' . esc_attr__( 'Create Sidebar',
						'marketing-and-seo-booster' ) . '" />
                                                    </div>
                                                    <br class="clear">
                                                </div>
                                            </form>
                                            </div>
                                        </div>
                                    </div>
                    </div>';
				echo $content;
			}

		}


		/* Register Sidebar */

		function register_custom_widget_areas() {

			if ( empty( $this->widget_areas ) ) {
				$this->get_widget_areas();
			}

			$options = array(
				'before_title'  => '<h3 class="widgettitle">',
				'after_title'   => '</h3>',
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
			);
			if ( is_array( $this->widget_areas ) ) {
				foreach ( array_unique( $this->widget_areas ) as $widget_area ) {
					$options['id']    = sanitize_key( $widget_area );
					$options['name']  = $widget_area;
					$options['class'] = 'sl-custom';
					register_sidebar( $options );
				}
			}
			wp_enqueue_style( 'dashicons' );
		}

		/* Sidebars array */


		public function get_widget_areas( $return = false ) {

			$saved_areas = get_theme_mod( 'sl-widget-areas' );

			if ( ! empty( $saved_areas ) ) {
				$this->widget_areas = array_unique( array_merge( $this->widget_areas, $saved_areas ) );
			}

			if ( $return ) {
				return $this->widget_areas;
			}

		}


		/* Delete Sidebar */

		function masb_delete_custom_widget_area() {

			if ( ! empty( $_REQUEST['name'] ) ) {
				$name = strip_tags( ( stripslashes( $_REQUEST['name'] ) ) );
				$this->get_widget_areas();
				$key = array_search( $name, $this->widget_areas );
				if ( $key >= 0 ) {
					unset( $this->widget_areas[ $key ] );
					set_theme_mod( 'sl-widget-areas', array_unique( $this->widget_areas ) );
					echo "widget_area-deleted";
				}
			}

			die();
		}

	}

	new masb_Widget_areas();

}

?>