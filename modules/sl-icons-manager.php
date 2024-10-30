<?php

/*
 * Description: Manages Icon Fonts from http://icomoon.io/app/
 * Version:     1.0.0
 */

if ( ! class_exists( 'SL_Icon_Manager' ) ) {
	class SL_Icon_Manager {

		const DEFAULT_FONT = 'Native';

		public $dir_name = 'sl_icons';

		public $upload_dir = array();

		public $icon_styles = array();

		static $instance = null;

		private $icons_manager_available = array(
			'toplevel_page_icon-manager-dashboard',
			'nav-menus.php',
			'upload.php',
			'toplevel_page_kingcomposer',
			'post.php'
		);

		public static function init() {
			self::$instance = new self;
		}

		public static function get_instance() {
			if ( null === static::$instance ) {
				static::$instance = new static();
			}

			return static::$instance;
		}

		public function __construct() {

			global $wp_filesystem;

			if ( empty( $wp_filesystem ) ) {
				require_once( ABSPATH . '/wp-admin/includes/file.php' );
				WP_Filesystem();
			}

			$upload_dir = wp_get_upload_dir();

			$this->upload_dir['path']    = $upload_dir['basedir'];
			$this->upload_dir['url']     = $upload_dir['baseurl'];
			$this->upload_dir['tempdir'] = trailingslashit( $this->upload_dir['path'] ) . $this->dir_name . '/temp_files';

			if ( ! is_dir( $this->upload_dir['path'] . '/' . $this->dir_name ) ) {
				$tempdir = $this->create_folder( $this->upload_dir['tempdir'], false );
			}

			$icon_folders = scandir( $this->upload_dir['path'] . '/' . $this->dir_name );
			if ( $icon_folders && count( $icon_folders ) > 0 ) {
				foreach ( $icon_folders as $icon_folder ) {
					if ( is_file( $this->upload_dir['path'] . '/' . $this->dir_name . '/' . $icon_folder . '/' . $icon_folder . '.css' ) && $icon_folder != '.' && $icon_folder != '..' ) {
						$this->icon_styles[ $icon_folder ]['dir'] = $this->upload_dir['path'] . '/' . $this->dir_name . '/' . $icon_folder . '/' . $icon_folder . '.css';
						$this->icon_styles[ $icon_folder ]['url'] = $this->upload_dir['url'] . '/' . $this->dir_name . '/' . $icon_folder . '/' . $icon_folder . '.css';
					}
				}
			}

			add_action( 'admin_menu', array( $this, 'icon_manager_submenu' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_icon_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_icon_styles' ) );

			if ( wp_doing_ajax() ) {
				add_action( 'wp_ajax_sl_add_zipped_font', array( $this, 'ajax_add_zipped_font' ) );
				add_action( 'wp_ajax_sl_remove_zipped_font', array( $this, 'remove_zipped_font' ) );
				add_action( 'wp_ajax_svg_get_attachment_url', array( $this, 'ajax_get_svg' ) );
			}

			add_action( 'init', array( $this, 'icon_fonts_for_king_composer' ) );
			add_shortcode( 'icon_manager', array( $this, 'icon_manager_shortcode' ) );
			add_filter( 'upload_mimes', array( $this, 'mime_types' ) );
		}

		public function unzip_default_font() {
			$archive = self::DEFAULT_FONT . 'Font.zip';
			if ( ! file_exists( plugin_dir_path( __FILE__ ) . $archive ) ) {
				if ( ! $this->upload_default_zip_font( $archive ) ) {
					return;
				}
			}
			$default_font_file = glob( plugin_dir_path( __FILE__ ) . $archive, GLOB_NOSORT );
			$end_def_font_file = end( $default_font_file );
			if ( is_array( $default_font_file ) && ! empty( $end_def_font_file ) ) {
				$default_font = function_exists( 'mb_strtolower' ) ? mb_strtolower( self::DEFAULT_FONT ) : strtolower( self::DEFAULT_FONT );
				if ( file_exists( $end_def_font_file ) && ! is_dir( $this->upload_dir['path'] . '/' . $this->dir_name . '/' . $default_font ) ) {
					$this->add_zipped_font( $end_def_font_file );
				}
			}
		}

		public function icon_manager_submenu() {
			$page = add_menu_page( esc_html__( 'Icon Manager', 'marketing-and-seo-booster' ),
				esc_html__( 'Icon Manager', 'marketing-and-seo-booster' ),
				'activate_plugins',
				"icon-manager-dashboard",
				array( $this, 'icons_table' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 1 );
		}

		public function enqueue_admin_scripts() {
			$this->localize_script();
			wp_enqueue_script( 'media-upload' );
			wp_enqueue_media();

		}

		public function enqueue_icon_styles( $hook ) {
			if ( in_array( $hook, $this->icons_manager_available ) ) {
				wp_enqueue_script( 'sl_icons_manager_admin',
					SLMSB_PATH . 'modules/js/sl-icons-manager-admin.js',
					array( 'jquery' ),
					false,
					true );
				$this->localize_script();
			}
			if ( is_admin() || ( ! function_exists( 'kc_add_icon' ) && is_array( $this->icon_styles ) ) ) {
				foreach ( $this->icon_styles as $name => $style ) {
					wp_enqueue_style( 'sl_icons_admin_' . $name, $style['url'] );
				}
				if ( class_exists( 'KingComposer' ) ) {
					wp_enqueue_style( 'sl_icons_admin_kc_icons', plugins_url( 'kingcomposer/assets/css/icons.css' ) );
				}
			}
		}

		public function icons_table() {
			// check user capabilities
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			$out = '
			<div id="sl_icons_wrap" class="container-fluid wrap">
			<div class="row">
			<h1>' . esc_html__( 'Icon Fonts Manager', 'marketing-and-seo-booster' ) .
			       '</h1><a href="#sl_iconsset_upload" id="sl_icons_upload" class="button button-secondary button-small">' . esc_html__( 'Upload New Icons',
					'marketing-and-seo-booster' ) . '</a>
			 <div id="sl_icons_uploader_msg"></div>';

			if ( count( $this->icon_styles ) > 0 ) {
				$out .= '<div id="sl_icons_admin_box" class="sl_icons_admin_box">';
				$out .= '<div id="sl_admin_icons_search" class="col-xs-12 sl_admin_icons_search">' . esc_html__( 'Search by icon name',
						'marketing-and-seo-booster' ) . ': <input type="text" placeholder="' . esc_html__( 'Search',
						'marketing-and-seo-booster' ) . '"></div>';
				foreach ( $this->icon_styles as $font_name => $style ) {
					$out .= $this->get_icons_font( $font_name, $style['dir'] );
				}
				$out .= '</div>';
			} else {
				$out .= '
				<div class="no_fonts">
					<p>' . esc_html__( 'No font icons uploaded. Upload some font icons to display here.',
						'marketing-and-seo-booster' ) . '</p>
				</div>';
			}
			$out .= '</div>
			</div>';

			echo $out;
		}

		public function get_icons_font( $font_name, $css_file, $view = null ) {

			$font_info = $this->sl_get_font_info( $css_file );
			$out       = ( ! isset( $out ) ? '' : $out );
			if ( $font_info && count( $font_info ) > 0 ) {
				if ( ! $view ) {
					$out .= $this->get_html_for_admin_panel( $font_name, $font_info['prefix'], $font_info['icons'] );
				} else {
					$out .= call_user_function( $view, $font_name, $font_info['prefix'], $font_info['icons'] );
				}
			} else {
				$out = '';
			}

			return $out;

		}

		public function get_html_for_admin_panel( $font_name, $prefix, $icons ) {

			$n   = count( $icons[0] );
			$out = '<div id="' . $font_name . '-icons" class="sl_icons_block">';
			$out .= '<div class="sl_icons_service_block">
						<h3>' . $font_name . '<span class="icons-count">' . $n . ' icons</span></h3>
						<button class="button button-secondary button-small remove_mega_icons_set" data-delete="' . $font_name . '" data-title="' . esc_html__( 'Delete This Icon Set',
					'marketing-and-seo-booster' ) . '">' . esc_html__( 'Delete This Icon Set',
					'marketing-and-seo-booster' ) . '</button>
					</div>';
			$out .= '<div id="' . $font_name . '-icons_table" class="sl_all_icons_container">';
			for ( $i = 0; $i < $n; $i ++ ) {
				$out .= '
				<span class="sl_icon_container">
					<i class="' . $prefix . $icons[1][ $i ] . '" data-unicode="' . $icons[2][ $i ] . '" title="' . $prefix . $icons[1][ $i ] . '"></i>
				</span>';
			}
			$out .= '</div></div>';

			return $out;

		}

		public function get_icons_for_menu_item( $menu_item_meta ) {

			$out = $item_id = '';
			if ( count( $this->icon_styles ) > 0 ) {
				$data = filter_input( INPUT_POST, 'data' );
				if ( is_array( $data ) && ! empty( $data['menu_item_id'] ) ) {
					$item_id = esc_attr( $data['menu_item_id'] );
				}
				$out .= '<div id="sl_icons_search"><input id="icon_search" type="text" placeholder="icon search"></div>';
				$out .= '<div id="selected_icon" item_id="' . $item_id . '">' . '<span class="' . $menu_item_meta['icon'] . '" aria-hidden="true"></span><input type="hidden" value="' . $menu_item_meta['icon'] . '" name="settings[icon]"><input type="hidden" value="' . $menu_item_meta['unicode'] . '" name="settings[unicode]"><a id="remove_icon" href="#">remove icon</a></div>';
				$out .= '<div id="sl_icons">';
				foreach ( $this->icon_styles as $font => $font_paths ) {

					$font_info = $this->sl_get_font_info( $font_paths['dir'] );

					if ( $font_info ) {
						$out .= '<div class="icons_table_title">' . $font . '</div>';
						$out .= '<div id="' . $font . '-icons_table" class="sl_all_icons_container">';
						$n   = count( $font_info['icons'][0] );
						for ( $i = 0; $i < $n; $i ++ ) {
							$out .= '<span class="sl_icon_container">
											<i class="' . $font_info['prefix'] . $font_info['icons'][1][ $i ] . '" data-unicode="' . $font_info['icons'][2][ $i ] . '"></i>
										</span>';
						}
						$out .= '</div>';
					}
				}
			} else {
				$out = '<div class="menu_item_has_no_icons">There ara no available icons sets now</div>';
			}

			return $out;
		}

		public function ajax_add_zipped_font() {
			//check nonce
			check_ajax_referer( 'sl_nonce', 'nonce' );
			$this->add_zipped_font();
			wp_die();
		}

		private function add_zipped_font( $path = null ) {
			if ( empty( $path ) ) {
				$attachment = filter_input( INPUT_POST, 'values', FILTER_DEFAULT , FILTER_REQUIRE_ARRAY );
				if ( is_array( $attachment ) && ! empty( $attachment['id'] ) ) {
					$path = realpath( get_attached_file( sanitize_key( $attachment['id'] ) ) );
				}
			}

			$unzipped = $this->unzip( $path, array( '\.eot', '\.svg', '\.ttf', '\.woff', '\.json', '\.css' ) );

			if ( $unzipped ) {
				$this->rewrite_css();
				$this->rename_files();
			}

			if ( ! $path ) {
				if ( $this->font_name == 'unknown' ) {
					$this->delete_folder( $this->upload_dir['tempdir'] );
					die( esc_html__( 'Was not able to retrieve the Font name from your Uploaded Folder',
						'marketing-and-seo-booster' ) );
				}
				die( esc_html__( 'font_added:', 'marketing-and-seo-booster' ) . $this->font_name );
			}
		}

		public function unzip( $zipfile, $filter ) {

			global $wp_filesystem;

			if ( is_dir( $this->upload_dir['tempdir'] ) ) {
				$this->delete_folder( $this->upload_dir['tempdir'] );
			}
			$tempdir = $this->create_folder( $this->upload_dir['tempdir'], false );

			if ( ! $tempdir ) {
				die( esc_html__( 'Wasn\'t able to create temp folder', 'marketing-and-seo-booster' ) );
			}

			$arch_dir = $this->upload_dir['tempdir'] . '/archive/';
			if ( unzip_file( $zipfile, $arch_dir ) ) {
				$font_name  = false;
				$files      = array();
				$files_list = $wp_filesystem->dirlist( $arch_dir, false, true );
				array_walk( $files_list,
					function ( $item, $key ) use ( &$files ) {
						if ( $item['type'] = 'd' && isset( $item['files'] ) ) {
							foreach ( $item['files'] as $file ) {
								$files[ $item['name'] . '/' . $file['name'] ] = $file;
							}
						} else {
							$files[ $item['name'] ] = $item;
						}
					} );
				if ( ! empty( $files ) && is_array( $files ) ) {
					foreach ( $files as $file => $f_data ) {
						$get_font_name = false;
						$remove        = true;
						if ( preg_match( '/' . implode( '|', $filter ) . '/', $file ) ) {
							$remove = false;
						}
						if ( substr( $file, - 1 ) == '/' || ! empty( $remove ) ) {
							continue;
						}
						$fp = $wp_filesystem->get_contents( $arch_dir . $file );
						if ( basename( $file ) == 'style.css' || basename( $file ) == 'font-awesome.css' || basename( $file ) == 'fontello.css' || basename( $file ) == 'styles.css' ) {
							$path          = $this->upload_dir['tempdir'] . '/style.css';
							$get_font_name = true;
						} else {
							if ( ! is_dir( $this->upload_dir['tempdir'] . '/fonts' ) ) {
								wp_mkdir_p( trailingslashit( $this->upload_dir['tempdir'] . '/fonts' ) );
							}
							$low_name = function_exists( 'mb_strtolower' ) ? mb_strtolower( basename( $file ) ) : strtolower( basename( $file ) );
							$path     = $this->upload_dir['tempdir'] . '/fonts/' . $low_name;
						}
						$ofp = $wp_filesystem->put_contents( $path, $fp, FS_CHMOD_FILE );
						if ( ! $fp && ! $ofp ) {
							die( esc_html__( 'Unable to extract the file.', 'marketing-and-seo-booster' ) );
						}
						if ( $get_font_name ) {
							$font_name = $this->get_font_name( $path );
						}
					}
					if ( $font_name ) {
						$wp_filesystem->delete( $arch_dir, true );
						$low_name = function_exists( 'mb_strtolower' ) ? mb_strtolower( $font_name ) : strtolower( $font_name );
						$result   = rename( $this->upload_dir['tempdir'],
							$this->upload_dir['path'] . '/' . $this->dir_name . '/' . $low_name );
					} else {
						die( esc_html__( "Wasn't able to read font name", 'marketing-and-seo-booster' ) );
					}
				} else {
					die( esc_html__( "Wasn't able get font files from archive", 'marketing-and-seo-booster' ) );
				}
			} else {
				die( esc_html__( "Wasn't able to work with Zip Archive", 'marketing-and-seo-booster' ) );
			}

			return true;
		}

		public function remove_zipped_font( $path = null ) {
			if ( wp_doing_ajax() ) {
				//check nonce
				check_ajax_referer( 'sl_nonce', 'nonce' );
			}
			if ( ! $path ) {
				$font = filter_input( INPUT_POST, 'font', FILTER_SANITIZE_STRING );
				if ( ! empty( $font ) ) {
					$path = $this->upload_dir['path'] . '/' . $this->dir_name . '/' . sanitize_key( $font );
				} else {
					if ( wp_doing_ajax() ) {
						wp_die();
					}

					return;
				}
			}
			$this->remove_font_path( $path );
			if ( wp_doing_ajax() ) {
				wp_die();
			}
			return;
		}

		public function remove_font_path( $path ) {
			$files = glob( $path . '/*' );
			foreach ( $files as $file ) {
				is_dir( $file ) ? $this->remove_font_path( $file ) : unlink( $file );
			}
			rmdir( $path );
		}


		public function get_font_name( $file ) {
			global $wp_filesystem;

			$content = $wp_filesystem->get_contents( $file );
			$content = preg_replace( '/\.\.\//', '', $content );
			$content = preg_replace( '/font\//', 'fonts/', $content );
			$wp_filesystem->put_contents( $file, $content, FS_CHMOD_FILE );
			preg_match( '/font-face[^\{]+\{[^f]+font-family.?:([^;]+)/is', $content, $font );
			$low_name = function_exists( 'mb_strtolower' ) ? mb_strtolower( preg_replace( '/\'|\s|\"/',
				'',
				$font[1] ) ) : strtolower( preg_replace( '/\'|\s|\"/', '', $font[1] ) );
			if ( isset ( $font ) && isset ( $font[1] ) ) {
				$font = $low_name;
			} else {
				$font = false;
			}
			$this->font_name = $font;

			return $font;
		}

		//edit the css file for the font
		public function rewrite_css() {
			$style = trailingslashit( $this->upload_dir['path'] ) . $this->dir_name . '/' . $this->font_name . '/style.css';
			$file  = @file_get_contents( $style );
			if ( $file ) {
				$str = str_replace( 'icon-', $this->font_name . '-', $file );
				$str = str_replace( '.icon {',
					'[class^="' . $this->font_name . '-"], [class*=" ' . $this->font_name . '-"] {',
					$str );
				$str = str_replace( 'i {',
					'[class^="' . $this->font_name . '-"], [class*=" ' . $this->font_name . '-"] {',
					$str );
				/* delete comments */
				$str = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str );
				/* replace _ to - for KingComposer*/
				$str = str_replace( '_', '-', $str );
				/* replace capital letters for KingComposer*/
				$str = function_exists( 'mb_strtolower' ) ? mb_strtolower( $str ) : strtolower( $str );
				/* delete tabs, spaces, newlines, etc. */
				//$str = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $str );
				@file_put_contents( $style, $str );
			} else {
				die( esc_html__( 'Unable to write css. Upload icons downloaded only from icomoon',
					'marketing-and-seo-booster' ) );
			}
		}

		public function rename_files() {
			$extensions = array( 'eot', 'svg', 'ttf', 'woff', 'css' );
			$folder     = trailingslashit( $this->upload_dir['path'] . '/' . $this->dir_name . '/' . $this->font_name );
			foreach ( glob( $folder . '*' ) as $file ) {
				$path_files = pathinfo( $file );
				if ( strpos( $path_files['filename'],
						'.dev' ) === false && isset( $path_files['extension'] ) && in_array( $path_files['extension'],
						$extensions ) ) {
					if ( $path_files['filename'] !== $this->font_name ) {
						rename( $file,
							trailingslashit( $path_files['dirname'] ) . $this->font_name . '.' . $path_files['extension'] );
					}
				}
			}
		}

		public function create_folder( &$folder ) {

			$created = wp_mkdir_p( trailingslashit( $folder ) );
			@chmod( $folder, 0777 );

			return $created;

		}

		public function delete_folder( $new_name ) {
			if ( is_dir( $new_name ) ) {
				$objects = scandir( $new_name );
				foreach ( $objects as $object ) {
					if ( $object != "." && $object != ".." ) {
						unlink( $new_name . "/" . $object );
					}
				}
				reset( $objects );
				rmdir( $new_name );
			}
		}

		public function icon_manager_field( $settings, $value ) {

			$out   = '<div class="icon_manager_block">';
			$icons = '';

			$out .= '<input name="' . esc_attr( $settings['param_name'] ) . '" class="icon_manager_input wpb_vc_param_value wpb-textinput ' .
			        esc_attr( $settings['param_name'] ) . ' ' .
			        esc_attr( $settings['type'] ) . '_field" type="hidden" value="' . esc_attr( $value ) . '" />';

			if ( $settings['param_name'] == 'sl_icon' ) {
				if ( count( $this->icon_styles ) > 0 ) {
					$icons = '<div class="icon_manager_display">';
					$out   .= '<select name="font_set" class="icons_set_select">';
					if ( ! $selected ) {
						$out .= '<option value="all" selected="selected">all</option>';
					} else {
						$out .= '<option value="all">all</option>';
					}
					foreach ( $this->icon_styles as $font => $paths ) {
						$font_info = $this->sl_get_font_info( $paths['dir'] );
						if ( $font_info && count( $font_info['icons'] ) > 0 ) {
							$icons .= '<div id="' . $font . '-icons" class="icons_manager_block">';
							$n     = count( $font_info['icons'][0] );
							for ( $i = 0; $i < $n; $i ++ ) {
								$class = $font_info['prefix'] . $font_info['icons'][1][ $i ];
								$icons .= '<span class="icon_manager_container"><i class="' . $class . '" data-unicode="' . $font_info['icons'][2][ $i ] . '"></i></span> ';
								if ( $class == $selected['font'] ) {
									$marked = ' selected';
								} else {
									$marked = '';
								}
							}
							$out   .= '<option value="' . $font . '"' . $marked . '>' . $font . '</option>';
							$icons .= '</div>';
						}
					}
					$out   .= '</select>';
					$icons .= '</div>';
				} else {
					$out .= '<div class="no_icons">' . esc_html__( 'You have loaded any Icon Fonts yet',
							'marketing-and-seo-booster' ) . '</div>';
				}
			}

			$selected_icon = '<span class="icon_manager_container"><i class="' . $value . '"></i></span> ';
			$out           .= '<div class="icon_manager_selected_icon">' . $selected_icon . '</div>';

			$out .= '<div id="search_' . $settings['param_name'] . '" class="icon_manager_search_box"><input type="text" placeholder="icons search"><div class="sl_icons_loader"></div></div>';

			$out .= '</div>';

			$out .= '<div class="icon_manager_display">' . $icons . '</div>';

			return $out;

		}

		public function sl_get_font_info( $css_file ) {
			global $wp_filesystem;

			$css         = $wp_filesystem->get_contents( $css_file );
			$preg_result = array();
			if ( $css ) {
				preg_match( '/\[class.?=.?\"([^\"]+)\"\]/', $css, $prefix );
				if ( $prefix && $prefix[1] ) {
					$prefix = preg_replace( '/\s/', '', $prefix[1] );
					$result = preg_match_all( '/' . $prefix . '([^:]+):before\s?\{[^["\']+["\']([^["\']+)/',
						$css,
						$icons );
					if ( $result && strpos( $icons[1][0], ']' ) ) {
						unset( $icons[0][0] );
						unset( $icons[1][0] );
						unset( $icons[2][0] );
					}
				} else {
					$prefix = 'fa fa-';
					$result = preg_match_all( '/fa-([^:]+):before\s\{[^\"]+\"([^\"]+)/', $css, $icons );
				}
			}
			if ( count( $icons ) > 0 ) {
				return array( 'prefix' => $prefix, 'icons' => $icons );
			} else {
				return false;
			}

		}

		public function icon_fonts_for_king_composer() {
			$icon_folders = scandir( $this->upload_dir['path'] . '/' . $this->dir_name );
			if ( $icon_folders && count( $icon_folders ) > 0 ) {
				foreach ( $icon_folders as $icon_folder ) {
					if ( is_file( $this->upload_dir['path'] . '/' . $this->dir_name . '/' . $icon_folder . '/' . $icon_folder . '.css' ) && $icon_folder != '.' && $icon_folder != '..' && function_exists( 'kc_add_icon' ) ) {
						kc_add_icon( $this->upload_dir['url'] . '/' . $this->dir_name . '/' . $icon_folder . '/' . $icon_folder . '.css' );
					}
				}
			}
		}

		public function localize_script() {
			$params = array(
				'nonce'   => wp_create_nonce( 'sl_nonce' ),
				'loadimg' => get_admin_url() . 'images/loading.gif',
				'title'   => esc_html__( 'Upload Icons Set', 'marketing-and-seo-booster' ),
			);
			wp_localize_script( 'sl_icons_manager_admin', 'sl_icons_manager_params', $params );
		}

		public function upload_default_zip_font( $file ) {
			global $wp_filesystem;
			if ( empty( $wp_filesystem ) ) {
				require_once( ABSPATH . '/wp-admin/includes/file.php' );
				WP_Filesystem();
			}

			$WP_Http = new WP_Http();
			$url     = 'http://secretlab.pw/import/' . $file;
			$headers = $WP_Http->get( $url, array( 'stream' => true, 'timeout' => 25 ) );
			if ( is_wp_error( $headers ) ) {
				error_log( __( 'Default icon font uploading error. Remote server did not respond',
					'marketing-and-seo-booster' ) );

				return false;
			}
			// make sure the fetch was successful
			if ( $headers['response']['code'] == '200' ) {
				$zip = $wp_filesystem->get_contents( $url );
				$wp_filesystem->put_contents( plugin_dir_path( __FILE__ ) . $file, $zip );
			} else {
				error_log(
					sprintf(
						__( 'Remote server returned error response %1$d %2$s',
							'marketing-and-seo-booster' ),
						esc_html( $headers['response']['code'] ),
						get_status_header_desc( $headers['response']['code'] )
					)
				);

				return false;
			}

			return true;
		}

		/**
		 * Add svg MIME type support
		 *
		 * @param $mimes
		 *
		 * @return mixed
		 */
		public function mime_types( $mimes ) {
			$mimes['svg'] = 'image/svg+xml';

			return $mimes;
		}

		public function ajax_get_svg() {

			$svg          = '';
			$attachmentID = isset( $_REQUEST['attachmentID'] ) ? $_REQUEST['attachmentID'] : '';
			if ( $attachmentID ) {
				$svg = $this->get_svg_by_id( $attachmentID );
			}

			echo $svg;
			die();
		}

		public function get_svg_by_id( $id ) {

			$svg_url  = wp_get_attachment_url( $id );
			$svg_file = wp_remote_get( esc_url_raw( $svg_url ) );
			$svg_file = wp_remote_retrieve_body( $svg_file );
			$pos      = strpos( $svg_file, '<svg' );

			return substr( $svg_file, $pos );
		}

	}

	add_action( 'after_setup_theme', array( 'SL_Icon_Manager', 'init' ), 20 );
}