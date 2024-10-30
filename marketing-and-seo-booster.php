<?php
/**
 * Plugin Name: Marketing and SEO Booster
 * Plugin URI:  http://secretlab.pw/marketing-and-seo-booster
 * Description: Marketing and SEO booster: marketing booster, SEO titles, lazy-load, A|B test, analytics codes, icons fonts manager, portfolio post types
 * Version:     1.9.10
 * Author:      SecretLab
 * Author URI:  http://secretlab.pw/
 * License:     GPL3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: marketing-and-seo-booster
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if ( ! defined( 'SLMSB_PATH' ) ) {
	define( 'SLMSB_PATH', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'SLMSB_MOD_PATH' ) ) {
	define( 'SLMSB_MOD_PATH', SLMSB_PATH . 'modules/' );
}

class SL_Marketing_and_SEO_Booster {

	public static $keys = array();
	public static $blog_keys = array();
	public static $shop_keys = array();

	public static $instance = null;
	private $def_mods = array();
	public $act_mods = array();

	public function __construct() {

		/*
         * Keys for Page Options
         */
		$tcbh = esc_html__( 'Select Header', 'marketing-and-seo-booster' );
		$tcbf = esc_html__( 'Select Footer', 'marketing-and-seo-booster' );
		$tsht = esc_html__( 'Select Header Type', 'marketing-and-seo-booster' );
		$tifh = esc_html__( 'Image for Header', 'marketing-and-seo-booster' );
		$tss  = esc_html__( 'Select Slider', 'marketing-and-seo-booster' );
		$tspl = esc_html__( 'Select Page Layout', 'marketing-and-seo-booster' );
		$tcol = esc_html__( 'Color', 'marketing-and-seo-booster' );
		$timg = esc_html__( 'Image', 'marketing-and-seo-booster' );
		$trep = esc_html__( 'Repeat', 'marketing-and-seo-booster' );
		$tsiz = esc_html__( 'Size', 'marketing-and-seo-booster' );
		$tatt = esc_html__( 'Attachment', 'marketing-and-seo-booster' );
		$tpos = esc_html__( 'Position', 'marketing-and-seo-booster' );
		$tssl = esc_html__( 'Select Sidebar Layout', 'marketing-and-seo-booster' );
		$twls = esc_html__( 'Widgets for Left Sidebar', 'marketing-and-seo-booster' );
		$twrs = esc_html__( 'Widgets for Right Sidebar', 'marketing-and-seo-booster' );

		self::$keys = array(
			'header_widget'      => $tcbh,
			'footer_widget'      => $tcbf,
			'header_type'        => $tsht,
			'header_image'       => $tifh,
			'pick_slider'        => $tss,
			'design-layout'      => $tspl,
			'boxed-background'   => array(
				'background-color'      => $tcol,
				'background-image'      => $timg,
				'background-repeat'     => $trep,
				'background-size'       => $tsiz,
				'background-attachment' => $tatt,
				'background-position'   => $tpos,
			),
			'content-background' => array(
				'background-color'      => $tcol,
				'background-image'      => $timg,
				'background-repeat'     => $trep,
				'background-size'       => $tsiz,
				'background-attachment' => $tatt,
				'background-position'   => $tpos,
			),

			'sidebar-layout'        => $tssl,
			'left_sidebar_widgets'  => $twls,
			'right_sidebar_widgets' => $twrs,
		);

		self::$blog_keys = array(
			'blog_header_widget'      => $tcbh,
			'blog_footer_widget'      => $tcbf,
			'blog-header_type'        => $tsht,
			'blog-header_image'       => $tifh,
			'blog-pick_slider'        => $tss,
			'blog-layout'             => $tspl,
			'blog-boxed-background'   => array(
				'background-color'      => $tcol,
				'background-image'      => $timg,
				'background-repeat'     => $trep,
				'background-size'       => $tsiz,
				'background-attachment' => $tatt,
				'background-position'   => $tpos,
			),
			'blog-content-background' => array(
				'background-color'      => $tcol,
				'background-image'      => $timg,
				'background-repeat'     => $trep,
				'background-size'       => $tsiz,
				'background-attachment' => $tatt,
				'background-position'   => $tpos,
			),

			'blog-sidebar-layout'        => $tssl,
			'blog_left_sidebar_widgets'  => $twls,
			'blog_right_sidebar_widgets' => $twrs,
		);

		self::$shop_keys = array(
			'shop_header_widget'         => $tcbh,
			'shop_footer_widget'         => $tcbf,
			'shop-header_type'           => $tsht,
			'shop-header_image'          => $tifh,
			'shop-pick_slider'           => $tss,
			'shop-layout'                => $tspl,
			'shop-boxed-background'      => array(
				'background-color'      => $tcol,
				'background-image'      => $timg,
				'background-repeat'     => $trep,
				'background-size'       => $tsiz,
				'background-attachment' => $tatt,
				'background-position'   => $tpos,
			),
			'shop-content-background'    => array(
				'background-color'      => $tcol,
				'background-image'      => $timg,
				'background-repeat'     => $trep,
				'background-size'       => $tsiz,
				'background-attachment' => $tatt,
				'background-position'   => $tpos,
			),
			'shop-sidebar-layout'        => $tssl,
			'shop_left_sidebar_widgets'  => $twls,
			'shop_right_sidebar_widgets' => $twrs,
		);

		/*
		 * All parts of the plugin
		*/
		$this->def_mods = array(
			'sl-icons-manager' => array(
				'icon'        => 'nat-smm2',
				'title'       => esc_html__( 'Icons Fonts Manager', 'marketing-and-seo-booster' ), // Icon Font manager
				'description' => esc_html__( 'Upload you own icon fonts prepared with an app https://icomoon.io/app/',
					'marketing-and-seo-booster' ),
			),
			'post-types'       => array(
				'icon'        => 'nat-315',
				'title'       => esc_html__( 'Portfolio Posts', 'marketing-and-seo-booster' ),
				// Register Portfolio Post Type
				'description' => esc_html__( 'Custom post type for portfolio or another content type.',
					'marketing-and-seo-booster' ),
			),
			'abc_split'        => array(
				'icon'        => 'nat-ab-split-test-2',
				'title'       => esc_html__( 'ABC Split', 'marketing-and-seo-booster' ), // ABC split
				'description' => esc_html__( 'Create pages and compare the conversion rate with the test tool. Split all advertising traffic to 2-4 pages and then measure a result in your analytics system.',
					'marketing-and-seo-booster' ),
			),
			'analytics_codes'  => array(
				'icon'        => 'nat-bse-bar-chart',
				'title'       => esc_html__( 'Analytics Codes', 'marketing-and-seo-booster' ), // Analytics Codes
				'description' => esc_html__( 'Install any analytics and conversion tracking codes.',
					'marketing-and-seo-booster' ),
			),
			'lazy_load'        => array(
				'icon'        => 'nat-rocket2',
				'title'       => esc_html__( 'Lazy Load', 'marketing-and-seo-booster' ), // Analytics Codes
				'description' => esc_html__( 'Lazy load of images and videos', 'marketing-and-seo-booster' ),
			),
		);

		if ( class_exists( 'Secret_Lab_Config' ) || class_exists( 'Atiframebuilder_Config' ) ) {
			$this->def_mods['modal_windows']       = array(
				'icon'        => 'nat-popout',
				'title'       => esc_html__( 'Modal Window', 'marketing-and-seo-booster' ),
				// Widget with composer for modal window
				'description' => esc_html__( 'Create modal windows with rules when to appear. You can build modals with drag&drop builder.',
					'marketing-and-seo-booster' ),
			);
			$this->def_mods['sidebars']            = array(
				'icon'        => 'nat-426',
				'title'       => esc_html__( 'Unlimited sidebars', 'marketing-and-seo-booster' ),
				// Unlimited sidebars: create and delete
				'description' => esc_html__( 'Create sidebars and select composer blocks to display it anywhere.',
					'marketing-and-seo-booster' ),
			);
			$this->def_mods['page_option']         = array(
				'icon'        => 'nat-bs-settings',
				'title'       => esc_html__( 'Page Options', 'marketing-and-seo-booster' ), // Metabox options for pages
				'description' => esc_html__( 'Convenient user interface tools that save you tons of time!',
					'marketing-and-seo-booster' ),
			);
			$this->def_mods['composer_widget']     = array(
				'icon'        => 'nat-puzzle2',
				'title'       => esc_html__( 'Composer Widget', 'marketing-and-seo-booster' ),
				// Widget with composer that you can set into sidebar
				'description' => esc_html__( 'Composer widget lets you create headers, footers, and sidebars with a drag&drop builder with real-time preview.',
					'marketing-and-seo-booster' ),
			);
			$this->def_mods['topbar_page_options'] = array(
				'icon'        => 'nat-556',
				'title'       => esc_html__( 'Front Editor Page Options', 'marketing-and-seo-booster' ),
				// Metabox options for pages on topbar
				'description' => esc_html__( 'Smart top bar tabs with page options.', 'marketing-and-seo-booster' ),
			);
		}

		if ( ! is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			$this->def_mods['seo_titles'] = array(
				'icon'        => 'nat-seo-search-symbol',
				'title'       => esc_html__( 'SEO Meta', 'marketing-and-seo-booster' ), // SEO
				'description' => esc_html__( 'SEO meta tags tool: setup your titles, meta keywords and description for post types and pages.',
					'marketing-and-seo-booster' ),
			);
		}

		$this->set_active_modules();

		foreach ( $this->act_mods as $mod => $data ) {
			if ( file_exists( plugin_dir_path( __FILE__ ) . '/modules/' . $mod . '.php' ) ) {
				require 'modules/' . $mod . '.php';
			}
		}
		unset( $mod, $data );

		if ( is_admin() ) {

			add_action( 'admin_menu', array( $this, 'register_menu_page' ), 9 );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_style' ) );

		}
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'admin_bar_menu', array( $this, 'kc_submenu' ), 2000 );

	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function admin_style( $hook ) {
		global $wp_version;
		wp_enqueue_style( 'masb_admin_style', SLMSB_MOD_PATH . 'css/admin_style.min.css' );
		wp_enqueue_style( 'wp-color-picker' );
		if( version_compare( $wp_version, '5.5', '>=' ) ){
			wp_localize_script(
				'wp-color-picker',
				'wpColorPickerL10n',
				array(
					'clear'            => esc_html__( 'Clear', 'marketing-and-seo-booster' ),
					'clearAriaLabel'   => esc_html__( 'Clear color', 'marketing-and-seo-booster' ),
					'defaultString'    => esc_html__( 'Default', 'marketing-and-seo-booster' ),
					'defaultAriaLabel' => esc_html__( 'Select default color', 'marketing-and-seo-booster' ),
					'pick'             => esc_html__( 'Select Color', 'marketing-and-seo-booster' ),
					'defaultLabel'     => esc_html__( 'Color value', 'marketing-and-seo-booster' ),
				)
			);
        }
		wp_enqueue_script( 'wp-color-picker-alpha',
			SLMSB_MOD_PATH . 'js/wp-color-picker-alpha.js',
			array( 'wp-color-picker' ),
			false,
			true );

		if ( in_array( $hook,
			array(
				'post.php',
				'page.php',
				'post-new.php',
				'toplevel_page_kingcomposer',
				'marketing_page_masb-seo-titles',
				'marketing_page_abc-split',
                'widgets.php',
			) ) ) {
			wp_enqueue_script( 'masb_admin_js', SLMSB_MOD_PATH . 'js/admin-func.min.js', array( 'jquery', ), false, true );
		}
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'marketing-and-seo-booster',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public function register_menu_page() {
		add_menu_page(
			esc_html__( 'Marketing', 'marketing-and-seo-booster' ),
			esc_html__( 'Marketing', 'marketing-and-seo-booster' ),
			'manage_options',
			'marketing-options',
			array( $this, 'options_page' ),
			'',
			"27.1"
		);
	}

	public function register_settings() {
		register_setting( 'masb_options_group', 'masb_modules', array( $this, 'save_options' ) );
	}

	public function save_options( $options ) {
		$nonce = filter_input( INPUT_POST, 'masb_nonce' );
		if ( empty( $nonce ) ) { // Submit from alternate forms.
			wp_die();
		}
		check_admin_referer( plugin_basename( __FILE__ ), 'masb_nonce' );
		/**
		 * @todo Delete next after several months from 07.2019
		 */
		delete_option( 'sst_modules' );

		return $options;
	}

	public function options_page() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
        <div class="wrap">
            <h1><?php esc_html_e( 'SEO and Marketing Booster', 'marketing-and-seo-booster' ) ?></h1>
            <form action="options.php" method="POST">
				<?php
				settings_fields( 'masb_options_group' );
				wp_nonce_field( plugin_basename( __FILE__ ), 'masb_nonce' );
				?>
                <div class="masb-modules container">
                    <h3><?php esc_html_e( 'Choose modules you want to use:', 'marketing-and-seo-booster' ) ?></h3>
					<?php
					foreach ( array_chunk( $this->def_mods, 3, true ) as $row ) {
						?>
                        <div class="row">
							<?php
							foreach ( $row as $mod => $data ) {
								?>
                                <div class="col-md-4">
                                    <input type="checkbox" id="masb-mod-<?php echo $mod; ?>" name="masb_modules[]"
                                           value="<?php echo $mod; ?>" <?php if ( isset( $this->act_mods[ $mod ] ) ) {
										echo "checked";
									} ?> />
                                    <div class="masb-module-block">
                                        <label for="masb-mod-<?php echo $mod; ?>">
                                            <span class="masb-module-icon">
                                                <i class="<?php if ( ! empty( $data['icon'] ) ) {
	                                                echo $data['icon'];
                                                } else {
	                                                echo 'nat-spinner9';
                                                } ?>"></i>
                                            </span>
                                            <span class="masb-module-title">
                                                <?php
                                                echo $data['title'];
                                                ?>
                                            </span>
                                            <span class="masb-module-description">
                                                <?php
                                                echo $data['description'];
                                                ?>
                                            </span>
                                        </label>
                                    </div>
                                </div>
								<?php
							}
							?>
                        </div>
						<?php
					}
					?>
                </div>
				<?php submit_button(); ?>
            </form>
        </div>
		<?php
	}

	public function set_active_modules() {
		/**
		 * @todo Delete checking and deleting after several months from 07.2019
		 */
		$act_mods = get_option( 'sst_modules' );
		if ( false === $act_mods ) {
			$act_mods = get_option( 'masb_modules' );
		}

		if ( $act_mods !== false ) {
			if ( is_array( $act_mods ) ) {
				foreach ( $act_mods as $mod ) {
					if ( isset( $this->def_mods[ $mod ] ) ) {
						$this->act_mods[ $mod ] = $this->def_mods[ $mod ];
					}
				}
			}
			unset( $act_mods );

			return;

		}
		$this->act_mods = $this->def_mods;

		return;
	}

	public function kc_submenu() {
		if ( class_exists( 'Secret_Lab_Config' ) ) {
			global $post, $secretlab, $secretlab_layout, $wp_admin_bar;
			if ( $post && $post instanceof WP_Post && isset( $wp_admin_bar ) ) {
				$menu_id = 'kc-edit';
				if ( ! empty( $secretlab_layout['header_widget'] ) ) {
					$header_id = $secretlab_layout['header_widget'];
				} elseif ( ! empty( $secretlab['header_widget'] ) ) {
					$header_id = $secretlab['header_widget'];
				} else {
					$header_id = '';
				}
				if ( ! empty( $secretlab_layout['footer_widget'] ) ) {
					$footer_id = $secretlab_layout['footer_widget'];
				} elseif ( ! empty( $secretlab['footer_widget'] ) ) {
					$footer_id = $secretlab['footer_widget'];
				} else {
					$footer_id = '';
				}

				$wp_admin_bar->add_menu( array(
					'parent' => $menu_id,
					'title'  => esc_html__( 'Edit Page', 'marketing-and-seo-booster' ),
					'id'     => 'kc-edit-page-edit',
					'href'   => admin_url( '?page=kingcomposer&kc_action=live-editor&id=' . $post->ID ),
				) );
				if ( $header_id ) {
					$wp_admin_bar->add_menu( array(
						'parent' => $menu_id,
						'title'  => esc_html__( 'Edit Header', 'marketing-and-seo-booster' ),
						'id'     => 'kc-edit-header-edit',
						'href'   => admin_url( '?page=kingcomposer&kc_action=live-editor&id=' . $header_id ),
						'meta'   => array( 'target' => '_blank' ),
					) );
				}
				if ( $footer_id ) {
					$wp_admin_bar->add_menu( array(
						'parent' => $menu_id,
						'title'  => esc_html__( 'Edit Footer', 'marketing-and-seo-booster' ),
						'id'     => 'kc-edit-footer-edit',
						'href'   => admin_url( '?page=kingcomposer&kc_action=live-editor&id=' . $footer_id ),
						'meta'   => array( 'target' => '_blank' ),
					) );
				}
			}
		} else {
			global $post, $secretlab, $atiframebuilder_layout, $wp_admin_bar;
			if ( $post && isset( $wp_admin_bar ) && ! is_archive() ) {
				$menu_id = 'kc-edit';
				if ( ! empty( $atiframebuilder_layout['header_widget'] ) ) {
					$header_id = $atiframebuilder_layout['header_widget'];
				} elseif ( ! empty( $secretlab['header_widget'] ) ) {
					$header_id = $secretlab['header_widget'];
				} else {
					$header_id = '';
				}
				if ( ! empty( $atiframebuilder_layout['footer_widget'] ) ) {
					$footer_id = $atiframebuilder_layout['footer_widget'];
				} elseif ( ! empty( $secretlab['footer_widget'] ) ) {
					$footer_id = $secretlab['footer_widget'];
				} else {
					$footer_id = '';
				}

				$wp_admin_bar->add_menu( array(
					'parent' => $menu_id,
					'title'  => esc_html__( 'Edit Page', 'atiframe' ),
					'id'     => 'kc-edit-page-edit',
					'href'   => admin_url( '?page=kingcomposer&kc_action=live-editor&id=' . $post->ID ),
				) );
				if ( $header_id ) {
					$wp_admin_bar->add_menu( array(
						'parent' => $menu_id,
						'title'  => esc_html__( 'Edit Header', 'atiframe' ),
						'id'     => 'kc-edit-header-edit',
						'href'   => admin_url( '?page=kingcomposer&kc_action=live-editor&id=' . $header_id ),
						'meta'   => array( 'target' => '_blank' ),
					) );
				}
				if ( $footer_id ) {
					$wp_admin_bar->add_menu( array(
						'parent' => $menu_id,
						'title'  => esc_html__( 'Edit Footer', 'atiframe' ),
						'id'     => 'kc-edit-footer-edit',
						'href'   => admin_url( '?page=kingcomposer&kc_action=live-editor&id=' . $footer_id ),
						'meta'   => array( 'target' => '_blank' ),
					) );
				}
			}
		}
	}

	/*
	 * Sliders list
	 */
	public static function get_sliders_array() {
		global $wpdb;
		$arr = array();

		if ( class_exists( 'LS_Sliders' ) ) {
			$sliders = LS_Sliders::find();
			foreach ( $sliders as $slider ) {
				$arr[ 'lay_' . $slider['id'] ] = $slider['name'];
			}
		}

		if ( class_exists( 'RevSliderSlider' ) ) {
			$RsExists = count( $wpdb->get_results( "SELECT * FROM information_schema.tables WHERE table_schema = '" . $wpdb->dbname . "' AND table_name = '" . $wpdb->prefix . "revslider_sliders' LIMIT 1",
				ARRAY_A ) );
			if ( $RsExists > 0 ) {
				$revSliders = $wpdb->get_results( "SELECT title, alias FROM " . $wpdb->prefix . "revslider_sliders WHERE ( type is NULL OR type = '' )",
					ARRAY_A );
				if ( count( $revSliders ) > 0 ) {
					foreach ( $revSliders as $slider ) {
						$arr[ 'rev_' . $slider['alias'] ] = $slider['title'];
					}
				}
			}
		}

		if ( count( $arr ) == 0 ) {
			$arr = array(
				0 => esc_attr__( 'The Theme Support Layer Slider and Slider Revolution, but couldn\'t find it. Install one of the plug-ins to choose the slider to display in the header.',
					'marketing-and-seo-booster' ),
			);
		}

		asort( $arr );
		array_unshift( $arr, 'none' );

		return $arr;
	}

	public static function array_insert( $array, $var, $position ) {
		$before = array_slice( $array, 0, $position );
		$after  = array_slice( $array, $position );
		$return = array_merge( $before, (array) $var );

		return array_merge( $return, $after );
	}


	public static function img_selector( $key, $imgs, $setting, $keys, $hidden, $echo = true ) {

		$out = '<div id="' . $key . '" class="custom_settings_box">';
		$out .= '<div class="ssc_label"><span>' . $keys[ $key ] . '</span></div>' .
		        '<ul class="custom settings">';
		foreach ( $imgs as $img ) {
			$out .= '<li class="custom_setting" ><img src=' . esc_attr( $img ) . '></li>';
		}
		$out .= '</ul>
	      <input id="' . $key . '_input" class="custom_setting_input hidden" type="text" name="' . esc_attr( $key . $hidden ) . '" value="' . esc_attr( $setting ) . '">
		  </div>';

		if ( $echo ) {
			echo $out;
		} else {
			return $out;
		}
	}

	public static function get_sidebars() {
		$sidebars = array();
		foreach ( $GLOBALS['wp_registered_sidebars'] as $sb ) {
			$sidebars[ $sb['id'] ] = $sb['name'];
		}

		return $sidebars;
	}
}

register_activation_hook( __FILE__, 'marketing_and_seo_booster_add_default_font' );

function marketing_and_seo_booster_add_default_font() {
	require 'modules/sl-icons-manager.php';
	if ( class_exists( 'SL_Icon_Manager' ) ) {
		$icon_manager_for_default_font = new SL_Icon_Manager();
		$icon_manager_for_default_font->unzip_default_font();
	}
}

add_action( 'after_setup_theme', array( 'SL_Marketing_and_SEO_Booster', 'get_instance' ), 1 );