<?php
/**
 * SEO Titles for the Marketing and SEO Booster
 */

class MASB_SEO {

	private $seo_admin;

	private $options;

	private $seo_front;

	private $replacer;

	static $instance = null;

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

		if ( ! wp_installing() && ( function_exists( 'spl_autoload_register' ) && function_exists( 'filter_input' ) ) ) {

			$this->options = new MASB_SEO_Options();

			if ( is_admin() ) {
				$this->seo_admin = new MASB_SEO_Admin( $this->options );
			} else {
				$this->replacer  = new MASB_SEO_Replace_Vars( $this->options );
				$this->seo_front = new MASB_SEO_Front( $this->options, $this->replacer );
			}
		}

	}

	/**
	 * @return MASB_SEO_Admin
	 */
	public function get_seo_admin() {
		return $this->seo_admin;
	}

	public static function standardize_whitespace( $string ) {
		return trim( str_replace( '  ', ' ', str_replace( array( "\t", "\n", "\r", "\f" ), ' ', $string ) ) );
	}

	public static function strip_shortcode( $text ) {
		return preg_replace( '`\[[^\]]+\]`s', '', strip_shortcodes( $text ) );
	}

	public static function clear_cache() {
		if ( function_exists( 'w3tc_pgcache_flush' ) ) {
			w3tc_pgcache_flush();
		} elseif ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
		}
	}

	public static function flush_w3tc_cache() {
		if ( defined( 'W3TC_DIR' ) && function_exists( 'w3tc_objectcache_flush' ) ) {
			w3tc_objectcache_flush();
		}
	}

}

class MASB_SEO_Admin {

	private $options;

	private $taxonomy;

	/**
	 * MASB_SEO constructor.
	 */
	public function __construct( $options ) {
		$this->options  = $options;
		$this->taxonomy = new MASB_SEO_Taxonomy( $this->options );
		add_action( 'admin_menu', array( $this, 'submenu' ), 30 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );
		add_action( 'show_user_profile', array( $this, 'user_profile' ) );
		add_action( 'edit_user_profile', array( $this, 'user_profile' ) );
		add_action( 'personal_options_update', array( $this, 'process_user_option_update' ) );
		add_action( 'edit_user_profile_update', array( $this, 'process_user_option_update' ) );
	}


	public function submenu() {
		add_submenu_page( 'marketing-options',
			esc_html__( 'SEO Titles', 'marketing-and-seo-booster' ),
			esc_html__( 'SEO Titles', 'marketing-and-seo-booster' ),
			'manage_options',
			'masb-seo-titles',
			array( $this, 'options_page' ) );

		add_action( 'admin_init', array( $this, 'seo_settings' ) );
	}

	/**
	 * @return MASB_SEO_Taxonomy
	 */
	public function getTaxonomy() {
		return $this->taxonomy;
	}

	public function options_page() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$post_types      = get_post_types( array( 'public' => true ), 'objects' );
		$taxonomies      = get_taxonomies( array( 'public' => true ), 'objects' );
		$options         = get_option( $this->options->get_titles()->get_option_name() );
		$defaults        = $this->options->get_titles()->get_defaults();
		$masb_seo_titles = wp_parse_args( $options, $defaults );
		$sep_options     = $this->options->get_titles()->get_separator_options(); // Fixed PHP5.4 using

		wp_enqueue_style( 'atiframe-welcome',
			esc_url( get_template_directory_uri() ) . '/inc/welcome.css',
			array(),
			'3.03' );
		wp_localize_script( 'atiframebuilder_mainjs',
			'abc_split',
			array(
				'nonce' => wp_create_nonce( 'masb_seo_nonce' ),
			)
		);

