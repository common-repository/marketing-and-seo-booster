<?php
/*
* Create submenu page.
*/
add_action( 'admin_menu', 'masb_abcs_submenu', 25 );
add_action( 'wp_ajax_masb_abcs_add_new_test', 'masb_abcs_add_new_test' );
add_action( 'wp_ajax_masb_abcs_clear_stats', 'masb_abcs_clear_stats' );
add_action( 'admin_enqueue_scripts', 'masb_analytics_localize_script', 20 );

add_action( 'init', 'masb_abcs_redir' );

function masb_abcs_submenu() {
	add_submenu_page( 'marketing-options',
		esc_html__( 'ABC split', 'marketing-and-seo-booster' ),
		esc_html__( 'ABC split test', 'marketing-and-seo-booster' ),
		'manage_options',
		'abc-split',
		'masb_abcs_options_page' );

	add_action( 'admin_init', 'masb_abcs_settings' );
}

function masb_abcs_options_page() {
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	wp_localize_script( 'masb_admin_js',
		'abc_split',
		array(
			'nonce' => wp_create_nonce( 'masb_nonce' ),
		)
	);

	echo '<div class="wrap">
        <h1>' . get_admin_page_title() . '</h1>
        <div class="masb-marketing-notice">
        <p>' . esc_html__( 'Split testing (also referred to as A/B testing or multivariate testing) is a method of conducting controlled, randomized experiments 
        with the goal of improving a website metric, such as clicks, form completions, or purchases. Incoming traffic to the website is distributed between the original 
        (control) and the different variations without any of the visitors knowing that they are part of an experiment. The tester waits for a statistically 
        significant difference in behavior to emerge. The results from each variation are compared to determine which version showed the greatest improvement.',
			'marketing-and-seo-booster' ) . '</p>
        <p><b>' . esc_html__( 'Compare performance of these pages in your web analytics system.',
			'marketing-and-seo-booster' ) . '</b></p>
        </div>
        <form action="options.php" method="POST">';
	settings_fields( 'abc_split_secr' );
	/**
	 * @todo Delete checking and deleting after several months from 07.2019
	 */
	$sections = get_option( 'sst_abc_split' );
	if ( false === $sections ) {
		$sections = get_option( 'masb_abc_split' );
	}
	if ( is_array( $sections ) ) {
		foreach ( $sections as $n => $section ) {
			echo masb_abcs_section( $n );
		}
	} else {
		echo masb_abcs_section();
	}
	echo '<button id="masb-abcs-add-new-test">' . esc_html__( 'One more test',
			'marketing-and-seo-booster' ) . '</button>';
	submit_button();
	echo '</form>
    </div>';
}

/**
 * Settings registration.
 * All settings in one array.
 */
function masb_abcs_settings() {
	register_setting( 'abc_split_secr', 'masb_abc_split', 'masb_abcs_opt_saving' );
}

function masb_abcs_slug( $n = 0 ) {
	/**
	 * @todo Delete checking and deleting after several months from 07.2019
	 */
	$slug = get_option( 'sst_abc_split' );
	if ( false === $slug ) {
		$slug = get_option( 'masb_abc_split' );
	}
	$slug = is_array( $slug ) ? $slug[ $n ]['slug'] : null;
	$link = ! empty( $slug ) ? '<p>' . home_url( '/' ) . $slug . '</p>' : '';

	return '<div class="row">
        <div class="col-md-3 col-sm-12"><h3>' . esc_html__( 'Slug for redirect: use it for incoming traffic',
			'marketing-and-seo-booster' ) . '</h3></div>
        <div class="col-md-9 col-sm-12"><input class="masb-abc-split-slug" type="text" name="masb_abc_split[' . $n . '][slug]" value="' . esc_attr( $slug ) . '" data-test-count="' . $n . '" />' . $link . '</div>
    </div>';
}

