<?php

class MASB_Portfolio_Post_Type {

	private $type = 'portfolio';
	private $slug;
	private $category_slug = 'portfolio';
	private $name;

	public function __construct() {
		// Register the post type
		$this->name = __( 'Portfolio', 'marketing-and-seo-booster' );
		global $secretlab;
		if ( isset ( $secretlab['portfolio_slug'] ) ) {
			$this->slug = $secretlab['portfolio_slug'];
		} else {
			$this->slug = 'portfolio';
		}

		add_action( 'init', array( $this, 'init' ), 98 );
		add_action( 'init', array( $this, 'register_post_types_to_kc' ), 99 );

	}

	public function init() {
		$this->register_post_types();
		$this->register_taxonomy();
		$this->register_taxonomy_tag();
	}

	private function register_taxonomy() { // Second part of taxonomy name

		$labels = array(
			'name'              => sprintf( __( '%s Categories', 'marketing-and-seo-booster' ), $this->name ),
			'menu_name'         => sprintf( __( '%s Categories', 'marketing-and-seo-booster' ), $this->name ),
			'singular_name'     => sprintf( __( '%s Category', 'marketing-and-seo-booster' ), $this->name ),
			'search_items'      => sprintf( __( 'Search %s Categories', 'marketing-and-seo-booster' ), $this->name ),
			'all_items'         => sprintf( __( 'All %s Categories', 'marketing-and-seo-booster' ), $this->name ),
			'parent_item'       => sprintf( __( 'Parent %s Category', 'marketing-and-seo-booster' ), $this->name ),
			'parent_item_colon' => sprintf( __( 'Parent %s Category:', 'marketing-and-seo-booster' ), $this->name ),
			'new_item_name'     => sprintf( __( 'New %s Category Name', 'marketing-and-seo-booster' ), $this->name ),
			'add_new_item'      => sprintf( __( 'Add New %s Category', 'marketing-and-seo-booster' ), $this->name ),
			'edit_item'         => sprintf( __( 'Edit %s Category', 'marketing-and-seo-booster' ), $this->name ),
			'update_item'       => sprintf( __( 'Update %s Category', 'marketing-and-seo-booster' ), $this->name ),
		);
		$args   = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => $this->slug . '-' . $this->category_slug ),
		);
		register_taxonomy( $this->type . '-' . $this->category_slug, array( $this->type ), $args );
	}

	private function register_taxonomy_tag() { // Second part of taxonomy name

		$labels = array(
			'name'              => sprintf( __( '%s Tags', 'marketing-and-seo-booster' ), $this->name ),
			'menu_name'         => sprintf( __( '%s Tags', 'marketing-and-seo-booster' ), $this->name ),
			'singular_name'     => sprintf( __( '%s Tag', 'marketing-and-seo-booster' ), $this->name ),
			'popular_items'     => sprintf( __( '%s Popular Tags', 'marketing-and-seo-booster' ), $this->name ),
			'search_items'      => sprintf( __( '%s Search Tag', 'marketing-and-seo-booster' ), $this->name ),
			'all_items'         => sprintf( __( '%s All Tags', 'marketing-and-seo-booster' ), $this->name ),
			'parent_item'       => null,
			'parent_item_colon' => null,
			'new_item_name'     => sprintf( __( '%s New Tag Name', 'marketing-and-seo-booster' ), $this->name ),
			'add_new_item'      => sprintf( __( '%s Add New Tag', 'marketing-and-seo-booster' ), $this->name ),
			'edit_item'         => sprintf( __( '%s Edit Tag', 'marketing-and-seo-booster' ), $this->name ),
			'update_item'       => sprintf( __( '%s Update Tag', 'marketing-and-seo-booster' ), $this->name ),
		);
		$args   = array(
			'labels'                => $labels,
			'hierarchical'          => false,
			'update_count_callback' => '_update_post_term_count',
			'show_ui'               => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => $this->slug . '-tag' ),
		);
		register_taxonomy( $this->type . '_tag', array( $this->type ), $args );
	}

	function register_post_types_to_kc() {
		global $kc;
		if ( isset( $kc ) ) {
			$kc->add_content_type( array(
				'portfolio',
				'composer_widget',
				'product',
				'modal_window',
			) );
		}
	}

	function register_post_types() {
		register_post_type( 'portfolio',
			array(
				'label'               => 'Portfolio',
				'labels'              => array(
					'name'               => esc_html__( 'Portfolio', 'marketing-and-seo-booster' ),
					'singular_name'      => esc_html__( 'Portfolio', 'marketing-and-seo-booster' ),
					'add_new'            => esc_html__( 'Add New', 'marketing-and-seo-booster' ),
					'add_new_item'       => esc_html__( 'Add New Portfolio Item', 'marketing-and-seo-booster' ),
					'edit_item'          => esc_html__( 'Edit Portfolio Item', 'marketing-and-seo-booster' ),
					'new_item'           => esc_html__( 'New Portfolio Item', 'marketing-and-seo-booster' ),
					'view_item'          => esc_html__( 'View Portfolio Item', 'marketing-and-seo-booster' ),
					'search_items'       => esc_html__( 'Search Portfolio Item', 'marketing-and-seo-booster' ),
					'not_found'          => esc_html__( 'No portfolio found.', 'marketing-and-seo-booster' ),
					'not_found_in_trash' => esc_html__( 'No portfolio found in Trash.', 'marketing-and-seo-booster' ),
					'parent_item_colon'  => '',
					'menu_name'          => esc_html__( 'Portfolio', 'marketing-and-seo-booster' ),
				),
				'description'         => '',
				'public'              => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'show_in_rest'        => false,
				'rest_base'           => true,
				'menu_position'       => 27,
				'menu_icon'           => 'dashicons-portfolio',
				'hierarchical'        => true,
				'supports'            => array( 'title', 'editor', 'thumbnail', 'comments', 'revisions', 'excerpt' ),
				// 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
				'taxonomies'          => array( 'post_tag', 'category', $this->slug . '-' . $this->category_slug ),
				'has_archive'         => $this->slug,
				'rewrite'             => array( 'slug' => $this->slug, 'with_front' => true ),
				'query_var'           => true,
			)
		);
	}
}

new MASB_Portfolio_Post_Type();


//function masb_show_cpt_archives( $query ) {
//	if( is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) {
//		var_dump( $query->query_vars[ 'post_type' ] );
//		$query->set( 'post_type', array(
//			'post', 'nav_menu_item', 'portfolio'
//		));
//		return $query;
//	}
//}
//add_filter( 'pre_get_posts', 'masb_show_cpt_archives' );