		ob_start();
		echo '<div class="wrap">
        <h1>', get_admin_page_title(), '</h1>
        <form action="options.php" method="POST">';
		settings_fields( 'masb_seot_secr' );
		echo '<div id="masb-seot-settings" class="admin-sl-box_container slmenu-tabs" style="display: block;">
        <ul>
            <li class="active" data-page="0">', esc_html__( 'Separator', 'marketing-and-seo-booster' ), '</li>
            <li data-page="1">', esc_html__( 'Homepage', 'marketing-and-seo-booster' ), '</li>
            <li data-page="2">', esc_html__( 'Titles', 'marketing-and-seo-booster' ), '</li>
            <li data-page="3">', esc_html__( 'Taxonomies', 'marketing-and-seo-booster' ), '</li>
            <li data-page="4">', esc_html__( 'Archives', 'marketing-and-seo-booster' ), '</li>
        </ul>
        <div class="slmenu-tab-box">
            <div class="slmenu-item-desc" style="display: block;">
                <h2 class="masb-seot-block-title">', esc_html__( 'Title Separator', 'marketing-and-seo-booster' ), '</h2>
                <div id="masb-seot-separator" class="" >';
		if ( is_array( $sep_options ) && ! empty( $sep_options ) ) {
			foreach ( $sep_options as $key => $sep ) {
				echo '<input type="radio" class="radio" id="masb-seot-title-', esc_attr( $key ), '" name="' . $this->options->get_titles()->get_option_name() . '[separator]" value="', esc_attr( $key ), '" ', checked( $masb_seo_titles['separator'],
					esc_attr( $key ),
					false ), '>
                            <label class="radio" for="masb-seot-title-', esc_attr( $key ), '">', esc_html( $sep ), '</label>';
			}
		}
		echo '</div>
            </div>
            <div class="slmenu-item-desc" style="display: none;">';
		if ( 'posts' == get_option( 'show_on_front' ) ) {
			echo '<h2 class="masb-seot-block-title">', esc_html__( 'Title Separator', 'marketing-and-seo-booster' ), '</h2>
                    <div id="masb-seot-homepage" class="" >';
			echo '<label class="textinput" for="masb-seot-title-homepage">', esc_html__( 'Title template:',
				'marketing-and-seo-booster' ), '</label>
                            <input class="textinput" placeholder="" type="text" id="masb-seot-title-homepage" name="' . $this->options->get_titles()->get_option_name() . '[title-homepage]" value="' . $masb_seo_titles['title-homepage'] . '">
                            <br class="clear">
                            <label class="textinput" for="masb-seot-metadesc-homepage">', esc_html__( 'Meta description template:',
				'marketing-and-seo-booster' ), '</label>
                            <textarea cols="" rows="4" class="textinput" id="masb-seot-metadesc-homepage" name="' . $this->options->get_titles()->get_option_name() . '[metadesc-homepage]">' . esc_textarea( $masb_seo_titles['metadesc-homepage'] ) . '</textarea>
                            <br class="clear">';
			echo '<label class="textinput" for="masb-seot-metakey-homepage">', esc_html__( 'Meta keywords template:',
				'marketing-and-seo-booster' ), '</label>
                            <input class="textinput" placeholder="" type="text" id="masb-seot-metakey-homepage" name="' . $this->options->get_titles()->get_option_name() . '[metakey-homepage]" value="' . $masb_seo_titles['metakey-homepage'] . '">
                            <br class="clear">';
			echo '</div>';
		} else {
			echo '<h2>', esc_html__( 'Homepage &amp; Front page', 'marketing-and-seo-booster' ), '</h2>';
			echo '<p>';
			printf( esc_html__( 'You can determine the title and description for the front page by %1$sediting the front page itself &raquo;%2$s',
				'marketing-and-seo-booster' ),
				'<a href="' . esc_url( get_edit_post_link( get_option( 'page_on_front' ) ) ) . '">',
				'</a>' );
			echo '</p>';
			if ( get_option( 'page_for_posts' ) > 0 ) {
				echo '<p>', sprintf( esc_html__( 'You can determine the title and description for the blog page by %1$sediting the blog page itself &raquo;%2$s',
					'marketing-and-seo-booster' ),
					'<a href="' . esc_url( get_edit_post_link( get_option( 'page_for_posts' ) ) ) . '">',
					'</a>' ), '</p>';
			}
		}
		echo '</div>
            <div class="slmenu-item-desc" style="display: none;">';
		if ( is_array( $post_types ) && ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				$name = $post_type->name;
				echo '<div id="masb-seot-', esc_attr( $name ), '-metas">
                            <h2 id="masb-seot-', esc_attr( $name ), '" class="masb-seot-block-title">', esc_html( ucfirst( $post_type->labels->name ) ), '</h2>
                            <label class="textinput" for="masb-seot-title-', esc_attr( $name ), '">', esc_html__( 'Title template:',
					'marketing-and-seo-booster' ), '</label>
                            <input class="textinput" placeholder="" type="text" id="masb-seot-title-' . esc_attr( $name ) . '" name="' . $this->options->get_titles()->get_option_name() . '[title-' . esc_attr( $name ) . ']" value="' . $masb_seo_titles[ 'title-' . esc_attr( $name ) ] . '">
                            <br class="clear">
                            <label class="textinput" for="masb-seot-metadesc-' . esc_attr( $name ) . '">', esc_html__( 'Meta description template:',
					'marketing-and-seo-booster' ), '</label>
                            <textarea cols="" rows="4" class="textinput" id="masb-seot-metadesc-' . esc_attr( $name ) . '" name="' . $this->options->get_titles()->get_option_name() . '[metadesc-' . esc_attr( $name ) . ']">' . esc_textarea( $masb_seo_titles[ 'metadesc-' . esc_attr( $name ) ] ) . '</textarea>
                            <br class="clear">
                        </div>';
			}
			unset( $post_types );

		}
		$post_types = get_post_types( array( '_builtin' => false, 'has_archive' => true ), 'objects' );
		if ( is_array( $post_types ) && ! empty( $post_types ) ) {
			echo '<h3>' . esc_html__( 'Custom Post Type Archives', 'marketing-and-seo-booster' ) . '</h3>';
			echo '<p>' . esc_html__( 'Note: instead of templates these are the actual titles and meta descriptions for these custom post type archive pages.',
					'marketing-and-seo-booster' ) . '</p>';
			foreach ( $post_types as $post_type ) {
				$name = $post_type->name;
				echo '<div id="masb-seot-', esc_attr( $name ), '-metas">
                            <h2 id="masb-seot-', esc_attr( $name ), '" class="masb-seot-block-title">', esc_html( ucfirst( $post_type->labels->name ) ), '</h2>
                            <label class="textinput" for="masb-seot-title-ptarchive-', esc_attr( $name ), '">', esc_html__( 'Title template:',
					'marketing-and-seo-booster' ), '</label>
                            <input class="textinput" placeholder="" type="text" id="masb-seot-title-ptarchive-' . esc_attr( $name ) . '" name="' . $this->options->get_titles()->get_option_name() . '[title-ptarchive-' . esc_attr( $name ) . ']" value="' . $masb_seo_titles[ 'title-ptarchive-' . esc_attr( $name ) ] . '">
                            <br class="clear">
                            <label class="textinput" for="masb-seot-metadesc-ptarchive-' . esc_attr( $name ) . '">', esc_html__( 'Meta description template:',
					'marketing-and-seo-booster' ), '</label>
                            <textarea cols="" rows="4" class="textinput" id="masb-seot-metadesc-ptarchive-' . esc_attr( $name ) . '" name="' . $this->options->get_titles()->get_option_name() . '[metadesc-ptarchive-' . esc_attr( $name ) . ']">' . esc_textarea( $masb_seo_titles[ 'metadesc-ptarchive-' . esc_attr( $name ) ] ) . '</textarea>
                            <br class="clear">
                        </div>';
			}
			unset( $post_type );
		}
		echo '</div>
            <div class="slmenu-item-desc" style="display: none;">';
		if ( is_array( $taxonomies ) && ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				if ( in_array( $taxonomy->name, array( 'link_category', 'nav_menu' ) ) ) {
					continue;
				}
				echo '<div id="masb-seot-', esc_attr( $taxonomy->name ), '-metas">';
				echo '<h2 class="masb-seot-block-title">' . esc_html( ucfirst( $taxonomy->labels->name ) ) . '</h2>';
				echo '<label class="textinput" for="masb-seot-title-', esc_attr( $taxonomy->name ), '">', esc_html__( 'Title template:',
					'marketing-and-seo-booster' ), '</label>
                            <input class="textinput" placeholder="" type="text" id="masb-seot-title-' . esc_attr( $taxonomy->name ) . '" name="' . $this->options->get_titles()->get_option_name() . '[title-tax-' . esc_attr( $taxonomy->name ) . ']" value="' . $masb_seo_titles[ 'title-tax-' . esc_attr( $taxonomy->name ) ] . '">
                            <br class="clear">
                            <label class="textinput" for="masb-seot-metadesc-' . esc_attr( $taxonomy->name ) . '">', esc_html__( 'Meta description template:',
					'marketing-and-seo-booster' ), '</label>
                            <textarea cols="" rows="4" class="textinput" id="masb-seot-metadesc-' . esc_attr( $taxonomy->name ) . '" name="' . $this->options->get_titles()->get_option_name() . '[metadesc-tax-' . esc_attr( $taxonomy->name ) . ']">' . esc_textarea( $masb_seo_titles[ 'metadesc-tax-' . esc_attr( $taxonomy->name ) ] ) . '</textarea>
                            <br class="clear">
                        </div>';
			}
		}
		echo '</div>
            <div class="slmenu-item-desc" style="display: none;">';
		echo '<div id="masb-seot-author-archives-title-metas">';
		echo '<h2 class="masb-seot-block-title">' . esc_html__( 'Author archives settings',
				'marketing-and-seo-booster' ) . '</h2>';
		echo '<label class="textinput" for="masb-seot-title-author-archives">', esc_html__( 'Title template:',
			'marketing-and-seo-booster' ), '</label>
                    <input class="textinput" placeholder="" type="text" id="masb-seot-title-author-archives" name="' . $this->options->get_titles()->get_option_name() . '[title-author-archives]" value="' . $masb_seo_titles['title-author-archives'] . '">
                    <br class="clear">
                    <label class="textinput" for="masb-seot-metadesc-author-archives">', esc_html__( 'Meta description template:',
			'marketing-and-seo-booster' ), '</label>
                    <textarea cols="" rows="4" class="textinput" id="masb-seot-metadesc-author-archives" name="' . $this->options->get_titles()->get_option_name() . '[metadesc-author-archives]">' . esc_textarea( $masb_seo_titles['metadesc-author-archives'] ) . '</textarea>
                    <br class="clear">
                    <label class="textinput" for="masb-seot-metakey-author">', esc_html__( 'Meta keywords template:',
			'marketing-and-seo-booster' ), '</label>
                    <input class="textinput" placeholder="" type="text" id="masb-seot-metakey-author" name="' . $this->options->get_titles()->get_option_name() . '[metakey-author]" value="' . $masb_seo_titles['metakey-author'] . '">
                    <br class="clear">
                </div>';
		echo '<div id="masb-seot-date-archives-title-metas">';
		echo '<h2 class="masb-seot-block-title">' . esc_html__( 'Date archives settings',
				'marketing-and-seo-booster' ) . '</h2>';
		echo '<label class="textinput" for="masb-seot-title-date-archives">', esc_html__( 'Title template:',
			'marketing-and-seo-booster' ), '</label>
                    <input class="textinput" placeholder="" type="text" id="masb-seot-title-date-archives" name="' . $this->options->get_titles()->get_option_name() . '[title-date-archives]" value="' . $masb_seo_titles['title-date-archives'] . '">
                    <br class="clear">
                    <label class="textinput" for="masb-seot-date-metadesc-date-archives">', esc_html__( 'Meta description template:',
			'marketing-and-seo-booster' ), '</label>
                    <textarea cols="" rows="4" class="textinput" id="masb-seot-metadesc-date-archives" name="' . $this->options->get_titles()->get_option_name() . '[metadesc-date-archives]">' . esc_textarea( $masb_seo_titles['metadesc-date-archives'] ) . '</textarea>
                    <br class="clear">
                </div>';
		echo '<div id="masb-seot-special-pages-titles-metas">';
		echo '<h2>' . esc_html__( 'Special Pages', 'marketing-and-seo-booster' ) . '</h2>';
		echo '<p>' . sprintf( esc_html__( 'These pages will be %s by default, so they will never show up in search results.',
				'marketing-and-seo-booster' ),
				'<code>noindex, follow</code>' ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Search pages', 'marketing-and-seo-booster' ) . '</strong><br/>';
		echo '<label class="textinput" for="masb-seot-title-search">', esc_html__( 'Title template:',
			'marketing-and-seo-booster' ), '</label>
                    <input class="textinput" placeholder="" type="text" id="masb-seot-title-search" name="' . $this->options->get_titles()->get_option_name() . '[title-search]" value="' . $masb_seo_titles['title-search'] . '">';
		echo '</p>';
		echo '<p><strong>' . esc_html__( '404 pages', 'marketing-and-seo-booster' ) . '</strong><br/>';
		echo '<label class="textinput" for="masb-seot-title-404">', esc_html__( 'Title template:',
			'marketing-and-seo-booster' ), '</label>
                    <input class="textinput" placeholder="" type="text" id="masb-seot-title-404" name="' . $this->options->get_titles()->get_option_name() . '[title-404]" value="' . $masb_seo_titles['title-404'] . '">';
		echo '</p>';
		echo '</div>';
		echo '</div>
        </div>
    </div>';
		submit_button();
		echo '</form>
    </div>';
		ob_end_flush();

	}


	public function seo_settings() {

		register_setting( 'masb_seot_secr',
			$this->options->get_titles()->get_option_name(),
			array( $this, 'masb_seo_opt_saving' ) );

	}

	public function masb_seot_opt_saving() {

	}

	public function add_meta_box() {
		$post_types = get_post_types( array( 'public' => true ) );

		if ( is_array( $post_types ) && $post_types !== array() ) {
			foreach ( $post_types as $post_type ) {
				$prod_title = esc_html__( 'MASB SEO', 'marketing-and-seo-booster' );

				add_meta_box(
					'masb_seo_meta',
					$prod_title,
					array( $this, 'meta_box', ),
					$post_type,
					'normal',
					'high'
				);
			}
		}
	}

	public function meta_box( $post ) {

		$content_sections = $this->options->get_meta()->meta_fields;

		ob_start();
		echo '<div id="masb-seot-settings" class="masb-seo-metabox-sidebar">';
		wp_nonce_field( plugin_basename( __FILE__ ), 'masb_seo_nonce' );
		if ( ! empty( $content_sections ) && is_array( $content_sections ) ) {
			foreach ( $content_sections as $type => $content_section ) {
				switch ( $type ) {
					case 'title' :
						$current = get_post_meta( $post->ID,
							$this->options->get_meta()->meta_prefix . esc_attr( $type ),
							true );
						if ( ! $current ) {
							$current = '';
						}
						echo '<div id="masb-seot-', esc_attr( $type ), '-metas">
                            <label class="textinput ssc_label" for="masb-seot-', esc_attr( $type ), '"><span>', esc_html__( 'SEO Title',
							'marketing-and-seo-booster' ), '</span></label>
                            <input class="textinput" placeholder="" type="text" id="masb-seot-' . esc_attr( $type ) . '" name="' . $this->options->get_meta()->meta_prefix . esc_attr( $type ) . '" value="' . esc_attr( $current ) . '">
                            <div class="masb-seo-title-help">' . esc_html__( 'You can use here:',
								'marketing-and-seo-booster' ) . ' <strong>%%sep%% %%sitename%%</strong></div>
                        </div>';
						unset( $current );
						break;
					case 'metadesc' :
						$current = get_post_meta( $post->ID,
							$this->options->get_meta()->meta_prefix . esc_attr( $type ),
							true );
						if ( ! $current ) {
							$current = '';
						}
						echo '<div id="masb-seot-', esc_attr( $type ), '-metas">
                            <label class="textinput ssc_label" for="masb-seot-metadesc-' . esc_attr( $type ) . '"><span>', esc_html__( 'Meta description:',
							'marketing-and-seo-booster' ), '</span></label>
                            <textarea cols="" rows="4" class="1 textinput" id="masb-seot-metadesc-' . esc_attr( $type ) . '" name="' . $this->options->get_meta()->meta_prefix . esc_attr( $type ) . '">' . esc_textarea( $current ) . '</textarea>
                        </div>';
						unset( $current );
						break;
					case 'metakeywords' :
						$current = get_post_meta( $post->ID,
							$this->options->get_meta()->meta_prefix . esc_attr( $type ),
							true );
						if ( ! $current ) {
							$current = '';
						}
						echo '<div id="masb-seot-', esc_attr( $type ), '-metas">
                            <label class="textinput ssc_label" for="masb-seot-', esc_attr( $type ), '"><span>', esc_html__( 'Keywords',
							'marketing-and-seo-booster' ), '</span></label>
                            <input class="textinput" placeholder="" type="text" id="masb-seot-' . esc_attr( $type ) . '" name="' . $this->options->get_meta()->meta_prefix . esc_attr( $type ) . '" value="' . esc_attr( $current ) . '">
                        </div>';
						unset( $current );
						break;
				}
			}
			unset( $type, $content_section );
		}
		echo '</div>';
		ob_end_flush();
	}

	public function save_meta( $post_id ) {

		if ( ! isset( $_POST [ $this->options->get_meta()->meta_prefix . 'metadesc' ] ) && ! isset( $_POST [ $this->options->get_meta()->meta_prefix . 'metakeywords' ] ) && ! isset( $_POST [ $this->options->get_meta()->meta_prefix . 'title' ] ) ) {
			return;
		}

		$nonce_value = $this->filter_input_post( 'masb_seo_nonce' );

		if ( empty( $nonce_value ) ) { // Submit from alternate forms.
			return;
		}

		check_admin_referer( plugin_basename( __FILE__ ), 'masb_seo_nonce' );

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		update_post_meta( $post_id,
			$this->options->get_meta()->meta_prefix . 'title',
			$this->filter_input_post( $this->options->get_meta()->meta_prefix . 'title' ) );
		update_post_meta( $post_id,
			$this->options->get_meta()->meta_prefix . 'metadesc',
			$this->filter_input_post( $this->options->get_meta()->meta_prefix . 'metadesc' ) );
		update_post_meta( $post_id,
			$this->options->get_meta()->meta_prefix . 'metakeywords',
			$this->filter_input_post( $this->options->get_meta()->meta_prefix . 'metakeywords' ) );


	}

	public function user_profile( $user ) {

		wp_nonce_field( 'masb_seo_nonce', 'masb_seo_nonce' );

		echo
			'<div id="masb-seot-settings">
                <h2>' . esc_html__( 'MASB SEO', 'marketing-and-seo-booster' ) . '</h2>
                <label class="textinput" for="masb-seo-author-title">' . esc_html__( 'Title for Author page',
				'marketing-and-seo-booster' ) . '</label>
                <input class="textinput" type="text" id="masb-seo-author-title" name="' . $this->options->get_meta()->meta_prefix . 'author_title" value="' . esc_attr( get_the_author_meta( $this->options->get_meta()->meta_prefix . 'author_title',
				$user->ID ) ) . '"/><br>
                <label class="textinput" for="masb-seo-author-metadesc">' . esc_html__( 'Meta description for Author page',
				'marketing-and-seo-booster' ) . '</label>
                <textarea rows="5" cols="30" id="masb-seo-author-metadesc" class="textinput" name="' . $this->options->get_meta()->meta_prefix . 'author_metadesc">' . esc_textarea( get_the_author_meta( $this->options->get_meta()->meta_prefix . 'author_metadesc',
				$user->ID ) ) . '</textarea><br>
                <label class="textinput" for="masb-seo-author-metakey">' . esc_html__( 'Meta keywords for Author page',
				'marketing-and-seo-booster' ) . '</label>
                <input class="textinput" type="text" id="masb-seo-author-metakey" name="' . $this->options->get_meta()->meta_prefix . 'author_metakey" value="' . esc_attr( get_the_author_meta( $this->options->get_meta()->meta_prefix . 'author_metakey',
				$user->ID ) ) . '"/><br>
            </div>';
	}

	public function process_user_option_update( $user_id ) {

		$nonce_value = $this->filter_input_post( 'masb_seo_nonce' );

		if ( empty( $nonce_value ) ) { // Submit from alternate forms.
			return;
		}

		check_admin_referer( 'masb_seo_nonce', 'masb_seo_nonce' );

		/**
		 * Changed old meta key
		 * @todo Delete checking after several months from 07.2019
		 */
		$old_author_title    = get_user_meta( $user_id, 'sst_seo_author_title', true );
		$old_author_metadesc = get_user_meta( $user_id, 'sst_seo_author_metadesc', true );
		$old_author_metakey  = get_user_meta( $user_id, 'sst_seo_author_metakey', true );
		if ( ! empty( $old_author_title ) ) {
			delete_user_meta( $user_id, 'sst_seo_author_title', $old_author_title );
		}
		if ( ! empty( $old_author_metadesc ) ) {
			delete_user_meta( $user_id, 'sst_seo_author_metadesc', $old_author_metadesc );
		}
		if ( ! empty( $old_author_metakey ) ) {
			delete_user_meta( $user_id, 'sst_seo_author_metakey', $old_author_metakey );
		}

		update_user_meta( $user_id,
			'masb_seo_author_title',
			$this->filter_input_post( $this->options->get_meta()->meta_prefix . 'author_title' ) );
		update_user_meta( $user_id,
			'masb_seo_author_metadesc',
			$this->filter_input_post( $this->options->get_meta()->meta_prefix . 'author_metadesc' ) );
		update_user_meta( $user_id,
			'masb_seo_author_metakey',
			$this->filter_input_post( $this->options->get_meta()->meta_prefix . 'author_metakey' ) );
	}

	private function filter_input_post( $var_name ) {
		$val = filter_input( INPUT_POST, $var_name );
		if ( $val ) {
			return $this->sanitize_text_field( $val );
		}

		return '';
	}

	private function sanitize_text_field( $value ) {
		$filtered = wp_check_invalid_utf8( $value );

		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );
			// This will strip extra whitespace for us.
			$filtered = wp_strip_all_tags( $filtered, true );
		} else {
			$filtered = trim( preg_replace( '`[\r\n\t ]+`', ' ', $filtered ) );
		}

		$found = false;
		while ( preg_match( '`[^%](%[a-f0-9]{2})`i', $filtered, $match ) ) {
			$filtered = str_replace( $match[1], '', $filtered );
			$found    = true;
		}
		unset( $match );

		if ( $found ) {
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace( '` +`', ' ', $filtered ) );
		}

		/**
		 * Filter a sanitized text field string.
		 *
		 * @since WP 2.9.0
		 *
		 * @param string $filtered The sanitized string.
		 * @param string $str The string prior to being sanitized.
		 */

		return apply_filters( 'masb_admin_sanitize_text_field', $filtered, $value );
	}
}

