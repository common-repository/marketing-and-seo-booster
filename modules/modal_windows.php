<?php
/*
 Show the content of a custom post of the type 'modal_window' ( it can be made with King Composer Plugin ) in a widget or with a shortcode.
 Version: 1.2

*/




// Launch the plugin.
function masb_modw_widget_plugin_init() {
    add_action( 'widgets_init', 'masb_modw_widget_load_widgets' );
}
add_action( 'after_setup_theme', 'masb_modw_widget_plugin_init', 20 );

// Loads the widgets packaged with the plugin.
function masb_modw_widget_load_widgets() {
    // Add featured image support
    if ( function_exists( 'add_theme_support' ) ) {
        add_theme_support( 'post-thumbnails' );
    }

// Create the Modal Window custom post type
    function masb_modw_post_type_init() {
        $labels = array(
            'name' => esc_html__( 'Modal Window', 'post type general name', 'marketing-and-seo-booster' ),
            'singular_name' => esc_html__( 'Modal Window', 'post type singular name', 'marketing-and-seo-booster' ),
            'plural_name' => esc_html__( 'Modal Window', 'post type plural name', 'marketing-and-seo-booster' ),
            'add_new' => esc_html__( 'Add a Modal Window', 'block', 'marketing-and-seo-booster' ),
            'add_new_item' => esc_html__( 'Add a New Modal Window', 'marketing-and-seo-booster' ),
            'edit_item' => esc_html__( 'Edit Modal Window', 'marketing-and-seo-booster' ),
            'new_item' => esc_html__( 'New Modal Window', 'marketing-and-seo-booster' ),
            'view_item' => esc_html__( 'View Modal Window', 'marketing-and-seo-booster' ),
            'search_items' => esc_html__( 'Search Modal Window', 'marketing-and-seo-booster' ),
            'not_found' =>  esc_html__( 'No Modal Window Found', 'marketing-and-seo-booster' ),
            'not_found_in_trash' => esc_html__( 'No Modal Window found in Trash', 'marketing-and-seo-booster' )
        );
        //$content_block_public = false; // added to make this a filterable option
        $content_block_public = current_user_can('manage_options') ? true : false;
        $options = array(
            'labels' => $labels,
            'public' => apply_filters( 'modal_window_post_type', $content_block_public ),
            'publicly_queryable' => $content_block_public,
            'exclude_from_search' => true,
            'show_ui' => true,
//            'show_in_menu' => 'marketing-options',
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'menu_icon' => 'dashicons-chart-area',
            'supports' => array( 'title','editor','revisions','author' )
        );
        if( current_user_can( 'activate_plugins' ) ){
            $options['show_in_menu'] = 'marketing-options';
        }
        register_post_type( 'modal_window', $options );
    }
    add_action( 'init', 'masb_modw_post_type_init' );

// Add the ability to display the content block in a reqular post using a shortcode
    function masb_modw_widget_shortcode( $atts ) {
        extract( shortcode_atts( array(
            'id' => '',
            'slug' => '',
            'class' => 'content_block',
            'title' => 'no',
            'title_tag' => 'h3'
        ), $atts ) );

        if ( $slug ) {
            $block = get_page_by_path( $slug, OBJECT, 'modal_window' );
            if ( $block ) {
                $id = $block->ID;
            }
        }

        $content = "";

        if( $id != "" ) {
            $args = array(
                'post__in' => array( $id ),
                'post_type' => 'modal_window',
            );

            $content_post = get_posts( $args );

            foreach( $content_post as $post ) :
                $content .= '<div class="'. esc_attr($class) .'" id="modal_window_widget-' . $id . '">';
                if ( $title === 'yes' ) {
                    $content .= '<' . esc_attr( $title_tag ) . '>' . $post->post_title . '</' . esc_attr( $title_tag ) . '>';
                }

                $content .= $post->post_content;

                $content .= '</div>';
            endforeach;
        }

        return $content;
    }
    add_shortcode( 'modal_window', 'masb_modw_widget_shortcode' );

}

function masb_modw_filter_widget_init() {
    $modal_window_public = true;
    return $modal_window_public;
}
add_filter('modal_window_post_type','masb_modw_filter_widget_init');

function masb_modw_add_meta_box() {
    add_meta_box(
        'modw_sectionid',
        esc_html__( 'Select Modal Window Type', 'marketing-and-seo-booster' ),
        'modw_meta_box',
        'modal_window',
        'side'
    );
}

function masb_modw_modify_post_table_row( $column_name, $post_id ) {
    $custom_fields = get_post_custom( $post_id );
    switch ( $column_name ) {
        case 'content_block_information' :
            if ( !empty( $custom_fields['_content_block_information'][0] ) ) {
                echo $custom_fields['_content_block_information'][0];
            }
            break;
    }
}
add_action( 'manage_posts_custom_column', 'masb_modw_modify_post_table_row', 10, 2 );

function masb_modw_get_content_css( $post ) {

    global $post, $kc;

    $post_css = '';
    $post_data = get_post_meta ($post->ID , 'kc_data', true);
    $settings = $kc->settings();

    if (!empty($post_data) && !empty($post_data['css']))
        $post_css .= $post_data['css'];

    if (!empty( $settings['css_code']))
        $post_css .= $settings['css_code'];

    if (!empty($post_data) && isset($post_data['max_width']) && !empty($post_data['max_width']))
        $post_css .= '.kc-container{max-width: '.esc_attr($post_data['max_width']).';}';
    else if (!empty($settings['max_width']) && isset($settings['max_width']) && !empty($settings['max_width']))
        $post_css .= '.kc-container{max-width: '.esc_attr($settings['max_width']).';}';

    $post_css = esc_html ($post_css);
    $post_css = str_replace(
        array( "\n","  ", ": ", " {", "  ", '&gt;', '&lt;', '&quot;', '&#039;', "</style>", "<style", "<script", "</script"),
        array( '', ' ', ':', '{', " ", '>', '<', '"', "'", "&lt;/style&quot;", "&lt;style", "&lt;script", "&lt;/script"),
        $post_css
    );

    $post_css = '<style type="text/css" id="kc-css-general">.kc-off-notice{display: inline-block !important;}'.$post_css.'</style>';

    /*
    *	Start render CSS of all elements
    */


    $post_css = str_replace(
        array( "\n","  ", ": ", " {", "  ", '&gt;', '&lt;', '&quot;', '&#039;', "</style>", "<style", "<script", "</script"),
        array( '', ' ', ':', '{', " ", '>', '<', '"', "'", "&lt;/style&quot;", "&lt;style", "&lt;script", "&lt;/script"),
        $post_css
    );
    return $post_css;
}