function masb_abcs_rpages( $n = 0 ) {
	/**
	 * @todo Delete checking and deleting after several months from 07.2019
	 */
	$rpages = get_option( 'sst_abc_split' );
	if ( false === $rpages ) {
		$rpages = get_option( 'masb_abc_split' );
	}
	$rpages = is_array( $rpages ) ? $rpages[ $n ]['rpages'] : null;
	$args   = array(
		'depth'                 => 0,
		'child_of'              => 0,
		'selected'              => 0,
		'echo'                  => 1,
		'name'                  => 'page_id',
		'id'                    => '',
		'class'                 => '',
		'show_option_none'      => '',
		'show_option_no_change' => '',
		'option_none_value'     => '',
		'value_field'           => 'ID',
	);
	$pages  = get_pages( $args );
	$args   = array(
		'numberposts'      => - 1,
		'category'         => 0,
		'orderby'          => 'date',
		'order'            => 'DESC',
		'include'          => array(),
		'exclude'          => array(),
		'meta_key'         => '',
		'meta_value'       => '',
		'post_type'        => array( 'post', ),
		'suppress_filters' => true,
	);
	$pages  = array_merge( $pages, get_posts( $args ) );

	return '<div class="row">
        <div class="col-md-3 col-sm-12"><h3>' . esc_html__( 'Pages to redirect', 'marketing-and-seo-booster' ) . '</h3></div>
        <div class="col-md-5 col-sm-12">' .
	       masb_abcs_dropdown( $pages,
		       array(
			       'name'              => 'masb_abc_split[' . $n . '][rpages][0]',
			       'echo'              => 0,
			       'id'                => 'masb-abcs-rpages-' . $n . '-0',
			       'show_option_none'  => esc_html__( '&mdash; Select &mdash;' ),
			       'option_none_value' => '0',
			       'selected'          => ( ! empty( $rpages[0] ) ? $rpages[0] : 0 ),
		       ) ) .
	       masb_abcs_dropdown( $pages,
		       array(
			       'name'              => 'masb_abc_split[' . $n . '][rpages][1]',
			       'echo'              => 0,
			       'id'                => 'masb-abcs-rpages-' . $n . '-1',
			       'show_option_none'  => esc_html__( '&mdash; Select &mdash;' ),
			       'option_none_value' => '0',
			       'selected'          => ( ! empty( $rpages[1] ) ? $rpages[1] : 0 ),
		       ) ) .
	       masb_abcs_dropdown( $pages,
		       array(
			       'name'              => 'masb_abc_split[' . $n . '][rpages][2]',
			       'echo'              => 0,
			       'id'                => 'masb-abcs-rpages-' . $n . '-2',
			       'show_option_none'  => esc_html__( '&mdash; Select &mdash;' ),
			       'option_none_value' => '0',
			       'selected'          => ( ! empty( $rpages[2] ) ? $rpages[2] : 0 ),
		       ) ) .
	       masb_abcs_dropdown( $pages,
		       array(
			       'name'              => 'masb_abc_split[' . $n . '][rpages][3]',
			       'echo'              => 0,
			       'id'                => 'masb-abcs-rpages-' . $n . '-3',
			       'show_option_none'  => esc_html__( '&mdash; Select &mdash;' ),
			       'option_none_value' => '0',
			       'selected'          => ( ! empty( $rpages[3] ) ? $rpages[3] : 0 ),
		       ) ) .
	       '</div>
        <div class="col-md-4 col-sm-12">' .
	       masb_abcs_statistic_sect( $n ) .
	       '</div>
    </div>';
}

function masb_abcs_opt_saving( $options ) {
	if ( is_array( $options ) ) {
		$options = array_slice( $options, 0 );
		foreach ( $options as &$option ) {
			if ( ! empty( $option['slug'] ) && ! empty( $option['rpages'] ) ) {
				$option['slug'] = sanitize_title_for_query( trim( str_replace( get_home_url(), '', $option['slug'] ),
					' \/' ) );
				foreach ( $option['rpages'] as $n => $rpage ) {
					if ( $rpage == 0 ) {
						unset( $option['rpages'][ $n ] );
					}
				}
			} else {
				$options = '';
			}
			unset( $option );
		}
	} else {
		$options = '';
	}
	/**
	 * @todo Delete next after several months from 07.2019
	 */
	delete_option( 'sst_abc_split' );

	return $options;
}