class MASB_SEO_Front {

	private $replacer;

	private $title = null;

	private $metadesc = null;

	public $options;

	private $settings;

	/**
	 * MASB_SEO_Front constructor.
	 */
	public function __construct( $options, $replacer ) {

		$this->replacer = $replacer;

		$this->options = $options;

		$this->settings = wp_parse_args( $this->options, $this->options->get_titles()->get_defaults() );

		add_action( 'wp_head', array( $this, 'head' ), 1 );

		add_action( 'masb_seo_head', array( $this, 'metadesc' ), 6 );

		add_action( 'masb_seo_head', array( $this, 'metakeywords' ), 11 );

		add_filter( 'pre_get_document_title', array( $this, 'title' ), 1 );

		add_filter( 'wp_title', array( $this, 'title' ), 1, 3 );

		add_filter( 'thematic_doctitle', array( $this, 'title' ), 1 );

		add_action( 'admin_bar_menu', array( $this, 'add_toolbar_items' ), 100 );

	}

	public function title( $title, $separator = '', $separator_location = '' ) {

		if ( is_null( $this->title ) ) {
			$this->title = $this->generate_title( $title, $separator_location );
		}

		return $this->title;
	}

	private function generate_title( $title, $separator_location ) {

		if ( is_feed() ) {
			return $title;
		}

		$separator = $this->replacer->replace( '%%sep%%', array() );
		$separator = ' ' . trim( $separator ) . ' ';

		if ( '' === trim( $separator_location ) ) {
			$separator_location = ( is_rtl() ) ? 'left' : 'right';
		}

		// This needs to be kept track of in order to generate
		// default titles for singular pages.
		$original_title = $title;

		// This flag is used to determine if any additional
		// processing should be done to the title after the
		// main section of title generation completes.
		$modified_title = true;

		// This variable holds the page-specific title part
		// that is used to generate default titles.
		$title_part = '';

		if ( $this->is_home_static_page() ) {
			$title = $this->get_content_title();
		} elseif ( $this->is_home_posts_page() ) {
			$title = $this->get_title_from_options( 'title-home-masb-seo' );
		} elseif ( $this->is_posts_page() ) {
			$title = $this->get_content_title( get_post( get_option( 'page_for_posts' ) ) );
		} elseif ( is_singular() ) {
			$title = $this->get_content_title();

			if ( ! is_string( $title ) || '' === $title ) {
				$title_part = $original_title;
			}
		} elseif ( is_search() ) {
			$title = $this->get_title_from_options( 'title-search' );

			if ( ! is_string( $title ) || '' === $title ) {
				$title_part = sprintf( esc_html__( 'Search for "%s"', 'marketing-and-seo-booster' ),
					esc_html( get_search_query() ) );
			}
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$title = $this->get_taxonomy_title();

			if ( ! is_string( $title ) || '' === $title ) {
				if ( is_category() ) {
					$title_part = single_cat_title( '', false );
				} elseif ( is_tag() ) {
					$title_part = single_tag_title( '', false );
				} else {
					$title_part = single_term_title( '', false );
					if ( $title_part === '' ) {
						$term       = $GLOBALS['wp_query']->get_queried_object();
						$title_part = $term->name;
					}
				}
			}
		} elseif ( is_author() ) {
			$title = $this->get_author_title();

			if ( ! is_string( $title ) || '' === $title ) {
				$title_part = get_the_author_meta( 'display_name', get_query_var( 'author' ) );
			}
		} elseif ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );

			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}

			$title = $this->get_title_from_options( 'title-ptarchive-' . $post_type );

			if ( ! is_string( $title ) || '' === $title ) {
				$post_type_obj = get_post_type_object( $post_type );
				if ( isset( $post_type_obj->labels->menu_name ) ) {
					$title_part = $post_type_obj->labels->menu_name;
				} elseif ( isset( $post_type_obj->name ) ) {
					$title_part = $post_type_obj->name;
				} else {
					$title_part = ''; // To be determined what this should be.
				}
			}
		} elseif ( is_archive() ) {
			$title = $this->get_title_from_options( 'title-date-archives' );
			// Replacement would be needed!
			if ( empty( $title ) ) {
				if ( is_month() ) {
					$title_part = sprintf( esc_html__( '%s Archives', 'marketing-and-seo-booster' ),
						single_month_title( ' ', false ) );
				} elseif ( is_year() ) {
					$title_part = sprintf( esc_html__( '%s Archives', 'marketing-and-seo-booster' ), get_query_var( 'year' ) );
				} elseif ( is_day() ) {
					$title_part = sprintf( esc_html__( '%s Archives', 'marketing-and-seo-booster' ), get_the_date() );
				} else {
					$title_part = esc_html__( 'Archives', 'marketing-and-seo-booster' );
				}
			}
		} elseif ( is_404() ) {

			$title = $this->get_title_from_options( 'title-404' );
			// Replacement would be needed!
			if ( empty( $title ) ) {
				$title_part = esc_html__( 'Page not found', 'marketing-and-seo-booster' );
			}
		} else {
			// In case the page type is unknown, leave the title alone.
			$modified_title = false;

			// If you would like to generate a default title instead,
			// the following code could be used
			// $title_part = $title;
			// instead of the line above.
		}

		if ( ( $modified_title && empty( $title ) ) || ! empty( $title_part ) ) {
			$title = $this->get_default_title( $separator, $separator_location, $title_part );
		}

		if ( defined( 'ICL_LANGUAGE_CODE' ) && false !== strpos( $title, ICL_LANGUAGE_CODE ) ) {
			$title = str_replace( ' @' . ICL_LANGUAGE_CODE, '', $title );
		}

		return esc_html( strip_tags( stripslashes( apply_filters( 'masb_seo_title', $title ) ) ) );
	}

	public function get_default_title( $sep, $seplocation, $title = '' ) {
		if ( 'right' == $seplocation ) {
			$regex = '`\s*' . preg_quote( trim( $sep ), '`' ) . '\s*`u';
		} else {
			$regex = '`^\s*' . preg_quote( trim( $sep ), '`' ) . '\s*`u';
		}
		$title = preg_replace( $regex, '', $title );

		if ( ! is_string( $title ) || ( is_string( $title ) && $title === '' ) ) {
			$title = get_bloginfo( 'name' );
			$title = $this->add_paging_to_title( $sep, $seplocation, $title );
			$title = $this->add_to_title( $sep, $seplocation, $title, strip_tags( get_bloginfo( 'description' ) ) );

			return $title;
		}

		$title = $this->add_paging_to_title( $sep, $seplocation, $title );
		$title = $this->add_to_title( $sep, $seplocation, $title, strip_tags( get_bloginfo( 'name' ) ) );

		return $title;
	}

	public function add_paging_to_title( $sep, $seplocation, $title ) {
		global $wp_query;

		if ( ! empty( $wp_query->query_vars['paged'] ) && $wp_query->query_vars['paged'] > 1 ) {
			return $this->add_to_title( $sep,
				$seplocation,
				$title,
				$wp_query->query_vars['paged'] . '/' . $wp_query->max_num_pages );
		}

		return $title;
	}

	public function add_to_title( $sep, $seplocation, $title, $title_part ) {
		if ( 'right' === $seplocation ) {
			return $title . $sep . $title_part;
		}

		return $title_part . $sep . $title;
	}

	public function is_home_static_page() {
		return ( is_front_page() && 'page' == get_option( 'show_on_front' ) && is_page( get_option( 'page_on_front' ) ) );
	}

	public function is_home_posts_page() {
		return ( is_home() && 'posts' == get_option( 'show_on_front' ) );
	}

	public function is_posts_page() {
		return ( is_home() && 'page' == get_option( 'show_on_front' ) );
	}

	public function get_content_title( $object = null ) {
		if ( is_null( $object ) ) {
			$object = $GLOBALS['wp_query']->get_queried_object();
		}

		if ( is_object( $object ) ) {
			$title = $this->options->get_meta()->get_value( 'title', $object->ID );

			if ( $title !== '' ) {
				return $this->replacer->replace( $title, $object );
			}

			$post_type = ( isset( $object->post_type ) ? $object->post_type : $object->query_var );

			return $this->get_title_from_options( 'title-' . $post_type, $object );
		}

		return $this->get_title_from_options( 'title-404' );
	}

	public function get_taxonomy_title() {
		$object = $GLOBALS['wp_query']->get_queried_object();

		$title = $this->options->get_taxonomy_meta()->get_term_meta( $object, $object->taxonomy, 'title' );

		if ( is_string( $title ) && $title !== '' ) {
			return $this->replacer->replace( $title, $object );
		} else {
			return $this->get_title_from_options( 'title-tax-' . $object->taxonomy, $object );
		}
	}

	public function get_author_title() {
		$author_id = get_query_var( 'author' );
		$title     = trim( get_the_author_meta( $this->options->get_meta()->meta_prefix . 'author_title',
			$author_id ) );

		if ( $title !== '' ) {
			return $this->replacer->replace( $title, array() );
		}

		return $this->get_title_from_options( 'title-author-archives' );
	}

	public function get_title_from_options( $index, $var_source = array() ) {
		if ( ! isset( $this->settings[ $index ] ) || $this->settings[ $index ] === '' ) {
			if ( is_singular() ) {
				return $this->replacer->replace( '%%title%% %%sep%% %%sitename%%', $var_source );
			} else {
				return '';
			}
		} else {
			return $this->replacer->replace( $this->settings[ $index ], $var_source );
		}
	}

	public function head() {
		global $wp_query;

		$old_wp_query = null;

		if ( ! $wp_query->is_main_query() ) {
			$old_wp_query = $wp_query;
			wp_reset_query();
		}

		do_action( 'masb_seo_head' );

		if ( ! empty( $old_wp_query ) ) {
			$GLOBALS['wp_query'] = $old_wp_query;
			unset( $old_wp_query );
		}

		return;
	}

	public function metakeywords() {
		global $wp_query, $post;

		$keywords = '';

		if ( is_singular() ) {
			$keywords = $this->options->get_meta()->get_value( 'metakeywords' );
			if ( $keywords === '' && ( is_object( $post ) && ( ( isset( $this->settings[ 'metakey-' . $post->post_type ] ) && $this->settings[ 'metakey-' . $post->post_type ] !== '' ) ) ) ) {
				$keywords = $this->replacer->replace( $this->settings[ 'metakey-' . $post->post_type ], $post );
			}
		} else {
			if ( $this->is_home_posts_page() && $this->settings['metakey-homepage'] !== '' ) {
				$keywords = $this->replacer->replace( $this->settings['metakey-homepage'], array() );
			} elseif ( $this->is_home_static_page() ) {
				$keywords = $this->options->get_meta()->get_value( 'metakeywords' );
				if ( $keywords === '' && ( is_object( $post ) && ( isset( $this->settings[ 'metakey-' . $post->post_type ] ) && $this->settings[ 'metakey-' . $post->post_type ] !== '' ) ) ) {
					$keywords = $this->replacer->replace( $this->settings[ 'metakey-' . $post->post_type ], $post );
				}
			} elseif ( $this->is_posts_page() ) {
				$keywords = $this->get_keywords( get_post( get_option( 'page_for_posts' ) ) );
			} elseif ( is_category() || is_tag() || is_tax() ) {
				$term = $wp_query->get_queried_object();

				if ( is_object( $term ) ) {
					$keywords = get_term_meta( $term->term_id,
						$this->options->get_meta()->meta_prefix . 'metakeywords',
						true );
					if ( empty( $keywords ) ) {
						$keywords = $this->options->get_taxonomy_meta()->get_term_meta( $term,
							$term->taxonomy,
							'metakey' );
					}
					if ( ( ! is_string( $keywords ) || $keywords === '' ) && ( isset( $this->settings[ 'metakey-tax-' . $term->taxonomy ] ) && $this->settings[ 'metakey-tax-' . $term->taxonomy ] !== '' ) ) {
						$keywords = $this->replacer->replace( $this->settings[ 'metakey-tax-' . $term->taxonomy ],
							$term );
					}
				}
			} elseif ( is_author() ) {
				$author_id = get_query_var( 'author' );
				$keywords  = get_the_author_meta( $this->options->get_meta()->meta_prefix . 'author_metakey',
					$author_id );
				if ( ! $keywords && $this->settings['metakey-author'] !== '' ) {
					$keywords = $this->replacer->replace( $this->settings['metakey-author'],
						$wp_query->get_queried_object() );
				}
			} elseif ( is_post_type_archive() ) {
				$post_type = get_query_var( 'post_type' );
				if ( is_array( $post_type ) ) {
					$post_type = reset( $post_type );
				}
				if ( isset( $this->settings[ 'metakey-ptarchive-' . $post_type ] ) && $this->settings[ 'metakey-ptarchive-' . $post_type ] !== '' ) {
					$keywords = $this->replacer->replace( $this->settings[ 'metakey-ptarchive-' . $post_type ],
						$wp_query->get_queried_object() );
				}
			}
		}

		$keywords = apply_filters( 'masb_seo_metakeywords', trim( $keywords ) ); // More appropriately named.

		if ( is_string( $keywords ) && $keywords !== '' ) {
			echo '<meta name="keywords" content="', esc_attr( strip_tags( stripslashes( $keywords ) ) ), '"/>', "\n";
		}
	}

	private function get_keywords( $post ) {
		$keywords        = $this->options->get_meta()->get_value( 'metakeywords', $post->ID );
		$option_meta_key = 'metakey-' . $post->post_type;

		if ( $keywords === '' && ( is_object( $post ) && ( isset( $this->settings[ $option_meta_key ] ) && $this->settings[ $option_meta_key ] !== '' ) ) ) {
			$keywords = $this->replacer->replace( $this->settings[ $option_meta_key ], $post );
		}

		return $keywords;
	}

	public function metadesc( $echo = true ) {
		if ( is_null( $this->metadesc ) ) {
			$this->generate_metadesc();
		}

		if ( $echo !== false ) {
			if ( is_string( $this->metadesc ) && $this->metadesc !== '' ) {
				echo '<meta name="description" content="', esc_attr( strip_tags( stripslashes( $this->metadesc ) ) ), '"/>', "\n";
			} elseif ( current_user_can( 'manage_options' ) && is_singular() ) {
				echo '<!-- ', esc_html__( 'Admin only notice: this page doesn\'t show a meta description because it doesn\'t have one, either write it for this page specifically or go into the SEO -> Titles menu and set up a template.',
					'marketing-and-seo-booster' ), ' -->', "\n";
			}
		} else {
			return $this->metadesc;
		}
	}

	private function generate_metadesc() {
		global $post, $wp_query;

		$metadesc          = '';
		$metadesc_override = false;
		$post_type         = '';
		$template          = '';

		if ( is_object( $post ) && ( isset( $post->post_type ) && $post->post_type !== '' ) ) {
			$post_type = $post->post_type;
		}

		if ( is_singular() ) {
			if ( ( $metadesc === '' && $post_type !== '' ) && isset( $this->settings[ 'metadesc-' . $post_type ] ) ) {
				$template = $this->settings[ 'metadesc-' . $post_type ];
				$term     = $post;
			}
			$metadesc_override = $this->options->get_meta()->get_value( 'metadesc' );
		} else {
			if ( is_search() ) {
				$metadesc = '';
			} elseif ( $this->is_home_posts_page() ) {
				$template = $this->settings['metadesc-homepage'];
				$term     = array();

				if ( empty( $template ) ) {
					$template = get_bloginfo( 'description' );
				}
			} elseif ( $this->is_posts_page() ) {
				$metadesc = $this->options->get_meta()->get_value( 'metadesc', get_option( 'page_for_posts' ) );
				if ( ( $metadesc === '' && $post_type !== '' ) && isset( $this->settings[ 'metadesc-' . $post_type ] ) ) {
					$page     = get_post( get_option( 'page_for_posts' ) );
					$template = $this->settings[ 'metadesc-' . $post_type ];
					$term     = $page;
				}
			} elseif ( $this->is_home_static_page() ) {
				$metadesc = $this->options->get_meta()->get_value( 'metadesc' );
				if ( ( $metadesc === '' && $post_type !== '' ) && isset( $this->settings[ 'metadesc-' . $post_type ] ) ) {
					$template = $this->settings[ 'metadesc-' . $post_type ];
				}
			} elseif ( is_category() || is_tag() || is_tax() ) {
				$term              = $wp_query->get_queried_object();
				$metadesc_override = get_term_meta( $term->term_id,
					$this->options->get_meta()->meta_prefix . 'metadesc',
					true );
				if ( empty( $metadesc_override ) ) {
					if ( ! empty ( $term->description ) ) {
						$metadesc_override = $term->description;
					} else {
						$metadesc_override = $this->options->get_taxonomy_meta()->get_term_meta( $term,
							$term->taxonomy,
							'desc' );
					}
				}
				if ( is_object( $term ) && isset( $term->taxonomy, $this->settings[ 'metadesc-tax-' . $term->taxonomy ] ) ) {
					$template = $this->settings[ 'metadesc-tax-' . $term->taxonomy ];
				}
			} elseif ( is_author() ) {
				$author_id = get_query_var( 'author' );
				$metadesc  = get_the_author_meta( $this->options->get_meta()->meta_prefix . 'author_metadesc',
					$author_id );
				if ( ( ! is_string( $metadesc ) || $metadesc === '' ) && '' !== $this->settings['metadesc-author-archives'] ) {
					$template = $this->settings['metadesc-author-archives'];
				}
			} elseif ( is_post_type_archive() ) {
				$post_type = get_query_var( 'post_type' );
				if ( is_array( $post_type ) ) {
					$post_type = reset( $post_type );
				}
				if ( isset( $this->settings[ 'metadesc-ptarchive-' . $post_type ] ) ) {
					$template = $this->settings[ 'metadesc-ptarchive-' . $post_type ];
				}
			} elseif ( is_archive() ) {
				$template = $this->settings['metadesc-date-archives'];
			}

			// If we're on a paginated page, and the template doesn't change for paginated pages, bail.
			if ( ( ! is_string( $metadesc ) || $metadesc === '' ) && get_query_var( 'paged' ) && get_query_var( 'paged' ) > 1 && $template !== '' ) {
				if ( strpos( $template, '%%page' ) === false ) {
					$metadesc = '';
				}
			}
		}

		$post_data = $post;

		if ( is_string( $metadesc_override ) && '' !== $metadesc_override ) {
			$metadesc = $metadesc_override;
			if ( isset( $term ) ) {
				$post_data = $term;
			}
		} else if ( ( ! is_string( $metadesc ) || '' === $metadesc ) && '' !== $template ) {
			if ( ! isset( $term ) ) {
				$term = $wp_query->get_queried_object();
			}

			$metadesc  = $template;
			$post_data = $term;
		}
		$metadesc = $this->replacer->replace( $metadesc, $post_data );

		$this->metadesc = apply_filters( 'masb_seo_metadesc', trim( $metadesc ) );
	}

	public function add_toolbar_items( $admin_bar ) {
		$admin_bar->add_menu(
			array(
				'id'    => 'masb-seo-titles',
				'title' => esc_html__( 'SEO Titles', 'marketing-and-seo-booster' ),
				'href'  => get_admin_url( null, 'admin.php?page=masb-seo-titles' ),
				'meta'  => array(
					'title' => esc_html__( 'SEO Titles', 'marketing-and-seo-booster' ),
				),
			)
		);
	}

}

