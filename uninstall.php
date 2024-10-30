<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option( 'masb_abc_split' );
delete_option( 'masb_analytics_codes' );