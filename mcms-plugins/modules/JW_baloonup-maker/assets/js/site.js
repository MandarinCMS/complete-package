/**
 * Adds needed backward compatibility for older versions of jQuery
 */
(function ($) {
    "use strict";
    if ($.fn.on === undefined) {
        $.fn.on = function (types, sel, fn) {
            return this.delegate(sel, types, fn);
        };
    }
    if ($.fn.off === undefined) {
        $.fn.off = function (types, sel, fn) {
            return this.undelegate(sel, types, fn);
        };
    }

    if ($.fn.bindFirst === undefined) {
        $.fn.bindFirst = function (which, handler) {
            var $el = $(this),
                events,
                registered;

            $el.unbind(which, handler);
            $el.bind(which, handler);

            events = $._data($el[0]).events;
            registered = events[which];
            registered.unshift(registered.pop());

            events[which] = registered;
        };
    }

    if ($.fn.outerHtml === undefined) {
        $.fn.outerHtml = function () {
            var $el = $(this).clone(),
                $temp = $('<div/>').append($el);

            return $temp.html();
        };
    }

    if (Date.now === undefined) {
        Date.now = function () {
            return new Date().getTime();
        };
    }
}(jQuery));

/**
 * Defines the core $.balooncreate function which will load the proper methods.
 * Version 1.4
 */