class MASB_SEO_Replace_Vars {

	private $options;

	/**
	 * @var    array    Default post/page/cpt information
	 */
	protected $defaults = array(
		'ID'            => '',
		'name'          => '',
		'post_author'   => '',
		'post_content'  => '',
		'post_date'     => '',
		'post_excerpt'  => '',
		'post_modified' => '',
		'post_title'    => '',
		'taxonomy'      => '',
		'term_id'       => '',
		'term404'       => '',
	);

	/**
	 * @var object    Current post/page/cpt information
	 */
	protected $args;

	/**
	 * @var    array    Help texts for use in WPSEO -> Titles and Meta's help tabs
	 */
	protected static $help_texts = array();

	/**
	 * @var array    Register of additional variable replacements registered by other plugins/themes
	 */

	/**
	 * Constructor
	 *
	 * @return \MASB_SEO_Replace_Vars
	 */
	public function __construct( $options ) {
		$this->options = $options;
	}

	/**
	 * Replace `%%variable_placeholders%%` with their real value based on the current requested page/post/cpt/etc
	 *
	 * @param string $string the string to replace the variables in.
	 * @param array $args the object some of the replacement values might come from,
	 *                       could be a post, taxonomy or term.
	 * @param array $omit variables that should not be replaced by this function.
	 *
	 * @return string
	 */
	public function replace( $string, $args, $omit = array() ) {

		$string = strip_tags( $string );

		// Let's see if we can bail super early.
		if ( strpos( $string, '%%' ) === false ) {
			return MASB_SEO::standardize_whitespace( $string );
		}

		$args = (array) $args;
		if ( isset( $args['post_content'] ) && ! empty( $args['post_content'] ) ) {
			$args['post_content'] = MASB_SEO::strip_shortcode( $args['post_content'] );
		}
		if ( isset( $args['post_excerpt'] ) && ! empty( $args['post_excerpt'] ) ) {
			$args['post_excerpt'] = MASB_SEO::strip_shortcode( $args['post_excerpt'] );
		}
		$this->args = (object) wp_parse_args( $args, $this->defaults );

		// Clean $omit array.
		if ( is_array( $omit ) && $omit !== array() ) {
			$omit = array_map( array( __CLASS__, 'remove_var_delimiter' ), $omit );
		}

		$replacements = array();
		if ( preg_match_all( '`%%([^%]+(%%single)?)%%?`iu', $string, $matches ) ) {
			$replacements = $this->set_up_replacements( $matches, $omit );
		}

		$replacements = apply_filters( 'masb_seo_replacements', $replacements );

		// Do the actual replacements.
		if ( is_array( $replacements ) && $replacements !== array() ) {
			$string = str_replace( array_keys( $replacements ), array_values( $replacements ), $string );
		}

		/**
		 * Filter: 'masb_seo_replacements_final' - Allow overruling of whether or not to remove placeholders
		 * which didn't yield a replacement
		 *
		 * @example add_filter( 'masb_seo_replacements_final', '__return_false' );
		 */
		if ( apply_filters( 'masb_seo_replacements_final',
				true ) === true && ( isset( $matches[1] ) && is_array( $matches[1] ) ) ) {
			// Remove non-replaced variables.
			$remove = array_diff( $matches[1], $omit ); // Make sure the $omit variables do not get removed.
			$remove = array_map( array( __CLASS__, 'add_var_delimiter' ), $remove );
			$string = str_replace( $remove, '', $string );
		}

		// Undouble separators which have nothing between them, i.e. where a non-replaced variable was removed.
		if ( isset( $replacements['%%sep%%'] ) && ( is_string( $replacements['%%sep%%'] ) && $replacements['%%sep%%'] !== '' ) ) {
			$q_sep  = preg_quote( $replacements['%%sep%%'], '`' );
			$string = preg_replace( '`' . $q_sep . '(?:\s*' . $q_sep . ')*`u', $replacements['%%sep%%'], $string );
		}

		// Remove superfluous whitespace.
		$string = MASB_SEO::standardize_whitespace( $string );

		return trim( $string );
	}