function masb_abcs_dropdown( $pages, $args = '' ) {
	$defaults = array(
		'depth'                 => 0,
		'child_of'              => 0,
		'selected'              => 0,
		'echo'                  => 1,
		'name'                  => 'page_id',
		'id'                    => '',
		'class'                 => '',
		'show_option_none'      => '',
		'show_option_no_change' => '',
		'option_none_value'     => '',
		'value_field'           => 'ID',
	);
	$r        = wp_parse_args( $args, $defaults );
	$output   = '';

	// Back-compat with old system where both id and name were based on $name argument
	if ( empty( $r['id'] ) ) {
		$r['id'] = $r['name'];
	}
	if ( ! empty( $pages ) ) {
		$class = '';
		if ( ! empty( $r['class'] ) ) {
			$class = " class='" . esc_attr( $r['class'] ) . "'";
		}
		$output = '<div><select name="' . esc_attr( $r['name'] ) . '"' . $class . ' id="' . esc_attr( $r['id'] ) . '" class="masb-abcs-rpages">';
		if ( $r['show_option_no_change'] ) {
			$output .= '<option value="-1">' . $r['show_option_no_change'] . '</option>';
		}
		if ( $r['show_option_none'] ) {
			$output .= '<option value="' . esc_attr( $r['option_none_value'] ) . '">' . $r['show_option_none'] . '</option>';
		}
		$output .= walk_page_dropdown_tree( $pages, $r['depth'], $r );
		$output .= '</select></div>';
	}
	$html = apply_filters( 'ssc_dropdown', $output, $r, $pages );

	if ( $r['echo'] ) {
		echo $html;
	}

	return $html;
}

function masb_abcs_section( $n = 0 ) {
	return '<div class="masb-abcs-section">
        <br />' .
	       masb_abcs_slug( $n ) .
	       masb_abcs_rpages( $n ) .
	       '<button class="masb-abcs-rem-test" data-section="' . $n . '">' . esc_html__( 'Remove this test',
			'marketing-and-seo-booster' ) . '</button>
    <hr /></div>';
}

function masb_abcs_statistic_sect( $n = 0 ) {
	/**
	 * @todo Delete checking and deleting after several months from 07.2019
	 */
	$rpages = get_option( 'sst_abc_split' );
	if ( false === $rpages ) {
		$rpages = get_option( 'masb_abc_split' );
	}
	$rpages    = is_array( $rpages ) ? $rpages[ $n ]['rpages'] : null;
	$out_pages = '';
	$all_stat  = 0;

	if ( is_array( $rpages ) ) {
		$i = 1;
		foreach ( $rpages as $rpage ) {
			if ( $rpage != 0 ) {
				$stat      = get_post_meta( $rpage, 'masb_abcs_stat', true );
				$stat      = $stat ? $stat : 0;
				$out_pages .= '<div>Redirected to page #' . $i . ': ' . $stat . '</div>';
				$i ++;
				$all_stat += $stat;
			}
		}
	}

	return '<div>Incoming traffic: ' . $all_stat . '</div>' . $out_pages . '<div><button class="masb-abcs-clear-stats" data-stats="' . $n . '">' . esc_html__( 'Clear stats',
			'marketing-and-seo-booster' ) . '</button></div>';
}

function masb_abcs_add_new_test() {
	check_ajax_referer( 'masb_nonce', 'masb_nonce' );
	$test_count = filter_input( INPUT_POST, 'test_count', FILTER_VALIDATE_INT );
	if ( is_int( $test_count ) ) {
		echo masb_abcs_section( ( $test_count ) );
	} else {
		echo '';
	}
	wp_die();
}

function masb_abcs_clear_stats() {
	check_ajax_referer( 'masb_nonce', 'masb_nonce' );
	$stats_count = filter_input( INPUT_POST, 'stats_count', FILTER_VALIDATE_INT );
	if ( is_int( $stats_count ) ) {
		/**
		 * @todo Delete checking and deleting after several months from 07.2019
		 */
		$rpages = get_option( 'sst_abc_split' );
		if ( false === $rpages ) {
			$rpages = get_option( 'masb_abc_split' );
		}
		$rpages = is_array( $rpages ) ? $rpages[ $stats_count ]['rpages'] : null;
		if ( is_array( $rpages ) ) {
			foreach ( $rpages as $rpage ) {
				if ( $rpage != 0 ) {
					delete_post_meta( $rpage, 'masb_abcs_stat' );
				}
			}
		}
		echo masb_abcs_statistic_sect( $stats_count );
	} else {
		echo '';
	}
	wp_die();
}