var PUM;
(function ($, document, undefined) {
    "use strict";

    window.pum_vars = window.pum_vars || {
        // TODO Add defaults.
        default_myskin: '0',
        home_url: '/',
        version: 1.7,
        ajaxurl: '',
        restapi: false,
        rest_nonce: null,
        debug_mode: false,
        disable_tracking: true,
        message_position: 'top',
        core_sub_forms_enabled: true,
        baloonups: {}
    };

    window.pum_baloonups = window.pum_baloonups || {};

    // Backward compatibility fill.
    window.pum_vars.baloonups = window.pum_baloonups;

    function isInt(value) {
        return !isNaN(value) && parseInt(Number(value)) === parseInt(value) && !isNaN(parseInt(value, 10));
    }

    function Selector_Cache() {
        var elementCache = {};

        var get_from_cache = function (selector, $ctxt, reset) {

            if ('boolean' === typeof $ctxt) {
                reset = $ctxt;
                $ctxt = false;
            }
            var cacheKey = $ctxt ? $ctxt.selector + ' ' + selector : selector;

            if (undefined === elementCache[cacheKey] || reset) {
                elementCache[cacheKey] = $ctxt ? $ctxt.find(selector) : jQuery(selector);
            }

            return elementCache[cacheKey];
        };

        get_from_cache.elementCache = elementCache;
        return get_from_cache;
    }

    function string_to_ref(object, reference) {
        function arr_deref(o, ref, i) {
            return !ref ? o : (o[ref.slice(0, i ? -1 : ref.length)]);
        }

        function dot_deref(o, ref) {
            return !ref ? o : ref.split('[').reduce(arr_deref, o);
        }

        return reference.split('.').reduce(dot_deref, object);
    }

    PUM = {
        get: new Selector_Cache(),
        getBaloonUp: function (el) {
            var $this;

            // Quick Shortcuts
            if (isInt(el)) {
                $this = PUM.get('#pum-' + el);
            } else if (el === 'current') {
                $this = PUM.get('.pum-overlay.pum-active:eq(0)', true);
            } else if (el === 'open') {
                $this = PUM.get('.pum-overlay.pum-active', true);
            } else if (el === 'closed') {
                $this = PUM.get('.pum-overlay:not(.pum-active)', true);
            } else if (el instanceof jQuery) {
                $this = el;
            } else {
                $this = $(el);
            }

            if ($this.hasClass('pum-overlay')) {
                return $this;
            }

            if ($this.hasClass('balooncreate')) {
                return $this.parents('.pum-overlay');
            }

            return $this.parents('.pum-overlay').length ? $this.parents('.pum-overlay') : $();
        },
        open: function (el, callback) {
            PUM.getBaloonUp(el).balooncreate('open', callback);
        },
        close: function (el, callback) {
            PUM.getBaloonUp(el).balooncreate('close', callback);
        },
        preventOpen: function (el) {
            PUM.getBaloonUp(el).addClass('preventOpen');
        },
        getSettings: function (el) {
            var $baloonup = PUM.getBaloonUp(el);

            return $baloonup.balooncreate('getSettings');
        },
        getSetting: function (el, key, _default) {
            var settings = PUM.getSettings(el),
                value = string_to_ref(settings, key);

            return typeof value !== 'undefined' ? value : (_default !== undefined ? _default : null);
        },
        checkConditions: function (el) {
            return PUM.getBaloonUp(el).balooncreate('checkConditions');
        },
        getCookie: function (cookie_name) {
            return $.pm_cookie(cookie_name);
        },
        setCookie: function (el, settings) {
            var $baloonup = PUM.getBaloonUp(el);

            $baloonup.balooncreate('setCookie', jQuery.extend({
                name: 'pum-' + PUM.getSetting(el, 'id'),
                expires: '+30 days'
            }, settings));
        },
        clearCookie: function (cookie_name, callback) {
            $.pm_remove_cookie(cookie_name);

            if (typeof callback === 'function') {
                callback();
            }
        },
        clearCookies: function (el, callback) {
            var $baloonup = PUM.getBaloonUp(el),
                settings = $baloonup.balooncreate('getSettings'),
                cookies = settings.cookies,
                cookie = null,
                i;

            if (cookies !== undefined && cookies.length) {
                for (i = 0; cookies.length > i; i += 1) {
                    $.pm_remove_cookie(cookies[i].settings.name);
                }
            }

            if (typeof callback === 'function') {
                callback();
            }
        },
        getClickTriggerSelector: function (el, trigger_settings) {
            var $baloonup = PUM.getBaloonUp(el),
                settings = PUM.getSettings(el),
                trigger_selectors = [
                    '.balooncreate-' + settings.id,
                    '.balooncreate-' + decodeURIComponent(settings.slug),
                    'a[href$="#balooncreate-' + settings.id + '"]'
                ];

            if (trigger_settings.extra_selectors && trigger_settings.extra_selectors !== '') {
                trigger_selectors.push(trigger_settings.extra_selectors);
            }

            trigger_selectors = pum.hooks.applyFilters('pum.trigger.click_open.selectors', trigger_selectors, trigger_settings, $baloonup);

            return trigger_selectors.join(', ');
        },
        disableClickTriggers: function (el, trigger_settings) {
            if (el === undefined) {
                // disable all triggers. Not available yet.
                return;
            }

            if (trigger_settings !== undefined) {
                var selector = PUM.getClickTriggerSelector(el, trigger_settings);
                $(selector).removeClass('pum-trigger');
                $(document).off('click.pumTrigger click.balooncreateOpen', selector)
            } else {
                var triggers = PUM.getSetting(el, 'triggers', []);
                if (triggers.length) {
                    for (var i = 0; triggers.length > i; i++) {
                        // If this isn't an explicitly allowed click trigger type skip it.
                        if (pum.hooks.applyFilters('pum.disableClickTriggers.clickTriggerTypes', ['click_open']).indexOf(triggers[i].type) === -1) {
                            continue;
                        }

                        var selector = PUM.getClickTriggerSelector(el, triggers[i].settings);
                        $(selector).removeClass('pum-trigger');
                        $(document).off('click.pumTrigger click.balooncreateOpen', selector)
                    }
                }
            }
        }

    };

    $.fn.balooncreate = function (method) {
        // Method calling logic
        if ($.fn.balooncreate.methods[method]) {
            $(document).trigger('pumMethodCall', arguments);
            return $.fn.balooncreate.methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        if (typeof method === 'object' || !method) {
            return $.fn.balooncreate.methods.init.apply(this, arguments);
        }
        if (window.console) {
            console.warn('Method ' + method + ' does not exist on $.fn.balooncreate');
        }
    };

    // Defines the core $.balooncreate methods.
    $.fn.balooncreate.methods = {
        init: function () {
            return this.each(function () {
                var $baloonup = PUM.getBaloonUp(this),
                    settings = $baloonup.balooncreate('getSettings');

                if (settings.myskin_id <= 0) {
                    settings.myskin_id = pum_vars.default_myskin;
                }

                // TODO Move this to be a single global $(window) function that looks at any open baloonup.
                if (settings.disable_reposition === undefined || !settings.disable_reposition) {
                    $(window).on('resize', function () {
                        if ($baloonup.hasClass('pum-active') || $baloonup.find('.balooncreate.active').length) {
                            $.fn.balooncreate.utilities.throttle(setTimeout(function () {
                                $baloonup.balooncreate('reposition');
                            }, 25), 500, false);
                        }
                    });
                }

                // Added balooncreate settings to the container for temporary backward compatibility with extensions.
                // TODO Once extensions updated remove this.
                $baloonup.find('.pum-container').data('balooncreate', settings);

                $baloonup
                    .data('balooncreate', settings)
                    .trigger('pumInit');

                return this;
            });
        },
        getOverlay: function () {
            return PUM.getBaloonUp(this);
        },
        getContainer: function () {
            return PUM.getBaloonUp(this).find('.pum-container');
        },
        getTitle: function () {
            return PUM.getBaloonUp(this).find('.pum-title') || null;
        },
        getContent: function () {
            return PUM.getBaloonUp(this).find('.pum-content') || null;
        },
        getClose: function () {
            return PUM.getBaloonUp(this).find('.pum-content + .pum-close') || null;
        },
        getSettings: function () {
            var $baloonup = PUM.getBaloonUp(this);
            return $.extend(true, {}, $.fn.balooncreate.defaults, $baloonup.data('balooncreate') || {}, typeof pum_baloonups === 'object' && typeof pum_baloonups[$baloonup.attr('id')] !== 'undefined' ? pum_baloonups[$baloonup.attr('id')] : {});
        },
        state: function (test) {
            var $baloonup = PUM.getBaloonUp(this);

            if (undefined !== test) {
                switch (test) {
                case 'isOpen':
                    return $baloonup.hasClass('pum-open') || $baloonup.balooncreate('getContainer').hasClass('active');
                case 'isClosed':
                    return !$baloonup.hasClass('pum-open') && !$baloonup.balooncreate('getContainer').hasClass('active');
                }
            }
        },
        open: function (callback) {
            var $baloonup = PUM.getBaloonUp(this),
                $container = $baloonup.balooncreate('getContainer'),
                $close = $baloonup.balooncreate('getClose'),
                settings = $baloonup.balooncreate('getSettings'),
                $html = $('html');

            $baloonup.trigger('pumBeforeOpen');

            /**
             * Allow for preventing baloonups from opening.
             */
            if ($baloonup.hasClass('preventOpen') || $container.hasClass('preventOpen')) {
                console.log('prevented');
                $baloonup
                    .removeClass('preventOpen')
                    .removeClass('pum-active')
                    .trigger('pumOpenPrevented');

                return this;
            }

            /**
             * If baloonup isn't stackable close all others.
             */
            if (!settings.stackable) {
                $baloonup.balooncreate('close_all');
            }

            $baloonup.addClass('pum-active');

            /**
             * Hide the close button if delay is active.
             */
            if (settings.close_button_delay > 0) {
                $close.fadeOut(0);
            }

            $html.addClass('pum-open');

            /**
             * Check for and disable the overlay.
             */
            if (settings.overlay_disabled) {
                $html.addClass('pum-open-overlay-disabled');
            } else {
                $html.addClass('pum-open-overlay');
            }

            /**
             * Set position fixed when active.
             */
            if (settings.position_fixed) {
                $html.addClass('pum-open-fixed');
            } else {
                $html.addClass('pum-open-scrollable');
            }

            $baloonup
                .balooncreate('setup_close')
                .balooncreate('reposition')
                .balooncreate('animate', settings.animation_type, function () {

                    /**
                     * Fade the close button in after specified delay.
                     */
                    if (settings.close_button_delay > 0) {
                        setTimeout(function () {
                            $close.fadeIn();
                        }, settings.close_button_delay);
                    }

                    $baloonup.trigger('pumAfterOpen');

                    $.fn.balooncreate.last_open_baloonup = $baloonup;

                    // Fire user passed callback.
                    if (callback !== undefined) {
                        callback();
                        // TODO Test this new method. Then remove the above.
                        //callback.apply(this);
                    }
                });

            return this;
        },
        setup_close: function () {
            var $baloonup = PUM.getBaloonUp(this),
                $close = $baloonup.balooncreate('getClose'),
                settings = $baloonup.balooncreate('getSettings');

            // Add For non built in close buttons and backward compatibility.
            $close = $close.add($('.balooncreate-close, .pum-close', $baloonup).not($close));

            // TODO: Move to a global $(document).on type bind. Possibly look for an inactive class to fail on.
            $close
                .off('click.pum')
                .on("click.pum", function (event) {
                    var $this = $(this),
                        do_default = $this.hasClass('pum-do-default') || ($this.data('do-default') !== undefined && $this.data('do-default'));

                    if (!do_default) {
                        event.preventDefault();
                    }

                    $.fn.balooncreate.last_close_trigger = 'Close Button';
                    $baloonup.balooncreate('close');
                });

            if (settings.close_on_esc_press || settings.close_on_f4_press) {
                // TODO: Move to a global $(document).on type bind. Possibly look for a class to succeed on.
                $(window)
                    .off('keyup.balooncreate')
                    .on('keyup.balooncreate', function (e) {
                        if (e.keyCode === 27 && settings.close_on_esc_press) {
                            $.fn.balooncreate.last_close_trigger = 'ESC Key';
                            $baloonup.balooncreate('close');
                        }
                        if (e.keyCode === 115 && settings.close_on_f4_press) {
                            $.fn.balooncreate.last_close_trigger = 'F4 Key';
                            $baloonup.balooncreate('close');
                        }
                    });
            }

            if (settings.close_on_overlay_click) {
                $baloonup.on('pumAfterOpen', function () {
                    $(document).on('click.pumCloseOverlay', function (e) {
                        var $target = $(e.target),
                            $container = $target.closest('.pum-container');

                        if (!$container.length) {
                            $.fn.balooncreate.last_close_trigger = 'Overlay Click';
                            $baloonup.balooncreate('close');
                        }
                    });
                });

                $baloonup.on('pumAfterClose', function () {
                    $(document).off('click.pumCloseOverlay');
                });
            }

            $baloonup.trigger('pumSetupClose');

            return this;
        },
        close: function (callback) {
            return this.each(function () {
                var $baloonup = PUM.getBaloonUp(this),
                    $container = $baloonup.balooncreate('getContainer'),
                    $close = $baloonup.balooncreate('getClose');

                $close = $close.add($('.balooncreate-close, .pum-close', $baloonup).not($close));

                $baloonup.trigger('pumBeforeClose');

                if ($baloonup.hasClass('preventClose') || $container.hasClass('preventClose')) {
                    $baloonup
                        .removeClass('preventClose')
                        .trigger('pumClosePrevented');

                    return this;
                }

                $container
                    .fadeOut('fast', function () {

                        if ($baloonup.is(":visible")) {
                            $baloonup.fadeOut('fast');
                        }

                        /**
                         * Clear global event spaces.
                         */
                        $(window).off('keyup.balooncreate');
                        $baloonup.off('click.balooncreate');
                        $close.off('click.balooncreate');

                        // Only re-enable scrolling for the document when the last baloonup has closed.
                        if ($('.pum-active').length === 1) {
                            $('html')
                                .removeClass('pum-open')
                                .removeClass('pum-open-scrollable')
                                .removeClass('pum-open-overlay')
                                .removeClass('pum-open-overlay-disabled')
                                .removeClass('pum-open-fixed');
                        }

                        $baloonup
                            .removeClass('pum-active')
                            .trigger('pumAfterClose');

                        // TODO: Move this to its own event binding to keep this method clean and simple.
                        $container.find('iframe').filter('[src*="youtube"],[src*="vimeo"]').each(function () {
                            var $iframe = $(this),
                                src = $iframe.attr('src'),
                                // Remove autoplay so video doesn't start playing again.
                                new_src = src.replace('autoplay=1', '1=1');

                            if (new_src !== src) {
                                src = new_src;
                            }

                            $iframe.prop('src', src);
                        });

                        // TODO: Move this to its own event binding to keep this method clean and simple.
                        $container.find('video').each(function () {
                            this.pause();
                        });

                        // Fire user passed callback.
                        if (callback !== undefined) {
                            callback();
                            // TODO Test this new method. Then remove the above.
                            //callback.apply(this);
                        }
                    });
                return this;
            });
        },
        close_all: function () {
            $('.pum-active').balooncreate('close');
            return this;
        },
        reposition: function (callback) {
            var $baloonup = PUM.getBaloonUp(this).trigger('pumBeforeReposition'),
                $container = $baloonup.balooncreate('getContainer'),
                settings = $baloonup.balooncreate('getSettings'),
                location = settings.location,
                reposition = {
                    my: "",
                    at: "",
                    of: window,
                    collision: 'none',
                    using: typeof callback === "function" ? callback : $.fn.balooncreate.callbacks.reposition_using
                },
                opacity = {overlay: null, container: null},
                $last_trigger = null;

            try {
                $last_trigger = $($.fn.balooncreate.last_open_trigger);
            } catch (error) {
                $last_trigger = $();
            }

            if (settings.position_from_trigger && $last_trigger.length) {

                reposition.of = $last_trigger;

                if (location.indexOf('left') >= 0) {
                    reposition.my += " right";
                    reposition.at += " left" + (settings.position_left !== 0 ? "-" + settings.position_left : "");
                }
                if (location.indexOf('right') >= 0) {
                    reposition.my += " left";
                    reposition.at += " right" + (settings.position_right !== 0 ? "+" + settings.position_right : "");
                }
                if (location.indexOf('center') >= 0) {
                    reposition.my = location === 'center' ? "center" : reposition.my + " center";
                    reposition.at = location === 'center' ? "center" : reposition.at + " center";
                }
                if (location.indexOf('top') >= 0) {
                    reposition.my += " bottom";
                    reposition.at += " top" + (settings.position_top !== 0 ? "-" + settings.position_top : "");
                }
                if (location.indexOf('bottom') >= 0) {
                    reposition.my += " top";
                    reposition.at += " bottom" + (settings.position_bottom !== 0 ? "+" + settings.position_bottom : "");
                }
            } else {
                if (location.indexOf('left') >= 0) {
                    reposition.my += " left" + (settings.position_left !== 0 ? "+" + settings.position_left : "");
                    reposition.at += " left";
                }
                if (location.indexOf('right') >= 0) {
                    reposition.my += " right" + (settings.position_right !== 0 ? "-" + settings.position_right : "");
                    reposition.at += " right";
                }
                if (location.indexOf('center') >= 0) {
                    reposition.my = location === 'center' ? "center" : reposition.my + " center";
                    reposition.at = location === 'center' ? "center" : reposition.at + " center";
                }
                if (location.indexOf('top') >= 0) {
                    reposition.my += " top" + (settings.position_top !== 0 ? "+" + ($('body').hasClass('admin-bar') ? parseInt(settings.position_top, 10) + 32 : settings.position_top) : "");
                    reposition.at += " top";
                }
                if (location.indexOf('bottom') >= 0) {
                    reposition.my += " bottom" + (settings.position_bottom !== 0 ? "-" + settings.position_bottom : "");
                    reposition.at += " bottom";
                }
            }

            reposition.my = $.trim(reposition.my);
            reposition.at = $.trim(reposition.at);

            if ($baloonup.is(':hidden')) {
                opacity.overlay = $baloonup.css("opacity");
                $baloonup.css({opacity: 0}).show(0);
            }

            if ($container.is(':hidden')) {
                opacity.container = $container.css("opacity");
                $container.css({opacity: 0}).show(0);
            }

            if (settings.position_fixed) {
                $container.addClass('fixed');
            }

            if (settings.size === 'custom') {
                $container.css({
                    width: settings.custom_width,
                    height: settings.custom_height_auto ? 'auto' : settings.custom_height
                });
            } else {
                if (settings.size !== 'auto') {
                    $container
                        .addClass('responsive')
                        .css({
                            minWidth: settings.responsive_min_width !== '' ? settings.responsive_min_width : 'auto',
                            maxWidth: settings.responsive_max_width !== '' ? settings.responsive_max_width : 'auto'
                        });
                }
            }

            $baloonup.trigger('pumAfterReposition');

            // TODO: Remove the add class and migrate the trigger to the $baloonup with pum prefix.
            $container
                .addClass('custom-position')
                .position(reposition)
                .trigger('balooncreateAfterReposition');

            if (opacity.overlay) {
                $baloonup.css({opacity: opacity.overlay}).hide(0);
            }
            if (opacity.container) {
                $container.css({opacity: opacity.container}).hide(0);
            }
            return this;
        },
        animation_origin: function (origin) {
            var $baloonup = PUM.getBaloonUp(this),
                $container = $baloonup.balooncreate('getContainer'),
                start = {
                    my: "",
                    at: ""
                };

            switch (origin) {
            case 'top':
                start = {
                    my: "left+" + $container.offset().left + " bottom-100",
                    at: "left top"
                };
                break;
            case 'bottom':
                start = {
                    my: "left+" + $container.offset().left + " top+100",
                    at: "left bottom"
                };
                break;
            case 'left':
                start = {
                    my: "right top+" + $container.offset().top,
                    at: "left top"
                };
                break;
            case 'right':
                start = {
                    my: "left top+" + $container.offset().top,
                    at: "right top"
                };
                break;
            default:
                if (origin.indexOf('left') >= 0) {
                    start = {
                        my: start.my + " right",
                        at: start.at + " left"
                    };
                }
                if (origin.indexOf('right') >= 0) {
                    start = {
                        my: start.my + " left",
                        at: start.at + " right"
                    };
                }
                if (origin.indexOf('center') >= 0) {
                    start = {
                        my: start.my + " center",
                        at: start.at + " center"
                    };
                }
                if (origin.indexOf('top') >= 0) {
                    start = {
                        my: start.my + " bottom-100",
                        at: start.at + " top"
                    };
                }
                if (origin.indexOf('bottom') >= 0) {
                    start = {
                        my: start.my + " top+100",
                        at: start.at + " bottom"
                    };
                }
                start.my = $.trim(start.my);
                start.at = $.trim(start.at);
                break;
            }
            start.of = window;
            start.collision = 'none';
            return start;
        }
    };

}(jQuery, document));
/**
 * Defines the core $.balooncreate binds.
 * Version 1.4
 */
var PUM_Accessibility;
(function ($, document, undefined) {
    "use strict";
    var $top_level_elements,
        focusableElementsString = "a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, *[tabindex], *[contenteditable]",
        previouslyFocused,
        currentModal

    PUM_Accessibility = {
        // Accessibility: Checks focus events to ensure they stay inside the modal.
        forceFocus: function (e) {
            if (currentModal && currentModal.length && !currentModal[0].contains(e.target)) {
                e.stopPropagation();
                PUM_Accessibility.setFocusToFirstItem();
            }
        },
        trapTabKey: function (e) {
            // if tab or shift-tab pressed
            if (e.keyCode === 9) {
                // get list of focusable items
                var focusableItems = currentModal.find('.pum-container *').filter(focusableElementsString).filter(':visible'),
                    // get currently focused item
                    focusedItem = $(':focus'),
                    // get the number of focusable items
                    numberOfFocusableItems = focusableItems.length,
                    // get the index of the currently focused item
                    focusedItemIndex = focusableItems.index(focusedItem);

                if (e.shiftKey) {
                    //back tab
                    // if focused on first item and user preses back-tab, go to the last focusable item
                    if (focusedItemIndex === 0) {
                        focusableItems.get(numberOfFocusableItems - 1).focus();
                        e.preventDefault();
                    }
                } else {
                    //forward tab
                    // if focused on the last item and user preses tab, go to the first focusable item
                    if (focusedItemIndex === numberOfFocusableItems - 1) {
                        focusableItems.get(0).focus();
                        e.preventDefault();
                    }
                }
            }
        },
        setFocusToFirstItem: function () {
            // set focus to first focusable item
            currentModal.find('.pum-container *').filter(focusableElementsString).filter(':visible').filter(':not(.pum-close)').first().focus();
        }
    };

    $(document)
        .on('pumInit', '.pum', function () {
            PUM.getBaloonUp(this).find('[tabindex]').each(function () {
                var $this = $(this);
                $this
                    .data('tabindex', $this.attr('tabindex'))
                    .prop('tabindex', '0');

            });
        })


        .on('pumBeforeOpen', '.pum', function () {
            var $baloonup = PUM.getBaloonUp(this),
                $focused = $(':focus');

            // Accessibility: Sets the previous focus element.
            if (!$baloonup.has($focused).length) {
                previouslyFocused = $focused;
            }

            // Accessibility: Sets the current modal for focus checks.
            currentModal = $baloonup
            // Accessibility: Trap tab key.
                .on('keydown.pum_accessibility', PUM_Accessibility.trapTabKey)
                .attr('aria-hidden', 'false');

            $top_level_elements = $('body > *').filter(':visible').not(currentModal);
            $top_level_elements.attr('aria-hidden', 'true');

            // Accessibility: Add focus check first time focus changes after baloonup opens that prevents tabbing outside of modal.
            $(document).one('focusin.pum_accessibility', PUM_Accessibility.forceFocus);

            // Accessibility: Focus on the modal.
            PUM_Accessibility.setFocusToFirstItem();
        })
        .on('pumAfterOpen', '.pum', function () {

        })


        .on('pumBeforeClose', '.pum', function () {

        })
        .on('pumAfterClose', '.pum', function () {
            var $baloonup = PUM.getBaloonUp(this);

            $baloonup
                .off('keydown.pum_accessibility')
                .attr('aria-hidden', 'true');

            if ($top_level_elements) {
                $top_level_elements.attr('aria-hidden', 'false');
                $top_level_elements = null;
            }

            // Accessibility: Focus back on the previously focused element.
            if (previouslyFocused !== undefined && previouslyFocused.length) {
                previouslyFocused.focus();
            }

            // Accessibility: Clears the currentModal var.
            currentModal = null;

            // Accessibility: Removes the force focus check.
            $(document).off('focusin.pum_accessibility');
        })

        .on('pumSetupClose', '.pum', function () {

        })

        .on('pumOpenPrevented', '.pum', function () {

        })

        .on('pumClosePrevented', '.pum', function () {

        })

        .on('pumBeforeReposition', '.pum', function () {

        });


}(jQuery, document));
/**
 * Defines the core pum analytics methods.
 * Version 1.4
 */

var PUM_Analytics;
(function ($) {
    "use strict";

    $.fn.balooncreate.last_open_trigger = null;
    $.fn.balooncreate.last_close_trigger = null;
    $.fn.balooncreate.conversion_trigger = null;

    var rest_enabled = !!(typeof pum_vars.restapi !== 'undefined' && pum_vars.restapi);

    PUM_Analytics = {
        beacon: function (data, callback) {
            var beacon = new Image(),
                url = rest_enabled ? pum_vars.restapi : pum_vars.ajaxurl,
                opts = {
                    route: '/analytics/',
                    data: $.extend({
                        event: 'open',
                        pid: null,
                        _cache: (+(new Date()))
                    }, data),
                    callback: typeof callback === 'function' ? callback : function () {
                    }
                };

            if (!rest_enabled) {
                opts.data.action = 'pum_analytics';
            } else {
                url += opts.route;
            }

            // Create a beacon if a url is provided
            if (url) {
                // Attach the event handlers to the image object
                $(beacon).on('error success load done', opts.callback);

                // Attach the src for the script call
                beacon.src = url + '?' + $.param(opts.data);
            }
        }
    };

    if (typeof pum_vars.disable_tracking === 'undefined' || !pum_vars.disable_tracking) {
        // Only baloonups from the editor should fire analytics events.
        $(document)
        /**
         * Track opens for baloonups.
         */
            .on('pumAfterOpen.core_analytics', 'body > .pum', function () {
                var $baloonup = PUM.getBaloonUp(this),
                    data = {
                        pid: parseInt($baloonup.balooncreate('getSettings').id, 10) || null
                    };

                if (data.pid > 0 && !$('body').hasClass('single-baloonup')) {
                    PUM_Analytics.beacon(data);
                }
            });
    }
}(jQuery));
/**
 * Defines the core $.balooncreate animations.
 * Version 1.4
 */
(function ($, document, undefined) {
    "use strict";

    $.fn.balooncreate.methods.animate_overlay = function (style, duration, callback) {
        // Method calling logic
        var settings = PUM.getBaloonUp(this).balooncreate('getSettings');

        if (settings.overlay_disabled) {
            return $.fn.balooncreate.overlay_animations.none.apply(this, [duration, callback]);
        }

        if ($.fn.balooncreate.overlay_animations[style]) {
            return $.fn.balooncreate.overlay_animations[style].apply(this, [duration, callback]);
        }

        if (window.console) {
            console.warn('Animation style ' + style + ' does not exist.');
        }
        return this;
    };

    $.fn.balooncreate.methods.animate = function (style) {
        // Method calling logic
        if ($.fn.balooncreate.animations[style]) {
            return $.fn.balooncreate.animations[style].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        if (window.console) {
            console.warn('Animation style ' + style + ' does not exist.');
        }
        return this;
    };

    $.fn.balooncreate.animations = {
        none: function (callback) {
            var $baloonup = PUM.getBaloonUp(this);

            // Ensure the container is visible immediately.
            $baloonup.balooncreate('getContainer').show(0);

            $baloonup.balooncreate('animate_overlay', 'none', 0, function () {
                // Fire user passed callback.
                if (callback !== undefined) {
                    callback();
                    // TODO Test this new method. Then remove the above.
                    //callback.apply(this);
                }
            });
            return this;
        },
        slide: function (callback) {
            var $baloonup = PUM.getBaloonUp(this).show(0).css({opacity: 0}),
                $container = $baloonup.balooncreate('getContainer').show(0).css({opacity: 0}),
                settings = $baloonup.balooncreate('getSettings'),
                speed = settings.animation_speed / 2,
                start = $baloonup.balooncreate('animation_origin', settings.animation_origin);

            $container
                .position(start)
                .css({opacity: 1});

            $baloonup
                .css({opacity: 1})
                .balooncreate('animate_overlay', 'fade', speed, function () {
                    $container.balooncreate('reposition', function (position) {
                        $container.animate(position, speed, 'swing', function () {
                            // Fire user passed callback.
                            if (callback !== undefined) {
                                callback();
                                // TODO Test this new method. Then remove the above.
                                //callback.apply(this);
                            }
                        });
                    });
                });
            return this;
        },
        fade: function (callback) {
            var $baloonup = PUM.getBaloonUp(this),
                $container = $baloonup.balooncreate('getContainer'),
                settings = $baloonup.balooncreate('getSettings'),
                speed = settings.animation_speed / 2;

            $container
                .show(0)
                .css({opacity: 0});

            $baloonup.balooncreate('animate_overlay', 'fade', speed, function () {
                $container.animate({opacity: 1}, speed, 'swing', function () {
                    // Fire user passed callback.
                    if (callback !== undefined) {
                        callback();
                        // TODO Test this new method. Then remove the above.
                        //callback.apply(this);
                    }
                });
            });
            return this;
        },
        fadeAndSlide: function (callback) {
            var $baloonup = PUM.getBaloonUp(this).show(0).css({opacity: 0}),
                $container = $baloonup.balooncreate('getContainer').show(0).css({opacity: 0}),
                settings = $baloonup.balooncreate('getSettings'),
                speed = settings.animation_speed / 2,
                start = $baloonup.balooncreate('animation_origin', settings.animation_origin);

            $container.position(start);

            $baloonup
                .hide()
                .css({opacity: 1})
                .balooncreate('animate_overlay', 'fade', speed, function () {
                    $container.balooncreate('reposition', function (position) {

                        position.opacity = 1;
                        $container.animate(position, speed, 'swing', function () {
                            // Fire user passed callback.
                            if (callback !== undefined) {
                                callback();
                                // TODO Test this new method. Then remove the above.
                                //callback.apply(this);
                            }
                        });

                    });
                });
            return this;
        },
        /**
         * TODO: Remove these and let import script replace them.
         * @deprecated
         * @returns {$.fn.balooncreate.animations}
         */
        grow: function (callback) {
            return $.fn.balooncreate.animations.fade.apply(this, arguments);
        },
        /**
         * @deprecated
         * @returns {$.fn.balooncreate.animations}
         */
        growAndSlide: function (callback) {
            return $.fn.balooncreate.animations.fadeAndSlide.apply(this, arguments);
        }
    };

    $.fn.balooncreate.overlay_animations = {
        none: function (duration, callback) {
            PUM.getBaloonUp(this).show(duration, callback);
        },
        fade: function (duration, callback) {
            PUM.getBaloonUp(this).fadeIn(duration, callback);
        },
        slide: function (duration, callback) {
            PUM.getBaloonUp(this).slideDown(duration, callback);
        }
    };

}(jQuery, document));
/**
 * Defines the core $.balooncreate binds.
 * Version 1.4
 */
(function ($, document, undefined) {
    "use strict";

    $(document)
    // Backward Compatibility
    // TODO: Add check for compatibility mode once available.
        .on('pumInit', '.pum', function () {
            $(this).balooncreate('getContainer').trigger('balooncreateInit');
        })


        /**
         * Fires the deprecated balooncreateBeforeOpen event
         */
        .on('pumBeforeOpen', '.pum', function () {
            $(this).balooncreate('getContainer')
                .addClass('active')
                .trigger('balooncreateBeforeOpen');
        })
        /**
         * Fires the deprecated balooncreateAfterOpen event
         */
        .on('pumAfterOpen', '.pum', function () {
            $(this).balooncreate('getContainer').trigger('balooncreateAfterOpen');
        })


        /**
         * Fires the deprecated balooncreateBeforeClose event
         */
        .on('pumBeforeClose', '.pum', function () {
            $(this).balooncreate('getContainer').trigger('balooncreateBeforeClose');
        })
        /**
         * Fires the deprecated balooncreateAfterClose event
         */
        .on('pumAfterClose', '.pum', function () {
            $(this).balooncreate('getContainer')
                .removeClass('active')
                .trigger('balooncreateAfterClose');
        })


        /**
         * Fires the deprecated balooncreateSetupClose event
         */
        .on('pumSetupClose', '.pum', function () {
            $(this).balooncreate('getContainer').trigger('balooncreateSetupClose');
        })


        /**
         * Removes the prevent open classes if they exist.
         */
        .on('pumOpenPrevented', '.pum', function () {
            $(this).balooncreate('getContainer')
                .removeClass('preventOpen')
                .removeClass('active');
        })
        /**
         * Removes the prevent close classes if they exist.
         */
        .on('pumClosePrevented', '.pum', function () {
            $(this).balooncreate('getContainer')
                .removeClass('preventClose');
        })


        /**
         * Fires the deprecated balooncreateBeforeReposition event
         */
        .on('pumBeforeReposition', '.pum', function () {
            $(this).balooncreate('getContainer').trigger('balooncreateBeforeReposition');
        });


}(jQuery, document));
/**
 * Defines the core $.balooncreate callbacks.
 * Version 1.4
 */
(function ($, document, undefined) {
    "use strict";

    $.fn.balooncreate.callbacks = {
        reposition_using: function (position) {
            $(this).css(position);
        }
    };

}(jQuery, document));
(function ($, document, undefined) {
    "use strict";

    // Used for Mobile Detect when needed.
    var _md,
        md = function () {
            if (_md === undefined) {
                _md = typeof MobileDetect !== 'undefined' ? new MobileDetect(window.navigator.userAgent) : {
                    phone: function () {
                        return false;
                    },
                    tablet: function() {
                        return false;
                    }
                };
            }

            return _md;
        };

    $.extend($.fn.balooncreate.methods, {
        checkConditions: function () {
            var $baloonup = PUM.getBaloonUp(this),
                settings = $baloonup.balooncreate('getSettings'),
                // Loadable defaults to true if no conditions. Making the baloonup available everywhere.
                loadable = true,
                group_check,
                g,
                c,
                group,
                condition;

            if (settings.disable_on_mobile) {
                if (md().phone()) {
                    return false;
                }
            }

            if (settings.disable_on_tablet) {
                if (md().tablet()) {
                    return false;
                }
            }

            if (settings.conditions.length) {

                // All Groups Must Return True. Break if any is false and set loadable to false.
                for (g = 0; settings.conditions.length > g; g++) {

                    group = settings.conditions[g];

                    // Groups are false until a condition proves true.
                    group_check = false;

                    // At least one group condition must be true. Break this loop if any condition is true.
                    for (c = 0; group.length > c; c++) {

                        condition = $.extend({}, {
                            not_operand: false
                        }, group[c]);

                        // If any condition passes, set group_check true and break.
                        if (!condition.not_operand && $baloonup.balooncreate('checkCondition', condition)) {
                            group_check = true;
                        } else if (condition.not_operand && !$baloonup.balooncreate('checkCondition', condition)) {
                            group_check = true;
                        }

                        $(this).trigger('pumCheckingCondition', [group_check, condition]);

                        if (group_check) {
                            break;
                        }
                    }

                    // If any group of conditions doesn't pass, baloonup is not loadable.
                    if (!group_check) {
                        loadable = false;
                    }

                }

            }

            return loadable;
        },
        checkCondition: function (condition) {
            var target = condition.target || null,
                settings = condition.settings || condition,
                check;

            if (!target) {
                console.warn('Condition type not set.');
                return false;
            }

            // TODO once all extensions updated and in circulation for v1.7, change the below to pass settings, not condition.

            // Method calling logic
            if ($.fn.balooncreate.conditions[target]) {
                return $.fn.balooncreate.conditions[target].apply(this, [condition]);
            }
            if (window.console) {
                console.warn('Condition ' + target + ' does not exist.');
                return true;
            }
        }
    });


    $.fn.balooncreate.conditions = {};

}(jQuery, document));

/**
 * Defines the core $.balooncreate.cookie functions.
 * Version 1.4
 *
 * Defines the pm_cookie & pm_remove_cookie global functions.
 */
var pm_cookie, pm_cookie_json, pm_remove_cookie;
(function ($) {
    "use strict";

    function cookie(converter) {
        if (converter === undefined) {
            converter = function () {
            };
        }

        function api(key, value, attributes) {
            var result,
                expires = new Date();
            if (typeof document === 'undefined') {
                return;
            }

            // Write
            if (arguments.length > 1) {
                attributes = $.extend({
                    path: pum_vars.home_url
                }, api.defaults, attributes);

                switch (typeof attributes.expires) {
                case 'number':
                    expires.setMilliseconds(expires.getMilliseconds() + attributes.expires * 864e+5);
                    attributes.expires = expires;
                    break;
                case 'string':
                    expires.setTime($.fn.balooncreate.utilities.strtotime("+" + attributes.expires) * 1000);
                    attributes.expires = expires;
                    break;
                }

                try {
                    result = JSON.stringify(value);
                    if (/^[\{\[]/.test(result)) {
                        value = result;
                    }
                } catch (e) {
                }

                if (!converter.write) {
                    value = encodeURIComponent(String(value))
                        .replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);
                } else {
                    value = converter.write(value, key);
                }

                key = encodeURIComponent(String(key));
                key = key.replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent);
                key = key.replace(/[\(\)]/g, escape);

                return (document.cookie = [
                    key, '=', value,
                    attributes.expires ? '; expires=' + attributes.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                    attributes.path ? '; path=' + attributes.path : '',
                    attributes.domain ? '; domain=' + attributes.domain : '',
                    attributes.secure ? '; secure' : ''
                ].join(''));
            }

            // Read

            if (!key) {
                result = {};
            }

            // To prevent the for loop in the first place assign an empty array
            // in case there are no cookies at all. Also prevents odd result when
            // calling "get()"
            var cookies = document.cookie ? document.cookie.split('; ') : [];
            var rdecode = /(%[0-9A-Z]{2})+/g;
            var i = 0;

            for (; i < cookies.length; i++) {
                var parts = cookies[i].split('=');
                var cookie = parts.slice(1).join('=');

                if (cookie.charAt(0) === '"') {
                    cookie = cookie.slice(1, -1);
                }

                try {
                    var name = parts[0].replace(rdecode, decodeURIComponent);
                    cookie = converter.read ?
                        converter.read(cookie, name) : converter(cookie, name) ||
                        cookie.replace(rdecode, decodeURIComponent);

                    if (this.json) {
                        try {
                            cookie = JSON.parse(cookie);
                        } catch (e) {
                        }
                    }

                    if (key === name) {
                        result = cookie;
                        break;
                    }

                    if (!key) {
                        result[name] = cookie;
                    }
                } catch (e) {
                }
            }

            return result;
        }

        api.set = api;
        api.get = function (key) {
            return api.call(api, key);
        };
        api.getJSON = function () {
            return api.apply({
                json: true
            }, [].slice.call(arguments));
        };
        api.defaults = {};

        api.remove = function (key, attributes) {
            // Clears keys with current path.
            api(key, '', $.extend({}, attributes, {
                expires: -1,
                path: ''
            }));
            // Clears sitewide keys.
            api(key, '', $.extend({}, attributes, {
                expires: -1
            }));
        };

        /**
         * Polyfill for jQuery Cookie argument arrangement.
         *
         * @param key
         * @param value
         * @param attributes || expires (deprecated)
         * @param path (deprecated)
         * @returns {*}
         */
        api.process = function (key, value, attributes, path) {
            if (arguments.length > 3 && typeof arguments[2] !== 'object' && value !== undefined) {
                return api.apply(api, [key, value, {
                    expires: attributes,
                    path: path
                }]);
            } else {
                return api.apply(api, [].slice.call(arguments, [0, 2]));
            }
        };

        api.withConverter = $.fn.balooncreate.cookie;

        return api;
    }

    $.extend($.fn.balooncreate, {
        cookie: cookie()
    });

    pm_cookie = $.pm_cookie = $.fn.balooncreate.cookie.process;
    pm_cookie_json = $.pm_cookie_json = $.fn.balooncreate.cookie.getJSON;
    pm_remove_cookie = $.pm_remove_cookie = $.fn.balooncreate.cookie.remove;

}(jQuery));
(function ($, document, undefined) {
    "use strict";

    $.extend($.fn.balooncreate.methods, {
        addCookie: function (type) {
            // Method calling logic

            pum.hooks.doAction('balooncreate.addCookie', arguments);

            if ($.fn.balooncreate.cookies[type]) {
                return $.fn.balooncreate.cookies[type].apply(this, Array.prototype.slice.call(arguments, 1));
            }
            if (window.console) {
                console.warn('Cookie type ' + type + ' does not exist.');
            }
            return this;
        },
        setCookie: function (settings) {
            $.pm_cookie(
                settings.name,
                true,
                settings.session ? null : settings.time,
                settings.path ? pum_vars.home_url || '/' : null
            );
            pum.hooks.doAction('balooncreate.setCookie', settings);
        },
        checkCookies: function (settings) {
            var i,
                ret = false;

            if (settings.cookie_name === undefined || settings.cookie_name === null || settings.cookie_name === '') {
                return false;
            }

            switch (typeof settings.cookie_name) {
            case 'object':
            case 'array':
                for (i = 0; settings.cookie_name.length > i; i += 1) {
                    if ($.pm_cookie(settings.cookie_name[i]) !== undefined) {
                        ret = true;
                    }
                }
                break;
            case 'string':
                if ($.pm_cookie(settings.cookie_name) !== undefined) {
                    ret = true;
                }
                break;
            }

            pum.hooks.doAction('balooncreate.checkCookies', settings, ret);

            return ret;
        }
    });

    $.fn.balooncreate.cookies = $.fn.balooncreate.cookies || {};

    $.extend($.fn.balooncreate.cookies, {
        on_baloonup_open: function (settings) {
            var $baloonup = PUM.getBaloonUp(this);
            $baloonup.on('pumAfterOpen', function () {
                $baloonup.balooncreate('setCookie', settings);
            });
        },
        on_baloonup_close: function (settings) {
            var $baloonup = PUM.getBaloonUp(this);
            $baloonup.on('pumBeforeClose', function () {
                $baloonup.balooncreate('setCookie', settings);
            });
        },
        manual: function (settings) {
            var $baloonup = PUM.getBaloonUp(this);
            $baloonup.on('pumSetCookie', function () {
                $baloonup.balooncreate('setCookie', settings);
            });
        },
        form_success: function (settings) {
            var $baloonup = PUM.getBaloonUp(this);
            $baloonup.on('pumFormSuccess', function () {
                $baloonup.balooncreate('setCookie', settings);
            });
        },
        pum_sub_form_success: function (settings) {
            var $baloonup = PUM.getBaloonUp(this);
            $baloonup.find('form.pum-sub-form').on('success', function () {
                $baloonup.balooncreate('setCookie', settings);
            });
        },
        /**
         * @deprecated 1.7.0
         *
         * @param settings
         */
        pum_sub_form_already_subscribed: function (settings) {
            var $baloonup = PUM.getBaloonUp(this);
            $baloonup.find('form.pum-sub-form').on('success', function () {
                $baloonup.balooncreate('setCookie', settings);
            });
        },
        ninja_form_success: function (settings) {
            return $.fn.balooncreate.cookies.form_success.apply(this, arguments);
        },
        cf7_form_success: function (settings) {
            return $.fn.balooncreate.cookies.form_success.apply(this, arguments);
        },
        gforms_form_success: function (settings) {
            return $.fn.balooncreate.cookies.form_success.apply(this, arguments);
        }
    });

    // Register All Cookies for a BaloonUp
    $(document)
        .on('pumInit', '.pum', function () {
            var $baloonup = PUM.getBaloonUp(this),
                settings = $baloonup.balooncreate('getSettings'),
                cookies = settings.cookies || [],
                cookie = null,
                i;

            if (cookies.length) {
                for (i = 0; cookies.length > i; i += 1) {
                    cookie = cookies[i];
                    $baloonup.balooncreate('addCookie', cookie.event, cookie.settings);
                }
            }
        });

}(jQuery, document));
var pum_debug_mode = false,
    pum_debug;
(function ($, pum_vars) {

    pum_vars = window.pum_vars || {
        debug_mode: false
    };

    pum_debug_mode = pum_vars.debug_mode !== undefined && pum_vars.debug_mode;

    // Force Debug Mode when the ?pum_debug query arg is present.
    if (!pum_debug_mode && window.location.href.indexOf('pum_debug') !== -1) {
        pum_debug_mode = true;
    }

    if (pum_debug_mode) {

        var inited = false,
            current_baloonup_event = false,
            vars = window.pum_debug_vars || {
                'debug_mode_enabled': 'BaloonUp Maker: Debug Mode Enabled',
                'debug_started_at': 'Debug started at:',
                'debug_more_info': 'For more information on how to use this information visit https://docs.mcmsbaloonupmaker.com/?utm_medium=js-debug-info&utm_campaign=ContextualHelp&utm_source=browser-console&utm_content=more-info',
                'global_info': 'Global Information',
                'localized_vars': 'Localized variables',
                'baloonups_initializing': 'BaloonUps Initializing',
                'baloonups_initialized': 'BaloonUps Initialized',
                'single_baloonup_label': 'BaloonUp: #',
                'myskin_id': 'mySkin ID: ',
                'label_method_call': 'Method Call:',
                'label_method_args': 'Method Arguments:',
                'label_baloonup_settings': 'Settings',
                'label_triggers': 'Triggers',
                'label_cookies': 'Cookies',
                'label_delay': 'Delay:',
                'label_conditions': 'Conditions',
                'label_cookie': 'Cookie:',
                'label_settings': 'Settings:',
                'label_selector': 'Selector:',
                'label_mobile_disabled': 'Mobile Disabled:',
                'label_tablet_disabled': 'Tablet Disabled:',
                'label_event': 'Event: %s',
                'triggers': [],
                'cookies': []
            };

        pum_debug = {
            odump: function (o) {
                return $.extend({}, o);
            },
            logo: function () {
                console.log("" +
                    " -------------------------------------------------------------" + '\n' +
                    "|  ____                           __  __       _              |" + '\n' +
                    "| |  _ \\ ___  _ __  _   _ _ __   |  \\/  | __ _| | _____ _ __  |" + '\n' +
                    "| | |_) / _ \\| '_ \\| | | | '_ \\  | |\\/| |/ _` | |/ / _ \\ '__| |" + '\n' +
                    "| |  __/ (_) | |_) | |_| | |_) | | |  | | (_| |   <  __/ |    |" + '\n' +
                    "| |_|   \\___/| .__/ \\__,_| .__/  |_|  |_|\\__,_|_|\\_\\___|_|    |" + '\n' +
                    "|            |_|         |_|                                  |" + '\n' +
                    " -------------------------------------------------------------"
                );
            },
            initialize: function () {
                inited = true;

                // Clear Console
                //console.clear();

                // Render Logo
                pum_debug.logo();

                console.debug(vars.debug_mode_enabled);
                console.log(vars.debug_started_at, new Date());
                console.info(vars.debug_more_info);

                // Global Info Divider
                pum_debug.divider(vars.global_info);

                // Localized Variables
                console.groupCollapsed(vars.localized_vars);
                console.log('pum_vars:', pum_debug.odump(pum_vars));
                $(document).trigger('pum_debug_initialize_localized_vars');
                console.groupEnd();

                // Trigger to add more debug info from extensions.
                $(document).trigger('pum_debug_initialize');
            },
            baloonup_event_header: function ($baloonup) {
                var settings = $baloonup.balooncreate('getSettings');


                if (current_baloonup_event === settings.id) {
                    return;
                }

                current_baloonup_event = settings.id;
                pum_debug.divider(vars.single_baloonup_label + settings.id + ' - ' + settings.slug);
            },
            divider: function (heading) {
                var totalWidth = 62,
                    extraSpace = 62,
                    padding = 0,
                    line = " " + new Array(totalWidth + 1).join("-") + " ";

                if (typeof heading === 'string') {
                    extraSpace = totalWidth - heading.length;
                    padding = {
                        left: Math.floor(extraSpace / 2),
                        right: Math.floor(extraSpace / 2)
                    };

                    if (padding.left + padding.right === extraSpace - 1) {
                        padding.right++;
                    }

                    padding.left = new Array(padding.left + 1).join(" ");
                    padding.right = new Array(padding.right + 1).join(" ");

                    console.log("" +
                        line + '\n' +
                        "|" + padding.left + heading + padding.right + "|" + '\n' +
                        line
                    );
                } else {
                    console.log(line);
                }
            },
            click_trigger: function ($baloonup, trigger_settings) {
                var settings = $baloonup.balooncreate('getSettings'),
                    trigger_selectors = [
                        '.balooncreate-' + settings.id,
                        '.balooncreate-' + decodeURIComponent(settings.slug),
                        'a[href$="#balooncreate-' + settings.id + '"]'
                    ],
                    trigger_selector;

                if (trigger_settings.extra_selectors && trigger_settings.extra_selectors !== '') {
                    trigger_selectors.push(trigger_settings.extra_selectors);
                }

                trigger_selectors = pum.hooks.applyFilters('pum.trigger.click_open.selectors', trigger_selectors, trigger_settings, $baloonup);

                trigger_selector = trigger_selectors.join(', ');

                console.log(vars.label_selector, trigger_selector);
            },
            trigger: function ($baloonup, trigger) {
                if (typeof vars.triggers[trigger.type] === 'string') {

                    console.groupCollapsed(vars.triggers[trigger.type]);

                    switch (trigger.type) {
                    case 'auto_open':
                        console.log(vars.label_delay, trigger.settings.delay);
                        console.log(vars.label_cookie, trigger.settings.cookie_name);
                        break;
                    case 'click_open':
                        pum_debug.click_trigger($baloonup, trigger.settings);
                        console.log(vars.label_cookie, trigger.settings.cookie_name);
                        break;
                    }

                    $(document).trigger('pum_debug_render_trigger', $baloonup, trigger);

                    console.groupEnd();
                }
            },
            cookie: function ($baloonup, cookie) {
                if (typeof vars.cookies[cookie.event] === 'string') {
                    console.groupCollapsed(vars.cookies[cookie.event]);

                    switch (cookie.event) {
                    case 'on_baloonup_open':
                    case 'on_baloonup_close':
                    case 'manual':
                    case 'ninja_form_success':
                        console.log(vars.label_cookie, pum_debug.odump(cookie.settings));
                        break;
                    }

                    $(document).trigger('pum_debug_render_trigger', $baloonup, cookie);

                    console.groupEnd();
                }
            }
        };

        $(document)
            .on('pumInit', '.pum', function () {
                var $baloonup = PUM.getBaloonUp($(this)),
                    settings = $baloonup.balooncreate('getSettings'),
                    triggers = settings.triggers || [],
                    cookies = settings.cookies || [],
                    conditions = settings.conditions || [],
                    i = 0;

                if (!inited) {
                    pum_debug.initialize();
                    pum_debug.divider(vars.baloonups_initializing);
                }

                console.groupCollapsed(vars.single_baloonup_label + settings.id + ' - ' + settings.slug);

                // BaloonUp mySkin ID
                console.log(vars.myskin_id, settings.myskin_id);

                // Triggers
                if (triggers.length) {
                    console.groupCollapsed(vars.label_triggers);
                    for (i = 0; triggers.length > i; i++) {
                        pum_debug.trigger($baloonup, triggers[i]);
                    }
                    console.groupEnd();
                }

                // Cookies
                if (cookies.length) {
                    console.groupCollapsed(vars.label_cookies);
                    for (i = 0; cookies.length > i; i += 1) {
                        pum_debug.cookie($baloonup, cookies[i]);
                    }
                    console.groupEnd();
                }

                // Conditions
                if (conditions.length) {
                    console.groupCollapsed(vars.label_conditions);
                    console.log(conditions);
                    console.groupEnd();
                }

                console.groupCollapsed(vars.label_baloonup_settings);


                // Mobile Disabled.
                console.log(vars.label_mobile_disabled, settings.disable_on_mobile !== false);

                // Tablet Disabled.
                console.log(vars.label_tablet_disabled, settings.disable_on_tablet !== false);

                // Settings.
                console.log(vars.label_display_settings, pum_debug.odump(settings));

                // Trigger to add more debug info from extensions.
                $baloonup.trigger('pum_debug_baloonup_settings');

                console.groupEnd();

                console.groupEnd();

            })
            .on('pumBeforeOpen', '.pum', function () {
                var $baloonup = PUM.getBaloonUp($(this)),
                    $last_trigger = $.fn.balooncreate.last_open_trigger;

                pum_debug.baloonup_event_header($baloonup);

                console.groupCollapsed(vars.label_event.replace('%s', 'pumBeforeOpen'));

                try {
                    $last_trigger = $($.fn.balooncreate.last_open_trigger);
                    $last_trigger = $last_trigger.length ? $last_trigger : $.fn.balooncreate.last_open_trigger.toString();
                } catch (error) {
                    $last_trigger = "";
                } finally {
                    console.log(vars.label_triggers, [$last_trigger]);
                }

                console.groupEnd();
            })
            .on('pumOpenPrevented', '.pum', function () {
                var $baloonup = PUM.getBaloonUp($(this));

                pum_debug.baloonup_event_header($baloonup);

                console.groupCollapsed(vars.label_event.replace('%s', 'pumOpenPrevented'));

                console.groupEnd();
            })
            .on('pumAfterOpen', '.pum', function () {
                var $baloonup = PUM.getBaloonUp($(this));

                pum_debug.baloonup_event_header($baloonup);

                console.groupCollapsed(vars.label_event.replace('%s', 'pumAfterOpen'));

                console.groupEnd();
            })
            .on('pumSetupClose', '.pum', function () {
                var $baloonup = PUM.getBaloonUp($(this));

                pum_debug.baloonup_event_header($baloonup);

                console.groupCollapsed(vars.label_event.replace('%s', 'pumSetupClose'));

                console.groupEnd();
            })
            .on('pumClosePrevented', '.pum', function () {
                var $baloonup = PUM.getBaloonUp($(this));

                pum_debug.baloonup_event_header($baloonup);

                console.groupCollapsed(vars.label_event.replace('%s', 'pumClosePrevented'));

                console.groupEnd();
            })
            .on('pumBeforeClose', '.pum', function () {
                var $baloonup = PUM.getBaloonUp($(this));

                pum_debug.baloonup_event_header($baloonup);

                console.groupCollapsed(vars.label_event.replace('%s', 'pumBeforeClose'));

                console.groupEnd();
            })
            .on('pumAfterClose', '.pum', function () {
                var $baloonup = PUM.getBaloonUp($(this));

                pum_debug.baloonup_event_header($baloonup);

                console.groupCollapsed(vars.label_event.replace('%s', 'pumAfterClose'));

                console.groupEnd();
            })
            .on('pumBeforeReposition', '.pum', function () {
                var $baloonup = PUM.getBaloonUp($(this));

                pum_debug.baloonup_event_header($baloonup);

                console.groupCollapsed(vars.label_event.replace('%s', 'pumBeforeReposition'));

                console.groupEnd();
            })
            .on('pumAfterReposition', '.pum', function () {
                var $baloonup = PUM.getBaloonUp($(this));

                pum_debug.baloonup_event_header($baloonup);

                console.groupCollapsed(vars.label_event.replace('%s', 'pumAfterReposition'));

                console.groupEnd();
            })
            .on('pumCheckingCondition', '.pum', function (event, result, condition) {
                var $baloonup = PUM.getBaloonUp($(this));

                pum_debug.baloonup_event_header($baloonup);

                console.groupCollapsed(vars.label_event.replace('%s', 'pumCheckingCondition'));

                console.log((condition.not_operand ? '(!) ' : '') + condition.target + ': ' + result, condition);

                console.groupEnd();
            });
    }

}(jQuery));
/**
 * Defines the core $.balooncreate defaults.
 * Version 1.4
 */
(function ($, document, undefined) {
    "use strict";

    $.fn.balooncreate.defaults = {
        id: null,
        slug: "",
        myskin_id: null,
        cookies: [],
        triggers: [],
        conditions: [],
        mobile_disabled: null,
        tablet_disabled: null,
        custom_height_auto: false,
        scrollable_content: false,
        position_from_trigger: false,
        position_fixed: false,
        overlay_disabled: false,
        stackable: false,
        disable_reposition: false,
        close_on_overlay_click: false,
        close_on_esc_press: false,
        close_on_f4_press: false,
        disable_on_mobile: false,
        disable_on_tablet: false,
        size: "medium",
        responsive_min_width: "0%",
        responsive_max_width: "100%",
        custom_width: "640px",
        custom_height: "380px",
        animation_type: "fade",
        animation_speed: "350",
        animation_origin: "center top",
        location: "center top",
        position_top: "100",
        position_bottom: "0",
        position_left: "0",
        position_right: "0",
        zindex: "1999999999",
        close_button_delay: "0",
        // TODO Remove these once extensions have all been updated.
        meta: {
            display: {
                stackable: false,
                overlay_disabled: false,
                size: "medium",
                responsive_max_width: "100",
                responsive_max_width_unit: '%',
                responsive_min_width: "0",
                responsive_min_width_unit: '%',
                custom_width: "640",
                custom_width_unit: 'px',
                custom_height: "380",
                custom_height_unit: 'px',
                custom_height_auto: false,
                location: "center top",
                position_top: 100,
                position_left: 0,
                position_bottom: 0,
                position_right: 0,
                position_fixed: false,
                animation_type: 'fade',
                animation_speed: 350,
                animation_origin: 'center top',
                scrollable_content: false,
                disable_reposition: false,
                position_from_trigger: false,
                overlay_zindex: false,
                zindex: "1999999999"
            },
            close: {
                overlay_click: false,
                esc_press: false,
                f4_press: false,
                text: "",
                button_delay: 0
            },
            click_open: []
        },
        container: {
            active_class: 'active',
            attr: {
                class: "balooncreate"
            }
        },
        title: {
            attr: {
                class: "balooncreate-title"
            }
        },
        content: {
            attr: {
                class: "balooncreate-content"
            }
        },
        close: {
            close_speed: 0,
            attr: {
                class: "balooncreate-close"
            }
        },
        overlay: {
            attr: {
                id: "balooncreate-overlay",
                class: "balooncreate-overlay"
            }
        }
    };

}(jQuery, document));
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/
(function ($) {
    "use strict";

    var defaults = {
        openbaloonup: false,
        openbaloonup_id: 0,
        closebaloonup: false,
        closedelay: 0,
        redirect_enabled: false,
        redirect: '',
        cookie: false
    };

    window.PUM = window.PUM || {};
    window.PUM.forms = window.PUM.forms || {};

    $.extend(window.PUM.forms, {
        form: {
            validation: {
                errors: []
            },
            responseHandler: function ($form, response) {
                var data = response.data;

                if (response.success) {
                    /**
                     * If there are no errors process the successful submission.
                     */
                    window.PUM.forms.form.success($form, data);
                } else {
                    /**
                     * Process any errors
                     */
                    window.PUM.forms.form.errors($form, data);
                }
            },
            display_errors: function ($form, errors) {
                window.PUM.forms.messages.add($form, errors || this.validation.errors, 'error');
            },
            beforeAjax: function ($form) {
                var $btn = $form.find('[type="submit"]'),
                    $loading = $btn.find('.pum-form__loader');

                window.PUM.forms.messages.clear_all($form);

                if (!$loading.length) {
                    $loading = $('<span class="pum-form__loader"></span>');
                    if ($btn.attr('value') !== '') {
                        $loading.insertAfter($btn);
                    } else {
                        $btn.append($loading);
                    }
                }

                $btn.prop('disabled', true);
                $loading.show();

                $form
                    .addClass('pum-form--loading')
                    .removeClass('pum-form--errors');
            },
            afterAjax: function ($form) {
                var $btn = $form.find('[type="submit"]'),
                    $loading = $btn.find('.pum-form__loader');

                $btn.prop('disabled', false);
                $loading.hide();

                $form.removeClass('pum-form--loading');
            },
            success: function ($form, data) {
                if (data.message !== undefined && data.message !== '') {
                    window.PUM.forms.messages.add($form, [{message: data.message}]);
                }

                $form.trigger('success', [data]);

                if (!$form.data('noredirect') && $form.data('redirect_enabled') !== undefined && data.redirect) {
                    if (data.redirect !== '') {
                        window.location = data.redirect;
                    } else {
                        window.location.reload(true);
                    }
                }
            },
            errors: function ($form, data) {
                if (data.errors !== undefined && data.errors.length) {
                    console.log(data.errors);

                    window.PUM.forms.form.display_errors($form, data.errors);

                    window.PUM.forms.messages.scroll_to_first($form);

                    $form
                        .addClass('pum-form--errors')
                        .trigger('errors', [data]);
                }
            },
            submit: function (event) {
                var $form = $(this),
                    values = $form.pumSerializeObject();

                event.preventDefault();
                event.stopPropagation();

                window.PUM.forms.form.beforeAjax($form);

                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: pum_vars.ajaxurl,
                    data: {
                        action: 'pum_form',
                        values: values
                    }
                })
                    .always(function () {
                        window.PUM.forms.form.afterAjax($form);
                    })
                    .done(function (response) {
                        window.PUM.forms.form.responseHandler($form, response);
                    })
                    .error(function (jqXHR, textStatus, errorThrown) {
                        console.log('Error: type of ' + textStatus + ' with message of ' + errorThrown);
                    });
            }
        },
        /**
         * Functions to manage form messages.
         */
        messages: {
            /**
             * Process & add messages to a form.
             *
             * @param $form
             * @param messages
             * @param type
             */
            add: function ($form, messages, type) {
                var $messages = $form.find('.pum-form__messages'),
                    i = 0;

                type = type || 'success';
                messages = messages || [];

                if (!$messages.length) {
                    $messages = $('<div class="pum-form__messages">').hide();
                    switch (pum_vars.message_position) {
                    case 'bottom':
                        $form.append($messages.addClass('pum-form__messages--bottom'));
                        break;
                    case 'top':
                        $form.prepend($messages.addClass('pum-form__messages--top'));
                        break;
                    }
                }

                if (['bottom', 'top'].indexOf(pum_vars.message_position) >= 0) {
                    for (; messages.length > i; i++) {
                        this.add_message($messages, messages[i].message, type);
                    }
                } else {
                    /**
                     * Per Field Messaging
                     */
                    for (; messages.length > i; i++) {

                        if (messages[i].field !== undefined) {
                            this.add_field_error($form, messages[i]);
                        } else {
                            this.add_message($messages, messages[i].message, type);
                        }
                    }
                }

                if ($messages.is(':hidden') && $('.pum-form__message', $messages).length) {
                    $messages.slideDown();
                }
            },
            add_message: function ($container, message, type) {
                var $message = $('<p class="pum-form__message">').html(message);

                type = type || 'success';

                $message.addClass('pum-form__message--' + type);

                $container.append($message);

                if ($container.is(':visible')) {
                    $message.hide().slideDown();
                }
            },
            add_field_error: function ($form, error) {
                var $field = $('[name="' + error.field + '"]', $form),
                    $wrapper = $field.parents('.pum-form__field').addClass('pum-form__field--error');

                this.add_message($wrapper, error.message, 'error');
            },
            clear_all: function ($form, hide) {
                var $messages = $form.find('.pum-form__messages'),
                    messages = $messages.find('.pum-form__message'),
                    $errors = $form.find('.pum-form__field.pum-form__field--error');

                hide = hide || false;

                // Remove forms main messages container.
                if ($messages.length) {
                    messages.slideUp('fast', function () {
                        $(this).remove();

                        if (hide) {
                            $messages.hide();
                        }
                    });

                }

                // Remove per field messages.
                if ($errors.length) {
                    $errors.removeClass('pum-form__field--error').find('p.pum-form__message').remove();
                }
            },
            scroll_to_first: function ($form) {
                window.PUM.utilities.scrollTo($('.pum-form__field.pum-form__field--error', $form).eq(0));
            }
        },
        /**
         * Used to process success actions for forms inside baloonups.
         *
         * @param $form
         * @param settings
         */
        success: function ($form, settings) {
            settings = $.extend({}, defaults, settings);

            if (!settings) {
                return;
            }

            var $parentBaloonUp = PUM.getBaloonUp($form),
                cookie = {},
                redirect = function () {
                    if (settings.redirect_enabled) {
                        if (settings.redirect !== '') {
                            // Redirect to the destination url.
                            window.location = settings.redirect;
                        } else {
                            // Refresh with force true.
                            window.location.reload(true);
                        }
                    }
                },
                callback = function () {
                    if (settings.openbaloonup && PUM.getBaloonUp(settings.openbaloonup_id).length) {
                        PUM.open(settings.openbaloonup_id);
                    } else {
                        redirect();
                    }
                };

            if ($parentBaloonUp.length) {
                $parentBaloonUp.trigger('pumFormSuccess');

                if (settings.cookie) {
                    cookie = $.extend({
                        name: 'pum-' + PUM.getSetting($parentBaloonUp, 'id'),
                        expires: '+1 year'
                    }, typeof settings.cookie === 'object' ? settings.cookie : {});

                    // Set a cookie
                    PUM.setCookie($parentBaloonUp, cookie);
                }

            }

            if ($parentBaloonUp.length && settings.closebaloonup) {
                setTimeout(function () {
                    $parentBaloonUp.balooncreate('close', callback);
                }, parseInt(settings.closedelay) * 1000);
            } else {
                callback();
            }
        }
    });


}(jQuery));
(function (window, undefined) {
    'use strict';

    /**
     * Handles managing all events for whatever you plug it into. Priorities for hooks are based on lowest to highest in
     * that, lowest priority hooks are fired first.
     */
    var EventManager = function () {
        var slice = Array.prototype.slice;

        /**
         * Maintain a reference to the object scope so our public methods never get confusing.
         */
        var MethodsAvailable = {
            removeFilter: removeFilter,
            applyFilters: applyFilters,
            addFilter: addFilter,
            removeAction: removeAction,
            doAction: doAction,
            addAction: addAction
        };

        /**
         * Contains the hooks that get registered with this EventManager. The array for storage utilizes a "flat"
         * object literal such that looking up the hook utilizes the native object literal hash.
         */
        var STORAGE = {
            actions: {},
            filters: {}
        };

        /**
         * Adds an action to the event manager.
         *
         * @param action Must contain namespace.identifier
         * @param callback Must be a valid callback function before this action is added
         * @param [priority=10] Used to control when the function is executed in relation to other callbacks bound to the same hook
         * @param [context] Supply a value to be used for this
         */
        function addAction(action, callback, priority, context) {
            if (typeof action === 'string' && typeof callback === 'function') {
                priority = parseInt((priority || 10), 10);
                _addHook('actions', action, callback, priority, context);
            }

            return MethodsAvailable;
        }

        /**
         * Performs an action if it exists. You can pass as many arguments as you want to this function; the only rule is
         * that the first argument must always be the action.
         */
        function doAction(/* action, arg1, arg2, ... */) {
            var args = slice.call(arguments);
            var action = args.shift();

            if (typeof action === 'string') {
                _runHook('actions', action, args);
            }

            return MethodsAvailable;
        }

        /**
         * Removes the specified action if it contains a namespace.identifier & exists.
         *
         * @param action The action to remove
         * @param [callback] Callback function to remove
         */
        function removeAction(action, callback) {
            if (typeof action === 'string') {
                _removeHook('actions', action, callback);
            }

            return MethodsAvailable;
        }

        /**
         * Adds a filter to the event manager.
         *
         * @param filter Must contain namespace.identifier
         * @param callback Must be a valid callback function before this action is added
         * @param [priority=10] Used to control when the function is executed in relation to other callbacks bound to the same hook
         * @param [context] Supply a value to be used for this
         */
        function addFilter(filter, callback, priority, context) {
            if (typeof filter === 'string' && typeof callback === 'function') {
                priority = parseInt((priority || 10), 10);
                _addHook('filters', filter, callback, priority, context);
            }

            return MethodsAvailable;
        }

        /**
         * Performs a filter if it exists. You should only ever pass 1 argument to be filtered. The only rule is that
         * the first argument must always be the filter.
         */
        function applyFilters(/* filter, filtered arg, arg2, ... */) {
            var args = slice.call(arguments);
            var filter = args.shift();

            if (typeof filter === 'string') {
                return _runHook('filters', filter, args);
            }

            return MethodsAvailable;
        }

        /**
         * Removes the specified filter if it contains a namespace.identifier & exists.
         *
         * @param filter The action to remove
         * @param [callback] Callback function to remove
         */
        function removeFilter(filter, callback) {
            if (typeof filter === 'string') {
                _removeHook('filters', filter, callback);
            }

            return MethodsAvailable;
        }

        /**
         * Removes the specified hook by resetting the value of it.
         *
         * @param type Type of hook, either 'actions' or 'filters'
         * @param hook The hook (namespace.identifier) to remove
         * @private
         */
        function _removeHook(type, hook, callback, context) {
            var handlers, handler, i;

            if (!STORAGE[type][hook]) {
                return;
            }
            if (!callback) {
                STORAGE[type][hook] = [];
            } else {
                handlers = STORAGE[type][hook];
                if (!context) {
                    for (i = handlers.length; i--;) {
                        if (handlers[i].callback === callback) {
                            handlers.splice(i, 1);
                        }
                    }
                }
                else {
                    for (i = handlers.length; i--;) {
                        handler = handlers[i];
                        if (handler.callback === callback && handler.context === context) {
                            handlers.splice(i, 1);
                        }
                    }
                }
            }
        }

        /**
         * Adds the hook to the appropriate storage container
         *
         * @param type 'actions' or 'filters'
         * @param hook The hook (namespace.identifier) to add to our event manager
         * @param callback The function that will be called when the hook is executed.
         * @param priority The priority of this hook. Must be an integer.
         * @param [context] A value to be used for this
         * @private
         */
        function _addHook(type, hook, callback, priority, context) {
            var hookObject = {
                callback: callback,
                priority: priority,
                context: context
            };

            // Utilize 'prop itself' : http://jsperf.com/hasownproperty-vs-in-vs-undefined/19
            var hooks = STORAGE[type][hook];
            if (hooks) {
                hooks.push(hookObject);
                hooks = _hookInsertSort(hooks);
            }
            else {
                hooks = [hookObject];
            }

            STORAGE[type][hook] = hooks;
        }

        /**
         * Use an insert sort for keeping our hooks organized based on priority. This function is ridiculously faster
         * than bubble sort, etc: http://jsperf.com/javascript-sort
         *
         * @param hooks The custom array containing all of the appropriate hooks to perform an insert sort on.
         * @private
         */
        function _hookInsertSort(hooks) {
            var tmpHook, j, prevHook;
            for (var i = 1, len = hooks.length; i < len; i++) {
                tmpHook = hooks[i];
                j = i;
                while ((prevHook = hooks[j - 1]) && prevHook.priority > tmpHook.priority) {
                    hooks[j] = hooks[j - 1];
                    --j;
                }
                hooks[j] = tmpHook;
            }

            return hooks;
        }

        /**
         * Runs the specified hook. If it is an action, the value is not modified but if it is a filter, it is.
         *
         * @param type 'actions' or 'filters'
         * @param hook The hook ( namespace.identifier ) to be ran.
         * @param args Arguments to pass to the action/filter. If it's a filter, args is actually a single parameter.
         * @private
         */
        function _runHook(type, hook, args) {
            var handlers = STORAGE[type][hook], i, len;

            if (!handlers) {
                return (type === 'filters') ? args[0] : false;
            }

            len = handlers.length;
            if (type === 'filters') {
                for (i = 0; i < len; i++) {
                    args[0] = handlers[i].callback.apply(handlers[i].context, args);
                }
            } else {
                for (i = 0; i < len; i++) {
                    handlers[i].callback.apply(handlers[i].context, args);
                }
            }

            return (type === 'filters') ? args[0] : true;
        }

        // return all of the publicly available methods
        return MethodsAvailable;

    };

    window.pum = window.pum || {};
    window.pum.hooks = window.pum.hooks || new EventManager();

})(window);
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/
(function ($) {
    "use strict";

    var gFormSettings = {},
        pumNFController = false;

    function initialize_nf_support() {
        /** Ninja Forms Support */
        if (typeof Marionette !== 'undefined' && typeof nfRadio !== 'undefined') {
            pumNFController = Marionette.Object.extend({
                initialize: function () {
                    this.listenTo(nfRadio.channel('forms'), 'submit:response', this.baloonupMaker)
                },
                baloonupMaker: function (response, textStatus, jqXHR, formID) {
                    var $form = $('#nf-form-' + formID + '-cont'),
                        settings = {};

                    if (response.errors.length) {
                        return;
                    }

                    if ('undefined' !== typeof response.data.actions) {
                        settings.openbaloonup = 'undefined' !== typeof response.data.actions.openbaloonup;
                        settings.openbaloonup_id = settings.openbaloonup ? parseInt(response.data.actions.openbaloonup) : 0;
                        settings.closebaloonup = 'undefined' !== typeof response.data.actions.closebaloonup;
                        settings.closedelay = settings.closebaloonup ? parseInt(response.data.actions.closebaloonup) : 0;
                        if (settings.closebaloonup && response.data.actions.closedelay) {
                            settings.closedelay = parseInt(response.data.actions.closedelay);
                        }
                    }

                    window.PUM.forms.success($form, settings);
                }
            });
        }
    }


    $(document)
        .ready(function () {
            /** Ninja Forms Support */
            if (pumNFController === false) {
                initialize_nf_support();
            }

            if (pumNFController !== false) {
                new pumNFController();
            }

            /** Gravity Forms Support */
            $('.gform_wrapper > form').each(function () {
                var $form = $(this),
                    form_id = $form.attr('id').replace('gform_', ''),
                    $settings = $form.find('input.gforms-pum'),
                    settings = $settings.length ? JSON.parse($settings.val()) : false;

                if (!settings || typeof settings !== 'object') {
                    return;
                }

                if (typeof settings === 'object' && settings.closedelay !== undefined && settings.closedelay.toString().length >= 3) {
                    settings['closedelay'] = settings.closedelay / 1000;
                }

                gFormSettings[form_id] = settings;
            });
        })
        /** Gravity Forms Support */
        .on('gform_confirmation_loaded', function (event, form_id) {
            var $form = $('#gform_confirmation_wrapper_' + form_id + ',#gforms_confirmation_message_' + form_id),
                settings = gFormSettings[form_id] || false;

            window.PUM.forms.success($form, settings);
        })
        /** Contact Form 7 Support */
        .on('mcmscf7:mailsent', '.mcmscf7', function (event) {
            var $form = $(event.target),
                $settings = $form.find('input.mcmscf7-pum'),
                settings = $settings.length ? JSON.parse($settings.val()) : false;

            if (typeof settings === 'object' && settings.closedelay !== undefined && settings.closedelay.toString().length >= 3) {
                settings['closedelay'] = settings.closedelay / 1000;
            }

            window.PUM.forms.success($form, settings);
        });

}(jQuery));
/*******************************************************************************
 * Copyright (c) 2017, MCMS BaloonUp Maker
 ******************************************************************************/