	/**
	 * Retrieve the replacements for the variables found.
	 *
	 * @param array $matches variables found in the original string - regex result.
	 * @param array $omit variables that should not be replaced by this function.
	 *
	 * @return array retrieved replacements - this might be a smaller array as some variables
	 *               may not yield a replacement in certain contexts.
	 */
	private function set_up_replacements( $matches, $omit ) {

		$replacements = array();

		foreach ( $matches[1] as $k => $var ) {

			// Don't set up replacements which should be omitted.
			if ( in_array( $var, $omit, true ) ) {
				continue;
			}

			// Deal with variable variable names first.
			if ( strpos( $var, 'cf_' ) === 0 ) {
				$replacement = $this->retrieve_cf_custom_field_name( $var );
			} elseif ( strpos( $var, 'ct_desc_' ) === 0 ) {
				$replacement = $this->retrieve_ct_desc_custom_tax_name( $var );
			} elseif ( strpos( $var, 'ct_' ) === 0 ) {
				$single      = ( isset( $matches[2][ $k ] ) && $matches[2][ $k ] !== '' ) ? true : false;
				$replacement = $this->retrieve_ct_custom_tax_name( $var, $single );
			} // Deal with non-variable variable names.
			elseif ( method_exists( $this, 'retrieve_' . $var ) ) {
				$method_name = 'retrieve_' . $var;
				$replacement = $this->$method_name();
			}

			// Replacement retrievals can return null if no replacement can be determined, root those outs.
			if ( isset( $replacement ) ) {
				$var                  = self::add_var_delimiter( $var );
				$replacements[ $var ] = $replacement;
			}
			unset( $replacement, $single, $method_name );
		}

		return $replacements;
	}



	/* *********************** BASIC VARIABLES ************************** */

	/**
	 * Retrieve the post/cpt categories (comma separated) for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_category() {
		$replacement = null;

		if ( ! empty( $this->args->ID ) ) {
			$cat = $this->get_terms( $this->args->ID, 'category' );
			if ( $cat !== '' ) {
				$replacement = $cat;
			}
		}

		if ( ( ! isset( $replacement ) || $replacement === '' ) && ( isset( $this->args->cat_name ) && ! empty( $this->args->cat_name ) ) ) {
			$replacement = $this->args->cat_name;
		}

		return $replacement;
	}

	/**
	 * Retrieve the category description for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_category_description() {
		return $this->retrieve_term_description();
	}

	/**
	 * Retrieve the date of the post/page/cpt for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_date() {
		$replacement = null;

		if ( $this->args->post_date !== '' ) {
			$replacement = mysql2date( get_option( 'date_format' ), $this->args->post_date, true );
		} else {
			if ( get_query_var( 'day' ) && get_query_var( 'day' ) !== '' ) {
				$replacement = get_the_date();
			} else {
				if ( single_month_title( ' ', false ) && single_month_title( ' ', false ) !== '' ) {
					$replacement = single_month_title( ' ', false );
				} elseif ( get_query_var( 'year' ) !== '' ) {
					$replacement = get_query_var( 'year' );
				}
			}
		}

		return $replacement;
	}

	/**
	 * Retrieve the post/page/cpt excerpt for use as replacement string.
	 * The excerpt will be auto-generated if it does not exist.
	 *
	 * @return string|null
	 */
	private function retrieve_excerpt() {
		$replacement = null;

		if ( ! empty( $this->args->ID ) ) {
			if ( $this->args->post_excerpt !== '' ) {
				$replacement = strip_tags( $this->args->post_excerpt );
			} elseif ( $this->args->post_content !== '' ) {
				$replacement = wp_html_excerpt( strip_shortcodes( $this->args->post_content ), 155 );
			}
		}

		return $replacement;
	}

	/**
	 * Retrieve the post/page/cpt excerpt for use as replacement string (without auto-generation).
	 *
	 * @return string|null
	 */
	private function retrieve_excerpt_only() {
		$replacement = null;

		if ( ! empty( $this->args->ID ) && $this->args->post_excerpt !== '' ) {
			$replacement = strip_tags( $this->args->post_excerpt );
		}

		return $replacement;
	}

	/**
	 * Retrieve the title of the parent page of the current page/cpt for use as replacement string.
	 * Only applicable for hierarchical post types.
	 *
	 * @return string|null
	 */
	private function retrieve_parent_title() {
		$replacement = null;

		if ( ! isset( $replacement ) && ( ( is_singular() || is_admin() ) && isset( $GLOBALS['post'] ) ) ) {
			if ( isset( $GLOBALS['post']->post_parent ) && 0 !== $GLOBALS['post']->post_parent ) {
				$replacement = get_the_title( $GLOBALS['post']->post_parent );
			}
		}

		return $replacement;
	}

	/**
	 * Retrieve the current search phrase for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_searchphrase() {
		$replacement = null;

		if ( ! isset( $replacement ) ) {
			$search = get_query_var( 's' );
			if ( $search !== '' ) {
				$replacement = esc_html( $search );
			}
		}

		return $replacement;
	}

	/**
	 * Retrieve the separator for use as replacement string.
	 *
	 * @return string
	 */
	private function retrieve_sep() {

		$replacement = $this->options->get_default( $this->options->get_titles(), 'separator' );

		// Get the titles option and the separator options.
		$titles_options = $this->options->get_option( $this->options->get_titles()->get_option_name() );

		$seperator_options = $this->options->get_titles()->get_separator_options();

		if ( is_array(  $titles_options ) && isset( $seperator_options[ $titles_options['separator'] ] ) ) {
			$replacement = $seperator_options[ $titles_options['separator'] ];
		} else {
			$replacement = $seperator_options[ $replacement ];
		};

		return $replacement;
	}

	/**
	 * Retrieve the site's tag line / description for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_sitedesc() {
		static $replacement;

		if ( ! isset( $replacement ) ) {
			$description = trim( strip_tags( get_bloginfo( 'description' ) ) );
			if ( $description !== '' ) {
				$replacement = $description;
			}
		}

		return $replacement;
	}


	/**
	 * Retrieve the site's name for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_sitename() {
		static $replacement;

		if ( ! isset( $replacement ) ) {
			$sitename = trim( strip_tags( get_bloginfo( 'name' ) ) );
			if ( $sitename !== '' ) {
				$replacement = $sitename;
			}
		}

		return $replacement;
	}

	/**
	 * Retrieve the current tag/tags for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_tag() {
		$replacement = null;

		if ( isset( $this->args->ID ) ) {
			$tags = $this->get_terms( $this->args->ID, 'post_tag' );
			if ( $tags !== '' ) {
				$replacement = $tags;
			}
		}

		return $replacement;
	}

	/**
	 * Retrieve the tag description for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_tag_description() {
		return $this->retrieve_term_description();
	}

	/**
	 * Retrieve the term description for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_term_description() {
		$replacement = null;

		if ( isset( $this->args->term_id ) && ! empty( $this->args->taxonomy ) ) {
			$term_desc = get_term_field( 'description', $this->args->term_id, $this->args->taxonomy );
			if ( $term_desc !== '' ) {
				$replacement = trim( strip_tags( $term_desc ) );
			}
		}

		return $replacement;
	}

	/**
	 * Retrieve the term name for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_term_title() {
		$replacement = null;

		if ( ! empty( $this->args->taxonomy ) && ! empty( $this->args->name ) ) {
			$replacement = $this->args->name;
		}

		return $replacement;
	}

	/**
	 * Retrieve the title of the post/page/cpt for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_title() {
		$replacement = null;

		if ( is_string( $this->args->post_title ) && $this->args->post_title !== '' ) {
			$replacement = stripslashes( $this->args->post_title );
		}

		return $replacement;
	}

	/**
	 * Retrieve primary category for use as replacement string.
	 *
	 * @return bool|int|null
	 */
	private function retrieve_primary_category() {
		$primary_category = null;

		if ( ! empty( $this->args->ID ) ) {
			$masb_seo_primary_category = new MASB_SEO_Primary_Term( 'category', $this->args->ID, $this->options );

			$term_id = $masb_seo_primary_category->get_primary_term();
			$term    = get_term( $term_id );

			if ( ! is_wp_error( $term ) && ! empty( $term ) ) {
				$primary_category = $term->name;
			}
		}

		return $primary_category;
	}


	/* *********************** ADVANCED VARIABLES ************************** */

	/**
	 * Determine the page numbering of the current post/page/cpt
	 *
	 * @param string $request 'nr'|'max' - whether to return the page number or the max number of pages.
	 *
	 * @return int|null
	 */
	private function determine_pagenumbering( $request = 'nr' ) {
		global $wp_query, $post;
		$max_num_pages = null;
		$page_number   = null;

		$max_num_pages = 1;

		if ( ! is_singular() ) {
			$page_number = get_query_var( 'paged' );
			if ( $page_number === 0 || $page_number === '' ) {
				$page_number = 1;
			}

			if ( isset( $wp_query->max_num_pages ) && ( $wp_query->max_num_pages != '' && $wp_query->max_num_pages != 0 ) ) {
				$max_num_pages = $wp_query->max_num_pages;
			}
		} else {
			$page_number = get_query_var( 'page' );
			if ( $page_number === 0 || $page_number === '' ) {
				$page_number = 1;
			}

			if ( isset( $post->post_content ) ) {
				$max_num_pages = ( substr_count( $post->post_content, '<!--nextpage-->' ) + 1 );
			}
		}

		$return = null;

		switch ( $request ) {
			case 'nr':
				$return = $page_number;
				break;
			case 'max':
				$return = $max_num_pages;
				break;
		}

		return $return;
	}


	/**
	 * Determine the post type names for the current post/page/cpt
	 *
	 * @param string $request 'single'|'plural' - whether to return the single or plural form.
	 *
	 * @return string|null
	 */
	private function determine_pt_names( $request = 'single' ) {
		global $wp_query;
		$pt_single = null;
		$pt_plural = null;

		if ( isset( $wp_query->query_vars['post_type'] ) && ( ( is_string( $wp_query->query_vars['post_type'] ) && $wp_query->query_vars['post_type'] !== '' ) || ( is_array( $wp_query->query_vars['post_type'] ) && $wp_query->query_vars['post_type'] !== array() ) ) ) {
			$post_type = $wp_query->query_vars['post_type'];
		} elseif ( isset( $this->args->post_type ) && ( is_string( $this->args->post_type ) && $this->args->post_type !== '' ) ) {
			$post_type = $this->args->post_type;
		} else {
			// Make it work in preview mode.
			$post_type = $wp_query->get_queried_object()->post_type;
		}

		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}

		if ( $post_type !== '' ) {
			$pt        = get_post_type_object( $post_type );
			$pt_plural = $pt_single = $pt->name;
			if ( isset( $pt->labels->singular_name ) ) {
				$pt_single = $pt->labels->singular_name;
			}
			if ( isset( $pt->labels->name ) ) {
				$pt_plural = $pt->labels->name;
			}
		}

		$return = null;

		switch ( $request ) {
			case 'single':
				$return = $pt_single;
				break;
			case 'plural':
				$return = $pt_plural;
				break;
		}