function masb_abcs_redir() {
	/**
	 * @todo Delete checking and deleting after several months from 07.2019
	 */
	$masb_abc_tests = get_option( 'sst_abc_split' );
	if ( false === $masb_abc_tests ) {
		$masb_abc_tests = get_option( 'masb_abc_split' );
	}
	if ( empty( $masb_abc_tests ) || ! is_array( $masb_abc_tests ) ) {
		return;
	} else {
		foreach ( $masb_abc_tests as $n => $masb_abc_s ) {
			if ( empty( $masb_abc_s['slug'] ) || empty( $masb_abc_s['rpages'] ) ) {
				return;
			} else {
				if ( strtok( filter_input( INPUT_SERVER, 'REQUEST_URI' ),
						'?' ) == '/' . $masb_abc_s['slug'] || strtok( filter_input( INPUT_SERVER, 'REQUEST_URI' ),
						'?' ) == '/' . $masb_abc_s['slug'] . '/' && ! masb_abcs_is_bot( filter_input( INPUT_SERVER,
						'HTTP_USER_AGENT' ) ) ) {
					$query = stristr( filter_input( INPUT_SERVER, 'REQUEST_URI' ),
						'?' ) ? stristr( filter_input( INPUT_SERVER, 'REQUEST_URI' ), '?' ) : '';
					$stats_count = filter_input( INPUT_COOKIE, 'masb_abcs_rpage_' . $n, FILTER_VALIDATE_INT );
					if ( is_int( $stats_count ) ) {
						$rpage = $stats_count;
					} else {
						$rpages = $masb_abc_s['rpages'];
						$rpage  = $rpages [ mt_rand( 0, count( $rpages ) - 1 ) ];
						setcookie( 'masb_abcs_rpage_' . $n,
							$rpage,
							15 * 3600 * 24 + time(),
							COOKIEPATH,
							COOKIE_DOMAIN );
					}
					$stat = get_post_meta( $rpage, 'masb_abcs_stat', true );
					$stat = $stat ? $stat + 1 : 1;
					update_post_meta( $rpage, 'masb_abcs_stat', $stat );
					wp_safe_redirect( get_permalink( $rpage ) . $query, 302 );
					exit;
				}
			}
		}
	}
}

function masb_abcs_is_bot( $botname = '' ) {
	$bots = array(
		'rambler',
		'googlebot',
		'aport',
		'yahoo',
		'msnbot',
		'turtle',
		'mail.ru',
		'omsktele',
		'yetibot',
		'picsearch',
		'sape.bot',
		'sape_context',
		'gigabot',
		'snapbot',
		'alexa.com',
		'megadownload.net',
		'askpeter.info',
		'igde.ru',
		'ask.com',
		'qwartabot',
		'yanga.co.uk',
		'scoutjet',
		'similarpages',
		'oozbot',
		'shrinktheweb.com',
		'aboutusbot',
		'followsite.com',
		'dataparksearch',
		'google-sitemaps',
		'appEngine-google',
		'feedfetcher-google',
		'liveinternet.ru',
		'xml-sitemaps.com',
		'agama',
		'metadatalabs.com',
		'h1.hrn.ru',
		'googlealert.com',
		'seo-rus.com',
		'yaDirectBot',
		'yandeG',
		'yandex',
		'yandexSomething',
		'Copyscape.com',
		'AdsBot-Google',
		'domaintools.com',
		'Nigma.ru',
		'bing.com',
		'dotnetdotcom',
	);

	foreach ( $bots as $bot ) {
		if ( stripos( $botname, $bot ) !== false ) {
			return true;
		}
	}

	return false;
}

function masb_analytics_localize_script() {
	wp_localize_script( 'masb_admin_js',
		'masb_abcs',
		array(
			'same_page' => esc_html__( 'You selected the same page, select different pages.', 'marketing-and-seo-booster' ),
		) );
}