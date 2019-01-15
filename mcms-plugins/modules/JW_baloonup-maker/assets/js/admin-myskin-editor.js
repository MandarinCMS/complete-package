/**
 * BaloonUp Maker v1.4
 */


var PopMakeAdmin;
(function ($, document, undefined) {
    "use strict";

    window.PUM_Admin = window.PUM_Admin || {};

    var $document = $(document);

    PopMakeAdmin = {
        myskin_page_listeners: function () {
            var self = this;

            $('.empreview .example-baloonup-overlay, .empreview .example-baloonup, .empreview .title, .empreview .content, .empreview .close-baloonup').css('cursor', 'pointer');
            $(document)
                .on('click', '.empreview .example-baloonup-overlay, .empreview .example-baloonup, .empreview .title, .empreview .content, .empreview .close-baloonup', function (event) {
                    var $this = $(this),
                        clicked_class = $this.attr('class'),
                        pos = 0;

                    event.preventDefault();
                    event.stopPropagation();

                    switch (clicked_class) {
                    case 'example-baloonup-overlay':
                        pos = $('#balooncreate_baloonup_myskin_overlay').offset().top;
                        break;
                    case 'example-baloonup':
                        pos = $('#balooncreate_baloonup_myskin_container').offset().top;
                        break;
                    case 'title':
                        pos = $('#balooncreate_baloonup_myskin_title').offset().top;
                        break;
                    case 'content':
                        pos = $('#balooncreate_baloonup_myskin_content').offset().top;
                        break;
                    case 'close-baloonup':
                        pos = $('#balooncreate_baloonup_myskin_close').offset().top;
                        break;
                    }

                    $("html, body").animate({
                        scrollTop: pos + 'px'
                    });
                })
                .on('change', 'select.font-family', function () {
                    $('select.font-weight option, select.font-style option', $(this).parents('table')).prop('selected', false);
                    self.update_font_selectboxes();
                })
                .on('change', 'select.font-weight, select.font-style', function () {
                    self.update_font_selectboxes();
                })
                .on('change input focusout', 'select, input', function () {
                    self.update_myskin();
                })
                .on('change', 'select.border-style', function () {
                    var $this = $(this);
                    if ($this.val() === 'none') {
                        $this.parents('table').find('.border-options').hide();
                    } else {
                        $this.parents('table').find('.border-options').show();
                    }
                })
                .on('change', '#baloonup_myskin_close_location', function () {
                    var $this = $(this),
                        table = $this.parents('table');
                    $('tr.topleft, tr.topright, tr.bottomleft, tr.bottomright', table).hide();
                    $('tr.' + $this.val(), table).show();
                });
        },
        update_myskin: function () {
            var form_values = $("[name^='baloonup_myskin_']").serializeArray(),
                myskin = {},
                i;
            for (i = 0; form_values.length > i; i += 1) {
                if (form_values[i].name.indexOf('baloonup_myskin_') === 0) {
                    myskin[form_values[i].name.replace('baloonup_myskin_', '')] = form_values[i].value;
                }
            }
            this.remyskin_baloonup(myskin);
        },
        myskin_preview_scroll: function () {
            var $preview = $('#balooncreate-myskin-editor .empreview, body.post-type-baloonup_myskin form#post #balooncreate_baloonup_myskin_preview'),
                $parent = $preview.parent(),
                startscroll = $preview.offset().top - 50;
            $(window).on('scroll', function () {
                if ($('> .postbox:visible', $parent).index($preview) === ($('> .postbox:visible', $parent).length - 1) && $(window).scrollTop() >= startscroll) {
                    $preview.css({
                        left: $preview.offset().left,
                        width: $preview.width(),
                        height: $preview.height(),
                        position: 'fixed',
                        top: 50
                    });
                } else {
                    $preview.removeAttr('style');
                }
            });
        },
        update_font_selectboxes: function () {
            return $('select.font-family').each(function () {
                var $this = $(this),
                    $font_weight = $this.parents('table').find('select.font-weight'),
                    $font_style = $this.parents('table').find('select.font-style'),
                    $font_weight_options = $font_weight.find('option'),
                    $font_style_options = $font_style.find('option'),
                    font,
                    i;


                // Google Font Chosen
                if (balooncreate_google_fonts[$this.val()] !== undefined) {
                    font = balooncreate_google_fonts[$this.val()];

                    $font_weight_options.hide();
                    $font_style_options.hide();

                    if (font.variants.length) {
                        for (i = 0; font.variants.length > i; i += 1) {
                            if (font.variants[i] === 'regular') {
                                $('option[value=""]', $font_weight).show();
                                $('option[value=""]', $font_style).show();
                            } else {
                                if (font.variants[i].indexOf('italic') >= 0) {

                                    $('option[value="italic"]', $font_style).show();
                                }
                                $('option[value="' + parseInt(font.variants[i], 10) + '"]', $font_weight).show();
                            }
                        }
                    }
                    // Standard Font Chosen
                } else {
                    $font_weight_options.show();
                    $font_style_options.show();
                }

                $font_weight.parents('tr:first').show();
                if ($font_weight.find('option:visible').length <= 1) {
                    $font_weight.parents('tr:first').hide();
                } else {
                    $font_weight.parents('tr:first').show();
                }

                $font_style.parents('tr:first').show();
                if ($font_style.find('option:visible').length <= 1) {
                    $font_style.parents('tr:first').hide();
                } else {
                    $font_style.parents('tr:first').show();
                }
            });
        },
        convert_myskin_for_preview: function (myskin) {
            return;
            //$.fn.balooncreate.myskins[balooncreate_default_myskin] = window.PUM_Admin.utilities.convert_meta_to_object(myskin);
        },
        initialize_myskin_page: function () {
            $('#baloonup-titlediv').insertAfter('#titlediv');

            var self = this,
                table = $('#baloonup_myskin_close_location').parents('table');
            self.update_myskin();
            self.myskin_page_listeners();
            self.myskin_preview_scroll();
            self.update_font_selectboxes();

            $(document)
                .on('click', '.balooncreate-preview', function (e) {
                    e.preventDefault();
                    $('#balooncreate-preview, #balooncreate-overlay').css({visibility: "visible"}).show();
                })
                .on('click', '.balooncreate-close', function () {
                    $('#balooncreate-preview, #balooncreate-overlay').hide();
                });

            $('select.border-style').each(function () {
                var $this = $(this);
                if ($this.val() === 'none') {
                    $this.parents('table').find('.border-options').hide();
                } else {
                    $this.parents('table').find('.border-options').show();
                }
            });

            $('.pum-color-picker.background-color').each(function () {
                var $this = $(this);
                if ($this.val() === '') {
                    $this.parents('table').find('.background-opacity').hide();
                } else {
                    $this.parents('table').find('.background-opacity').show();
                }
            });

            $('tr.topleft, tr.topright, tr.bottomleft, tr.bottomright', table).hide();
            switch ($('#baloonup_myskin_close_location').val()) {
            case "topleft":
                $('tr.topleft', table).show();
                break;
            case "topright":
                $('tr.topright', table).show();
                break;
            case "bottomleft":
                $('tr.bottomleft', table).show();
                break;
            case "bottomright":
                $('tr.bottomright', table).show();
                break;
            }
        },
        remyskin_baloonup: function (myskin) {
            var $overlay = $('.empreview .example-baloonup-overlay, #balooncreate-overlay'),
                $container = $('.empreview .example-baloonup, #balooncreate-preview'),
                $title = $('.title, .balooncreate-title', $container),
                $content = $('.content, .balooncreate-content', $container),
                $close = $('.close-baloonup, .balooncreate-close', $container),
                container_inset = myskin.container_boxshadow_inset === 'yes' ? 'inset ' : '',
                close_inset = myskin.close_boxshadow_inset === 'yes' ? 'inset ' : '',
                link;

            this.convert_myskin_for_preview(myskin);

            if (balooncreate_google_fonts[myskin.title_font_family] !== undefined) {

                link = "//fonts.googleapis.com/css?family=" + myskin.title_font_family;

                if (myskin.title_font_weight !== 'normal') {
                    link += ":" + myskin.title_font_weight;
                }
                if (myskin.title_font_style === 'italic') {
                    if (link.indexOf(':') === -1) {
                        link += ":";
                    }
                    link += "italic";
                }
                $('body').append('<link href="' + link + '" rel="stylesheet" type="text/css">');
            }
            if (balooncreate_google_fonts[myskin.content_font_family] !== undefined) {

                link = "//fonts.googleapis.com/css?family=" + myskin.content_font_family;

                if (myskin.content_font_weight !== 'normal') {
                    link += ":" + myskin.content_font_weight;
                }
                if (myskin.content_font_style === 'italic') {
                    if (link.indexOf(':') === -1) {
                        link += ":";
                    }
                    link += "italic";
                }
                $('body').append('<link href="' + link + '" rel="stylesheet" type="text/css">');
            }
            if (balooncreate_google_fonts[myskin.close_font_family] !== undefined) {

                link = "//fonts.googleapis.com/css?family=" + myskin.close_font_family;

                if (myskin.close_font_weight !== 'normal') {
                    link += ":" + myskin.close_font_weight;
                }
                if (myskin.close_font_style === 'italic') {
                    if (link.indexOf(':') === -1) {
                        link += ":";
                    }
                    link += "italic";
                }
                $('body').append('<link href="' + link + '" rel="stylesheet" type="text/css">');
            }

            $overlay.removeAttr('style').css({
                backgroundColor: window.PUM_Admin.utils.convert_hex(myskin.overlay_background_color, myskin.overlay_background_opacity)
            });
            $container.removeAttr('style').css({
                padding: myskin.container_padding + 'px',
                backgroundColor: window.PUM_Admin.utils.convert_hex(myskin.container_background_color, myskin.container_background_opacity),
                borderStyle: myskin.container_border_style,
                borderColor: myskin.container_border_color,
                borderWidth: myskin.container_border_width + 'px',
                borderRadius: myskin.container_border_radius + 'px',
                boxShadow: container_inset + myskin.container_boxshadow_horizontal + 'px ' + myskin.container_boxshadow_vertical + 'px ' + myskin.container_boxshadow_blur + 'px ' + myskin.container_boxshadow_spread + 'px ' + window.PUM_Admin.utils.convert_hex(myskin.container_boxshadow_color, myskin.container_boxshadow_opacity)
            });
            $title.removeAttr('style').css({
                color: myskin.title_font_color,
                lineHeight: myskin.title_line_height + 'px',
                fontSize: myskin.title_font_size + 'px',
                fontFamily: myskin.title_font_family,
                fontStyle: myskin.title_font_style,
                fontWeight: myskin.title_font_weight,
                textAlign: myskin.title_text_align,
                textShadow: myskin.title_textshadow_horizontal + 'px ' + myskin.title_textshadow_vertical + 'px ' + myskin.title_textshadow_blur + 'px ' + window.PUM_Admin.utils.convert_hex(myskin.title_textshadow_color, myskin.title_textshadow_opacity)
            });
            $content.removeAttr('style').css({
                color: myskin.content_font_color,
                //fontSize: myskin.content_font_size+'px',
                fontFamily: myskin.content_font_family,
                fontStyle: myskin.content_font_style,
                fontWeight: myskin.content_font_weight
            });
            $close.html(myskin.close_text).removeAttr('style').css({
                padding: myskin.close_padding + 'px',
                height: myskin.close_height > 0 ? myskin.close_height + 'px' : 'auto',
                width: myskin.close_width > 0 ? myskin.close_width + 'px' : 'auto',
                backgroundColor: window.PUM_Admin.utils.convert_hex(myskin.close_background_color, myskin.close_background_opacity),
                color: myskin.close_font_color,
                lineHeight: myskin.close_line_height + 'px',
                fontSize: myskin.close_font_size + 'px',
                fontFamily: myskin.close_font_family,
                fontWeight: myskin.close_font_weight,
                fontStyle: myskin.close_font_style,
                borderStyle: myskin.close_border_style,
                borderColor: myskin.close_border_color,
                borderWidth: myskin.close_border_width + 'px',
                borderRadius: myskin.close_border_radius + 'px',
                boxShadow: close_inset + myskin.close_boxshadow_horizontal + 'px ' + myskin.close_boxshadow_vertical + 'px ' + myskin.close_boxshadow_blur + 'px ' + myskin.close_boxshadow_spread + 'px ' + window.PUM_Admin.utils.convert_hex(myskin.close_boxshadow_color, myskin.close_boxshadow_opacity),
                textShadow: myskin.close_textshadow_horizontal + 'px ' + myskin.close_textshadow_vertical + 'px ' + myskin.close_textshadow_blur + 'px ' + window.PUM_Admin.utils.convert_hex(myskin.close_textshadow_color, myskin.close_textshadow_opacity)
            });
            switch (myskin.close_location) {
            case "topleft":
                $close.css({
                    top: myskin.close_position_top + 'px',
                    left: myskin.close_position_left + 'px'
                });
                break;
            case "topright":
                $close.css({
                    top: myskin.close_position_top + 'px',
                    right: myskin.close_position_right + 'px'
                });
                break;
            case "bottomleft":
                $close.css({
                    bottom: myskin.close_position_bottom + 'px',
                    left: myskin.close_position_left + 'px'
                });
                break;
            case "bottomright":
                $close.css({
                    bottom: myskin.close_position_bottom + 'px',
                    right: myskin.close_position_right + 'px'
                });
                break;
            }
            $(document).trigger('balooncreate-admin-remyskin', [myskin]);
        }

    };

    $('.balooncreate-range-manual').addClass('pum-range-manual').parent('td').addClass('pum-field').addClass('pum-field-rangeslider');
    $('.range-value-unit').addClass('pum-range-value-unit');


    $document.ready(function () {

        PopMakeAdmin.initialize_myskin_page();
        $document.trigger('pum_init');

        // TODO Can't figure out why this is needed, but it looks stupid otherwise when the first condition field defaults to something other than the placeholder.
        $('#pum-first-condition, #pum-first-trigger, #pum-first-cookie')
            .val(null)
            .trigger('change');
    });
}(jQuery, document));