		return $return;
	}

	/**
	 * Retrieve the attachment caption for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_caption() {
		return $this->retrieve_excerpt_only();
	}


	/**
	 * Retrieve a post/page/cpt's custom field value for use as replacement string
	 *
	 * @param string $var The complete variable to replace which includes the name of
	 *                    the custom field which value is to be retrieved.
	 *
	 * @return string|null
	 */
	private function retrieve_cf_custom_field_name( $var ) {
		global $post;
		$replacement = null;

		if ( is_string( $var ) && $var !== '' ) {
			$field = substr( $var, 3 );
			if ( ( is_singular() || is_admin() ) && ( is_object( $post ) && isset( $post->ID ) ) ) {
				$name = get_post_meta( $post->ID, $field, true );
				if ( $name !== '' ) {
					$replacement = $name;
				}
			}
		}

		return $replacement;
	}


	/**
	 * Retrieve a post/page/cpt's custom taxonomies for use as replacement string
	 *
	 * @param string $var The complete variable to replace which includes the name of
	 *                       the custom taxonomy which value(s) is to be retrieved.
	 * @param bool $single Whether to retrieve only the first or all values for the taxonomy.
	 *
	 * @return string|null
	 */
	private function retrieve_ct_custom_tax_name( $var, $single = false ) {
		$replacement = null;

		if ( ( is_string( $var ) && $var !== '' ) && ! empty( $this->args->ID ) ) {
			$tax  = substr( $var, 3 );
			$name = $this->get_terms( $this->args->ID, $tax, $single );
			if ( $name !== '' ) {
				$replacement = $name;
			}
		}

		return $replacement;
	}


	/**
	 * Retrieve a post/page/cpt's custom taxonomies description for use as replacement string
	 *
	 * @param string $var The complete variable to replace which includes the name of
	 *                    the custom taxonomy which description is to be retrieved.
	 *
	 * @return string|null
	 */
	private function retrieve_ct_desc_custom_tax_name( $var ) {
		global $post;
		$replacement = null;

		if ( is_string( $var ) && $var !== '' ) {
			$tax = substr( $var, 8 );
			if ( is_object( $post ) && isset( $post->ID ) ) {
				$terms = get_the_terms( $post->ID, $tax );
				if ( is_array( $terms ) && $terms !== array() ) {
					$term      = current( $terms );
					$term_desc = get_term_field( 'description', $term->term_id, $tax );
					if ( $term_desc !== '' ) {
						$replacement = trim( strip_tags( $term_desc ) );
					}
				}
			}
		}

		return $replacement;
	}

	/**
	 * Retrieve the current date for use as replacement string.
	 *
	 * @return string
	 */
	private function retrieve_currentdate() {
		static $replacement;

		if ( ! isset( $replacement ) ) {
			$replacement = date_i18n( get_option( 'date_format' ) );
		}

		return $replacement;
	}

	/**
	 * Retrieve the current day for use as replacement string.
	 *
	 * @return string
	 */
	private function retrieve_currentday() {
		static $replacement;

		if ( ! isset( $replacement ) ) {
			$replacement = date_i18n( 'j' );
		}

		return $replacement;
	}

	/**
	 * Retrieve the current month for use as replacement string.
	 *
	 * @return string
	 */
	private function retrieve_currentmonth() {
		static $replacement;

		if ( ! isset( $replacement ) ) {
			$replacement = date_i18n( 'F' );
		}

		return $replacement;
	}

	/**
	 * Retrieve the current time for use as replacement string.
	 *
	 * @return string
	 */
	private function retrieve_currenttime() {
		static $replacement;

		if ( ! isset( $replacement ) ) {
			$replacement = date_i18n( get_option( 'time_format' ) );
		}

		return $replacement;
	}

	/**
	 * Retrieve the current year for use as replacement string.
	 *
	 * @return string
	 */
	private function retrieve_currentyear() {
		static $replacement;

		if ( ! isset( $replacement ) ) {
			$replacement = date_i18n( 'Y' );
		}

		return $replacement;
	}

	/**
	 * Retrieve the post/page/cpt's focus keyword for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_focuskw() {
		$replacement = null;

		if ( ! empty( $this->args->ID ) ) {
			$focus_kw = $this->options->get_meta()->get_value( 'focuskw', $this->args->ID );
			if ( $focus_kw !== '' ) {
				$replacement = $focus_kw;
			}
		}

		return $replacement;
	}

	/**
	 * Retrieve the post/page/cpt ID for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_id() {
		$replacement = null;

		if ( ! empty( $this->args->ID ) ) {
			$replacement = $this->args->ID;
		}

		return $replacement;
	}

	/**
	 * Retrieve the post/page/cpt modified time for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_modified() {
		$replacement = null;

		if ( ! empty( $this->args->post_modified ) ) {
			$replacement = mysql2date( get_option( 'date_format' ), $this->args->post_modified, true );
		}

		return $replacement;
	}

	/**
	 * Retrieve the post/page/cpt author's "nice name" for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_name() {
		$replacement = null;

		$user_id = $this->retrieve_userid();
		$name    = get_the_author_meta( 'display_name', $user_id );
		if ( $name !== '' ) {
			$replacement = $name;
		}

		return $replacement;
	}

	/**
	 * Retrieve the post/page/cpt author's users description for use as a replacement string.
	 *
	 * @return null|string
	 */
	private function retrieve_user_description() {
		$replacement = null;

		$user_id     = $this->retrieve_userid();
		$description = get_the_author_meta( 'description', $user_id );
		if ( $description != '' ) {
			$replacement = $description;
		}

		return $replacement;
	}

	/**
	 * Retrieve the current page number with context (i.e. 'page 2 of 4') for use as replacement string.
	 *
	 * @return string
	 */
	private function retrieve_page() {
		$replacement = null;

		$max = $this->determine_pagenumbering( 'max' );
		$nr  = $this->determine_pagenumbering( 'nr' );
		$sep = $this->retrieve_sep();

		if ( $max > 1 && $nr > 1 ) {
			$replacement = sprintf( $sep . ' ' . esc_html__( 'Page %1$d of %2$d', 'marketing-and-seo-booster' ), $nr, $max );
		}

		return $replacement;
	}

	/**
	 * Retrieve the current page number for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_pagenumber() {
		$replacement = null;

		$nr = $this->determine_pagenumbering( 'nr' );
		if ( isset( $nr ) && $nr > 0 ) {
			$replacement = (string) $nr;
		}

		return $replacement;
	}

	/**
	 * Retrieve the current page total for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_pagetotal() {
		$replacement = null;

		$max = $this->determine_pagenumbering( 'max' );
		if ( isset( $max ) && $max > 0 ) {
			$replacement = (string) $max;
		}

		return $replacement;
	}

	/**
	 * Retrieve the post type plural label for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_pt_plural() {
		$replacement = null;

		$name = $this->determine_pt_names( 'plural' );
		if ( isset( $name ) && $name !== '' ) {
			$replacement = $name;
		}

		return $replacement;
	}

	/**
	 * Retrieve the post type single label for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_pt_single() {
		$replacement = null;

		$name = $this->determine_pt_names( 'single' );
		if ( isset( $name ) && $name !== '' ) {
			$replacement = $name;
		}

		return $replacement;
	}

	/**
	 * Retrieve the slug which caused the 404 for use as replacement string.
	 *
	 * @return string|null
	 */
	private function retrieve_term404() {
		$replacement = null;

		if ( $this->args->term404 !== '' ) {
			$replacement = sanitize_text_field( str_replace( '-', ' ', $this->args->term404 ) );
		} else {
			$error_request = get_query_var( 'pagename' );
			if ( $error_request !== '' ) {
				$replacement = sanitize_text_field( str_replace( '-', ' ', $error_request ) );
			} else {
				$error_request = get_query_var( 'name' );
				if ( $error_request !== '' ) {
					$replacement = sanitize_text_field( str_replace( '-', ' ', $error_request ) );
				}
			}
		}

		return $replacement;
	}

	/**
	 * Retrieve the post/page/cpt author's user id for use as replacement string.
	 *
	 * @return string
	 */
	private function retrieve_userid() {
		$replacement = ! empty( $this->args->post_author ) ? $this->args->post_author : get_query_var( 'author' );

		return $replacement;
	}

	/**
	 * Set the help text for a user/plugin/theme defined extra variable.
	 *
	 * @param  string $type Type of variable: 'basic' or 'advanced'.
	 * @param  string $replace Variable to replace, i.e. '%%var%%'.
	 * @param  string $help_text The actual help text string.
	 */
	private static function register_help_text( $type, $replace, $help_text = '' ) {
		if ( is_string( $replace ) && $replace !== '' ) {
			$replace = self::remove_var_delimiter( $replace );

			if ( ( is_string( $type ) && in_array( $type,
						array(
							'basic',
							'advanced',
						),
						true ) ) && ( $replace !== '' && ! isset( self::$help_texts[ $type ][ $replace ] ) )
			) {
				self::$help_texts[ $type ][ $replace ] = $help_text;
			}
		}
	}

	/* *********************** GENERAL HELPER METHODS ************************** */

	/**
	 * Remove the '%%' delimiters from a variable string
	 *
	 * @param  string $string Variable string to be cleaned.
	 *
	 * @return string
	 */
	private static function remove_var_delimiter( $string ) {
		return trim( $string, '%' );
	}

	/**
	 * Add the '%%' delimiters to a variable string
	 *
	 * @param  string $string Variable string to be delimited.
	 *
	 * @return string
	 */
	private static function add_var_delimiter( $string ) {
		return '%%' . $string . '%%';
	}

	/**
	 * Retrieve a post's terms, comma delimited.
	 *
	 * @param int $id ID of the post to get the terms for.
	 * @param string $taxonomy The taxonomy to get the terms for this post from.
	 * @param bool $return_single If true, return the first term.
	 *
	 * @return string either a single term or a comma delimited string of terms.
	 */
	public function get_terms( $id, $taxonomy, $return_single = false ) {

		$output = '';

		// If we're on a specific tag, category or taxonomy page, use that.
		if ( is_category() || is_tag() || is_tax() ) {
			$term   = $GLOBALS['wp_query']->get_queried_object();
			$output = $term->name;
		} elseif ( ! empty( $id ) && ! empty( $taxonomy ) ) {
			$terms = get_the_terms( $id, $taxonomy );
			if ( is_array( $terms ) && $terms !== array() ) {
				foreach ( $terms as $term ) {
					if ( $return_single ) {
						$output = $term->name;
						break;
					} else {
						$output .= $term->name . ', ';
					}
				}
				$output = rtrim( trim( $output ), ',' );
			}
		}
		unset( $terms, $term );

		/**
		 * Allows filtering of the terms list used to replace %%category%%, %%tag%% and %%ct_<custom-tax-name>%% variables
		 *
		 * @api    string    $output    Comma-delimited string containing the terms
		 */
		return apply_filters( 'masb_seo_terms', $output );
	}
} /* End of class MASB_SEO_Replace_Vars */

class MASB_SEO_Options {

	private $titles;

	private $taxonomy_meta;

	private $meta;

	private $required_options = array(
		'masb_seo_titles' => 'MASB_SEO_Titles_Options',
	);

	private $options = array();

	function __construct() {

		if ( is_array( $this->required_options ) ) {
			foreach ( $this->required_options as $option_name => $option_class ) {
				if ( class_exists( $option_class ) ) {
					$option = $this->get_option( $option_name );
					if ( $option ) {
						$this->options = array_merge( $this->options, $option );
					}
				}
			}
		}

		$this->titles = new MASB_SEO_Titles_Options();

		$this->taxonomy_meta = new MASB_SEO_Taxonomy_Meta();

		$this->meta = new MASB_SEO_Meta();

	}

	/**
	 * @return MASB_SEO_Titles_Options
	 */
	public function get_titles() {
		return $this->titles;
	}

	/**
	 * @return MASB_SEO_Taxonomy_Meta
	 */
	public function get_taxonomy_meta() {
		return $this->taxonomy_meta;
	}

	/**
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * @return MASB_SEO_Meta
	 */
	public function get_meta() {
		return $this->meta;
	}


	public function get_default( $option_name, $key ) {
		if ( isset( $option_name ) ) {
			$defaults = $option_name->get_defaults();
			if ( isset( $defaults[ $key ] ) ) {
				return $defaults[ $key ];
			}
		}

		return null;
	}

	public function get_option( $option_name ) {
		$option = null;
		if ( is_string( $option_name ) && ! empty( $option_name ) ) {
			$option = get_site_option( $option_name );
		}

		return $option;
	}

}/* End of class MASB_SEO_Options */


class MASB_SEO_Titles_Options {

	private $option_name = 'masb_seo_titles';

	private $separator_options = array(
		'masb-seo-dash'   => '-',
		'masb-seo-ndash'  => '&ndash;',
		'masb-seo-mdash'  => '&mdash;',
		'masb-seo-middot' => '&middot;',
		'masb-seo-bull'   => '&bull;',
		'masb-seo-star'   => '*',
		'masb-seo-smstar' => '&#8902;',
		'masb-seo-pipe'   => '|',
		'masb-seo-tilde'  => '~',
		'masb-seo-laquo'  => '&laquo;',
		'masb-seo-raquo'  => '&raquo;',
		'masb-seo-lt'     => '&lt;',
		'masb-seo-gt'     => '&gt;',
	);

	protected $defaults = array(
		// Form fields.
		'separator'                => 'masb-seo-dash',
		'title-homepage'           => '%%sitename%% %%page%% %%sep%% %%sitedesc%%', // Text field.
		'title-tax-category'       => '%%date%% %%page%% %%sep%% %%sitename%%', // Text field.
		'title-author-archives'    => '', // Text field.
		'metadesc-author-archives' => '', // Text area.
		'title-date-archives'      => '%%date%% %%page%% %%sep%% %%sitename%%', // Text field.
		'metadesc-date-archives'   => '',
		'title-search'             => '', // Text field.
		'title-404'                => '', // Text field.
		'metadesc-homepage'        => '', // Text area.
		'metakey-homepage'         => '', // Text field.
		'metakey-author'           => '', // Text field.
	);

	public function __construct() {
		add_action( 'update_option_' . $this->option_name, array( 'MASB_SEO', 'clear_cache' ) );
	}

	public function get_option_name() {
		return $this->option_name;
	}

	public function get_separator_options() {
		return $this->separator_options;
	}

