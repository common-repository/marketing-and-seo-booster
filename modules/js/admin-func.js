/* Page options */
jQuery(document).ready(function () {
    "use strict";

    let regular_keys = [
            "design-layout",
            "header_type",
            "boxed-background",
            "content-background",
            "header_image",
            "pick_slider",
            "sidebar-layout",
            "left_sidebar_widgets",
            "right_sidebar_widgets"
        ],

        blog_keys = [
            "blog-layout",
            "blog-boxed-background",
            "blog-boxed-background",
            "blog-content-background",
            "blog-sidebar-layout",
            "blog_left_sidebar_widgets",
            "blog_right_sidebar_widgets",
            "blog-header_type",
            "blog-header_image",
            "blog-pick_slider"
        ],

        shop_keys = [
            "shop-layout",
            "shop-boxed-background",
            "shop-boxed-background",
            "shop-content-background",
            "shop-sidebar-layout",
            "shop_left_sidebar_widgets",
            "shop_right_sidebar_widgets",
            "shop-header_type",
            "shop-header_image",
            "shop-pick_slider"
        ],

        loader = jQuery("<span id='ajax_loader'><span>"),
        blog_cat_id = null;

    jQuery('#subtitle').append(loader);

    loader.css({display: 'none'});

    function slRunImgSelectorHandler() {
        let keys = ['blog-layout', 'blog-sidebar-layout', 'design-layout', 'sidebar-layout', 'shop-layout', 'shop-sidebar-layout'];
        jQuery.each(keys, function (i, val) {
            if (jQuery("#" + val).length) {
                slImgSelectorHandler(val);
            }
        });
    }

    slRunImgSelectorHandler();

    function getBlogCatId() {
        let inpt;
        jQuery('#categorychecklist').find('label').each(function (i, el) {
            if (jQuery(el).html().match(/blog/i)) {
                inpt = jQuery(el).find('input');
                blog_cat_id = jQuery(inpt).val();
                return false;
            }
        });
    }

    getBlogCatId();

    function slImgSelectorHandler(key) {
        let val = Number(jQuery('#' + key + ' input').val()),
            helpers = {};

        helpers.blog_layout_on_click = function (n) {
            if (n === 2) {
                jQuery('div#blog-boxed-background').removeClass('hidden').addClass('visible');
            } else {
                jQuery('div#blog-boxed-background').removeClass('visible').addClass('hidden');
                jQuery('div#blog-boxed-background div.background-color .wp-color-result span').css('background', '');
                jQuery('div#blog-boxed-background input.background-color_input').attr('value', '');
                jQuery('div#blog-boxed-background img.background-image_holder').attr('src', '').removeClass('visible').addClass('hidden');
                jQuery('div#blog-boxed-background input.background-image_input').attr('value', '');
            }
        };

        helpers.blog_sidebar_layout_on_click = function (n) {
            let dbc = jQuery('div#blog-columns'),
                ibc = jQuery('input#blog-columns_input'),
                dblsw = jQuery('div#blog_left_sidebar_widgets'),
                sblsw = jQuery('select#blog_left_sidebar_widgets'),
                dbrsw = jQuery('div#blog_right_sidebar_widgets'),
                sbrsw = jQuery('select#blog_right_sidebar_widgets');
            switch (n) {
                case 1:
                    dbc.removeClass('hidden').addClass('visible');
                    ibc.attr('name', 'blog-columns');
                    dblsw.removeClass('visible').addClass('hidden');
                    sblsw.attr('name', 'blog_left_sidebar_widgets_fake');
                    dbrsw.removeClass('visible').addClass('hidden');
                    sbrsw.attr('name', 'blog_right_sidebar_widgets_fake');
                    break;
                case 2:
                    dbrsw.removeClass('hidden').addClass('visible');
                    sbrsw.attr('name', 'blog_right_sidebar_widgets');
                    dblsw.removeClass('hidden').addClass('visible');
                    sblsw.attr('name', 'blog_left_sidebar_widgets');
                    dbc.removeClass('visible').addClass('hidden');
                    ibc.attr('name', 'blog-columns_fake');
                    break;
                case 3:
                    dblsw.removeClass('hidden').addClass('visible');
                    sblsw.attr('name', 'blog_left_sidebar_widgets');
                    dbrsw.removeClass('visible').addClass('hidden');
                    sbrsw.attr('name', 'blog_right_sidebar_widgets_fake');
                    dbc.removeClass('visible').addClass('hidden');
                    ibc.attr('name', 'blog-columns_fake');
                    break;
                case 4:
                    dbrsw.removeClass('hidden').addClass('visible');
                    sbrsw.attr('name', 'blog_right_sidebar_widgets');
                    dblsw.removeClass('visible').addClass('hidden');
                    sblsw.attr('name', 'blog_left_sidebar_widgets_fake');
                    dbc.removeClass('visible').addClass('hidden');
                    ibc.attr('name', 'blog-columns_fake');
                    break;
            }
        };

        helpers.design_layout_on_click = function (n) {
            if (Number(n) === 2) {
                jQuery('div#boxed-background').removeClass('hidden').addClass('visible');
            }
            else {
                jQuery('div#boxed-background').removeClass('visible').addClass('hidden');
                jQuery('div#boxed-background div.background-color .wp-color-result span').css('background', '');
                jQuery('div#boxed-background input.background-color_input').attr('value', '');
                jQuery('div#boxed-background img.background-image_holder').attr('src', '').removeClass('visible').addClass('hidden');
                jQuery('div#boxed-background input.background-image_input').attr('value', '');
            }
        };

        helpers.sidebar_layout_on_click = function (n) {
            let lsw = jQuery('#left_sidebar_widgets'),
                rsw = jQuery('#right_sidebar_widgets'),
                srsw = jQuery('select#right_sidebar_widgets'),
                slsw = jQuery('select#left_sidebar_widgets');
            switch (n) {
                case 3:
                    lsw.removeClass('hidden').addClass('visible');
                    slsw.attr('name', 'left_sidebar_widgets');
                    rsw.removeClass('visible').addClass('hidden');
                    srsw.attr('name', 'right_sidebar_widgets_fake');
                    break;
                case 4:
                    rsw.removeClass('hidden').addClass('visible');
                    srsw.attr('name', 'right_sidebar_widgets');
                    lsw.removeClass('visible').addClass('hidden');
                    slsw.attr('name', 'left_sidebar_widgets_fake');
                    break;
                case 2:
                    rsw.removeClass('hidden').addClass('visible');
                    srsw.attr('name', 'right_sidebar_widgets');
                    lsw.removeClass('hidden').addClass('visible');
                    slsw.attr('name', 'left_sidebar_widgets');
                    break;
                case 1:
                    lsw.removeClass('visible').addClass('hidden');
                    slsw.attr('name', 'left_sidebar_widgets_fake');
                    rsw.removeClass('visible').addClass('hidden');
                    srsw.attr('name', 'right_sidebar_widgets_fake');
                    break;
            }
        };

        helpers.shop_layout_on_click = function (n) {
            if (Number(n) === 2) {
                jQuery('div#shop-boxed-background').removeClass('hidden').addClass('visible');
            }
            else {
                jQuery('div#shop-boxed-background').removeClass('visible').addClass('hidden');
                jQuery('div#shop-boxed-background div.background-color .wp-color-result span').css('background', '');
                jQuery('div#shop-boxed-background input.background-color_input').attr('value', '');
                jQuery('div#shop-boxed-background img.background-image_holder').attr('src', '').removeClass('visible').addClass('hidden');
                jQuery('div#shop-boxed-background input.background-image_input').attr('value', '');
            }
        };

        helpers.shop_sidebar_layout_on_click = function (n) {
            let dsc = jQuery('div#shop-columns'),
                isci = jQuery('input#shop-columns_input'),
                slsw = jQuery('#shop_left_sidebar_widgets'),
                sslsw = jQuery('select#shop_left_sidebar_widgets'),
                srsw = jQuery('#shop_right_sidebar_widgets'),
                ssrsw = jQuery('select#shop_right_sidebar_widgets');
            switch (n) {
                case 1:
                    dsc.removeClass('hidden').addClass('visible');
                    isci.attr('name', 'shop-columns');
                    slsw.removeClass('visible').addClass('hidden');
                    sslsw.attr('name', 'shop_left_sidebar_widgets_fake');
                    srsw.removeClass('visible').addClass('hidden');
                    ssrsw.attr('name', 'shop_right_sidebar_widgets_fake');
                    break;
                case 2:
                    srsw.removeClass('hidden').addClass('visible');
                    ssrsw.attr('name', 'shop_right_sidebar_widgets');
                    slsw.removeClass('hidden').addClass('visible');
                    sslsw.attr('name', 'shop_left_sidebar_widgets');
                    dsc.removeClass('visible').addClass('hidden');
                    isci.attr('name', 'shop-columns_fake');
                    break;
                case 3:
                    slsw.removeClass('hidden').addClass('visible');
                    sslsw.attr('name', 'shop_left_sidebar_widgets');
                    srsw.removeClass('visible').addClass('hidden');
                    ssrsw.attr('name', 'shop_right_sidebar_widgets_fake');
                    dsc.removeClass('visible').addClass('hidden');
                    isci.attr('name', 'shop-columns_fake');
                    break;
                case 4:
                    srsw.removeClass('hidden').addClass('visible');
                    ssrsw.attr('name', 'shop_right_sidebar_widgets');
                    slsw.removeClass('visible').addClass('hidden');
                    sslsw.attr('name', 'shop_left_sidebar_widgets_fake');
                    dsc.removeClass('visible').addClass('hidden');
                    isci.attr('name', 'shop-columns_fake');
                    break;
            }
        };

        jQuery('#' + key + ' .custom_setting').each(function (i, el) {
            if (val === i + 1) {
                jQuery(el).addClass('active');
            }
            switch (key) {
                case 'design-layout':
                    if (Number(val) === 2) {
                        jQuery('div#boxed-background').removeClass('hidden').addClass('visible');
                    }
                    else {
                        jQuery('div#boxed-background').removeClass('visible').addClass('hidden');
                    }
                    break;
                case 'sidebar-layout':
                    let dlsw = jQuery('div#left_sidebar_widgets'),
                        drsw = jQuery('div#right_sidebar_widgets'),
                        srsw = jQuery('select#right_sidebar_widgets'),
                        slsw = jQuery('select#left_sidebar_widgets');
                    switch (Number(val)) {
                        case 3:
                            dlsw.removeClass('hidden').addClass('visible');
                            drsw.removeClass('visible').addClass('hidden');
                            srsw.attr('name', 'right_sidebar_widgets_fake');
                            break;
                        case 2:
                            dlsw.removeClass('hidden').addClass('visible');
                            drsw.removeClass('hidden').addClass('visible');
                            break;
                        case 4:
                            dlsw.removeClass('visible').addClass('hidden');
                            drsw.removeClass('hidden').addClass('visible');
                            slsw.attr('name', 'left_sidebar_widgets_fake');
                            break;
                        case 1:
                            dlsw.removeClass('visible').addClass('hidden');
                            slsw.attr('name', 'left_sidebar_widgets_fake');
                            drsw.removeClass('visible').addClass('hidden');
                            srsw.attr('name', 'right_sidebar_widgets_fake');
                            break;
                    }
                    break;
                case 'blog-layout':
                    if (val === 2) {
                        jQuery('div#blog-boxed-background').removeClass('hidden').addClass('visible');
                    }
                    else {
                        jQuery('div#blog-boxed-background').removeClass('visible').addClass('hidden');
                    }
                    break;
                case 'blog-sidebar-layout':
                    let dbc = jQuery('div#blog-columns'),
                        ibc = jQuery('input#blog-columns_input'),
                        dblsw = jQuery('div#blog_left_sidebar_widgets'),
                        sblsw = jQuery('select#blog_left_sidebar_widgets'),
                        dbrsw = jQuery('div#blog_right_sidebar_widgets'),
                        sbrsw = jQuery('select#blog_right_sidebar_widgets');
                    switch (Number(val)) {
                        case 1:
                            dbc.removeClass('hidden').addClass('visible');
                            ibc.attr('name', 'blog-columns');
                            dblsw.removeClass('visible').addClass('hidden');
                            sblsw.attr('name', 'blog_left_sidebar_widgets_fake');
                            dbrsw.removeClass('visible').addClass('hidden');
                            sbrsw.attr('name', 'blog_right_sidebar_widgets_fake');
                            break;
                        case 2:
                            dblsw.removeClass('hidden').addClass('visible');
                            dbrsw.removeClass('hidden').addClass('visible');
                            dbc.removeClass('visible').addClass('hidden');
                            ibc.attr('name', 'blog-columns_fake');
                            break;
                        case 3:
                            dblsw.removeClass('hidden').addClass('visible');
                            dbrsw.removeClass('visible').addClass('hidden');
                            sbrsw.attr('name', 'blog_right_sidebar_widgets_fake');
                            dbc.removeClass('visible').addClass('hidden');
                            ibc.attr('name', 'blog-columns_fake');
                            break;
                        case 4:
                            dblsw.removeClass('visible').addClass('hidden');
                            dbrsw.removeClass('hidden').addClass('visible');
                            sblsw.attr('name', 'blog_left_sidebar_widgets_fake');
                            dbc.removeClass('visible').addClass('hidden');
                            ibc.attr('name', 'blog-columns_fake');
                            break;
                    }
                    break;
                case 'shop-layout':
                    if (val === 2) {
                        jQuery('div#shop-boxed-background').removeClass('hidden').addClass('visible');
                    }
                    else {
                        jQuery('div#shop-boxed-background').removeClass('visible').addClass('hidden');
                    }
                    break;
                case 'shop-sidebar-layout':
                    let dsc = jQuery('div#shop-columns'),
                        isci = jQuery('input#shop-columns_input'),
                        dslsw = jQuery('div#shop_left_sidebar_widgets'),
                        sslsw = jQuery('select#shop_left_sidebar_widgets'),
                        dsrsw = jQuery('div#shop_right_sidebar_widgets'),
                        ssrsw = jQuery('select#shop_right_sidebar_widgets');
                    switch (val) {
                        case 1:
                            dsc.removeClass('hidden').addClass('visible');
                            isci.attr('name', 'shop-columns');
                            dslsw.removeClass('visible').addClass('hidden');
                            sslsw.attr('name', 'shop_left_sidebar_widgets_fake');
                            dsrsw.removeClass('visible').addClass('hidden');
                            ssrsw.attr('name', 'shop_right_sidebar_widgets_fake');
                            break;
                        case 2:
                            dslsw.removeClass('hidden').addClass('visible');
                            dsrsw.removeClass('hidden').addClass('visible');
                            dsc.removeClass('visible').addClass('hidden');
                            isci.attr('name', 'shop-columns_fake');
                            break;
                        case 3:
                            dslsw.removeClass('hidden').addClass('visible');
                            dsrsw.removeClass('visible').addClass('hidden');
                            ssrsw.attr('name', 'shop_right_sidebar_widgets_fake');
                            dsc.removeClass('visible').addClass('hidden');
                            isci.attr('name', 'shop-columns_fake');
                            break;
                        case 4:
                            dslsw.removeClass('visible').addClass('hidden');
                            dsrsw.removeClass('hidden').addClass('visible');
                            sslsw.attr('name', 'shop_left_sidebar_widgets_fake');
                            dsc.removeClass('visible').addClass('hidden');
                            isci.attr('name', 'shop-columns_fake');
                            break;
                    }
                    break;
            }
            jQuery(el).attr('n', i + 1);
        });

        jQuery('#' + key).on('click', function (e) {
            let n = Number(jQuery(e.target.parentNode).attr('n')),
                func = key.replace(/-/g, '_') + '_on_click';
            // Run helpers functions for switching
            helpers[func](n);

            jQuery('#' + key + ' .active').removeClass('active');
            jQuery(e.target.parentNode).addClass('active');
            jQuery('#' + key + '_input').attr('value', n);
            jQuery('#' + key + '_input').trigger('change');

            jQuery('input#sealed').attr('value', 0);
        });

    }

    function AltImageUpdate(action, img_id, img_url, input_id, img_holder_id, remove_img_id, key_id) {
        let input = jQuery('input#' + input_id);
        let hinput = jQuery('input#hiden-' + input_id);
        if (action !== -1) {
            jQuery('#' + img_holder_id).attr('src', img_url).attr('img_n', img_id).removeClass('hidden').addClass('visible');
            jQuery('#' + remove_img_id).removeClass('hidden').addClass('visible');
            input.attr('value', img_url);
            hinput.attr('value', img_id);
        }
        else {
            jQuery('#' + img_holder_id).removeClass('visible').addClass('hidden');
            jQuery('#' + remove_img_id).removeClass('visible').addClass('hidden');
            input.attr('value', 'none');
            hinput.attr('value', 0);
        }
        input.trigger('change');
        jQuery('input#sealed').attr('value', 0);
    }

    jQuery('.slnm-color-rgba').wpColorPicker();

    // open settings window
    jQuery('.ssc-open-options').on('click', function () {
        jQuery('.ssc-dropdown').css('top', '32px');
    });

    jQuery('.ssc_close').on('click', function () {
        jQuery('.ssc-dropdown').css('top', '-2000px');
    });

    jQuery('.background-color .wp-color-picker').wpColorPicker(
        {
            change:
                function (e, ui) {
                    let bbbii = jQuery(e.target).parents('div.background-color').find('input.background-color_input').attr('id'),
                        bbbiin = jQuery(e.target).parents('div.background-color').find('input#' + bbbii).attr('name');
                    jQuery(e.target).parents('div.background-color').find('input.background-color_input').attr('value', ui.color.toString()).attr('name', bbbiin);
                    jQuery('#sealed').attr('value', 0);
                },
            clear: function (e) {
                let bbbii = jQuery(e.target).parents('div.background-color').find('input.background-color_input').attr('id'),
                    bbbiin = jQuery(e.target).parents('div.background-color').find('input#' + bbbii).attr('name');
                jQuery(e.target).parents('div.background-color').find('input.background-color_input').attr('value', '').attr('name', bbbiin);
                jQuery('#sealed').attr('value', 0);
            }
        }
    );

    jQuery('#manage_type_selector_input').on('click', function (e) {
        jQuery('.masb-ajax-message').remove();
        let id = jQuery('#secretlab-post-layout-settings-box').attr('n');

        e.preventDefault();

        loader.css({display: ''});
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: 'action=handle_post_layout&post_id=' + id,
            complete: function (answer) {
                if (!jQuery.isEmptyObject(answer.responseText)) {
                    answer = JSON.parse(answer.responseText);
                    jQuery(e.target).parent().append('<div class="masb-ajax-message">' + answer.message + '</div>');
                    if (answer.success === true) {
                        let settings = answer.settings,
                            arr = regular_keys.concat(blog_keys).concat(shop_keys);
                        jQuery('.active').each(function (i, el) {
                            jQuery(el).removeClass('active');
                        });
                        for (let i = 0; i < arr.length; i++) {
                            let el = jQuery("[name^='" + arr[i] + "']")[0],
                                name = jQuery(el).attr('name');
                            if (name && !name.match(/_locked/)) {
                                jQuery(el).attr('name', name + '_locked');

                            }
                        }
                        slRunImgSelectorHandler();
                        location.reload();
                    }
                }
                loader.css({display: 'none'});
            }

        });

    });

    jQuery('#categorydiv').change(function (e) {
        let i, el, name, parent = null, active_blog_cats = [],
            active_unblog_cats = [], inpt, n = 0;
        if (!blog_cat_id) return;
        jQuery('#categorychecklist').children('li').each(
            function (i, el) {
                console.log(typeof jQuery(el).attr('id'));
                if (jQuery(el).attr('id') !== 'category-' + blog_cat_id) {
                    inpt = jQuery(el).find('input:checked');
                    if (inpt && inpt.length === 1) {
                        active_unblog_cats.push(inpt);
                    }
                }
            }
        );
        parent = jQuery(e.target).parents('li#category-' + blog_cat_id);
        if (parent && parent.length === 1) {
            active_blog_cats = parent.find('input:checked');
            if (jQuery(e.target).is(":checked")) {
                jQuery('div#secretlab-post-layout-settings-box').removeClass('unlocked').addClass('locked');
                for (i = 0; i < regular_keys.length; i++) {
                    el = jQuery("[name^='" + regular_keys[i] + "']")[0];
                    name = jQuery(el).attr('name');
                    if (name && !name.match(/_locked/)) {
                        jQuery(el).attr('name', name + '_locked');
                    }
                }
                for (i = 0; i < blog_keys.length; i++) {
                    el = jQuery("[name^='" + blog_keys[i] + "']")[0];
                    name = jQuery(el).attr('name').replace(/_locked/g, '');
                    jQuery(el).attr('name', name);
                }
                jQuery('div#blog_secretlab-post-layout-settings-box').removeClass('locked hidden').addClass('unlocked visible');
            }
            else {
                if (active_unblog_cats.length === 0) return;
                jQuery('div#blog_secretlab-post-layout-settings-box').removeClass('unlocked').addClass('locked');
                for (i = 0; i < blog_keys.length; i++) {
                    el = jQuery("[name^='" + blog_keys[i] + "']")[0];
                    name = jQuery(el).attr('name');
                    if (name && !name.match(/_locked/)) {
                        jQuery(el).attr('name', name + '_locked');
                    }
                }
                for (i = 0; i < regular_keys.length; i++) {
                    el = jQuery("[name^='" + regular_keys[i] + "']")[0];
                    name = jQuery(el).attr('name').replace(/_locked/g, '');
                    jQuery(el).attr('name', name);
                }
                jQuery('div#secretlab-post-layout-settings-box').removeClass('locked hidden').addClass('unlocked visible');
            }
        }
        else {
            active_blog_cats = jQuery('li#category-' + blog_cat_id).find('input:checked');
            if (active_blog_cats.length > 0) return false;
            else if (jQuery(e.target).is(":checked")) {
                jQuery('div#blog_secretlab-post-layout-settings-box').removeClass('unlocked').addClass('locked');
                for (i = 0; i < blog_keys.length; i++) {
                    el = jQuery("[name^='" + blog_keys[i] + "']")[0];
                    name = jQuery(el).attr('name');
                    if (name && !name.match(/_locked/)) {
                        jQuery(el).attr('name', name + '_locked');
                    }
                }
                for (i = 0; i < regular_keys.length; i++) {
                    el = jQuery("[name^='" + regular_keys[i] + "']")[0];
                    name = jQuery(el).attr('name').replace(/_locked/g, '');
                    jQuery(el).attr('name', name);
                }
                jQuery('div#secretlab-post-layout-settings-box').removeClass('locked hidden').addClass('unlocked visible');
            }
            else {
                jQuery('div#secretlab-post-layout-settings-box').removeClass('unlocked').addClass('locked');
                for (i = 0; i < regular_keys.length; i++) {
                    el = jQuery("[name^='" + regular_keys[i] + "']")[0];
                    name = jQuery(el).attr('name');
                    if (name && !name.match(/_locked/)) {
                        jQuery(el).attr('name', name + '_locked');
                    }
                }
                for (i = 0; i < blog_keys.length; i++) {
                    el = jQuery("[name^='" + blog_keys[i] + "']")[0];
                    name = jQuery(el).attr('name').replace(/_locked/g, '');
                    jQuery(el).attr('name', name);
                }
                jQuery('div#blog_secretlab-post-layout-settings-box').removeClass('locked hidden').addClass('unlocked visible');
            }
        }
        jQuery('#sealed').attr('value', 0);
    });

    jQuery('.regular_widget').on('change', function (e) {
        jQuery('#sealed').attr('value', 0);
    });

    jQuery('.switcher').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        jQuery('input#' + this.id + '_input').attr('value', jQuery(e.target).attr('val'));
        jQuery('#' + this.id + ' a.meta_btn.on').removeClass('on').addClass('off');
        jQuery(e.target).removeClass('off').addClass('on');
        jQuery('#sealed').attr('value', 0);
    });

    jQuery('.background-image_remove_img').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let bbbii = jQuery(e.target).parents('div.background-image').find('input.background-image_input').attr('id'),
            bbbiin = jQuery(e.target).parents('div.background-image').find('input#' + bbbii).attr('name'),
            bih = jQuery(e.target).parents('div.background-image').find('img.background-image_holder').attr('id');

        AltImageUpdate(-1, null, null, bbbii, bih, 'background-image_remove_img', bbbiin);

        jQuery('input#sealed').attr('value', 0);

    });

    jQuery('.background-image_change_img').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let bbbii = jQuery(e.target).parents('div.background-image').find('input.background-image_input').attr('id'),
            bbbiin = jQuery(e.target).parents('div.background-image').find('input#' + bbbii).attr('name'),
            bih = jQuery(e.target).parents('div.background-image').find('img.background-image_holder').attr('id');
        jQuery('div#background-image').trigger('click', {target: jQuery('div#background-image')});
        let uploader = wp.media({
            title: 'set new image',
            button: {text: 'upload image'},
            multiple: false
        });
        uploader.on('select', function () {
            let attachment = uploader.state().get('selection').first().toJSON();
            AltImageUpdate(wp.media.view.settings.post.id, attachment.id, attachment.url, bbbii, bih, 'background-image_remove_img', bbbiin);
        });
        uploader.open();
    });

    jQuery('#secretlab-post-layout-settings-box').change(function () {
        jQuery('#sealed').attr('value', 0);
    });

    jQuery('#header_type, #blog-header_type, #shop-header_type').on('change', function (e) {
        let val = Number(jQuery(e.target).val());
        if (val === 2) {
            jQuery(e.target).parents('.custom_settings_box').next().removeClass('hidden').addClass('visible');
            jQuery(e.target).parents('.custom_settings_box').next().next().removeClass('visible').addClass('hidden');
        } else if (val === 1) {
            jQuery(e.target).parents('.custom_settings_box').next().removeClass('visible').addClass('hidden');
            jQuery(e.target).parents('.custom_settings_box').next().next().removeClass('hidden').addClass('visible');
        }
        jQuery('#sealed').attr('value', 0);
    });

    jQuery('#header_image_remover, #blog-header_image_remover, #shop-header_image_remover').on('click', function (e) {

        e.preventDefault();
        jQuery(e.target).parent().find('input.header_img').attr('value', '');
        jQuery(e.target).parent().find('input.header_img').trigger('change');
        jQuery(e.target).parent().find('.header_img_preview img').attr('src', '');
        jQuery('#sealed').attr('value', 0);
        return false;
    });

    jQuery('#header_image_loader, #blog-header_image_loader, #shop-header_image_loader').click(function (e) {

        e.preventDefault();
        let image = wp.media({
            title: 'Upload Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
            .on('select', function (e1) {
                let viewport = jQuery(e.target).parent().find('.header_img_preview img'), url;
                let uploaded_image = image.state().get('selection').first();
                let image_url = uploaded_image.toJSON().url;

                jQuery(e.target).parent().find('input.header_img').attr('value', image_url);
                jQuery(e.target).parent().find('input.header_img').trigger('change');
                if (viewport && viewport.length > 0) jQuery(viewport).attr('src', image_url);
                jQuery('#sealed').attr('value', 0);
            });
    });

    // Front settings js
    if (typeof( kc ) === 'object') {

        jQuery('#secretlab-f-post-layout-settings .header_widget').on('change', function (e) {
            let h_id = Number(jQuery(e.target).val());
            if (h_id === -1) {
                _$('.header-widget').remove();
                jQuery('#masb-f-edit-header-widget-block').html('');
            } else {
                jQuery.post(ajaxurl,
                    {
                        action: 'masb_get_header_widget',
                        h_id: h_id,
                        nonce: masb_ajax.nonce
                    },
                    function (result) {
                        if (result === -1) {
                            // 403 Error
                            return;
                        }

                        if (typeof( result ) !== 'object') {
                            // Error HTML
                            return;
                        }

                        if (result.status === 0) {
                            // Wrong widget type
                            kc.msg(result.message, 'error', 'sl-close');
                            return;
                        }
                        if (result.widget !== undefined || result.widget !== '') {
                            jQuery('#masb-f-edit-header-widget-block').html(result.edit_link);
                            _$('.headline > div:not(.headerslider)').html(result.widget);
                        }
                    });
            }
        });

        jQuery('#secretlab-f-post-layout-settings .footer_widget').on('change', function (e) {
            let f_id = Number(jQuery(e.target).val());
            if (f_id === -1) {
                _$('.footer-widget').remove();
                jQuery('#masb-f-edit-footer-widget-block').html('');
            } else {
                jQuery.post(ajaxurl,
                    {
                        action: 'masb_get_footer_widget',
                        f_id: f_id,
                        nonce: masb_ajax.nonce
                    },
                    function (result) {
                        if (result === -1) {
                            // 403 Error
                            return;
                        }

                        if (typeof( result ) !== 'object') {
                            // Error HTML
                            return;
                        }

                        if (result.status === 0) {
                            // Wrong widget type
                            kc.msg(result.message, 'error', 'sl-close');
                            return;
                        }
                        if (result.widget !== undefined || result.widget !== '') {
                            jQuery('#masb-f-edit-footer-widget-block').html(result.edit_link);
                            _$('.footer-widget').remove();
                            _$('main').after(result.widget);
                        }
                    });
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'header_type\']').on('change', function (e) {
            let val = Number(jQuery(e.target).val());
            if (val === 1) {
                let slider = jQuery('#secretlab-f-post-layout-settings .slider_select');
                _$('.headline .headerimage').remove();
                if (_$('.headline .headerslider').length === 0) {
                    _$('body .headline').append('<div class="headerslider"></div>');
                }
                jQuery.post(ajaxurl,
                    {
                        action: 'masb_get_slider',
                        s_id: slider.val(),
                        nonce: masb_ajax.nonce
                    },
                    function (result) {
                        if (result === -1) {
                            // 403 Error
                            return;
                        }

                        if (typeof( result ) !== 'object' || result.html === undefined) {
                            // Error HTML
                            return;
                        }

                        if (result.status === 0) {
                            // Wrong slider type
                            return;
                        }
                        if (result.html !== '') {
                            _$('.headerslider').html(_$(result.html));
                        }
                    });
            } else if (val === 2) {
                _$('.headline .headerslider').remove();
                if (_$('.headline .headerimage').length === 0) {
                    _$('body .headline').append('<div class="headerimage"></div>');
                }
            }
        });

        jQuery('#secretlab-f-post-layout-settings .slider_select').on('change', function (e) {
            if (_$('.headerslider').length === 0) {
                _$('body .headline').append('<div class="headerslider"></div>');
            }
            if (Number(jQuery(e.target).val()) === 0) {
                _$('.headerslider').html('');
            } else {
                jQuery.post(ajaxurl,
                    {
                        action: 'masb_get_slider',
                        s_id: jQuery(e.target).val(),
                        nonce: masb_ajax.nonce
                    },
                    function (result) {
                        if (result === -1) {
                            // 403 Error
                            return;
                        }

                        if (typeof( result ) !== 'object' || result.html === undefined) {
                            // Error HTML
                            return;
                        }

                        if (result.status === 0) {
                            // Wrong slider type
                            return;
                        }
                        if (result.html !== '') {
                            _$('.headerslider').html(_$(result.html));
                        }
                    });
            }
        });

        jQuery('#secretlab-f-post-layout-settings input.header_img').on('change', function (e) {
            if (Number(jQuery(e.target).val()) === 0) {
                _$('.headerimage').html('');
            } else {
                _$('.headerimage').html('<img src="' + jQuery(e.target).val() + '">');
            }
        });

        jQuery('#secretlab-f-post-layout-settings .content-background-color').on('change', function (e) {
            let color = jQuery(e.target).val();
            if ('' === color) {
                _$('main').css('background-color', 'transparent');
            } else {
                _$('main').css('background-color', color);
            }
        });

        jQuery('#secretlab-f-post-layout-settings .boxed-background-color').on('change', function (e) {
            let color = jQuery(e.target).val();
            if ('' === color) {
                _$('.mainbgr').css('background-color', 'transparent');
            } else {
                _$('.mainbgr').css('background-color', color);
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'layout\']:not([id$=\'sidebar-layout\'])').on('click', function (e) {
            let n = Number(jQuery(e.target).parent().attr('n'));
            if (n === 1) {
                // Full width
                _$('.footer-widget').prependTo(_$('body'));
                _$('main').removeClass('boxed-wrapper').addClass('mainsidebar').prependTo(_$('body'));
                _$('.headline').prependTo(_$('body'));
            } else if (n === 2) {
                // Boxed
                if (_$('.mainbgr .row').length === 0) {
                    _$('body').prepend('<div class="mainbgr"><div class="container"><div class="row"></div></div></div>');
                    _$('.mainbgr').css('background-color', _$('#secretlab-f-post-layout-settings #blog-boxed-background-background-color_input').val());
                }
                _$('.footer-widget').prependTo(_$('.mainbgr .row'));
                _$('main').removeClass('mainsidebar').addClass('boxed-wrapper').prependTo(_$('.mainbgr .row'));
                _$('.headline').prependTo(_$('.mainbgr .row'));
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'content-background-background-image_input\']').on('change', function (e) {
            if (jQuery(e.target).val() === 'none' || jQuery(e.target).val() === '') {
                _$('main').css('background-image', 'none');
                jQuery(e.target).parent().find('[id$=\'content-background-background-image_remove_img\']').addClass('hidden');
            } else {
                _$('main').css('background-image', 'url(' + jQuery(e.target).val() + ')');
                jQuery(e.target).parent().find('[id$=\'content-background-background-image_remove_img\']').removeClass('hidden');
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'content-background-background-repeat_select\']').on('change', function (e) {
            if (jQuery(e.target).val() === 'default') {
                _$('main').css('background-repeat', '');
            } else {
                _$('main').css('background-repeat', jQuery(e.target).val());
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'content-background-background-size_select\']').on('change', function (e) {
            if (jQuery(e.target).val() === 'default') {
                _$('main').css('background-size', '');
            } else {
                _$('main').css('background-size', jQuery(e.target).val());
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'content-background-background-attachment_select\']').on('change', function (e) {
            if (jQuery(e.target).val() === 'default') {
                _$('main').css('background-attachment', '');
            } else {
                _$('main').css('background-attachment', jQuery(e.target).val());
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'content-background-background-position_select\']').on('change', function (e) {
            if (jQuery(e.target).val() === 'default') {
                _$('main').css('background-position', '');
            } else {
                _$('main').css('background-position', jQuery(e.target).val());
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'boxed-background-background-image_input\']').on('change', function (e) {
            if (jQuery(e.target).val() === 'none' || jQuery(e.target).val() === '') {
                _$('.mainbgr').css('background-image', 'none');
                jQuery(e.target).parent().find('[id$=\'boxed-background-background-image_remove_img\']').addClass('hidden');
            } else {
                _$('.mainbgr').css('background-image', 'url(' + jQuery(e.target).val() + ')');
                jQuery(e.target).parent().find('[id$=\'boxed-background-background-image_remove_img\']').removeClass('hidden');
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'boxed-background-background-repeat_select\']').on('change', function (e) {
            if (jQuery(e.target).val() === 'default') {
                _$('.mainbgr').css('background-repeat', '');
            } else {
                _$('.mainbgr').css('background-repeat', jQuery(e.target).val());
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'boxed-background-background-size_select\']').on('change', function (e) {
            if (jQuery(e.target).val() === 'default') {
                _$('.mainbgr').css('background-size', '');
            } else {
                _$('.mainbgr').css('background-size', jQuery(e.target).val());
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'boxed-background-background-attachment_select\']').on('change', function (e) {
            if (jQuery(e.target).val() === 'default') {
                _$('.mainbgr').css('background-attachment', '');
            } else {
                _$('.mainbgr').css('background-attachment', jQuery(e.target).val());
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'boxed-background-background-position_select\']').on('change', function (e) {
            if (jQuery(e.target).val() === 'default') {
                _$('.mainbgr').css('background-position', '');
            } else {
                _$('.mainbgr').css('background-position', jQuery(e.target).val());
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'sidebar-layout\']').on('click', function (e) {
            let n = Number(jQuery(e.target).parent().attr('n'));
            if (_$('.sbcenter').length === 0) {
                _$('main').prepend('<div class="sbcenter"></div>');
            }
            if (n === 1) {
                // Full width
                _$('.sbcenter .widget-area').remove();
                _$('main').prepend(_$('main .main').removeClass('col-lg-6 col-md-6 col-sm-8 col-xs-12 cont-box-area blogsidebarspage'));
            } else if (n === 2) {
                // Both sidebares
                if (_$('.sbcenter div:first-child.widget-area').length === 0) {
                    _$('.sbcenter').prepend('<div class="col-lg-3 col-md-3 col-sm-4 col-xs-12 1 widget-area"></div>');
                }
                jQuery.post(ajaxurl,
                    {
                        action: 'masb_get_sidebar',
                        s_id: jQuery('#secretlab-f-post-layout-settings [name$=\'left_sidebar_widgets\']').val(),
                        nonce: masb_ajax.nonce
                    },
                    function (result) {
                        if (result === -1) {
                            // 403 Error
                            return;
                        }

                        if (typeof( result ) !== 'object' || result.sidebar === undefined) {
                            // Error HTML
                            return;
                        }

                        if (result.status === 0) {
                            // Wrong sidebar type
                            return;
                        }
                        if (result.sidebar !== '') {
                            _$('.sbcenter div:first-child.widget-area').prepend(result.sidebar);
                        } else {
                            _$('.sbcenter div:first-child.widget-area').html('');
                        }
                    });
                _$('main .main').removeClass('col-lg-9 col-md-9 col-sm-8 col-xs-12').addClass('col-lg-6 col-md-6 col-sm-8 col-xs-12 cont-box-area blogsidebarspage').insertAfter(_$('.sbcenter div:first-child.widget-area'));
                if (_$('.sbcenter div:last-child.widget-area').length === 0) {
                    _$('.sbcenter').append('<div class="col-lg-3 col-md-3 col-sm-4 col-xs-12 widget-area"></div>');
                }
                jQuery.post(ajaxurl,
                    {
                        action: 'masb_get_sidebar',
                        s_id: jQuery('#secretlab-f-post-layout-settings [name$=\'right_sidebar_widgets\']').val(),
                        nonce: masb_ajax.nonce
                    },
                    function (result) {
                        if (result === -1) {
                            // 403 Error
                            return;
                        }

                        if (typeof( result ) !== 'object' || result.sidebar === undefined) {
                            // Error HTML
                            return;
                        }

                        if (result.status === 0) {
                            // Wrong sidebar type
                            return;
                        }
                        if (result.sidebar !== '') {
                            _$('.sbcenter div:last-child.widget-area').prepend(result.sidebar);
                        } else {
                            _$('.sbcenter div:last-child.widget-area').html('');
                        }
                    });
            } else if (n === 3) {
                // Left sidebar
                if (_$('.sbcenter div:first-child.widget-area').length === 0) {
                    _$('.sbcenter').prepend('<div class="col-lg-3 col-md-3 col-sm-4 col-xs-12 1 widget-area"></div>');
                }
                _$('main .main').removeClass('col-lg-6 col-md-6 col-sm-8 col-xs-12').addClass('col-lg-9 col-md-9 col-sm-8 col-xs-12 cont-box-area blogsidebarspage').insertAfter(_$('.sbcenter div:first-child.widget-area'));
                jQuery.post(ajaxurl,
                    {
                        action: 'masb_get_sidebar',
                        s_id: jQuery('#secretlab-f-post-layout-settings [name$=\'left_sidebar_widgets\']').val(),
                        nonce: masb_ajax.nonce
                    },
                    function (result) {
                        if (result === -1) {
                            // 403 Error
                            return;
                        }

                        if (typeof( result ) !== 'object' || result.sidebar === undefined) {
                            // Error HTML
                            return;
                        }

                        if (result.status === 0) {
                            // Wrong sidebar type
                            return;
                        }
                        if (result.sidebar !== '') {
                            _$('.sbcenter div:first-child.widget-area').prepend(result.sidebar);
                        } else {
                            _$('.sbcenter div:first-child.widget-area').html('');
                        }
                    });
                _$('.sbcenter div:last-child.widget-area').remove();
            } else if (n === 4) {
                // Right sidebar
                if (_$('.sbcenter div:last-child.widget-area').length === 0) {
                    _$('.sbcenter').append('<div class="col-lg-3 col-md-3 col-sm-4 col-xs-12 widget-area"></div>');
                }
                _$('main .main').removeClass('col-lg-6 col-md-6 col-sm-8 col-xs-12').addClass('col-lg-9 col-md-9 col-sm-8 col-xs-12 cont-box-area blogsidebarspage').insertBefore(_$('.sbcenter div:last-child.widget-area'));
                jQuery.post(ajaxurl,
                    {
                        action: 'masb_get_sidebar',
                        s_id: jQuery('#secretlab-f-post-layout-settings [name$=\'right_sidebar_widgets\']').val(),
                        nonce: masb_ajax.nonce
                    },
                    function (result) {
                        if (result === -1) {
                            // 403 Error
                            return;
                        }

                        if (typeof( result ) !== 'object' || result.sidebar === undefined) {
                            // Error HTML
                            return;
                        }

                        if (result.status === 0) {
                            // Wrong sidebar type
                            return;
                        }
                        if (result.sidebar !== '') {
                            _$('.sbcenter div:last-child.widget-area').prepend(result.sidebar);
                        } else {
                            _$('.sbcenter div:last-child.widget-area').html('');
                        }
                    });
                _$('.sbcenter div:first-child.widget-area').remove();
            }
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'left_sidebar_widgets\']').on('change', function (e) {
            jQuery.post(ajaxurl,
                {
                    action: 'masb_get_sidebar',
                    s_id: jQuery(e.target).val(),
                    nonce: masb_ajax.nonce
                },
                function (result) {
                    if (result === -1) {
                        // 403 Error
                        return;
                    }

                    if (typeof( result ) !== 'object' || result.sidebar === undefined) {
                        // Error HTML
                        return;
                    }

                    if (result.status === 0) {
                        // Wrong sidebar type
                        return;
                    }
                    if (result.sidebar !== '') {
                        _$('.sbcenter div:first-child.widget-area').prepend(result.sidebar);
                    } else {
                        _$('.sbcenter div:first-child.widget-area').html('');
                    }
                });
        });

        jQuery('#secretlab-f-post-layout-settings [id$=\'right_sidebar_widgets\']').on('change', function (e) {
            jQuery.post(ajaxurl,
                {
                    action: 'masb_get_sidebar',
                    s_id: jQuery(e.target).val(),
                    nonce: masb_ajax.nonce
                },
                function (result) {
                    if (result === -1) {
                        // 403 Error
                        return;
                    }

                    if (typeof( result ) !== 'object' || result.sidebar === undefined) {
                        // Error HTML
                        return;
                    }

                    if (result.status === 0) {
                        // Wrong sidebar type
                        return;
                    }
                    if (result.sidebar !== '') {
                        _$('.sbcenter div:last-child.widget-area').prepend(result.sidebar);
                    } else {
                        _$('.sbcenter div:last-child.widget-area').html('');
                    }
                });
        });

        jQuery.fn.serializeMultiArray = function () {
            let data = {};

            function buildInputObject(arr, val) {
                if (arr.length < 1)
                    return val;
                let objkey = arr[0];
                if (objkey.slice(-1) === ']') {
                    objkey = objkey.slice(0, -1);
                }
                let result = {};
                if (arr.length === 1) {
                    result[objkey] = val;
                } else {
                    arr.shift();
                    result[objkey] = buildInputObject(arr, val);
                }
                return result;
            }

            jQuery.each(this.serializeArray(), function () {
                let val = this.value;
                let c = this.name.split("[");
                let a = buildInputObject(c, val);
                jQuery.extend(true, data, a);
            });

            return data;
        };

        jQuery('#masb-f-save-page-settings').on('click', function (e) {

            let id = jQuery(e.target).parent().find('#secretlab-post-layout-settings-box').attr('n');
            let data = JSON.stringify(jQuery('.custom_settings_box .custom_setting_input').serializeMultiArray());
            let seo = JSON.stringify(jQuery('#masb-seot-settings .textinput').serializeMultiArray());

            kc.msg(masb_ajax.loading, 'loading');

            jQuery.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'masb_f_save_page_settings',
                    post_id: id,
                    l_set: data,
                    masb_seo: seo,
                    nonce: masb_ajax.nonce
                },
                success: function (result) {
                    if (result === -1) {
                        kc.msg(masb_ajax.forbidden, 'error', 'sl-close');
                        return;
                    }

                    if (typeof( result ) !== 'object' || result.message === undefined) {
                        // Error HTML
                        return;
                    }

                    if (result.status === 0) {
                        kc.msg(result.message, 'error', 'sl-close', 500);
                    } else if (result.status === 1) {
                        kc.msg(result.message, 'success', 'sl-check');
                    }
                },
                complete: function (answer) {
                }

            });

        });

        jQuery('#masb-f-save-composer-box-settings').on('click', function (e) {

            let id = jQuery(e.target).parent().find('#secretlab-post-layout-settings-box').attr('n');
            let data = JSON.stringify(jQuery('#secretlab-post-layout-settings-box .composer-block-type-input').serializeMultiArray());

            kc.msg(masb_ajax.loading, 'loading');

            jQuery.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'masb_f_save_composer_block_settings',
                    post_id: id,
                    post_type: 'composer_widget',
                    w_type: data,
                    nonce: masb_ajax.nonce
                },
                success: function (result) {
                    if (result === -1) {
                        kc.msg(masb_ajax.forbidden, 'error', 'sl-close');
                        return;
                    }

                    if (typeof( result ) !== 'object' || result.message === undefined) {
                        // Error HTML
                        return;
                    }

                    if (result.status === 0) {
                        kc.msg(result.message, 'error', 'sl-close');
                    } else if (result.status === 1) {
                        kc.msg(result.message, 'success', 'sl-check');
                    }
                },
                complete: function (answer) {
                }

            });

        });

    }

    /* Custom Sidebars */
    function handle_widgets() {
        let addWidgetTemplate = jQuery('#sl-add-widget-template');

        if(addWidgetTemplate.length < 1){
            return;
        }

        let widgetsRight = jQuery('#widgets-right');

        widgetsRight.prepend(addWidgetTemplate.html());
        addWidgetTemplate.remove();

        function remove_widget_area(e) {
            let widget = jQuery(e.currentTarget).parents('.widgets-holder-wrap:eq(0)'),
                title = widget.find('.sidebar-name h2'),
                spinner = jQuery(title).children('span'),
                widget_name = jQuery.trim(title.text());

            jQuery(spinner).css({'visibility': 'visible'});
            widget.addClass('closed');

            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: 'masb_delete_widget_area',
                    name: widget_name
                },

                success: function (response) {
                    if (response.trim() === 'widget_area-deleted') {
                        widget.slideUp(200).remove();
                    }
                }
            });
        }

        function add_widget_handler(e) {
            let response,
                widget_name = jQuery('input#sl-add-widget-input').val(),
                spinner = jQuery('#sl-add-widget .spinner');
            e.preventDefault();
            spinner.css({'display': 'block'});
            jQuery.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: 'masb_add_widget_area',
                    name: widget_name
                },
                success: function (response) {
                    response = JSON.parse(response.trim());
                    if (response['code'] === 0) {
                        jQuery('input#sl-add-widget-input').css({'borderColor': 'red'})
                        jQuery('#widget_informer').css({'display': 'block'}).html(response['text']);
                        spinner.css({'display': 'none'});
                    }
                    if (response['code'] === 1) {
                        jQuery('input#sl-add-widget-input').css({'borderColor': '#ddd'});
                        spinner.removeClass('spinner').addClass('redirect').html('<strong>redirecting ...</strong>');
                        window.location.href = document.location.href;
                    }
                }
            });
            return false;
        }

        widgetsRight.find('.sidebar-sl-custom .widgets-sortables').each(function (i, el) {
            jQuery(el).append('<span class="sl-widget_area-delete"></span>');
        });

        jQuery('.widget-liquid-right').on('click', 'span.sl-widget_area-delete', remove_widget_area);
        jQuery('input.addWidgetArea-button').on('click', add_widget_handler)
    }

    handle_widgets();

    function choose_header_type() {

        jQuery('.inside [name="composer_block_type"]').on('click', function (e) {

            let val = (jQuery(e.target).attr('value'));
            let tar = jQuery(e.target).parents('.inside').find('.header_block_type_container');
            if (val === 'composer_block_header') {
                tar.css('display', 'block');
            } else {
                tar.css('display', 'none');
            }

        });
    }

    choose_header_type();

    if (!jQuery.isFunction(jQuery.fn.lightTabs)) {
        jQuery.fn.lightTabs = function (options) {

            let createTabs = function () {
                let tabs = this, i = 0;

                let showTab = function (i, e) {
                    if ('' === e) {
                        jQuery(tabs).children("div").children("div").hide();
                        jQuery(tabs).children("div").children("div").eq(i).show();
                        jQuery(tabs).children("ul").children("li").removeClass("active");
                        jQuery(tabs).children("ul").children("li").eq(i).addClass("active");
                    } else {
                        jQuery(e.target).parents(".slmenu-tabs").children("div").children("div").hide();
                        jQuery(e.target).parents(".slmenu-tabs").children("div").children("div").eq(i).show();
                        jQuery(e.target).parent("ul").children("li").removeClass("active");
                        jQuery(e.target).parent("ul").children("li").eq(i).addClass("active");
                    }
                };

                showTab(0, '');

                jQuery(tabs).children("ul").children("li").each(function (index, element) {
                    jQuery(element).attr("data-page", i);
                    i++;
                });

                jQuery(tabs).children("ul").children("li").click(function (e) {
                    showTab(parseInt(jQuery(this).attr("data-page")), e);
                });
            };
            return this.each(createTabs);
        };
    }

    jQuery(document).ready(function () {
        jQuery(".slmenu-tabs").lightTabs();
    });

    /* ABC Split */

    jQuery("#masb-abcs-add-new-test").on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        let n = (jQuery("form .masb-abcs-section").last().find(".masb-abc-split-slug").length !== 0) ? jQuery("form .masb-abcs-section").last().find(".masb-abc-split-slug").data("test-count") + 1 : 0;
        let data = {
            action: "masb_abcs_add_new_test",
            masb_nonce: abc_split.nonce,
            test_count: n
        };
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: data,
            success: function (data) {
                if (data !== '') {
                    jQuery(data).insertBefore("#masb-abcs-add-new-test");
                }
            }
        });
    });
    jQuery(document).on("click", ".masb-abcs-rem-test", function (e) {
        e.preventDefault();
        e.stopPropagation();
        let n = jQuery(e.target).parents(".masb-abcs-section");
        n.remove();

    });
    jQuery(document).on("click", ".masb-abcs-clear-stats", function (e) {
        e.preventDefault();
        e.stopPropagation();
        let n = jQuery(e.target).data("stats");
        let data = {
            action: "masb_abcs_clear_stats",
            masb_nonce: abc_split.nonce,
            stats_count: n
        };
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: data,
            success: function (data) {
                if (data !== '') {
                    let div = jQuery(e.target).parents(".col-sm-12");
                    div.empty();
                    div.append(data);
                }
            }
        });
    });

    let masb_page_prev;
    jQuery(document).on("focus", ".masb-abcs-rpages", function (e) {
        masb_page_prev = jQuery(e.target).val();
    });
    jQuery(document).on("change", ".masb-abcs-rpages", function (e) {
        console.log(e);
        let val = Number(jQuery(e.target).val());
        let rpages = jQuery(".masb-abcs-rpages[id != " + jQuery(e.target).attr("id") + "]").map(function () {
            return Number(jQuery(this).val());
        });
        if (jQuery.inArray(val, rpages) !== -1 && val !== 0) {
            jQuery(e.target).val(masb_page_prev);
            alert(masb_abcs.same_page);
        }
    });

});

/* For Base64 */
let Base64 = {
    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=", encode: function (e) {
        let t = "";
        let n, r, i, s, o, u, a;
        let f = 0;
        e = Base64._utf8_encode(e);
        while (f < e.length) {
            n = e.charCodeAt(f++);
            r = e.charCodeAt(f++);
            i = e.charCodeAt(f++);
            s = n >> 2;
            o = (n & 3) << 4 | r >> 4;
            u = (r & 15) << 2 | i >> 6;
            a = i & 63;
            if (isNaN(r)) {
                u = a = 64
            } else if (isNaN(i)) {
                a = 64
            }
            t = t + this._keyStr.charAt(s) + this._keyStr.charAt(o) + this._keyStr.charAt(u) + this._keyStr.charAt(a)
        }
        return t
    }, decode: function (e) {
        let t = "";
        let n, r, i;
        let s, o, u, a;
        let f = 0;
        e = e.replace(/[^A-Za-z0-9+/=]/g, "");
        while (f < e.length) {
            s = this._keyStr.indexOf(e.charAt(f++));
            o = this._keyStr.indexOf(e.charAt(f++));
            u = this._keyStr.indexOf(e.charAt(f++));
            a = this._keyStr.indexOf(e.charAt(f++));
            n = s << 2 | o >> 4;
            r = (o & 15) << 4 | u >> 2;
            i = (u & 3) << 6 | a;
            t = t + String.fromCharCode(n);
            if (u !== 64) {
                t = t + String.fromCharCode(r)
            }
            if (a !== 64) {
                t = t + String.fromCharCode(i)
            }
        }
        t = Base64._utf8_decode(t);
        return t
    }, _utf8_encode: function (e) {
        e = e.replace(/rn/g, "n");
        let t = "";
        for (let n = 0; n < e.length; n++) {
            let r = e.charCodeAt(n);
            if (r < 128) {
                t += String.fromCharCode(r)
            } else if (r > 127 && r < 2048) {
                t += String.fromCharCode(r >> 6 | 192);
                t += String.fromCharCode(r & 63 | 128)
            } else {
                t += String.fromCharCode(r >> 12 | 224);
                t += String.fromCharCode(r >> 6 & 63 | 128);
                t += String.fromCharCode(r & 63 | 128)
            }
        }
        return t
    }, _utf8_decode: function (e) {
        let t = "";
        let n = 0;
        let r = c1 = c2 = 0;
        while (n < e.length) {
            r = e.charCodeAt(n);
            if (r < 128) {
                t += String.fromCharCode(r);
                n++
            } else if (r > 191 && r < 224) {
                c2 = e.charCodeAt(n + 1);
                t += String.fromCharCode((r & 31) << 6 | c2 & 63);
                n += 2
            } else {
                c2 = e.charCodeAt(n + 1);
                c3 = e.charCodeAt(n + 2);
                t += String.fromCharCode((r & 15) << 12 | (c2 & 63) << 6 | c3 & 63);
                n += 3
            }
        }
        return t
    }
};

function kc_admin_view_masb_table(data, el) {
    let html = '';
    if (undefined !== data.content) {
        html = data.content;
    }
    return html;
}