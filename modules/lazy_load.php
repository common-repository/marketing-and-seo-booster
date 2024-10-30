<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 21.03.2019
 * Time: 9:05
 */


add_action( 'after_setup_theme', array( 'MASB_Lazy_Load', 'init' ), 30 );

class MASB_Lazy_Load {

	static $instance = null;

	private $except_classes;

	/**
	 * MASB_Lazy_Load constructor.
	 */
	public function __construct() {

		$this->except_classes = get_option( 'masb_lazy_exception_classes' );
		if ( false === $this->except_classes ) {
			$this->except_classes = '';
		}

		add_filter( 'the_content', array( $this, 'filter_images' ), 100 );
		add_filter( 'the_content', array( $this, 'filter_iframes' ), 100 );

		add_filter( 'secretlab_theme_custom_footer', array( $this, 'filter_images' ), 100 );
		add_filter( 'secretlab_theme_custom_footer', array( $this, 'filter_iframes' ), 100 );

		add_filter( 'atiframebuilder_custom_footer', array( $this, 'filter_images' ), 100 );
		add_filter( 'atiframebuilder_custom_footer', array( $this, 'filter_iframes' ), 100 );
		
		add_filter( 'widget_text', array( $this, 'filter_images' ), 100 );
		add_filter( 'acf_the_content', array( $this, 'filter_images' ), 100 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_submenu_page' ), 9 );
		}

	}

	public static function init() {
		self::$instance = new self;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function register_submenu_page() {
		add_submenu_page( 'marketing-options',
			esc_html__( 'Lazy Load', 'marketing-and-seo-booster' ),
			esc_html__( 'Lazy Load', 'marketing-and-seo-booster' ),
			'manage_options',
			'masb-lazy-load-options',
			array( $this, 'settings_page' ) );

		add_action( 'admin_init', array( $this, 'add_settings' ) );
	}

	public function settings_page() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

		if ( false === $settings ) {
			return;
		}
		echo '<div class="wrap">

        <h1>' . get_admin_page_title() . '</h1>
        <div class="masb-marketing-notice">
        <p>' . esc_html__( 'To disable lazy load - just add classes with separating by comma, for example: my-image-class-1, my-image-class-2.',
				'marketing-and-seo-booster' ) . '</p>
        </div>
        <form action="options.php" method="POST">';
		settings_fields( 'masb-lazy-exception-classes' );
		do_settings_sections( 'masb-lazy-load-options' );
		submit_button();
		echo '</form>
    </div>';
	}

	public function add_settings() {
		register_setting(
			'masb-lazy-exception-classes',
			'masb_lazy_exception_classes',
			array( $this, 'exception_classes_saving' )
		);
		add_settings_section( 'masb_lazy_load_settings', '', '', 'masb-lazy-load-options' );
		add_settings_field(
			'masb-lazy-exception-classes',
			esc_html__( 'Lazy Load Settings:', 'marketing-and-seo-booster' ),
			array( $this, 'exception_classes_input' ),
			'masb-lazy-load-options',
			'masb_lazy_load_settings'
		);
	}

	function exception_classes_input() {
		echo '<input type="text" id="masb-lazy-exception-classes" name="masb_lazy_exception_classes" style="width: 100%;" value="' . esc_attr( $this->except_classes ) . '">';
	}

	function exception_classes_saving( $options ) {
		return $options;
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'lazysizes', SLMSB_PATH . 'js/lazysizes.min.js', array(), '', true );
	}

	public function filter_images( $content ) {

		if ( empty( $content ) || is_feed() || is_admin() ) {
			return $content;
		}
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() === true ) {
			return $content;
		}

		$matches = array();
		preg_match_all( '/<img[\s\r\n]+(.*?)>/is', $content, $matches );
		$search  = array();
		$replace = array();


		foreach ( $matches[0] as $img_html ) {
			if ( strpos( $img_html, 'data-src' ) !== false || strpos( $img_html, 'data-srcset' ) !== false ) {
				continue;
			}

			//CSS classes to exclude
			if ( ! empty( $this->except_classes ) ) {
				if ( $this->is_except_class( $img_html ) ) {
					continue;
				}
			}

			$width  = array();
			$heigth = array();

			preg_match( '/width=["\']([0-9]{2,})["\']/i', $img_html, $width );
			preg_match( '/height=["\']([0-9]{2,})["\']/i', $img_html, $heigth );

			if ( ! empty( $width ) && ! empty( $heigth ) ) {
				$isWidth = 1;
			} else {
				preg_match( '/-([0-9]{2,})x/i', $img_html, $width );
				preg_match( '/[0-9]{2,}x([0-9]{2,})\./i', $img_html, $heigth );
				if ( ! empty( $width ) && ! empty( $heigth ) ) {
					$isWidth = 0;
				}
			}

			$widthHtml = '';

			$widthSizes = array();
			preg_match( '/sizes=\"\(max-width: ([0-9]{2,})px/i', $img_html, $widthSizes );
			if ( empty( $isWidth ) ) {
				if ( ! empty( $widthSizes ) ) {
					$widthHtml = 'width="' . $widthSizes[1] . '"';
					$width[1]  = $widthSizes[1];
				} else {
					$widthHtml = '';
				}
			}

			$output = '';
			$output = preg_replace( '/<img(.*?)src=/is', '<img  ' . $widthHtml . ' $1data-src=', $img_html );
			$output = preg_replace( '/<img(.*?)srcset=/is', '<img$1data-srcset=', $output );

			if ( preg_match( '/class=["\']/i', $output ) ) {
				$output = preg_replace( '/class=(["\'])(.*?)["\']/is', 'class=$1$2 lazyload$1', $output );
			} else {
				$output = preg_replace( '/<img/is', '<img class="lazyload"', $output );
			}

			array_push( $search, $img_html );
			array_push( $replace, $output );
		}

		$search  = array_unique( $search );
		$replace = array_unique( $replace );
		$content = str_replace( $search, $replace, $content );

		return $content;

	}

	public function filter_iframes( $content ) {

		if ( empty( $content ) || is_feed() || is_admin() ) {
			return $content;
		}
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() === true ) {
			return $content;
		}

		$matches = array();
		preg_match_all( '/<iframe[\s\r\n]+(.*?)>/is', $content, $matches );

		$search  = array();
		$replace = array();

		if ( ! empty( $matches ) ) {
			foreach ( $matches[0] as $ifr_html ) {

				//CSS classes to exclude
				if ( ! empty( $this->except_classes ) ) {
					if ( $this->is_except_class( $ifr_html ) ) {
						continue;
					}
				}

				$output = '';
				$output = preg_replace( '/<iframe(.*?)src=/is', '<iframe $1data-src=', $ifr_html );


				if ( preg_match( '/class=["\']/i', $output ) ) {
					$output = preg_replace( '/class=(["\'])(.*?)["\']/is', 'class=$1$2 lazyload$1', $output );
				} else {
					$output = preg_replace( '/<iframe/is', '<iframe class="lazyload"', $output );
				}

				array_push( $search, $ifr_html );
				array_push( $replace, $output );
			}
		}


		$search  = array_unique( $search );
		$replace = array_unique( $replace );
		$content = str_replace( $search, $replace, $content );

		return $content;

	}

	public function is_except_class( $elem_html ) {
		$except_classes = explode( ',', $this->except_classes );
		$is             = false;
		foreach ( $except_classes as $class ) {
			if ( ! empty( $class ) ) {
				if ( strpos( $elem_html, $class ) !== false ) {
					$is = true;
					break;
				}
			}
		}

		return $is;
	}

}