	/**
	 * @return array
	 */
	public function get_defaults() {

		if ( method_exists( $this, 'translate_defaults' ) ) {
			$this->translate_defaults();
		}

		if ( method_exists( $this, 'add_defaults' ) ) {
			$this->add_defaults();
		}

		return $this->defaults;
	}

	public function translate_defaults() {
		/* translators: 1: Author name; 2: Site name. */
		$this->defaults['title-author-archives'] = sprintf( esc_html__( '%1$s, Author at %2$s', 'marketing-and-seo-booster' ),
				'%%name%%',
				'%%sitename%%' ) . ' %%page%% ';
		$this->defaults['title-search']          = sprintf( esc_html__( 'You searched for %s', 'marketing-and-seo-booster' ),
				'%%searchphrase%%' ) . ' %%page%% %%sep%% %%sitename%%';
		$this->defaults['title-404']             = esc_html__( 'Page not found',
				'marketing-and-seo-booster' ) . ' %%sep%% %%sitename%%';
	}

	public function add_defaults() {

		// Retrieve all the relevant post type and taxonomy arrays.
		$post_type_names = get_post_types( array( 'public' => true ), 'names' );

		$post_type_objects_custom = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );

		$taxonomy_names = get_taxonomies( array( 'public' => true ), 'names' );


		if ( $post_type_names !== array() ) {
			foreach ( $post_type_names as $pt ) {
				$this->defaults[ 'title-' . $pt ]    = '%%title%% %%sep%% %%sitename%%'; // Text field.
				$this->defaults[ 'metadesc-' . $pt ] = ''; // Text area.
				$this->defaults[ 'metakey-' . $pt ]  = ''; // Text field.
			}
			unset( $pt );
		}

		if ( $post_type_objects_custom !== array() ) {
			$archive = sprintf( esc_html__( '%s Archive', 'marketing-and-seo-booster' ), '%%pt_plural%%' );
			foreach ( $post_type_objects_custom as $pt ) {
				if ( ! $pt->has_archive ) {
					continue;
				}

				$this->defaults[ 'title-ptarchive-' . $pt->name ]    = $archive . ' %%page%% %%sep%% %%sitename%%'; // Text field.
				$this->defaults[ 'metadesc-ptarchive-' . $pt->name ] = ''; // Text area.
				$this->defaults[ 'metakey-ptarchive-' . $pt->name ]  = ''; // Text field.
				$this->defaults[ 'bctitle-ptarchive-' . $pt->name ]  = ''; // Text field.
			}
			unset( $pt );
		}

		if ( $taxonomy_names !== array() ) {
			$archives = sprintf( esc_html__( '%s Archives', 'marketing-and-seo-booster' ), '%%term_title%%' );
			foreach ( $taxonomy_names as $tax ) {
				$this->defaults[ 'title-tax-' . $tax ]    = $archives . ' %%page%% %%sep%% %%sitename%%'; // Text field.
				$this->defaults[ 'metadesc-tax-' . $tax ] = ''; // Text area.
				$this->defaults[ 'metakey-tax-' . $tax ]  = ''; // Text field.
			}
			unset( $tax );
		}
	}

}/* End of class MASB_SEO_Titles_Options */

class MASB_SEO_Taxonomy_Meta {

	/**
	 * @var  string  option name
	 */
	private $option_name = 'masb_seo_taxonomy_meta';

	/**
	 * @var  bool  whether to include the option in the return for WPSEO_Options::get_all()
	 */
	public $include_in_all = false;

	/**
	 * @var  array  Array of defaults for the option
	 *        Shouldn't be requested directly, use $this->get_defaults();
	 * @internal  Important: in contrast to most defaults, the below array format is
	 *        very bare. The real option is in the format [taxonomy_name][term_id][...]
	 *        where [...] is any of the $defaults_per_term options shown below.
	 *        This is of course taken into account in the below methods.
	 */
	protected $defaults = array();


	/**
	 * @var  string  Option name - same as $option_name property, but now also available to static methods
	 * @static
	 */
	public static $name;

	/**
	 * @var  array  Array of defaults for individual taxonomy meta entries
	 * @static
	 */
	public $defaults_per_term = array(
		'masb_seo_title'   => '',
		'masb_seo_desc'    => '',
		'masb_seo_metakey' => '',
	);

	/**
	 * constructor MASB_SEO_Taxonomy_Meta
	 */
	public function __construct() {
		add_action( 'update_option_' . $this->option_name, array( 'MASB_SEO', 'flush_w3tc_cache' ) );
	}

	/**
	 * @return string
	 */
	public function get_option_name() {
		return $this->option_name;
	}

	/**
	 * Retrieve a taxonomy term's meta value(s).
	 *
	 * @static
	 *
	 * @param  mixed $term Term to get the meta value for
	 *                          either (string) term name, (int) term id or (object) term.
	 * @param  string $taxonomy Name of the taxonomy to which the term is attached.
	 * @param  string $meta (optional) Meta value to get (without prefix).
	 *
	 * @return  mixed|bool    Value for the $meta if one is given, might be the default.
	 *              If no meta is given, an array of all the meta data for the term.
	 *              False if the term does not exist or the $meta provided is invalid.
	 */
	public function get_term_meta( $term, $taxonomy, $meta = null ) {
		/* Figure out the term id */
		if ( is_int( $term ) ) {
			$term = get_term_by( 'id', $term, $taxonomy );
		} elseif ( is_string( $term ) ) {
			$term = get_term_by( 'slug', $term, $taxonomy );
		}

		if ( is_object( $term ) && isset( $term->term_id ) ) {
			$term_id = $term->term_id;
		} else {
			return false;
		}

		$tax_meta = $this->get_term_tax_meta( $term_id, $taxonomy );

		/*
		Either return the complete array or a single value from it or false if the value does not exist
			   (shouldn't happen after merge with defaults, indicates typo in request)
		*/
		if ( ! isset( $meta ) ) {
			return $tax_meta;
		}


		if ( isset( $tax_meta[ 'masb_seo_' . $meta ] ) ) {
			return $tax_meta[ 'masb_seo_' . $meta ];
		}

		return false;
	}

	/**
	 * Get the current queried object and return the meta value
	 *
	 * @param string $meta The meta field that is needed.
	 *
	 * @return bool|mixed
	 */
	public function get_meta_without_term( $meta ) {
		$term = $GLOBALS['wp_query']->get_queried_object();

		return $this->get_term_meta( $term, $term->taxonomy, $meta );

	}

	/**
	 * Getting the meta from the options
	 *
	 * @return false|array
	 */
	private function get_tax_meta() {
		$option_name = $this->get_option_name();

		return get_option( $option_name );
	}

	/**
	 * Getting the taxonomy meta for the given term_id and taxonomy
	 *
	 * @param int $term_id The id of the term.
	 * @param string $taxonomy Name of the taxonomy to which the term is attached.
	 *
	 * @return array
	 */
	private function get_term_tax_meta( $term_id, $taxonomy ) {
		$tax_meta = $this->get_tax_meta();

		get_term_meta( $term_id, 'masb_seo_desc', true );
		/* If we have data for the term, merge with defaults for complete array, otherwise set defaults */
		if ( isset( $tax_meta[ $taxonomy ][ $term_id ] ) ) {
			return array_merge( $this->defaults_per_term, $tax_meta[ $taxonomy ][ $term_id ] );
		}

		return $this->defaults_per_term;
	}
}/* End of class MASB_SEO_Taxonomy_Meta */

class MASB_SEO_Meta {

	/**
	 * @var    string    Prefix for all WPSEO meta values in the database
	 * @static
	 *
	 * @internal if at any point this would change, quite apart from an upgrade routine, this also will need to
	 * be changed in the wpml-config.xml file.
	 */
	public $meta_prefix = 'masb_seo_';

	public $meta_fields = array(
		'title'        => array(
			'type'          => 'hidden',
			'title'         => '', // Translation added later.
			'default_value' => '',
			'description'   => '', // Translation added later.
			'help'          => '', // Translation added later.
		),
		'metadesc'     => array(
			'type'          => 'hidden',
			'title'         => '', // Translation added later.
			'default_value' => '',
			'class'         => 'metadesc',
			'rows'          => 2,
			'description'   => '', // Translation added later.
			'help'          => '', // Translation added later.
		),
		'metakeywords' => array(
			'type'          => 'metakeywords',
			'title'         => '', // Translation added later.
			'default_value' => '',
			'class'         => 'metakeywords',
			'description'   => '', // Translation added later.
		),
	);

	/**
	 * @var    array    Helper property - reverse index of the definition array
	 *                  Format: [full meta key including prefix]    => array
	 *                          ['subset']    => (string) primary index
	 *                          ['key']       => (string) internal key
	 * @static
	 */
	public static $fields_index = array();


	/**
	 * @var    array    Helper property - array containing only the defaults in the format:
	 *                  [full meta key including prefix]    => (string) default value
	 * @static
	 */
	public static $defaults = array();

	/**
	 * @var    array    Helper property to define the social network meta field definitions - networks
	 * @static
	 */
	private static $social_networks = array(
		'opengraph' => 'opengraph',
		'twitter'   => 'twitter',
	);

	/**
	 * @var    array    Helper property to define the social network meta field definitions - fields and their type
	 * @static
	 */
	private static $social_fields = array(
		'title'       => 'text',
		'description' => 'textarea',
		'image'       => 'upload',
	);

	/**
	 * Prevent saving of default values and remove potential old value from the database if replaced by a default
	 *
	 * @static
	 *
	 * @param  null $null Old, disregard.
	 * @param  int $object_id ID of the current object for which the meta is being updated.
	 * @param  string $meta_key The full meta key (including prefix).
	 * @param  string $meta_value New meta value.
	 * @param  string $prev_value The old meta value.
	 *
	 * @return null|bool          true = stop saving, null = continue saving
	 */
	public static function remove_meta_if_default( $null, $object_id, $meta_key, $meta_value, $prev_value = '' ) {
		/* If it's one of our meta fields, check against default */
		if ( isset( self::$fields_index[ $meta_key ] ) && self::meta_value_is_default( $meta_key,
				$meta_value ) === true ) {
			if ( $prev_value !== '' ) {
				delete_post_meta( $object_id, $meta_key, $prev_value );
			} else {
				delete_post_meta( $object_id, $meta_key );
			}

			return true; // Stop saving the value.
		}

		return null; // Go on with the normal execution (update) in meta.php.
	}


	/**
	 * Prevent adding of default values to the database
	 *
	 * @static
	 *
	 * @param  null $null Old, disregard.
	 * @param  int $object_id ID of the current object for which the meta is being added.
	 * @param  string $meta_key The full meta key (including prefix).
	 * @param  string $meta_value New meta value.
	 *
	 * @return null|bool          true = stop saving, null = continue saving
	 */
	public static function dont_save_meta_if_default( $null, $object_id, $meta_key, $meta_value ) {
		/* If it's one of our meta fields, check against default */
		if ( isset( self::$fields_index[ $meta_key ] ) && self::meta_value_is_default( $meta_key,
				$meta_value ) === true ) {
			return true; // Stop saving the value.
		}

		return null; // Go on with the normal execution (add) in meta.php.
	}


	/**
	 * Is the given meta value the same as the default value ?
	 *
	 * @static
	 *
	 * @param  string $meta_key The full meta key (including prefix).
	 * @param  mixed $meta_value The value to check.
	 *
	 * @return bool
	 */
	public static function meta_value_is_default( $meta_key, $meta_value ) {
		return ( isset( self::$defaults[ $meta_key ] ) && $meta_value === self::$defaults[ $meta_key ] );
	}