(function ($) {
    'use strict';

    if (pum_vars && pum_vars.core_sub_forms_enabled !== undefined && !pum_vars.core_sub_forms_enabled) {
        return;
    }

    window.PUM = window.PUM || {};
    window.PUM.newsletter = window.PUM.newsletter || {};

    $.extend(window.PUM.newsletter, {
        form: $.extend({}, window.PUM.forms.form, {
            submit: function (event) {
                var $form = $(this),
                    values = $form.pumSerializeObject();

                event.preventDefault();
                event.stopPropagation();

                window.PUM.newsletter.form.beforeAjax($form);

                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: pum_vars.ajaxurl,
                    data: {
                        action: 'pum_sub_form',
                        values: values
                    }
                })
                    .always(function () {
                        window.PUM.newsletter.form.afterAjax($form);
                    })
                    .done(function (response) {
                        window.PUM.newsletter.form.responseHandler($form, response);
                    })
                    .error(function (jqXHR, textStatus, errorThrown) {
                        console.log('Error: type of ' + textStatus + ' with message of ' + errorThrown);
                    });
            }

        })
    });

    $(document)
        .on('submit', 'form.pum-sub-form', window.PUM.newsletter.form.submit)
        .on('success', 'form.pum-sub-form', function (event, data) {
            var $form = $(event.target),
                settings = $form.data('settings') || {};

            $form
                .trigger('pumNewsletterSuccess', [data])
                .addClass('pum-newsletter-success');

            $form[0].reset();

            window.pum.hooks.doAction('pum-sub-form.success', data, $form);

            if (typeof settings.redirect === 'string') {
                if (settings.redirect !== '') {
                    settings.redirect = atob(settings.redirect);
                }
            }

            window.PUM.forms.success($form, settings);
        })
        .on('error', 'form.pum-sub-form', function (event, data) {
            var $form = $(event.target);

            $form.trigger('pumNewsletterError', [data]);

            window.pum.hooks.doAction('pum-sub-form.errors', data, $form);
        });

}(jQuery));

