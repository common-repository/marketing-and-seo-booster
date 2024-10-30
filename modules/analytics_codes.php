<?php
/*
* Create submenu page.
*/
add_action( 'admin_menu', 'masb_analytics_codes_submenu', 20 );
add_action( 'wp_head', 'masb_analytics_codes_enqueue', 999 );

function masb_analytics_codes_submenu() {
	add_submenu_page( 'marketing-options',
		esc_html__( 'Analytics Codes', 'marketing-and-seo-booster' ),
		esc_html__( 'Analytics Codes', 'marketing-and-seo-booster' ),
		'manage_options',
		'analytics_codes',
		'masb_analytics_codes_page' );

	add_action( 'admin_init', 'masb_analytics_codes_settings' );
}

function masb_analytics_codes_page() {
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$settings = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

	if ( false === $settings ) {
		return;
	}
	wp_add_inline_script( 'code-editor',
		sprintf( 'jQuery( function() { wp.codeEditor.initialize( "masb-analytics-codes", %s ); } );',
			wp_json_encode( $settings ) ) );
	echo '<div class="wrap">
        <h1>' . get_admin_page_title() . '</h1>
        <div class="masb-marketing-notice">
        <p>' . sprintf( esc_html__( 'Here you can insert the codes of your analytical systems. But we recommend using %shttps://segment.com%s to connect all your analytics systems, advetising accounts, AB test and marketing funnel tools in 1 place.',
			'marketing-and-seo-booster' ),
			'<a href="https://segment.com" rel="nofollow" target="_blank">',
			'</a>' ) . '</p>
        </div>
        <form action="options.php" method="POST">';
	settings_fields( 'analytics-codes-secr' );
	do_settings_sections( 'analytics_codes_opts' );
	submit_button();
	echo '</form>
    </div>';
}

function masb_analytics_codes_settings() {
	register_setting( 'analytics-codes-secr', 'masb_analytics_codes', 'masb_analytics_codes_saving' );
	add_settings_section( 'masb_analytics_codes_settings', '', '', 'analytics_codes_opts' );
	add_settings_field( 'masb_analytics_codes',
		esc_html__( 'Analytics Codes:', 'marketing-and-seo-booster' ),
		'masb_analytics_codes',
		'analytics_codes_opts',
		'masb_analytics_codes_settings' );
}

function masb_analytics_codes() {
	/**
	 * @todo Delete checking and deleting after several months from 07.2019
	 */
	$codes = get_option( 'sst_analytics_codes' );
	if ( false === $codes ) {
		$codes = get_option( 'masb_analytics_codes' );
	}
	$codes = $codes ? $codes : null;

	echo '<textarea id="masb-analytics-codes" cols="70" rows="30" name="masb_analytics_codes">' . $codes . '</textarea>';
}

function masb_analytics_codes_saving( $options ) {
	$options = preg_replace( '/<\\?.*(\\?>|$)/Us', '', wp_unslash( $options ) );
	/**
	 * @todo Delete next after several months from 07.2019
	 */
	delete_option( 'sst_analytics_codes' );

	return $options;
}

function masb_analytics_codes_enqueue() {
	/**
	 * @todo Delete checking and deleting after several months from 07.2019
	 */
	$codes = get_option( 'sst_analytics_codes' );
	if ( false === $codes ) {
		$codes = get_option( 'masb_analytics_codes' );
	}
	if ( empty( $codes ) || is_admin() ) {
		return;
	} else {
		echo $codes;
	}
}
