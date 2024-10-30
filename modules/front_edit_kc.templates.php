<?php
/**
*
*	King Composer
*	(c) KingComposer.com
*
*/
if(!defined('KC_FILE')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

$kc = KingComposer::globe();
$pdk = $kc->get_pdk();
$kc_maps = $kc->get_maps();
$id = !empty($_GET['id']) ? esc_attr($_GET['id']) : '';
$post = get_post($id);
?>
<script type="text/javascript">
	jQuery('#wpadminbar,#wpfooter,#adminmenuwrap,#adminmenuback,#adminmenumain,#screen-meta').remove();
</script>
<div id="kc-preload">
	<h3 class="mesg loading">
		<span class="kc-loader"></span>
		<br /><?php _e('Loading', 'kingcomposer'); ?>
	</h3>
</div>
<div id="kc-welcome" class="kc-preload-body hidden">
		<h3><?php printf( esc_html__( 'Welcome to %sKingComposer Live Editor ', 'kingcomposer' ), '<br />' ); ?></h3>
		<ul>
			<?php if( $pdk['pack'] == 'trial' ){ ?>

			<?php if( $pdk['date'] > time() ){ ?>

				<li class="notice">
					<?php _e('You are using the trial version.', 'kingcomposer'); ?>
					 (<?php echo round( ($pdk['date']-time())/86400); ?> <?php _e(' days left', 'kingcomposer'); ?>)
					<br />
					<a href="#" class="verify"><?php _e('Activate License', 'kingcomposer'); ?></a> or
					<a href="#" class="enter"><?php _e('Continue Trial', 'kingcomposer'); ?> <i class="fa-long-arrow-right"></i></a>
				</li>

			<?php }else{ ?>

				<li class="notice">
					<?php _e('Your free 7-day trial has expired.<br />Please submit license key to use full features of Pro version.', 'kingcomposer'); ?>
					<br />
					<br />
					<a href="#" class="button verify"><?php _e('Activate License Now', 'kingcomposer'); ?> <i class="fa-key"></i></a>
					<br />
				</li>

			<?php } ?>

			<?php } ?>
			<li><a href="http://docs.kingcomposer.com" target=_blank><i class="sl-arrow-right"></i> <?php _e('Check the documentation', 'kingcomposer'); ?></a></li>
			<li><a href="https://kingcomposer.com/contact/" target=_blank><i class="sl-arrow-right"></i> <?php _e('Send us your feedback', 'kingcomposer'); ?></a></li>
			<li><a href="http://bit.ly/kcdiscuss" target=_blank><i class="sl-arrow-right"></i> <?php _e('Join our group discussion if you need help', 'kingcomposer'); ?></a></li>
		</ul>
		<a href="#" class="enter close">
			<i class="sl-close" aria-hidden="true"></i>
		</a>
		<div id="kc-preload-footer">
			<button class="button nope gray left"><?php _e('Don\'t show again', 'kingcomposer'); ?></button>
			<button class="button tour right">
				<?php _e('Watch the quick video', 'kingcomposer'); ?>
			</button>
		</div>
	</div>
<div id="wpadminbar">
    <a class="screen-reader-shortcut" href="#wp-toolbar" tabindex="1"><?php _e('Skip to toolbar', 'kingcomposer'); ?></a>
    <div class="quicklinks" id="kc-top-nav">
        <ul class="ab-top-menu">
            <li id="kc-bar-logo" class="menupop">
            	<a class="ab-item" title="<?php _e('Visit the KingComposer\'s home page', 'kingcomposer'); ?>" target=_blank href="http://KingComposer.com">
	            	<img src="<?php echo KC_URL; ?>/assets/images/logo_white.png" height="25" />
	            </a>
            </li>
            <li class="kc-curent-editing">
            	<?php
	            	echo $post->post_type.': '.wp_trim_words($post->post_title, 4);
            	?>
            </li>
            <li id="kc-content-settings" class="mtips">
            	<i class="fa-cog"></i>
            	<span class="mt-mes"><?php _e('Content settings', 'kingcomposer'); ?></span>
            </li>
            <li id="masb-front-page-options" class="masb-front-page-options">
                <a href="#masb-f-page-options" class="ssc-open-options"><?php esc_html_e( 'Page Options', 'marketing-and-seo-booster' ); ?></a>
                <div  id="" class="ssc-dropdown"><span class="ssc_close"><i class="far fa-times-circle"></i></span>
                    <?php
                    if ( $post->post_type == 'composer_widget' ){
                        masb_front_composer_block_options( $post );
                    }else{
                        masb_front_page_options( $post, $screens[$post->post_type] );
                    }
                    ?>
                </div>
            </li>
        </ul>
        <ul id="kc-top-toolbar" class="ab-top-secondary ab-top-menu">
            <li id="wp-admin-bar-exit" class="kc-bar-save mtips">
                <div class="ab-item">
                	<a href="#exit" id="kc-front-exit">
	                	<i class="fa-sign-in"></i>  <?php _e('Exit Editor', 'kingcomposer'); ?></a>
	                </a>
                </div>
                <span class="mt-mes"><?php _e('(ctrl+e)', 'kingcomposer'); ?></span>
            </li>
            <li id="wp-admin-bar-exit-back" class="kc-bar-save mtips">
                <div class="ab-item">
                	<a href="<?php echo admin_url('/post.php?post='.$id.'&action=edit'); ?>" id="kc-exit-backend">
	                	<i class="fa-paper-plane"></i> <?php _e('Back-End Editor', 'kingcomposer'); ?>
	                </a>
                </div>
                <span class="mt-mes"><?php _e('Edit page with backend editor (ctrl+b)', 'kingcomposer'); ?></span>
            </li>
            <li id="wp-admin-bar-save" class="kc-bar-save mtips">
                <div class="ab-item">
                	<a href="#save" id="kc-front-save">
	                	<i class="fa-check"></i> <?php _e('Save Changes', 'kingcomposer'); ?>
	                </a>
                </div>
                <span class="mt-mes"><?php _e('Press Ctrl+S to save content', 'kingcomposer'); ?></span>
            </li>
             <li id="kc-enable-inspect" class="mtips">
            	<i class="toggle"></i>
            	<span class="mt-mes"><?php _e('Enable / Disable inspect elements to edit', 'kingcomposer'); ?></span>
            </li>
            <li id="kc-bar-desktop-view" data-screen="100%" class="kc-bar-devices active mtips">
				<i class="fa-desktop"></i>
				<span class="mt-mes"><?php _e('Destop Mode', 'kingcomposer'); ?></span>
            </li>
            <li id="kc-bar-tablet-landscape-view" data-screen="1024" class="kc-bar-devices mtips">
				<i class="fa-tablet"></i>
				<span class="mt-mes"><?php _e('Tablet Mode', 'kingcomposer'); ?> (landscape 1024px)</span>
            </li>
            <li id="kc-bar-tablet-view" data-screen="768" class="kc-bar-devices mtips">
				<i class="fa-tablet"></i>
				<span class="mt-mes"><?php _e('Tablet Mode', 'kingcomposer'); ?> (768px)</span>
            </li>
            <li id="kc-bar-mobile-landscape-view" data-screen="767" class="kc-bar-devices mtips">
				<i class="fa-mobile"></i>
				<span class="mt-mes"><?php _e('Mobile Mode', 'kingcomposer'); ?> (landscape 767px)</span>
            </li>
            <li id="kc-bar-mobile-view" data-screen="479" class="kc-bar-devices mtips">
				<i class="fa-mobile"></i>
				<span class="mt-mes"><?php _e('Mobile Mode', 'kingcomposer'); ?> (479px)</span>
            </li>
            <li id="kc-curent-screen-view" data-screen="custom" class="kc-bar-devices mtips">
            	<i>100%</i>
            	<span class="mt-mes"><?php _e('Click to set custom screen', 'kingcomposer'); ?></span>
            </li>
            <li id="kc-bar-redo" class="mtips">
				<i class="fa-share"></i>
				<span class="mt-mes"><?php _e('Redo (ctrl+shift+z)', 'kingcomposer'); ?></span>
            </li>
            <li id="kc-bar-undo" class="mtips">
				<i class="fa-reply"></i>
				<span class="mt-mes"><?php _e('Undo (ctrl+z)', 'kingcomposer'); ?></span>
            </li>
            <li id="kc-bar-tour-view" class="mtips">
				<a href="#tour"><i class="fa-play-circle"></i> <?php _e('Videos', 'kingcomposer'); ?></a>
				<span class="mt-mes"><?php _e('Watch the quick tour video', 'kingcomposer'); ?></span>
            </li>
        </ul>
		<div id="kc-css-inspector" data-label="<?php _e('CSS Inspector', 'kingcomposer'); ?>">
			<i class="fa-paint-brush"></i>
		</div>
    </div>
</div>
<div id="kc-ask-to-buy" class="hidden">
	<div id="kc-welcome" class="kc-preload-body enter-license">
		<h3><?php printf( esc_html__( 'Oops, hold on a sec!', 'kingcomposer' ), '<br />' ); ?></h3>
		<div class="kc-pl-form">
			<p class="notice">
				<?php _e( 'Your free 7-day trial expired, You need to verify your license key to do this action and use full of all another premium features.', 'kingcomposer' ); ?>
			</p>
			<input type="hidden" value="<?php echo wp_create_nonce( "kc-verify-nonce" ); ?>" name="sercurity" />
			<input type="text" value="<?php if( defined('KC_LICENSE') )echo KC_LICENSE; ?>" placeholder="<?php _e('Enter your license key', 'kingcomposer'); ?>" name="kc-license-key" />
			<br />
			<p><?php  printf( esc_html__( 'If you\'ve got one %s to login and copy the license', 'kingcomposer' ), '<a href="https://kingcomposer.com/my-account/" target=_blank>Click Here</a>' ); ?></p>
		</div>
		<a href="#" class="enter close"><i class="sl-close"></i></a>
		<div id="kc-preload-footer">
			<a href="https://kingcomposer.com/pricing/" target=_blank class="button gray left"><?php _e('Buy the license', 'kingcomposer'); ?> <i class="fa-shopping-cart"></i></a>
			<a class="button verify right"><?php _e('Verify your license', 'kingcomposer'); ?> <i class="fa-unlock-alt"></i></a>
		</div>
	</div>
</div>

<?php

	kc_after_editor ($post);

	/*
	* Load live template
	*/
	foreach ($kc_maps as $name => $map)
	{
		if (isset( $map['live_editor'] ) && is_file( $map['live_editor'] ) && $map['flag'] != 'core')
		{
			echo '<script type="text/html" id="tmpl-kc-'.esc_attr( $name ).'-template">';
			@include( $map['live_editor'] );
			echo '</script>';
		}
	}

?>