(function ($, document, undefined) {
    "use strict";

    $.extend($.fn.balooncreate.methods, {
        addTrigger: function (type) {
            // Method calling logic
            if ($.fn.balooncreate.triggers[type]) {
                return $.fn.balooncreate.triggers[type].apply(this, Array.prototype.slice.call(arguments, 1));
            }
            if (window.console) {
                console.warn('Trigger type ' + type + ' does not exist.');
            }
            return this;
        }
    });

    $.fn.balooncreate.triggers = {
        auto_open: function (settings) {
            var $baloonup = PUM.getBaloonUp(this);

            // Set a delayed open.
            setTimeout(function () {

                // If the baloonup is already open return.
                if ($baloonup.balooncreate('state', 'isOpen')) {
                    return;
                }

                // If cookie exists or conditions fail return.
                if ($baloonup.balooncreate('checkCookies', settings) || !$baloonup.balooncreate('checkConditions')) {
                    return;
                }

                // Set the global last open trigger to the a text description of the trigger.
                $.fn.balooncreate.last_open_trigger = 'Auto Open - Delay: ' + settings.delay;

                // Open the baloonup.
                $baloonup.balooncreate('open');

            }, settings.delay);
        },
        click_open: function (settings) {
            var $baloonup = PUM.getBaloonUp(this),
                baloonup_settings = $baloonup.balooncreate('getSettings'),
                trigger_selectors = [
                    '.balooncreate-' + baloonup_settings.id,
                    '.balooncreate-' + decodeURIComponent(baloonup_settings.slug),
                    'a[href$="#balooncreate-' + baloonup_settings.id + '"]'
                ],
                trigger_selector;


            if (settings.extra_selectors && settings.extra_selectors !== '') {
                trigger_selectors.push(settings.extra_selectors);
            }

            trigger_selectors = pum.hooks.applyFilters('pum.trigger.click_open.selectors', trigger_selectors, settings, $baloonup);

            trigger_selector = trigger_selectors.join(', ');

            $(trigger_selector)
                .addClass('pum-trigger')
                .css({cursor: "pointer"});

            $(document).on('click.pumTrigger', trigger_selector, function (event) {
                var $trigger = $(this),
                    do_default = settings.do_default || false;

                // If trigger is inside of the baloonup that it opens, do nothing.
                if ($baloonup.has($trigger).length > 0) {
                    return;
                }

                // If the baloonup is already open return.
                if ($baloonup.balooncreate('state', 'isOpen')) {
                    return;
                }

                // If cookie exists or conditions fail return.
                if ($baloonup.balooncreate('checkCookies', settings) || !$baloonup.balooncreate('checkConditions')) {
                    return;
                }

                if ($trigger.data('do-default')) {
                    do_default = $trigger.data('do-default');
                } else if ($trigger.hasClass('do-default') || $trigger.hasClass('balooncreate-do-default') || $trigger.hasClass('pum-do-default')) {
                    do_default = true;
                }

                // If trigger has the class do-default we don't prevent default actions.
                if (!event.ctrlKey && !pum.hooks.applyFilters('pum.trigger.click_open.do_default', do_default, $baloonup, $trigger)) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                // Set the global last open trigger to the clicked element.
                $.fn.balooncreate.last_open_trigger = $trigger;

                // Open the baloonup.
                $baloonup.balooncreate('open');
            });
        },
        admin_debug: function () {
            PUM.getBaloonUp(this).balooncreate('open');
        }
    };

    // Register All Triggers for a BaloonUp
    $(document)
        .on('pumInit', '.pum', function () {
            var $baloonup = PUM.getBaloonUp(this),
                settings = $baloonup.balooncreate('getSettings'),
                triggers = settings.triggers || [],
                trigger = null,
                i;

            if (triggers.length) {
                for (i = 0; triggers.length > i; i += 1) {
                    trigger = triggers[i];
                    $baloonup.balooncreate('addTrigger', trigger.type, trigger.settings);
                }
            }
        });

}(jQuery, document));
/**
 * Defines the core $.balooncreate.utilites methods.
 * Version 1.4
 */