	/**
	 * Get a custom post meta value
	 * Returns the default value if the meta value has not been set
	 *
	 * @internal Unfortunately there isn't a filter available to hook into before returning the results
	 * for get_post_meta(), get_post_custom() and the likes. That would have been the preferred solution.
	 *
	 * @static
	 *
	 * @param  string $key Internal key of the value to get (without prefix).
	 * @param  int $postid Post ID of the post to get the value for.
	 *
	 * @return string         All 'normal' values returned from get_post_meta() are strings.
	 *                        Objects and arrays are possible, but not used by this plugin
	 *                        and therefore discarted (except when the special 'serialized' field def
	 *                        value is set to true - only used by add-on plugins for now).
	 *                        Will return the default value if no value was found..
	 *                        Will return empty string if no default was found (not one of our keys) or
	 *                        if the post does not exist.
	 */
	public function get_value( $key, $postid = 0 ) {
		global $post;

		$postid = absint( $postid );
		if ( $postid === 0 ) {
			if ( ( isset( $post ) && is_object( $post ) ) && ( isset( $post->post_status ) && $post->post_status !== 'auto-draft' ) ) {
				$postid = $post->ID;
			} else {
				return '';
			}
		}

		$custom = get_post_custom( $postid ); // Array of strings or empty array.

		if ( isset( $custom[ $this->meta_prefix . $key ][0] ) ) {
			$unserialized = maybe_unserialize( $custom[ $this->meta_prefix . $key ][0] );
			if ( $custom[ $this->meta_prefix . $key ][0] === $unserialized ) {
				return $custom[ $this->meta_prefix . $key ][0];
			} else {
				$field_def = $this->meta_fields[ self::$fields_index[ $this->meta_prefix . $key ]['subset'] ][ self::$fields_index[ $this->meta_prefix . $key ]['key'] ];
				if ( isset( $field_def['serialized'] ) && $field_def['serialized'] === true ) {
					// Ok, serialize value expected/allowed.
					return $unserialized;
				}
			}
		}

		// Meta was either not found or found, but object/array while not allowed to be.
		if ( isset( self::$defaults[ $this->meta_prefix . $key ] ) ) {
			return self::$defaults[ $this->meta_prefix . $key ];
		} else {
			/*
			Shouldn't ever happen, means not one of our keys as there will always be a default available
			   for all our keys
			*/
			return '';
		}
	}

	/**
	 * Recursively merge a variable number of arrays, using the left array as base,
	 * giving priority to the right array.
	 *
	 * Difference with native array_merge_recursive():
	 * array_merge_recursive converts values with duplicate keys to arrays rather than
	 * overwriting the value in the first array with the duplicate value in the second array.
	 *
	 * array_merge_recursive_distinct does not change the data types of the values in the arrays.
	 * Matching keys' values in the second array overwrite those in the first array, as is the
	 * case with array_merge.
	 *
	 * Freely based on information found on http://www.php.net/manual/en/function.array-merge-recursive.php
	 *
	 * @internal Should be moved to a general utility class
	 *
	 * @return array
	 */
	public static function array_merge_recursive_distinct() {

		$arrays = func_get_args();
		if ( count( $arrays ) < 2 ) {
			if ( $arrays === array() ) {
				return array();
			} else {
				return $arrays[0];
			}
		}

		$merged = array_shift( $arrays );

		foreach ( $arrays as $array ) {
			foreach ( $array as $key => $value ) {
				if ( is_array( $value ) && ( isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) ) {
					$merged[ $key ] = self::array_merge_recursive_distinct( $merged[ $key ], $value );
				} else {
					$merged[ $key ] = $value;
				}
			}
			unset( $key, $value );
		}

		return $merged;
	}

	/**
	 * Get a value from $_POST for a given key
	 * Returns the $_POST value if exists, returns an empty string if key does not exist
	 *
	 * @static
	 *
	 * @param  string $key Key of the value to get from $_POST.
	 *
	 * @return string      Returns $_POST value, which will be a string the majority of the time
	 *                     Will return empty string if key does not exists in $_POST
	 */
	public static function get_post_value( $key ) {
		return ( array_key_exists( $key, $_POST ) ) ? $_POST[ $key ] : '';
	}
}/* End of class MASB_SEO_Meta */

class MASB_SEO_Taxonomy {

	/**
	 * The current active taxonomy
	 *
	 * @var string
	 */
	private $taxonomy = '';

	private $options;

	/**
	 * Class constructor
	 */
	public function __construct( $options ) {
		$this->taxonomy = $this->get_taxonomy();

		$this->options = $options;

		add_action( 'edit_term', array( $this, 'update_term' ), 99, 3 );
		add_action( 'init', array( $this, 'custom_category_descriptions_allow_html' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_filter( 'category_description', array( $this, 'custom_category_descriptions_add_shortcode_support' ) );
	}

	/**
	 * Add hooks late enough for taxonomy object to be available for checks.
	 */
	public function admin_init() {

		$taxonomy = get_taxonomy( $this->taxonomy );

		if ( empty( $taxonomy ) || empty( $taxonomy->public ) ) {
			return;
		}

		add_action( sanitize_text_field( $this->taxonomy ) . '_edit_form', array( $this, 'term_metabox' ), 90, 1 );
	}

	/**
	 * Show the SEO inputs for term.
	 *
	 * @param stdClass|WP_Term $term Term to show the edit boxes for.
	 */
	public function term_metabox( $term ) {
		$content_sections = $this->options->get_meta()->meta_fields;

		ob_start();
		echo '<div id="masb-seot-settings" class="masb-seo-metabox-sidebar">';
		wp_nonce_field( plugin_basename( __FILE__ ), 'masb_seo_nonce' );
		if ( ! empty( $content_sections ) && is_array( $content_sections ) ) {
			foreach ( $content_sections as $type => $content_section ) {
				switch ( $type ) {
					case 'metadesc' :
						/**
						 * @todo Delete checking and deleting after several months from 07.2019
						 */
						$current = get_term_meta( $term->term_id, 'sst_seo_metadesc', true );
						if ( empty( $current ) ) {
							$current = get_term_meta( $term->term_id,
								$this->options->get_meta()->meta_prefix . esc_attr( $type ),
								true );
						}
						if ( ! $current ) {
							$current = '';
						}
						echo '<div id="masb-seot-', esc_attr( $type ), '-metas">
                            <label class="textinput" for="masb-seot-metadesc-' . esc_attr( $type ) . '">', esc_html__( 'Meta description:',
							'marketing-and-seo-booster' ), '</label>
                            <textarea cols="" rows="4" class="textinput" id="masb-seot-metadesc-' . esc_attr( $type ) . '" name="' . $this->options->get_meta()->meta_prefix . esc_attr( $type ) . '">' . esc_textarea( $current ) . '</textarea>
                        </div>';
						unset( $current );
						break;
					case 'metakeywords' :
						/**
						 * @todo Delete checking and deleting after several months from 07.2019
						 */
						$current = get_term_meta( $term->term_id, 'sst_seo_metakeywords', true );
						if ( empty( $current ) ) {
							$current = get_term_meta( $term->term_id,
								$this->options->get_meta()->meta_prefix . esc_attr( $type ),
								true );
						}
						if ( ! $current ) {
							$current = '';
						}
						echo '<div id="masb-seot-', esc_attr( $type ), '-metas">
                            <label class="textinput" for="masb-seot-', esc_attr( $type ), '">', esc_html__( 'Keywords',
							'marketing-and-seo-booster' ), '</label>
                            <input class="textinput" placeholder="" type="text" id="masb-seot-' . esc_attr( $type ) . '" name="' . $this->options->get_meta()->meta_prefix . esc_attr( $type ) . '" value="' . esc_attr( $current ) . '">
                        </div>';
						unset( $current );
						break;
				}
			}
			unset( $type, $content_section );
		}

		echo '</div>';
		ob_end_flush();
	}

	/**
	 * Update the taxonomy meta data on save.
	 *
	 * @param int $term_id ID of the term to save data for.
	 * @param int $tt_id The taxonomy_term_id for the term.
	 * @param string $taxonomy The taxonomy the term belongs to.
	 */
	public function update_term( $term_id, $tt_id, $taxonomy ) {

		if ( ! isset( $_POST [ $this->options->get_meta()->meta_prefix . 'metadesc' ] ) && ! isset( $_POST [ $this->options->get_meta()->meta_prefix . 'metakeywords' ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['masb_seo_nonce'], plugin_basename( __FILE__ ) ) ) {
			return;
		}

		$metadesc     = sanitize_text_field( $_POST [ $this->options->get_meta()->meta_prefix . 'metadesc' ] );
		$metakeywords = sanitize_text_field( $_POST [ $this->options->get_meta()->meta_prefix . 'metakeywords' ] );

		/**
		 * @todo Delete checking and deleting after several months from 07.2019
		 */
		$old_metadesc     = get_term_meta( $term_id, 'sst_seo_metadesc', true );
		$old_metakeywords = get_term_meta( $term_id, 'sst_seo_metakeywords', true );
		if ( ! empty( $old_metadesc ) ) {
			delete_term_meta( $term_id, 'sst_seo_metadesc', $old_metadesc );
		}
		if ( ! empty( $old_metakeywords ) ) {
			delete_term_meta( $term_id, 'sst_seo_metadesc', $old_metakeywords );
		}

		update_term_meta( $term_id, $this->options->get_meta()->meta_prefix . 'metadesc', $metadesc );
		update_term_meta( $term_id, $this->options->get_meta()->meta_prefix . 'metakeywords', $metakeywords );
	}

	/**
	 * Allows HTML in descriptions
	 */
	public function custom_category_descriptions_allow_html() {
		$filters = array(
			'pre_term_description',
			'pre_link_description',
			'pre_link_notes',
			'pre_user_description',
		);

		foreach ( $filters as $filter ) {
			remove_filter( $filter, 'wp_filter_kses' );
		}
		remove_filter( 'term_description', 'wp_kses_data' );
	}

	/**
	 * Output the WordPress editor.
	 */
	public function custom_category_description_editor() {
		wp_editor( '', 'description' );
	}

	/**
	 * Adds shortcode support to category descriptions.
	 *
	 * @param string $desc String to add shortcodes in.
	 *
	 * @return string
	 */
	public function custom_category_descriptions_add_shortcode_support( $desc ) {
		// Wrap in output buffering to prevent shortcodes that echo stuff instead of return from breaking things.
		ob_start();
		$desc = do_shortcode( $desc );
		ob_end_clean();

		return $desc;
	}

	/**
	 * Determines the scope based on the current taxonomy.
	 * This can be used by the replacevar plugin to determine if a replacement needs to be executed.
	 *
	 * @return string String decribing the current scope.
	 */
	private function determine_scope() {
		$taxonomy = $this->get_taxonomy();

		if ( $taxonomy === 'category' ) {
			return 'category';
		}

		if ( $taxonomy === 'post_tag' ) {
			return 'tag';
		}

		return 'term';
	}

	/**
	 * @param string $page The string to check for the term overview page.
	 *
	 * @return bool
	 */
	public static function is_term_overview( $page ) {
		return 'edit-tags.php' === $page;
	}

	/**
	 * @param string $page The string to check for the term edit page.
	 *
	 * @return bool
	 */
	public static function is_term_edit( $page ) {
		return 'term.php' === $page
		       || 'edit-tags.php' === $page; // After we drop support for <4.5 this can be removed.
	}

	/**
	 * Getting the taxonomy from the URL
	 *
	 * @return string
	 */
	private function get_taxonomy() {
		return filter_input( INPUT_GET, 'taxonomy', FILTER_DEFAULT, array( 'options' => array( 'default' => '' ) ) );
	}
}/* End of class MASB_SEO_Taxonomy */

class MASB_SEO_Primary_Term {

	/**
	 * @var string
	 */
	protected $taxonomy_name;

	/**
	 * @var int
	 */
	protected $post_ID;

	/**
	 * @var int
	 */
	protected $options;

	/**
	 * The taxonomy this term is part of
	 *
	 * @param string $taxonomy_name Taxonomy name for the term.
	 * @param int $post_id Post ID for the term.
	 */
	public function __construct( $taxonomy_name, $post_id, $options ) {
		$this->taxonomy_name = $taxonomy_name;
		$this->post_ID       = $post_id;
		$this->options       = $options;
	}

	/**
	 * Returns the primary term ID
	 *
	 * @return int|bool
	 */
	public function get_primary_term() {
		$primary_term = get_post_meta( $this->post_ID,
			$this->options->get_meta()->meta_prefix . 'primary_' . $this->taxonomy_name,
			true );

		$terms = $this->get_terms();

		if ( ! in_array( $primary_term, wp_list_pluck( $terms, 'term_id' ) ) ) {
			$primary_term = false;
		}

		$primary_term = (int) $primary_term;

		return ( $primary_term ) ? ( $primary_term ) : false;
	}

	/**
	 * Sets the new primary term ID
	 *
	 * @param int $new_primary_term New primary term ID.
	 */
	public function set_primary_term( $new_primary_term ) {
		update_post_meta( $this->post_ID,
			$this->options->get_meta()->meta_prefix . 'primary_' . $this->taxonomy_name,
			$new_primary_term );
	}

	/**
	 * Get the terms for the current post ID.
	 * When $terms is not an array, set $terms to an array.
	 *
	 * @return array
	 */
	protected function get_terms() {
		$terms = get_the_terms( $this->post_ID, $this->taxonomy_name );

		if ( ! is_array( $terms ) ) {
			$terms = array();
		}

		return $terms;
	}
}/* End of class MASB_SEO_Primary_Term */

add_action( 'after_setup_theme', array( 'MASB_SEO', 'init' ), 20 );