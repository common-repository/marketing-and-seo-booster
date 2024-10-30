jQuery(document).ready(function () {
    "use strict";

    jQuery('#sl_icons_upload').on('click', function (e) {
        e.preventDefault();
        var icons_loader = wp.media({
            frame: jQuery(this).data.frame,
            title: sl_icons_manager_params.title,
            multiple: false
        });
        icons_loader.on('select update insert', function (e) {
            var state = icons_loader.state(), selection = state.get('selection').first().toJSON(),
                sii = jQuery('#sl_icons_upload');
            sii.after('<img id="suppaSaveLoader" style="padding:0 15px;" src="' + sl_icons_manager_params.loadimg + '" >');
            sii.attr("disabled", "disabled");
            sii.addClass("not-allowed");
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {action: 'sl_add_zipped_font', values: selection, nonce: sl_icons_manager_params.nonce,},
                cache: false,
                complete: function (data) {
                },
                success: function (data) {
                    if (data.match(/Error/)) alert(data);
                    else location.reload();
                },
                error: function (data) {
                }
            });
        });
        icons_loader.open();
    });

    jQuery('.remove_mega_icons_set').on('click', function (e) {
        console.log(sl_icons_manager_params.nonce);
        jQuery(e.currentTarget).before('<img id="suppaSaveLoader" style="padding:0 15px;" src="' + sl_icons_manager_params.loadimg + '" >');
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'sl_remove_zipped_font',
                font: jQuery(e.target).attr('data-delete'),
                nonce: sl_icons_manager_params.nonce
            },
            cache: false,
            complete: function (data) {
                location.reload();
            },
            success: function (data) {
            }
        });
    });

    function js_searcher() {
        var icon_search_process = false;
        jQuery('.sl_admin_icons_search input').on('keyup', function (e) {
            if (!icon_search_process) {
                var needle = jQuery(e.target).val(), j;
                icon_search_process = true;
                jQuery(this).closest(".sl_icons_admin_box").find('.sl_icons_block [id*="-icons_table"]').each(function (i, el) {
                    j = 0;
                    jQuery(el).find('span i').each(function (i, el) {
                        var rgxp = new RegExp(needle);
                        if (jQuery(el).attr('class').match(rgxp)) {
                            jQuery(el).parent().css({display: 'inline-block'});
                            j++;
                        }
                        else jQuery(el).parent().css({display: 'none'});
                    });
                    jQuery(el).find('.icons-count').html(j + ' icons');
                });
                icon_search_process = false;
            }
        });

    }

    js_searcher();

    var mediaGridObserver = new MutationObserver(function (mutations) {

        for (var i = 0; i < mutations.length; i++) {

            for (var j = 0; j < mutations[i].addedNodes.length; j++) {
                var element = jQuery(mutations[i].addedNodes[j]);

                if (element.attr('class')) {
                    var elementClass = element.attr('class');
                    if (element.attr('class').indexOf('attachment') != -1) {

                        var attachmentPreview = element.children('.attachment-preview');
                        if (attachmentPreview.length != 0) {
                            if (attachmentPreview.attr('class').indexOf('subtype-svg+xml') != -1) {
                                var handler = function (element) {

                                    jQuery.ajax({

                                        url: ajaxurl,
                                        type: "POST",
                                        dataType: 'html',
                                        data: {
                                            'action': 'svg_get_attachment_url',
                                            'attachmentID': element.attr('data-id')
                                        },
                                        success: function (data) {
                                            if (data) {
                                                element.find('img').parent('div').html(data);
                                                element.find('.filename').text(element.attr('aria-label')+'.svg');
                                            }
                                        }
                                    });

                                }(element);

                            }
                        }
                    }
                }
            }
        }
    });

    var attachmentPreviewObserver = new MutationObserver(function (mutations) {
        for (var i = 0; i < mutations.length; i++) {
            for (var j = 0; j < mutations[i].addedNodes.length; j++) {
                var element = jQuery(mutations[i].addedNodes[j]);
                var onAttachmentPage = false;
                if ((element.hasClass('attachment-details')) || element.find('.attachment-details').length != 0) {
                    onAttachmentPage = true;
                }

                if (onAttachmentPage == true) {
                    var urlLabel = element.find('label[data-setting="url"]');
                    if (urlLabel.length != 0) {
                        var value = urlLabel.find('input[readonly]').val();
                        if(value.includes('.svg')){
                            element.find('.thumbnail-image img').attr('src', value);
                        } else {
                            element.find('.details-image').attr('src', value);
                        }
                    }
                }
            }
        }
    });

    mediaGridObserver.observe(document.body, {
        childList: true,
        subtree: true
    });

    attachmentPreviewObserver.observe(document.body, {
        childList: true,
        subtree: true
    });

});