(function ($, document, undefined) {
    "use strict";

    var inputTypes = 'color,date,datetime,datetime-local,email,hidden,month,number,password,range,search,tel,text,time,url,week'.split(','),
        inputNodes = 'select,textarea'.split(','),
        rName = /\[([^\]]*)\]/g;

    /**
     * Polyfill for IE < 9
     */
    if (!Array.prototype.indexOf) {
        Array.prototype.indexOf = function (searchElement /*, fromIndex */) {
            "use strict";

            if (this === void 0 || this === null)
                throw new TypeError();

            var t = Object(this);
            var len = t.length >>> 0;
            if (len === 0)
                return -1;

            var n = 0;
            if (arguments.length > 0) {
                n = Number(arguments[1]);
                if (n !== n) // shortcut for verifying if it's NaN
                    n = 0;
                else if (n !== 0 && n !== (1 / 0) && n !== -(1 / 0))
                    n = (n > 0 || -1) * Math.floor(Math.abs(n));
            }

            if (n >= len)
                return -1;

            var k = n >= 0
                ? n
                : Math.max(len - Math.abs(n), 0);

            for (; k < len; k++) {
                if (k in t && t[k] === searchElement)
                    return k;
            }
            return -1;
        };
    }

    function storeValue(container, parsedName, value) {

        var part = parsedName[0];

        if (parsedName.length > 1) {
            if (!container[part]) {
                // If the next part is eq to '' it means we are processing complex name (i.e. `some[]`)
                // for this case we need to use Array instead of an Object for the index increment purpose
                container[part] = parsedName[1] ? {} : [];
            }
            storeValue(container[part], parsedName.slice(1), value);
        } else {

            // Increment Array index for `some[]` case
            if (!part) {
                part = container.length;
            }

            container[part] = value;
        }
    }

    $.fn.balooncreate.utilities = {
        scrollTo: function (target, callback) {
            var $target = $(target) || $();

            if (!$target.length) {
                return;
            }

            $('html, body').animate({
                scrollTop: $target.offset().top - 100
            }, 1000, 'swing', function () {
                // Find the first :input that isn't a button or hidden type.
                var $input = $target.find(':input:not([type="button"]):not([type="hidden"]):not(button)').eq(0);

                if ($input.hasClass('mcms-editor-area')) {
                    tinyMCE.execCommand('mceFocus', false, $input.attr('id'));
                } else {
                    $input.focus();
                }

                if (typeof callback === 'function') {
                    callback();
                }
            });
        },
        /**
         * In Array tester function. Similar to PHP's in_array()

         * @param needle
         * @param array
         * @returns {boolean}
         */
        inArray: function (needle, array) {
            return !!~array.indexOf(needle);
        },
        convert_hex: function (hex, opacity) {
            hex = hex.replace('#', '');
            var r = parseInt(hex.substring(0, 2), 16),
                g = parseInt(hex.substring(2, 4), 16),
                b = parseInt(hex.substring(4, 6), 16);
            return 'rgba(' + r + ',' + g + ',' + b + ',' + opacity / 100 + ')';
        },
        debounce: function (callback, threshold) {
            var timeout;
            return function () {
                var context = this, params = arguments;
                window.clearTimeout(timeout);
                timeout = window.setTimeout(function () {
                    callback.apply(context, params);
                }, threshold);
            };
        },
        throttle: function (callback, threshold) {
            var suppress = false,
                clear = function () {
                    suppress = false;
                };
            return function () {
                if (!suppress) {
                    callback.apply(this, arguments);
                    window.setTimeout(clear, threshold);
                    suppress = true;
                }
            };
        },
        getXPath: function (element) {
            var path = [],
                current,
                id,
                classes,
                tag,
                eq;

            $.each($(element).parents(), function (index, value) {
                current = $(value);
                id = current.attr("id") || '';
                classes = current.attr("class") || '';
                tag = current.get(0).tagName.toLowerCase();
                eq = current.parent().children(tag).index(current);
                if (tag === 'body') {
                    return false;
                }
                if (classes.length > 0) {
                    classes = classes.split(' ');
                    classes = classes[0];
                }
                path.push(tag + (id.length > 0 ? "#" + id : (classes.length > 0 ? "." + classes.split(' ').join('.') : ':eq(' + eq + ')')));
            });
            return path.reverse().join(' > ');
        },
        strtotime: function (text, now) {
            //  discuss at: http://phpjs.org/functions/strtotime/
            //     version: 1109.2016
            // original by: Caio Ariede (http://caioariede.com)
            // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // improved by: Caio Ariede (http://caioariede.com)
            // improved by: A. Matas Quezada (http://amatiasq.com)
            // improved by: preuter
            // improved by: Brett Zamir (http://brett-zamir.me)
            // improved by: Mirko Faber
            //    input by: David
            // bugfixed by: Wagner B. Soares
            // bugfixed by: Artur Tchernychev
            //        note: Examples all have a fixed timestamp to prevent tests to fail because of variable time(zones)
            //   example 1: strtotime('+1 day', 1129633200);
            //   returns 1: 1129719600
            //   example 2: strtotime('+1 week 2 days 4 hours 2 seconds', 1129633200);
            //   returns 2: 1130425202
            //   example 3: strtotime('last month', 1129633200);
            //   returns 3: 1127041200
            //   example 4: strtotime('2009-05-04 08:30:00 GMT');
            //   returns 4: 1241425800
            var parsed, match, today, year, date, days, ranges, len, times, regex, i, fail = false;
            if (!text) {
                return fail;
            }
            // Unecessary spaces
            text = text.replace(/^\s+|\s+$/g, '')
                .replace(/\s{2,}/g, ' ')
                .replace(/[\t\r\n]/g, '')
                .toLowerCase();
            // in contrast to php, js Date.parse function interprets:
            // dates given as yyyy-mm-dd as in timezone: UTC,
            // dates with "." or "-" as MDY instead of DMY
            // dates with two-digit years differently
            // etc...etc...
            // ...therefore we manually parse lots of common date formats
            match = text.match(/^(\d{1,4})([\-\.\/\:])(\d{1,2})([\-\.\/\:])(\d{1,4})(?:\s(\d{1,2}):(\d{2})?:?(\d{2})?)?(?:\s([A-Z]+)?)?$/);
            if (match && match[2] === match[4]) {
                if (match[1] > 1901) {
                    switch (match[2]) {
                    case '-':
                        // YYYY-M-D
                        if (match[3] > 12 || match[5] > 31) {
                            return fail;
                        }
                        return new Date(match[1], parseInt(match[3], 10) - 1, match[5],
                            match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
                    case '.':
                        // YYYY.M.D is not parsed by strtotime()
                        return fail;
                    case '/':
                        // YYYY/M/D
                        if (match[3] > 12 || match[5] > 31) {
                            return fail;
                        }
                        return new Date(match[1], parseInt(match[3], 10) - 1, match[5],
                            match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
                    }
                } else if (match[5] > 1901) {
                    switch (match[2]) {
                    case '-':
                        // D-M-YYYY
                        if (match[3] > 12 || match[1] > 31) {
                            return fail;
                        }
                        return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
                            match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
                    case '.':
                        // D.M.YYYY
                        if (match[3] > 12 || match[1] > 31) {
                            return fail;
                        }
                        return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
                            match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
                    case '/':
                        // M/D/YYYY
                        if (match[1] > 12 || match[3] > 31) {
                            return fail;
                        }
                        return new Date(match[5], parseInt(match[1], 10) - 1, match[3],
                            match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
                    }
                } else {
                    switch (match[2]) {
                    case '-':
                        // YY-M-D
                        if (match[3] > 12 || match[5] > 31 || (match[1] < 70 && match[1] > 38)) {
                            return fail;
                        }
                        year = match[1] >= 0 && match[1] <= 38 ? +match[1] + 2000 : match[1];
                        return new Date(year, parseInt(match[3], 10) - 1, match[5],
                            match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
                    case '.':
                        // D.M.YY or H.MM.SS
                        if (match[5] >= 70) { // D.M.YY
                            if (match[3] > 12 || match[1] > 31) {
                                return fail;
                            }
                            return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
                                match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
                        }
                        if (match[5] < 60 && !match[6]) { // H.MM.SS
                            if (match[1] > 23 || match[3] > 59) {
                                return fail;
                            }
                            today = new Date();
                            return new Date(today.getFullYear(), today.getMonth(), today.getDate(),
                                match[1] || 0, match[3] || 0, match[5] || 0, match[9] || 0) / 1000;
                        }
                        return fail; // invalid format, cannot be parsed
                    case '/':
                        // M/D/YY
                        if (match[1] > 12 || match[3] > 31 || (match[5] < 70 && match[5] > 38)) {
                            return fail;
                        }
                        year = match[5] >= 0 && match[5] <= 38 ? +match[5] + 2000 : match[5];
                        return new Date(year, parseInt(match[1], 10) - 1, match[3],
                            match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
                    case ':':
                        // HH:MM:SS
                        if (match[1] > 23 || match[3] > 59 || match[5] > 59) {
                            return fail;
                        }
                        today = new Date();
                        return new Date(today.getFullYear(), today.getMonth(), today.getDate(),
                            match[1] || 0, match[3] || 0, match[5] || 0) / 1000;
                    }
                }
            }
            // other formats and "now" should be parsed by Date.parse()
            if (text === 'now') {
                return now === null || isNaN(now) ? new Date()
                    .getTime() / 1000 || 0 : now || 0;
            }
            parsed = Date.parse(text);
            if (!isNaN(parsed)) {
                return parsed / 1000 || 0;
            }
            date = now ? new Date(now * 1000) : new Date();
            days = {
                'sun': 0,
                'mon': 1,
                'tue': 2,
                'wed': 3,
                'thu': 4,
                'fri': 5,
                'sat': 6
            };
            ranges = {
                'yea': 'FullYear',
                'mon': 'Month',
                'day': 'Date',
                'hou': 'Hours',
                'min': 'Minutes',
                'sec': 'Seconds'
            };

            function lastNext(type, range, modifier) {
                var diff, day = days[range];
                if (day !== undefined) {
                    diff = day - date.getDay();
                    if (diff === 0) {
                        diff = 7 * modifier;
                    } else if (diff > 0 && type === 'last') {
                        diff -= 7;
                    } else if (diff < 0 && type === 'next') {
                        diff += 7;
                    }
                    date.setDate(date.getDate() + diff);
                }
            }

            function process(val) {
                var splt = val.split(' '),
                    type = splt[0],
                    range = splt[1].substring(0, 3),
                    typeIsNumber = /\d+/.test(type),
                    ago = splt[2] === 'ago',
                    num = (type === 'last' ? -1 : 1) * (ago ? -1 : 1);
                if (typeIsNumber) {
                    num *= parseInt(type, 10);
                }
                if (ranges.hasOwnProperty(range) && !splt[1].match(/^mon(day|\.)?$/i)) {
                    return date['set' + ranges[range]](date['get' + ranges[range]]() + num);
                }
                if (range === 'wee') {
                    return date.setDate(date.getDate() + (num * 7));
                }
                if (type === 'next' || type === 'last') {
                    lastNext(type, range, num);
                } else if (!typeIsNumber) {
                    return false;
                }
                return true;
            }

            times = '(years?|months?|weeks?|days?|hours?|minutes?|min|seconds?|sec' +
                '|sunday|sun\\.?|monday|mon\\.?|tuesday|tue\\.?|wednesday|wed\\.?' +
                '|thursday|thu\\.?|friday|fri\\.?|saturday|sat\\.?)';
            regex = '([+-]?\\d+\\s' + times + '|' + '(last|next)\\s' + times + ')(\\sago)?';
            match = text.match(new RegExp(regex, 'gi'));
            if (!match) {
                return fail;
            }
            for (i = 0, len = match.length; i < len; i += 1) {
                if (!process(match[i])) {
                    return fail;
                }
            }
            // ECMAScript 5 only
            // if (!match.every(process))
            //    return false;
            return (date.getTime() / 1000);
        },
        serializeObject: function (options) {
            $.extend({}, options);

            var values = {},
                settings = $.extend(true, {
                    include: [],
                    exclude: [],
                    includeByClass: ''
                }, options);

            this.find(':input').each(function () {

                var parsedName;

                // Apply simple checks and filters
                if (!this.name || this.disabled ||
                    window.PUM.utilities.inArray(this.name, settings.exclude) ||
                    (settings.include.length && !window.PUM.utilities.inArray(this.name, settings.include)) ||
                    this.className.indexOf(settings.includeByClass) === -1) {
                    return;
                }

                // Parse complex names
                // JS RegExp doesn't support "positive look behind" :( that's why so weird parsing is used
                parsedName = this.name.replace(rName, '[$1').split('[');
                if (!parsedName[0]) {
                    return;
                }

                if (this.checked ||
                    window.PUM.utilities.inArray(this.type, inputTypes) ||
                    window.PUM.utilities.inArray(this.nodeName.toLowerCase(), inputNodes)) {

                    // Simulate control with a complex name (i.e. `some[]`)
                    // as it handled in the same way as Checkboxes should
                    if (this.type === 'checkbox') {
                        parsedName.push('');
                    }

                    // jQuery.val() is used to simplify of getting values
                    // from the custom controls (which follow jQuery .val() API) and Multiple Select
                    storeValue(values, parsedName, $(this).val());
                }
            });

            return values;
        }
    };

    //$.fn.pumSerializeObject = $.fn.balooncreate.utilities.serializeObject;

    // Deprecated fix. utilies was renamed because of typo.
    $.fn.balooncreate.utilies = $.fn.balooncreate.utilities;

    window.PUM = window.PUM || {};
    window.PUM.utilities = window.PUM.utilities || {};
    window.PUM.utilities = $.extend(window.PUM.utilities, $.fn.balooncreate.utilities);

}(jQuery, document));
/*******************************************************************************
 * Copyright (c) 2018, MCMS BaloonUp Maker
 ******************************************************************************/
(function (root, factory) {

    // AMD
    if (typeof define === "function" && define.amd) {
        define(["exports", "jquery"], function (exports, $) {
            return factory(exports, $);
        });
    }

    // CommonJS
    else if (typeof exports !== "undefined") {
        var $ = require("jquery");
        factory(exports, $);
    }

    // Browser
    else {
        factory(root, (root.jQuery || root.Zepto || root.ender || root.$));
    }

}(this, function (exports, $) {

    var patterns = {
        validate: /^[a-z_][a-z0-9_]*(?:\[(?:\d*|[a-z0-9_]+)\])*$/i,
        key: /[a-z0-9_]+|(?=\[\])/gi,
        push: /^$/,
        fixed: /^\d+$/,
        named: /^[a-z0-9_]+$/i
    };

    function FormSerializer(helper, $form) {

        // private variables
        var data = {},
            pushes = {};

        // private API
        function build(base, key, value) {
            base[key] = value;
            return base;
        }

        function makeObject(root, value) {

            var keys = root.match(patterns.key), k;

            try {
                value = JSON.parse(value);
            } catch (Error) {
            }

            // nest, nest, ..., nest
            while ((k = keys.pop()) !== undefined) {
                // foo[]
                if (patterns.push.test(k)) {
                    var idx = incrementPush(root.replace(/\[\]$/, ''));
                    value = build([], idx, value);
                }

                // foo[n]
                else if (patterns.fixed.test(k)) {
                    value = build([], k, value);
                }

                // foo; foo[bar]
                else if (patterns.named.test(k)) {
                    value = build({}, k, value);
                }
            }

            return value;
        }

        function incrementPush(key) {
            if (pushes[key] === undefined) {
                pushes[key] = 0;
            }
            return pushes[key]++;
        }

        function encode(pair) {
            switch ($('[name="' + pair.name + '"]', $form).attr("type")) {
            case "checkbox":
                return pair.value === "1" ? true : pair.value;
            default:
                return pair.value;
            }
        }

        function addPair(pair) {
            if (!patterns.validate.test(pair.name)) return this;
            var obj = makeObject(pair.name, encode(pair));

            data = helper.extend(true, data, obj);
            return this;
        }

        function addPairs(pairs) {
            if (!helper.isArray(pairs)) {
                throw new Error("formSerializer.addPairs expects an Array");
            }
            for (var i = 0, len = pairs.length; i < len; i++) {
                this.addPair(pairs[i]);
            }
            return this;
        }

        function serialize() {
            return data;
        }

        function serializeJSON() {
            return JSON.stringify(serialize());
        }

        // public API
        this.addPair = addPair;
        this.addPairs = addPairs;
        this.serialize = serialize;
        this.serializeJSON = serializeJSON;
    }

    FormSerializer.patterns = patterns;

    FormSerializer.serializeObject = function serializeObject() {
        var serialized;

        if (this.is('form')) {
            serialized = this.serializeArray();
        } else {
            serialized = this.find(':input').serializeArray();
        }

        return new FormSerializer($, this)
            .addPairs(serialized)
            .serialize();
    };

    FormSerializer.serializeJSON = function serializeJSON() {
        var serialized;

        if (this.is('form')) {
            serialized = this.serializeArray();
        } else {
            serialized = this.find(':input').serializeArray();
        }

        return new FormSerializer($, this)
            .addPairs(serialized)
            .serializeJSON();
    };

    if (typeof $.fn !== "undefined") {
        $.fn.pumSerializeObject = FormSerializer.serializeObject;
        $.fn.pumSerializeJSON = FormSerializer.serializeJSON;
    }

    exports.FormSerializer = FormSerializer;

    return FormSerializer;
}));
/**
 * Initialize BaloonUp Maker.
 * Version 1.7
 */
(function ($, document, undefined) {
    "use strict";
    // Defines the current version.
    $.fn.balooncreate.version = 1.7;

    // Stores the last open baloonup.
    $.fn.balooncreate.last_open_baloonup = null;

    $(document).ready(function () {
        $('.pum').balooncreate();
        $(document).trigger('pumInitialized');

        /**
         * Process php based form submissions when the form_success args are passed.
         */
        if (typeof pum_vars.form_success === 'object') {
            pum_vars.form_success = $.extend({
                baloonup_id: null,
                settings: {}
            });

            PUM.forms.success(pum_vars.form_success.baloonup_id, pum_vars.form_success.settings);
        }
    });

    /**
     * Add hidden field to all baloonup forms.
     */
    $('.pum').on('pumInit', function () {
        var $baloonup = PUM.getBaloonUp(this),
            baloonupID = PUM.getSetting($baloonup, 'id'),
            $forms = $baloonup.find('form');

        /**
         * If there are forms in the baloonup add a hidden field for use in retriggering the baloonup on reload.
         */
        if ($forms.length) {
            $forms.prepend('<input type="hidden" name="pum_form_baloonup_id" value="' + baloonupID + '" />');
        }
    });


}(jQuery));