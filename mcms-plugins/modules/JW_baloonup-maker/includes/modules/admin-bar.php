<?php

if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

/**
 * Class PUM_Modules_Admin_Bar
 *
 * This class adds admin bar menu for BaloonUp Management.
 */
class PUM_Modules_Admin_Bar {

	/**
	 * Initializes this module.
	 */
	public static function init() {
		add_action( 'admin_bar_menu', array( __CLASS__, 'toolbar_links' ), 999 );
		add_action( 'mcms_footer', array( __CLASS__, 'admin_bar_styles' ), 999 );
		add_action( 'init', array( __CLASS__, 'show_debug_bar' ) );
	}

	/**
	 * Renders the admin debug bar when PUM Debug is enabled.
	 */
	public static function show_debug_bar() {
		if ( current_user_can( 'manage_options' ) && BaloonUp_Maker::debug_mode() ) {
			show_admin_bar( true );
		}
	}

	public static function admin_bar_styles() {

		if ( is_admin() || ! is_admin_bar_showing() || PUM_Options::get( 'disabled_admin_bar', false ) ) {
			return;
		} ?>

		<style id="pum-admin-bar-styles">
			/* Layer admin bar over baloonups. */
			#mcmsadminbar {
				z-index: 999999999999;
			}

			#mcms-admin-bar-baloonup-maker > .ab-item::before {
				background: url("<?php echo POPMAKE_URL; ?>/assets/images/admin/icon-info-21x21.png") center center no-repeat transparent !important;
				top: 3px;
				content: "";
				width: 20px;
				height: 20px;
			}

			#mcms-admin-bar-baloonup-maker:hover > .ab-item::before {
				background-image: url("<?php echo POPMAKE_URL; ?>/assets/images/admin/icon-info-21x21.png") !important;
			}
		</style>
		<script id="pum-admin-bar-tools" type="text/javascript">
            /**
             * CssSelectorGenerator
             */
            (function () {
                var CssSelectorGenerator, root,
                    indexOf = [].indexOf || function (item) {
                        for (var i = 0, l = this.length; i < l; i++) {
                            if (i in this && this[i] === item) return i;
                        }
                        return -1;
                    };

                CssSelectorGenerator = (function () {
                    CssSelectorGenerator.prototype.default_options = {
                        selectors: ['id', 'class', 'tag', 'nthchild']
                    };

                    function CssSelectorGenerator(options) {
                        if (options == null) {
                            options = {};
                        }
                        this.options = {};
                        this.setOptions(this.default_options);
                        this.setOptions(options);
                    }

                    CssSelectorGenerator.prototype.setOptions = function (options) {
                        var key, results, val;
                        if (options == null) {
                            options = {};
                        }
                        results = [];
                        for (key in options) {
                            val = options[key];
                            if (this.default_options.hasOwnProperty(key)) {
                                results.push(this.options[key] = val);
                            } else {
                                results.push(void 0);
                            }
                        }
                        return results;
                    };

                    CssSelectorGenerator.prototype.isElement = function (element) {
                        return !!((element != null ? element.nodeType : void 0) === 1);
                    };

                    CssSelectorGenerator.prototype.getParents = function (element) {
                        var current_element, result;
                        result = [];
                        if (this.isElement(element)) {
                            current_element = element;
                            while (this.isElement(current_element)) {
                                result.push(current_element);
                                current_element = current_element.parentNode;
                            }
                        }
                        return result;
                    };

                    CssSelectorGenerator.prototype.getTagSelector = function (element) {
                        return this.sanitizeItem(element.tagName.toLowerCase());
                    };

                    CssSelectorGenerator.prototype.sanitizeItem = function (item) {
                        var characters;
                        characters = (item.split('')).map(function (character) {
                            if (character === ':') {
                                return "\\" + (':'.charCodeAt(0).toString(16).toUpperCase()) + " ";
                            } else if (/[ !"#$%&'()*+,.\/;<=>?@\[\\\]^`{|}~]/.test(character)) {
                                return "\\" + character;
                            } else {
                                return escape(character).replace(/\%/g, '\\');
                            }
                        });
                        return characters.join('');
                    };

                    CssSelectorGenerator.prototype.getIdSelector = function (element) {
                        var id, sanitized_id;
                        id = element.getAttribute('id');
                        if ((id != null) && (id !== '') && !(/\s/.exec(id)) && !(/^\d/.exec(id))) {
                            sanitized_id = "#" + (this.sanitizeItem(id));
                            if (element.ownerDocument.querySelectorAll(sanitized_id).length === 1) {
                                return sanitized_id;
                            }
                        }
                        return null;
                    };

                    CssSelectorGenerator.prototype.getClassSelectors = function (element) {
                        var class_string, item, result;
                        result = [];
                        class_string = element.getAttribute('class');
                        if (class_string != null) {
                            class_string = class_string.replace(/\s+/g, ' ');
                            class_string = class_string.replace(/^\s|\s$/g, '');
                            if (class_string !== '') {
                                result = (function () {
                                    var k, len, ref, results;
                                    ref = class_string.split(/\s+/);
                                    results = [];
                                    for (k = 0, len = ref.length; k < len; k++) {
                                        item = ref[k];
                                        results.push("." + (this.sanitizeItem(item)));
                                    }
                                    return results;
                                }).call(this);
                            }
                        }
                        return result;
                    };

                    CssSelectorGenerator.prototype.getAttributeSelectors = function (element) {
                        var attribute, blacklist, k, len, ref, ref1, result;
                        result = [];
                        blacklist = ['id', 'class'];
                        ref = element.attributes;
                        for (k = 0, len = ref.length; k < len; k++) {
                            attribute = ref[k];
                            if (ref1 = attribute.nodeName, indexOf.call(blacklist, ref1) < 0) {
                                result.push("[" + attribute.nodeName + "=" + attribute.nodeValue + "]");
                            }
                        }
                        return result;
                    };

                    CssSelectorGenerator.prototype.getNthChildSelector = function (element) {
                        var counter, k, len, parent_element, sibling, siblings;
                        parent_element = element.parentNode;
                        if (parent_element != null) {
                            counter = 0;
                            siblings = parent_element.childNodes;
                            for (k = 0, len = siblings.length; k < len; k++) {
                                sibling = siblings[k];
                                if (this.isElement(sibling)) {
                                    counter++;
                                    if (sibling === element) {
                                        return ":nth-child(" + counter + ")";
                                    }
                                }
                            }
                        }
                        return null;
                    };

                    CssSelectorGenerator.prototype.testSelector = function (element, selector) {
                        var is_unique, result;
                        is_unique = false;
                        if ((selector != null) && selector !== '') {
                            result = element.ownerDocument.querySelectorAll(selector);
                            if (result.length === 1 && result[0] === element) {
                                is_unique = true;
                            }
                        }
                        return is_unique;
                    };

                    CssSelectorGenerator.prototype.getAllSelectors = function (element) {
                        var result;
                        result = {
                            t: null,
                            i: null,
                            c: null,
                            a: null,
                            n: null
                        };
                        if (indexOf.call(this.options.selectors, 'tag') >= 0) {
                            result.t = this.getTagSelector(element);
                        }
                        if (indexOf.call(this.options.selectors, 'id') >= 0) {
                            result.i = this.getIdSelector(element);
                        }
                        if (indexOf.call(this.options.selectors, 'class') >= 0) {
                            result.c = this.getClassSelectors(element);
                        }
                        if (indexOf.call(this.options.selectors, 'attribute') >= 0) {
                            result.a = this.getAttributeSelectors(element);
                        }
                        if (indexOf.call(this.options.selectors, 'nthchild') >= 0) {
                            result.n = this.getNthChildSelector(element);
                        }
                        return result;
                    };

                    CssSelectorGenerator.prototype.testUniqueness = function (element, selector) {
                        var found_elements, parent;
                        parent = element.parentNode;
                        found_elements = parent.querySelectorAll(selector);
                        return found_elements.length === 1 && found_elements[0] === element;
                    };

                    CssSelectorGenerator.prototype.testCombinations = function (element, items, tag) {
                        var item, k, l, len, len1, ref, ref1;
                        ref = this.getCombinations(items);
                        for (k = 0, len = ref.length; k < len; k++) {
                            item = ref[k];
                            if (this.testUniqueness(element, item)) {
                                return item;
                            }
                        }
                        if (tag != null) {
                            ref1 = items.map(function (item) {
                                return tag + item;
                            });
                            for (l = 0, len1 = ref1.length; l < len1; l++) {
                                item = ref1[l];
                                if (this.testUniqueness(element, item)) {
                                    return item;
                                }
                            }
                        }
                        return null;
                    };

                    CssSelectorGenerator.prototype.getUniqueSelector = function (element) {
                        var found_selector, k, len, ref, selector_type, selectors;
                        selectors = this.getAllSelectors(element);
                        ref = this.options.selectors;
                        for (k = 0, len = ref.length; k < len; k++) {
                            selector_type = ref[k];
                            switch (selector_type) {
                            case 'id':
                                if (selectors.i != null) {
                                    return selectors.i;
                                }
                                break;
                            case 'tag':
                                if (selectors.t != null) {
                                    if (this.testUniqueness(element, selectors.t)) {
                                        return selectors.t;
                                    }
                                }
                                break;
                            case 'class':
                                if ((selectors.c != null) && selectors.c.length !== 0) {
                                    found_selector = this.testCombinations(element, selectors.c, selectors.t);
                                    if (found_selector) {
                                        return found_selector;
                                    }
                                }
                                break;
                            case 'attribute':
                                if ((selectors.a != null) && selectors.a.length !== 0) {
                                    found_selector = this.testCombinations(element, selectors.a, selectors.t);
                                    if (found_selector) {
                                        return found_selector;
                                    }
                                }
                                break;
                            case 'nthchild':
                                if (selectors.n != null) {
                                    return selectors.n;
                                }
                            }
                        }
                        return '*';
                    };

                    CssSelectorGenerator.prototype.getSelector = function (element) {
                        var all_selectors, item, k, l, len, len1, parents, result, selector, selectors;
                        all_selectors = [];
                        parents = this.getParents(element);
                        for (k = 0, len = parents.length; k < len; k++) {
                            item = parents[k];
                            selector = this.getUniqueSelector(item);
                            if (selector != null) {
                                all_selectors.push(selector);
                            }
                        }
                        selectors = [];
                        for (l = 0, len1 = all_selectors.length; l < len1; l++) {
                            item = all_selectors[l];
                            selectors.unshift(item);
                            result = selectors.join(' > ');
                            if (this.testSelector(element, result)) {
                                return result;
                            }
                        }
                        return null;
                    };

                    CssSelectorGenerator.prototype.getCombinations = function (items) {
                        var i, j, k, l, ref, ref1, result;
                        if (items == null) {
                            items = [];
                        }
                        result = [[]];
                        for (i = k = 0, ref = items.length - 1; 0 <= ref ? k <= ref : k >= ref; i = 0 <= ref ? ++k : --k) {
                            for (j = l = 0, ref1 = result.length - 1; 0 <= ref1 ? l <= ref1 : l >= ref1; j = 0 <= ref1 ? ++l : --l) {
                                result.push(result[j].concat(items[i]));
                            }
                        }
                        result.shift();
                        result = result.sort(function (a, b) {
                            return a.length - b.length;
                        });
                        result = result.map(function (item) {
                            return item.join('');
                        });
                        return result;
                    };

                    return CssSelectorGenerator;

                })();

                if (typeof define !== "undefined" && define !== null ? define.amd : void 0) {
                    define([], function () {
                        return CssSelectorGenerator;
                    });
                } else {
                    root = typeof exports !== "undefined" && exports !== null ? exports : this;
                    root.CssSelectorGenerator = CssSelectorGenerator;
                }

            }).call(this);

            (function ($) {
                var selector_generator = new CssSelectorGenerator;

                $(document).on('click', '#mcms-admin-bar-pum-get-selector', function (event) {

                    alert("<?php _e( 'After clicking ok, click the element you want a selector for.', 'baloonup-maker' ); ?>");

                    event.preventDefault();
                    event.stopPropagation();

                    $(document).one('click', function (event) {
                        // get reference to the element user clicked on
                        var element  = event.target,
                            // get unique CSS selector for that element
                            selector = selector_generator.getSelector(element);

                        alert("<?php _ex( 'Selector', 'JS alert for CSS get selector tool', 'baloonup-maker' ); ?>: " + selector);

                        event.preventDefault();
                        event.stopPropagation();
                    });
                });
            }(jQuery));
		</script><?php
	}

	/**
	 * Add additional toolbar menu items to the front end.
	 *
	 * @param $mcms_admin_bar
	 */
	public static function toolbar_links( $mcms_admin_bar ) {

		if ( is_admin() || ! is_admin_bar_showing() || PUM_Options::get( 'disabled_admin_bar', false ) ) {
			return;
		}

		$mcms_admin_bar->add_node( array(
			'id'     => 'baloonup-maker',
			'title'  => __( 'BaloonUp Maker', 'baloonup-maker' ),
			'href'   => '#baloonup-maker',
			'meta'   => array( 'class' => 'baloonup-maker-toolbar' ),
			'parent' => false,
		) );

		$baloonups_url = current_user_can( 'edit_posts' ) ? admin_url( 'edit.php?post_type=baloonup' ) : '#';

		$mcms_admin_bar->add_node( array(
			'id'     => 'baloonups',
			'title'  => __( 'BaloonUps', 'baloonup-maker' ),
			'href'   => $baloonups_url,
			'parent' => 'baloonup-maker',
		) );

		$baloonups = PUM_Modules_Admin_Bar::loaded_baloonups();

		if ( count( $baloonups ) ) {

			foreach ( $baloonups as $baloonup ) {
				/** @var MCMS_Post $baloonup */

				$node_id = 'baloonup-' . $baloonup->ID;

				$can_edit = current_user_can( 'edit_post', $baloonup->ID );

				$edit_url = $can_edit ? admin_url( 'post.php?post=' . $baloonup->ID . '&action=edit' ) : '#';

				// Single BaloonUp Menu Node
				$mcms_admin_bar->add_node( array(
					'id'     => $node_id,
					'title'  => $baloonup->post_title,
					'href'   => $edit_url,
					'parent' => 'baloonups',
				) );

				// Trigger Link
				$mcms_admin_bar->add_node( array(
					'id'     => $node_id . '-open',
					'title'  => __( 'Open BaloonUp', 'baloonup-maker' ),
					'meta'   => array(
						'onclick' => 'PUM.open(' . $baloonup->ID . '); return false;',
					),
					'href'   => '#baloonup-maker-open-baloonup-' . $baloonup->ID,
					'parent' => $node_id,
				) );

				$mcms_admin_bar->add_node( array(
					'id'     => $node_id . '-close',
					'title'  => __( 'Close BaloonUp', 'baloonup-maker' ),
					'meta'   => array(
						'onclick' => 'PUM.close(' . $baloonup->ID . '); return false;',
					),
					'href'   => '#baloonup-maker-close-baloonup-' . $baloonup->ID,
					'parent' => $node_id,
				) );

				if ( pum_baloonup( $baloonup->ID )->has_conditions( array( 'js_only' => true ) ) ) {
					$mcms_admin_bar->add_node( array(
						'id'     => $node_id . '-conditions',
						'title'  => __( 'Check Conditions', 'baloonup-maker' ),
						'meta'   => array(
							'onclick' => 'alert(PUM.checkConditions(' . $baloonup->ID . ') ? "Pass" : "Fail"); return false;',
						),
						'href'   => '#baloonup-maker-check-conditions-baloonup-' . $baloonup->ID,
						'parent' => $node_id,
					) );
				}

				$mcms_admin_bar->add_node( array(
					'id'     => $node_id . '-reset-cookies',
					'title'  => __( 'Reset Cookies', 'baloonup-maker' ),
					'meta'   => array(
						'onclick' => 'PUM.clearCookies(' . $baloonup->ID . '); alert("' . __( 'Success', 'baloonup-maker' ) . '"); return false;',
					),
					'href'   => '#baloonup-maker-reset-cookies-baloonup-' . $baloonup->ID,
					'parent' => $node_id,
				) );

				if ( $can_edit ) {
					// Edit BaloonUp Link
					$mcms_admin_bar->add_node( array(
						'id'     => $node_id . '-edit',
						'title'  => __( 'Edit BaloonUp', 'baloonup-maker' ),
						'href'   => $edit_url,
						'parent' => $node_id,
					) );
				}

			}
		} else {
			$mcms_admin_bar->add_node( array(
				'id'     => 'no-baloonups-loaded',
				'title'  => __( 'No BaloonUps Loaded', 'baloonup-maker' ) . '<strong style="color:#fff; margin-left: 5px;">?</strong>',
				'href'   => 'https://docs.mcmsbaloonupmaker.com/article/265-my-baloonup-wont-work-how-can-i-fix-it?utm_capmaign=Self+Help&utm_source=No+BaloonUps&utm_medium=Admin+Bar',
				'parent' => 'baloonups',
				'meta'   => array(
					'target' => '_blank',
				),

			) );
		}

		/**
		 * Tools
		 */
		$mcms_admin_bar->add_node( array(
			'id'     => 'pum-tools',
			'title'  => __( 'Tools', 'baloonup-maker' ),
			'href'   => '#baloonup-maker-tools',
			'parent' => 'baloonup-maker',
		) );

		/**
		 * Get Selector
		 */
		$mcms_admin_bar->add_node( array(
			'id'     => 'pum-get-selector',
			'title'  => __( 'Get Selector', 'baloonup-maker' ),
			'href'   => '#baloonup-maker-get-selector-tool',
			'parent' => 'pum-tools',
		) );

	}

	public static function loaded_baloonups() {
		static $baloonups;

		if ( ! isset( $baloonups ) ) {
			$loaded = PUM_Site_BaloonUps::get_loaded_baloonups();
			$baloonups = $loaded->posts;
		}

		return $baloonups;
	}
}

PUM_Modules_Admin_Bar::init();