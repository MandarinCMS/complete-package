<?php
/**
 * MandarinCMS core upgrade functionality.
 *
 * @package MandarinCMS
 * @subpackage Administration
 * @since 2.7.0
 */

/**
 * Stores files to be deleted.
 *
 * @since 2.7.0
 * @global array $_old_files
 * @var array
 * @name $_old_files
 */
global $_old_files;

$_old_files = array(
// 2.0
'mcms-admin/import-b2.php',
'mcms-admin/import-blogger.php',
'mcms-admin/import-greymatter.php',
'mcms-admin/import-livejournal.php',
'mcms-admin/import-mt.php',
'mcms-admin/import-rss.php',
'mcms-admin/import-textpattern.php',
'mcms-admin/quicktags.js',
'mcms-images/fade-butt.png',
'mcms-images/get-firefox.png',
'mcms-images/header-shadow.png',
'mcms-images/smilies',
'mcms-images/mcms-small.png',
'mcms-images/mcmsminilogo.png',
'mcms.php',
// 2.0.8
'mcms-roots/js/tinymce/modules/inlinepopups/readme.txt',
// 2.1
'mcms-admin/edit-form-ajax-cat.php',
'mcms-admin/execute-pings.php',
'mcms-admin/inline-uploading.php',
'mcms-admin/link-categories.php',
'mcms-admin/list-manipulation.js',
'mcms-admin/list-manipulation.php',
'mcms-roots/comment-functions.php',
'mcms-roots/feed-functions.php',
'mcms-roots/functions-compat.php',
'mcms-roots/functions-formatting.php',
'mcms-roots/functions-post.php',
'mcms-roots/js/dbx-key.js',
'mcms-roots/js/tinymce/modules/autosave/langs/cs.js',
'mcms-roots/js/tinymce/modules/autosave/langs/sv.js',
'mcms-roots/links.php',
'mcms-roots/pluggable-functions.php',
'mcms-roots/template-functions-author.php',
'mcms-roots/template-functions-category.php',
'mcms-roots/template-functions-general.php',
'mcms-roots/template-functions-links.php',
'mcms-roots/template-functions-post.php',
'mcms-roots/mcms-l10n.php',
// 2.2
'mcms-admin/cat-js.php',
'mcms-admin/import/b2.php',
'mcms-roots/js/autosave-js.php',
'mcms-roots/js/list-manipulation-js.php',
'mcms-roots/js/mcms-ajax-js.php',
// 2.3
'mcms-admin/admin-db.php',
'mcms-admin/cat.js',
'mcms-admin/categories.js',
'mcms-admin/custom-fields.js',
'mcms-admin/dbx-admin-key.js',
'mcms-admin/edit-comments.js',
'mcms-admin/install-rtl.css',
'mcms-admin/install.css',
'mcms-admin/upgrade-schema.php',
'mcms-admin/upload-functions.php',
'mcms-admin/upload-rtl.css',
'mcms-admin/upload.css',
'mcms-admin/upload.js',
'mcms-admin/users.js',
'mcms-admin/widgets-rtl.css',
'mcms-admin/widgets.css',
'mcms-admin/xfn.js',
'mcms-roots/js/tinymce/license.html',
// 2.5
'mcms-admin/css/upload.css',
'mcms-admin/images/box-bg-left.gif',
'mcms-admin/images/box-bg-right.gif',
'mcms-admin/images/box-bg.gif',
'mcms-admin/images/box-butt-left.gif',
'mcms-admin/images/box-butt-right.gif',
'mcms-admin/images/box-butt.gif',
'mcms-admin/images/box-head-left.gif',
'mcms-admin/images/box-head-right.gif',
'mcms-admin/images/box-head.gif',
'mcms-admin/images/heading-bg.gif',
'mcms-admin/images/login-bkg-bottom.gif',
'mcms-admin/images/login-bkg-tile.gif',
'mcms-admin/images/notice.gif',
'mcms-admin/images/toggle.gif',
'mcms-admin/includes/upload.php',
'mcms-admin/js/dbx-admin-key.js',
'mcms-admin/js/link-cat.js',
'mcms-admin/profile-update.php',
'mcms-admin/templates.php',
'mcms-roots/images/wlw/WpComments.png',
'mcms-roots/images/wlw/WpIcon.png',
'mcms-roots/images/wlw/WpWatermark.png',
'mcms-roots/js/dbx.js',
'mcms-roots/js/fat.js',
'mcms-roots/js/list-manipulation.js',
'mcms-roots/js/tinymce/langs/en.js',
'mcms-roots/js/tinymce/modules/autosave/editor_module_src.js',
'mcms-roots/js/tinymce/modules/autosave/langs',
'mcms-roots/js/tinymce/modules/directionality/images',
'mcms-roots/js/tinymce/modules/directionality/langs',
'mcms-roots/js/tinymce/modules/inlinepopups/css',
'mcms-roots/js/tinymce/modules/inlinepopups/images',
'mcms-roots/js/tinymce/modules/inlinepopups/jscripts',
'mcms-roots/js/tinymce/modules/paste/images',
'mcms-roots/js/tinymce/modules/paste/jscripts',
'mcms-roots/js/tinymce/modules/paste/langs',
'mcms-roots/js/tinymce/modules/spellchecker/classes/HttpClient.class.php',
'mcms-roots/js/tinymce/modules/spellchecker/classes/TinyGoogleSpell.class.php',
'mcms-roots/js/tinymce/modules/spellchecker/classes/TinyPspell.class.php',
'mcms-roots/js/tinymce/modules/spellchecker/classes/TinyPspellShell.class.php',
'mcms-roots/js/tinymce/modules/spellchecker/css/spellchecker.css',
'mcms-roots/js/tinymce/modules/spellchecker/images',
'mcms-roots/js/tinymce/modules/spellchecker/langs',
'mcms-roots/js/tinymce/modules/spellchecker/tinyspell.php',
'mcms-roots/js/tinymce/modules/mandarincms/images',
'mcms-roots/js/tinymce/modules/mandarincms/langs',
'mcms-roots/js/tinymce/modules/mandarincms/mandarincms.css',
'mcms-roots/js/tinymce/modules/mcmshelp',
'mcms-roots/js/tinymce/myskins/advanced/css',
'mcms-roots/js/tinymce/myskins/advanced/images',
'mcms-roots/js/tinymce/myskins/advanced/jscripts',
'mcms-roots/js/tinymce/myskins/advanced/langs',
// 2.5.1
'mcms-roots/js/tinymce/tiny_mce_gzip.php',
// 2.6
'mcms-admin/bookmarklet.php',
'mcms-roots/js/jquery/jquery.dimensions.min.js',
'mcms-roots/js/tinymce/modules/mandarincms/popups.css',
'mcms-roots/js/mcms-ajax.js',
// 2.7
'mcms-admin/css/press-this-ie-rtl.css',
'mcms-admin/css/press-this-ie.css',
'mcms-admin/css/upload-rtl.css',
'mcms-admin/edit-form.php',
'mcms-admin/images/comment-pill.gif',
'mcms-admin/images/comment-stalk-classic.gif',
'mcms-admin/images/comment-stalk-fresh.gif',
'mcms-admin/images/comment-stalk-rtl.gif',
'mcms-admin/images/del.png',
'mcms-admin/images/gear.png',
'mcms-admin/images/media-button-gallery.gif',
'mcms-admin/images/media-buttons.gif',
'mcms-admin/images/postbox-bg.gif',
'mcms-admin/images/tab.png',
'mcms-admin/images/tail.gif',
'mcms-admin/js/forms.js',
'mcms-admin/js/upload.js',
'mcms-admin/link-import.php',
'mcms-roots/images/audio.png',
'mcms-roots/images/css.png',
'mcms-roots/images/default.png',
'mcms-roots/images/doc.png',
'mcms-roots/images/exe.png',
'mcms-roots/images/html.png',
'mcms-roots/images/js.png',
'mcms-roots/images/pdf.png',
'mcms-roots/images/swf.png',
'mcms-roots/images/tar.png',
'mcms-roots/images/text.png',
'mcms-roots/images/video.png',
'mcms-roots/images/zip.png',
'mcms-roots/js/tinymce/tiny_mce_config.php',
'mcms-roots/js/tinymce/tiny_mce_ext.js',
// 2.8
'mcms-admin/js/users.js',
'mcms-roots/js/swfupload/modules/swfupload.documentready.js',
'mcms-roots/js/swfupload/modules/swfupload.graceful_degradation.js',
'mcms-roots/js/swfupload/swfupload_f9.swf',
'mcms-roots/js/tinymce/modules/autosave',
'mcms-roots/js/tinymce/modules/paste/css',
'mcms-roots/js/tinymce/utils/mclayer.js',
'mcms-roots/js/tinymce/mandarincms.css',
// 2.8.5
'mcms-admin/import/btt.php',
'mcms-admin/import/jkw.php',
// 2.9
'mcms-admin/js/page.dev.js',
'mcms-admin/js/page.js',
'mcms-admin/js/set-post-thumbnail-handler.dev.js',
'mcms-admin/js/set-post-thumbnail-handler.js',
'mcms-admin/js/slug.dev.js',
'mcms-admin/js/slug.js',
'mcms-roots/gettext.php',
'mcms-roots/js/tinymce/modules/mandarincms/js',
'mcms-roots/streams.php',
// MU
'README.txt',
'htaccess.dist',
'index-install.php',
'mcms-admin/css/mu-rtl.css',
'mcms-admin/css/mu.css',
'mcms-admin/images/site-admin.png',
'mcms-admin/includes/mu.php',
'mcms-admin/mcmsmu-admin.php',
'mcms-admin/mcmsmu-blogs.php',
'mcms-admin/mcmsmu-edit.php',
'mcms-admin/mcmsmu-options.php',
'mcms-admin/mcmsmu-myskins.php',
'mcms-admin/mcmsmu-upgrade-site.php',
'mcms-admin/mcmsmu-users.php',
'mcms-roots/images/mandarincms-mu.png',
'mcms-roots/mcmsmu-default-filters.php',
'mcms-roots/mcmsmu-functions.php',
'mcmsmu-settings.php',
// 3.0
'mcms-admin/categories.php',
'mcms-admin/edit-category-form.php',
'mcms-admin/edit-page-form.php',
'mcms-admin/edit-pages.php',
'mcms-admin/images/admin-header-footer.png',
'mcms-admin/images/browse-happy.gif',
'mcms-admin/images/ico-add.png',
'mcms-admin/images/ico-close.png',
'mcms-admin/images/ico-edit.png',
'mcms-admin/images/ico-viemcmsage.png',
'mcms-admin/images/fav-top.png',
'mcms-admin/images/screen-options-left.gif',
'mcms-admin/images/mcms-logo-vs.gif',
'mcms-admin/images/mcms-logo.gif',
'mcms-admin/import',
'mcms-admin/js/mcms-gears.dev.js',
'mcms-admin/js/mcms-gears.js',
'mcms-admin/options-misc.php',
'mcms-admin/page-new.php',
'mcms-admin/page.php',
'mcms-admin/rtl.css',
'mcms-admin/rtl.dev.css',
'mcms-admin/update-links.php',
'mcms-admin/mcms-admin.css',
'mcms-admin/mcms-admin.dev.css',
'mcms-roots/js/codepress',
'mcms-roots/js/codepress/engines/khtml.js',
'mcms-roots/js/codepress/engines/older.js',
'mcms-roots/js/jquery/autocomplete.dev.js',
'mcms-roots/js/jquery/autocomplete.js',
'mcms-roots/js/jquery/interface.js',
'mcms-roots/js/scriptaculous/prototype.js',
'mcms-roots/js/tinymce/mcms-tinymce.js',
// 3.1
'mcms-admin/edit-attachment-rows.php',
'mcms-admin/edit-link-categories.php',
'mcms-admin/edit-link-category-form.php',
'mcms-admin/edit-post-rows.php',
'mcms-admin/images/button-grad-active-vs.png',
'mcms-admin/images/button-grad-vs.png',
'mcms-admin/images/fav-arrow-vs-rtl.gif',
'mcms-admin/images/fav-arrow-vs.gif',
'mcms-admin/images/fav-top-vs.gif',
'mcms-admin/images/list-vs.png',
'mcms-admin/images/screen-options-right-up.gif',
'mcms-admin/images/screen-options-right.gif',
'mcms-admin/images/visit-site-button-grad-vs.gif',
'mcms-admin/images/visit-site-button-grad.gif',
'mcms-admin/link-category.php',
'mcms-admin/sidebar.php',
'mcms-roots/classes.php',
'mcms-roots/js/tinymce/blank.htm',
'mcms-roots/js/tinymce/modules/media/css/content.css',
'mcms-roots/js/tinymce/modules/media/img',
'mcms-roots/js/tinymce/modules/safari',
// 3.2
'mcms-admin/images/logo-login.gif',
'mcms-admin/images/star.gif',
'mcms-admin/js/list-table.dev.js',
'mcms-admin/js/list-table.js',
'mcms-roots/default-embeds.php',
'mcms-roots/js/tinymce/modules/mandarincms/img/help.gif',
'mcms-roots/js/tinymce/modules/mandarincms/img/more.gif',
'mcms-roots/js/tinymce/modules/mandarincms/img/toolbars.gif',
'mcms-roots/js/tinymce/myskins/advanced/img/fm.gif',
'mcms-roots/js/tinymce/myskins/advanced/img/sflogo.png',
// 3.3
'mcms-admin/css/colors-classic-rtl.css',
'mcms-admin/css/colors-classic-rtl.dev.css',
'mcms-admin/css/colors-fresh-rtl.css',
'mcms-admin/css/colors-fresh-rtl.dev.css',
'mcms-admin/css/dashboard-rtl.dev.css',
'mcms-admin/css/dashboard.dev.css',
'mcms-admin/css/global-rtl.css',
'mcms-admin/css/global-rtl.dev.css',
'mcms-admin/css/global.css',
'mcms-admin/css/global.dev.css',
'mcms-admin/css/install-rtl.dev.css',
'mcms-admin/css/login-rtl.dev.css',
'mcms-admin/css/login.dev.css',
'mcms-admin/css/ms.css',
'mcms-admin/css/ms.dev.css',
'mcms-admin/css/nav-menu-rtl.css',
'mcms-admin/css/nav-menu-rtl.dev.css',
'mcms-admin/css/nav-menu.css',
'mcms-admin/css/nav-menu.dev.css',
'mcms-admin/css/module-install-rtl.css',
'mcms-admin/css/module-install-rtl.dev.css',
'mcms-admin/css/module-install.css',
'mcms-admin/css/module-install.dev.css',
'mcms-admin/css/press-this-rtl.dev.css',
'mcms-admin/css/press-this.dev.css',
'mcms-admin/css/myskin-editor-rtl.css',
'mcms-admin/css/myskin-editor-rtl.dev.css',
'mcms-admin/css/myskin-editor.css',
'mcms-admin/css/myskin-editor.dev.css',
'mcms-admin/css/myskin-install-rtl.css',
'mcms-admin/css/myskin-install-rtl.dev.css',
'mcms-admin/css/myskin-install.css',
'mcms-admin/css/myskin-install.dev.css',
'mcms-admin/css/widgets-rtl.dev.css',
'mcms-admin/css/widgets.dev.css',
'mcms-admin/includes/internal-linking.php',
'mcms-roots/images/admin-bar-sprite-rtl.png',
'mcms-roots/js/jquery/ui.button.js',
'mcms-roots/js/jquery/ui.core.js',
'mcms-roots/js/jquery/ui.dialog.js',
'mcms-roots/js/jquery/ui.draggable.js',
'mcms-roots/js/jquery/ui.droppable.js',
'mcms-roots/js/jquery/ui.mouse.js',
'mcms-roots/js/jquery/ui.position.js',
'mcms-roots/js/jquery/ui.resizable.js',
'mcms-roots/js/jquery/ui.selectable.js',
'mcms-roots/js/jquery/ui.sortable.js',
'mcms-roots/js/jquery/ui.tabs.js',
'mcms-roots/js/jquery/ui.widget.js',
'mcms-roots/js/l10n.dev.js',
'mcms-roots/js/l10n.js',
'mcms-roots/js/tinymce/modules/mcmslink/css',
'mcms-roots/js/tinymce/modules/mcmslink/img',
'mcms-roots/js/tinymce/modules/mcmslink/js',
'mcms-roots/js/tinymce/myskins/advanced/img/mcmsicons.png',
'mcms-roots/js/tinymce/myskins/advanced/skins/mcms_myskin/img/butt2.png',
'mcms-roots/js/tinymce/myskins/advanced/skins/mcms_myskin/img/button_bg.png',
'mcms-roots/js/tinymce/myskins/advanced/skins/mcms_myskin/img/down_arrow.gif',
'mcms-roots/js/tinymce/myskins/advanced/skins/mcms_myskin/img/fade-butt.png',
'mcms-roots/js/tinymce/myskins/advanced/skins/mcms_myskin/img/separator.gif',
// Don't delete, yet: 'mcms-rss.php',
// Don't delete, yet: 'mcms-rdf.php',
// Don't delete, yet: 'mcms-rss2.php',
// Don't delete, yet: 'mcms-commentsrss2.php',
// Don't delete, yet: 'mcms-atom.php',
// Don't delete, yet: 'mcms-feed.php',
// 3.4
'mcms-admin/images/gray-star.png',
'mcms-admin/images/logo-login.png',
'mcms-admin/images/star.png',
'mcms-admin/index-extra.php',
'mcms-admin/network/index-extra.php',
'mcms-admin/user/index-extra.php',
'mcms-admin/images/screenshots/admin-flyouts.png',
'mcms-admin/images/screenshots/coediting.png',
'mcms-admin/images/screenshots/drag-and-drop.png',
'mcms-admin/images/screenshots/help-screen.png',
'mcms-admin/images/screenshots/media-icon.png',
'mcms-admin/images/screenshots/new-feature-pointer.png',
'mcms-admin/images/screenshots/welcome-screen.png',
'mcms-roots/css/editor-buttons.css',
'mcms-roots/css/editor-buttons.dev.css',
'mcms-roots/js/tinymce/modules/paste/blank.htm',
'mcms-roots/js/tinymce/modules/mandarincms/css',
'mcms-roots/js/tinymce/modules/mandarincms/editor_module.dev.js',
'mcms-roots/js/tinymce/modules/mandarincms/img/embedded.png',
'mcms-roots/js/tinymce/modules/mandarincms/img/more_bug.gif',
'mcms-roots/js/tinymce/modules/mandarincms/img/page_bug.gif',
'mcms-roots/js/tinymce/modules/mcmsdialogs/editor_module.dev.js',
'mcms-roots/js/tinymce/modules/mcmseditimage/css/editimage-rtl.css',
'mcms-roots/js/tinymce/modules/mcmseditimage/editor_module.dev.js',
'mcms-roots/js/tinymce/modules/mcmsfullscreen/editor_module.dev.js',
'mcms-roots/js/tinymce/modules/mcmsgallery/editor_module.dev.js',
'mcms-roots/js/tinymce/modules/mcmsgallery/img/gallery.png',
'mcms-roots/js/tinymce/modules/mcmslink/editor_module.dev.js',
// Don't delete, yet: 'mcms-pass.php',
// Don't delete, yet: 'mcms-register.php',
// 3.5
'mcms-admin/gears-manifest.php',
'mcms-admin/includes/manifest.php',
'mcms-admin/images/archive-link.png',
'mcms-admin/images/blue-grad.png',
'mcms-admin/images/button-grad-active.png',
'mcms-admin/images/button-grad.png',
'mcms-admin/images/ed-bg-vs.gif',
'mcms-admin/images/ed-bg.gif',
'mcms-admin/images/fade-butt.png',
'mcms-admin/images/fav-arrow-rtl.gif',
'mcms-admin/images/fav-arrow.gif',
'mcms-admin/images/fav-vs.png',
'mcms-admin/images/fav.png',
'mcms-admin/images/gray-grad.png',
'mcms-admin/images/loading-publish.gif',
'mcms-admin/images/logo-ghost.png',
'mcms-admin/images/logo.gif',
'mcms-admin/images/menu-arrow-frame-rtl.png',
'mcms-admin/images/menu-arrow-frame.png',
'mcms-admin/images/menu-arrows.gif',
'mcms-admin/images/menu-bits-rtl-vs.gif',
'mcms-admin/images/menu-bits-rtl.gif',
'mcms-admin/images/menu-bits-vs.gif',
'mcms-admin/images/menu-bits.gif',
'mcms-admin/images/menu-dark-rtl-vs.gif',
'mcms-admin/images/menu-dark-rtl.gif',
'mcms-admin/images/menu-dark-vs.gif',
'mcms-admin/images/menu-dark.gif',
'mcms-admin/images/required.gif',
'mcms-admin/images/screen-options-toggle-vs.gif',
'mcms-admin/images/screen-options-toggle.gif',
'mcms-admin/images/toggle-arrow-rtl.gif',
'mcms-admin/images/toggle-arrow.gif',
'mcms-admin/images/upload-classic.png',
'mcms-admin/images/upload-fresh.png',
'mcms-admin/images/white-grad-active.png',
'mcms-admin/images/white-grad.png',
'mcms-admin/images/widgets-arrow-vs.gif',
'mcms-admin/images/widgets-arrow.gif',
'mcms-admin/images/mcmsspin_dark.gif',
'mcms-roots/images/upload.png',
'mcms-roots/js/prototype.js',
'mcms-roots/js/scriptaculous',
'mcms-admin/css/mcms-admin-rtl.dev.css',
'mcms-admin/css/mcms-admin.dev.css',
'mcms-admin/css/media-rtl.dev.css',
'mcms-admin/css/media.dev.css',
'mcms-admin/css/colors-classic.dev.css',
'mcms-admin/css/customize-controls-rtl.dev.css',
'mcms-admin/css/customize-controls.dev.css',
'mcms-admin/css/ie-rtl.dev.css',
'mcms-admin/css/ie.dev.css',
'mcms-admin/css/install.dev.css',
'mcms-admin/css/colors-fresh.dev.css',
'mcms-roots/js/customize-base.dev.js',
'mcms-roots/js/json2.dev.js',
'mcms-roots/js/comment-reply.dev.js',
'mcms-roots/js/customize-preview.dev.js',
'mcms-roots/js/mcmslink.dev.js',
'mcms-roots/js/tw-sack.dev.js',
'mcms-roots/js/mcms-list-revisions.dev.js',
'mcms-roots/js/autosave.dev.js',
'mcms-roots/js/admin-bar.dev.js',
'mcms-roots/js/quicktags.dev.js',
'mcms-roots/js/mcms-ajax-response.dev.js',
'mcms-roots/js/mcms-pointer.dev.js',
'mcms-roots/js/hoverIntent.dev.js',
'mcms-roots/js/colorpicker.dev.js',
'mcms-roots/js/mcms-lists.dev.js',
'mcms-roots/js/customize-loader.dev.js',
'mcms-roots/js/jquery/jquery.table-hotkeys.dev.js',
'mcms-roots/js/jquery/jquery.color.dev.js',
'mcms-roots/js/jquery/jquery.color.js',
'mcms-roots/js/jquery/jquery.hotkeys.dev.js',
'mcms-roots/js/jquery/jquery.form.dev.js',
'mcms-roots/js/jquery/suggest.dev.js',
'mcms-admin/js/xfn.dev.js',
'mcms-admin/js/set-post-thumbnail.dev.js',
'mcms-admin/js/comment.dev.js',
'mcms-admin/js/myskin.dev.js',
'mcms-admin/js/cat.dev.js',
'mcms-admin/js/password-strength-meter.dev.js',
'mcms-admin/js/user-profile.dev.js',
'mcms-admin/js/myskin-preview.dev.js',
'mcms-admin/js/post.dev.js',
'mcms-admin/js/media-upload.dev.js',
'mcms-admin/js/word-count.dev.js',
'mcms-admin/js/module-install.dev.js',
'mcms-admin/js/edit-comments.dev.js',
'mcms-admin/js/media-gallery.dev.js',
'mcms-admin/js/custom-fields.dev.js',
'mcms-admin/js/custom-background.dev.js',
'mcms-admin/js/common.dev.js',
'mcms-admin/js/inline-edit-tax.dev.js',
'mcms-admin/js/gallery.dev.js',
'mcms-admin/js/utils.dev.js',
'mcms-admin/js/widgets.dev.js',
'mcms-admin/js/mcms-fullscreen.dev.js',
'mcms-admin/js/nav-menu.dev.js',
'mcms-admin/js/dashboard.dev.js',
'mcms-admin/js/link.dev.js',
'mcms-admin/js/user-suggest.dev.js',
'mcms-admin/js/postbox.dev.js',
'mcms-admin/js/tags.dev.js',
'mcms-admin/js/image-edit.dev.js',
'mcms-admin/js/media.dev.js',
'mcms-admin/js/customize-controls.dev.js',
'mcms-admin/js/inline-edit-post.dev.js',
'mcms-admin/js/categories.dev.js',
'mcms-admin/js/editor.dev.js',
'mcms-roots/js/tinymce/modules/mcmseditimage/js/editimage.dev.js',
'mcms-roots/js/tinymce/modules/mcmsdialogs/js/popup.dev.js',
'mcms-roots/js/tinymce/modules/mcmsdialogs/js/mcmsdialog.dev.js',
'mcms-roots/js/plupload/handlers.dev.js',
'mcms-roots/js/plupload/mcms-plupload.dev.js',
'mcms-roots/js/swfupload/handlers.dev.js',
'mcms-roots/js/jcrop/jquery.Jcrop.dev.js',
'mcms-roots/js/jcrop/jquery.Jcrop.js',
'mcms-roots/js/jcrop/jquery.Jcrop.css',
'mcms-roots/js/imgareaselect/jquery.imgareaselect.dev.js',
'mcms-roots/css/mcms-pointer.dev.css',
'mcms-roots/css/editor.dev.css',
'mcms-roots/css/jquery-ui-dialog.dev.css',
'mcms-roots/css/admin-bar-rtl.dev.css',
'mcms-roots/css/admin-bar.dev.css',
'mcms-roots/js/jquery/ui/jquery.effects.clip.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.scale.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.blind.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.core.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.shake.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.fade.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.explode.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.slide.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.drop.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.highlight.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.bounce.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.pulsate.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.transfer.min.js',
'mcms-roots/js/jquery/ui/jquery.effects.fold.min.js',
'mcms-admin/images/screenshots/captions-1.png',
'mcms-admin/images/screenshots/captions-2.png',
'mcms-admin/images/screenshots/flex-header-1.png',
'mcms-admin/images/screenshots/flex-header-2.png',
'mcms-admin/images/screenshots/flex-header-3.png',
'mcms-admin/images/screenshots/flex-header-media-library.png',
'mcms-admin/images/screenshots/myskin-customizer.png',
'mcms-admin/images/screenshots/twitter-embed-1.png',
'mcms-admin/images/screenshots/twitter-embed-2.png',
'mcms-admin/js/utils.js',
'mcms-admin/options-privacy.php',
'mcms-app.php',
'mcms-roots/class-mcms-atom-server.php',
'mcms-roots/js/tinymce/myskins/advanced/skins/mcms_myskin/ui.css',
// 3.5.2
'mcms-roots/js/swfupload/swfupload-all.js',
// 3.6
'mcms-admin/js/revisions-js.php',
'mcms-admin/images/screenshots',
'mcms-admin/js/categories.js',
'mcms-admin/js/categories.min.js',
'mcms-admin/js/custom-fields.js',
'mcms-admin/js/custom-fields.min.js',
// 3.7
'mcms-admin/js/cat.js',
'mcms-admin/js/cat.min.js',
'mcms-roots/js/tinymce/modules/mcmseditimage/js/editimage.min.js',
// 3.8
'mcms-roots/js/tinymce/myskins/advanced/skins/mcms_myskin/img/page_bug.gif',
'mcms-roots/js/tinymce/myskins/advanced/skins/mcms_myskin/img/more_bug.gif',
'mcms-roots/js/thickbox/tb-close-2x.png',
'mcms-roots/js/thickbox/tb-close.png',
'mcms-roots/images/mcmsmini-blue-2x.png',
'mcms-roots/images/mcmsmini-blue.png',
'mcms-admin/css/colors-fresh.css',
'mcms-admin/css/colors-classic.css',
'mcms-admin/css/colors-fresh.min.css',
'mcms-admin/css/colors-classic.min.css',
'mcms-admin/js/about.min.js',
'mcms-admin/js/about.js',
'mcms-admin/images/arrows-dark-vs-2x.png',
'mcms-admin/images/mcms-logo-vs.png',
'mcms-admin/images/arrows-dark-vs.png',
'mcms-admin/images/mcms-logo.png',
'mcms-admin/images/arrows-pr.png',
'mcms-admin/images/arrows-dark.png',
'mcms-admin/images/press-this.png',
'mcms-admin/images/press-this-2x.png',
'mcms-admin/images/arrows-vs-2x.png',
'mcms-admin/images/welcome-icons.png',
'mcms-admin/images/mcms-logo-2x.png',
'mcms-admin/images/stars-rtl-2x.png',
'mcms-admin/images/arrows-dark-2x.png',
'mcms-admin/images/arrows-pr-2x.png',
'mcms-admin/images/menu-shadow-rtl.png',
'mcms-admin/images/arrows-vs.png',
'mcms-admin/images/about-search-2x.png',
'mcms-admin/images/bubble_bg-rtl-2x.gif',
'mcms-admin/images/mcms-badge-2x.png',
'mcms-admin/images/mandarincms-logo-2x.png',
'mcms-admin/images/bubble_bg-rtl.gif',
'mcms-admin/images/mcms-badge.png',
'mcms-admin/images/menu-shadow.png',
'mcms-admin/images/about-globe-2x.png',
'mcms-admin/images/welcome-icons-2x.png',
'mcms-admin/images/stars-rtl.png',
'mcms-admin/images/mcms-logo-vs-2x.png',
'mcms-admin/images/about-updates-2x.png',
// 3.9
'mcms-admin/css/colors.css',
'mcms-admin/css/colors.min.css',
'mcms-admin/css/colors-rtl.css',
'mcms-admin/css/colors-rtl.min.css',
// Following files added back in 4.5 see #36083
// 'mcms-admin/css/media-rtl.min.css',
// 'mcms-admin/css/media.min.css',
// 'mcms-admin/css/farbtastic-rtl.min.css',
'mcms-admin/images/lock-2x.png',
'mcms-admin/images/lock.png',
'mcms-admin/js/myskin-preview.js',
'mcms-admin/js/myskin-install.min.js',
'mcms-admin/js/myskin-install.js',
'mcms-admin/js/myskin-preview.min.js',
'mcms-roots/js/plupload/plupload.html4.js',
'mcms-roots/js/plupload/plupload.html5.js',
'mcms-roots/js/plupload/changelog.txt',
'mcms-roots/js/plupload/plupload.silverlight.js',
'mcms-roots/js/plupload/plupload.flash.js',
// Added back in 4.9 [41328], see #41755
// 'mcms-roots/js/plupload/plupload.js',
'mcms-roots/js/tinymce/modules/spellchecker',
'mcms-roots/js/tinymce/modules/inlinepopups',
'mcms-roots/js/tinymce/modules/media/js',
'mcms-roots/js/tinymce/modules/media/css',
'mcms-roots/js/tinymce/modules/mandarincms/img',
'mcms-roots/js/tinymce/modules/mcmsdialogs/js',
'mcms-roots/js/tinymce/modules/mcmseditimage/img',
'mcms-roots/js/tinymce/modules/mcmseditimage/js',
'mcms-roots/js/tinymce/modules/mcmseditimage/css',
'mcms-roots/js/tinymce/modules/mcmsgallery/img',
'mcms-roots/js/tinymce/modules/mcmsfullscreen/css',
'mcms-roots/js/tinymce/modules/paste/js',
'mcms-roots/js/tinymce/myskins/advanced',
'mcms-roots/js/tinymce/tiny_mce.js',
'mcms-roots/js/tinymce/mark_loaded_src.js',
'mcms-roots/js/tinymce/mcms-tinymce-schema.js',
'mcms-roots/js/tinymce/modules/media/editor_module.js',
'mcms-roots/js/tinymce/modules/media/editor_module_src.js',
'mcms-roots/js/tinymce/modules/media/media.htm',
'mcms-roots/js/tinymce/modules/mcmsview/editor_module_src.js',
'mcms-roots/js/tinymce/modules/mcmsview/editor_module.js',
'mcms-roots/js/tinymce/modules/directionality/editor_module.js',
'mcms-roots/js/tinymce/modules/directionality/editor_module_src.js',
'mcms-roots/js/tinymce/modules/mandarincms/editor_module.js',
'mcms-roots/js/tinymce/modules/mandarincms/editor_module_src.js',
'mcms-roots/js/tinymce/modules/mcmsdialogs/editor_module_src.js',
'mcms-roots/js/tinymce/modules/mcmsdialogs/editor_module.js',
'mcms-roots/js/tinymce/modules/mcmseditimage/editimage.html',
'mcms-roots/js/tinymce/modules/mcmseditimage/editor_module.js',
'mcms-roots/js/tinymce/modules/mcmseditimage/editor_module_src.js',
'mcms-roots/js/tinymce/modules/fullscreen/editor_module_src.js',
'mcms-roots/js/tinymce/modules/fullscreen/fullscreen.htm',
'mcms-roots/js/tinymce/modules/fullscreen/editor_module.js',
'mcms-roots/js/tinymce/modules/mcmslink/editor_module_src.js',
'mcms-roots/js/tinymce/modules/mcmslink/editor_module.js',
'mcms-roots/js/tinymce/modules/mcmsgallery/editor_module_src.js',
'mcms-roots/js/tinymce/modules/mcmsgallery/editor_module.js',
'mcms-roots/js/tinymce/modules/tabfocus/editor_module.js',
'mcms-roots/js/tinymce/modules/tabfocus/editor_module_src.js',
'mcms-roots/js/tinymce/modules/mcmsfullscreen/editor_module.js',
'mcms-roots/js/tinymce/modules/mcmsfullscreen/editor_module_src.js',
'mcms-roots/js/tinymce/modules/paste/editor_module.js',
'mcms-roots/js/tinymce/modules/paste/pasteword.htm',
'mcms-roots/js/tinymce/modules/paste/editor_module_src.js',
'mcms-roots/js/tinymce/modules/paste/pastetext.htm',
'mcms-roots/js/tinymce/langs/mcms-langs.php',
// 4.1
'mcms-roots/js/jquery/ui/jquery.ui.accordion.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.autocomplete.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.button.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.core.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.datepicker.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.dialog.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.draggable.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.droppable.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-blind.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-bounce.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-clip.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-drop.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-explode.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-fade.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-fold.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-highlight.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-pulsate.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-scale.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-shake.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-slide.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect-transfer.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.effect.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.menu.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.mouse.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.position.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.progressbar.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.resizable.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.selectable.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.slider.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.sortable.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.spinner.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.tabs.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.tooltip.min.js',
'mcms-roots/js/jquery/ui/jquery.ui.widget.min.js',
'mcms-roots/js/tinymce/skins/mandarincms/images/dashicon-no-alt.png',
// 4.3
'mcms-admin/js/mcms-fullscreen.js',
'mcms-admin/js/mcms-fullscreen.min.js',
'mcms-roots/js/tinymce/mcms-mce-help.php',
'mcms-roots/js/tinymce/modules/mcmsfullscreen',
// 4.5
'mcms-roots/myskin-compat/comments-popup.php',
// 4.6
'mcms-admin/includes/class-mcms-automatic-upgrader.php', // Wrong file name, see #37628.
// 4.8
'mcms-roots/js/tinymce/modules/mcmsembed',
'mcms-roots/js/tinymce/modules/media/moxieplayer.swf',
'mcms-roots/js/tinymce/skins/lightgray/fonts/readme.md',
'mcms-roots/js/tinymce/skins/lightgray/fonts/tinymce-small.json',
'mcms-roots/js/tinymce/skins/lightgray/fonts/tinymce.json',
'mcms-roots/js/tinymce/skins/lightgray/skin.ie7.min.css',
// 4.9
'mcms-admin/css/press-this-editor-rtl.css',
'mcms-admin/css/press-this-editor-rtl.min.css',
'mcms-admin/css/press-this-editor.css',
'mcms-admin/css/press-this-editor.min.css',
'mcms-admin/css/press-this-rtl.css',
'mcms-admin/css/press-this-rtl.min.css',
'mcms-admin/css/press-this.css',
'mcms-admin/css/press-this.min.css',
'mcms-admin/includes/class-mcms-press-this.php',
'mcms-admin/js/bookmarklet.js',
'mcms-admin/js/bookmarklet.min.js',
'mcms-admin/js/press-this.js',
'mcms-admin/js/press-this.min.js',
'mcms-roots/js/mediaelement/background.png',
'mcms-roots/js/mediaelement/bigplay.png',
'mcms-roots/js/mediaelement/bigplay.svg',
'mcms-roots/js/mediaelement/controls.png',
'mcms-roots/js/mediaelement/controls.svg',
'mcms-roots/js/mediaelement/flashmediaelement.swf',
'mcms-roots/js/mediaelement/froogaloop.min.js',
'mcms-roots/js/mediaelement/jumpforward.png',
'mcms-roots/js/mediaelement/loading.gif',
'mcms-roots/js/mediaelement/silverlightmediaelement.xap',
'mcms-roots/js/mediaelement/skipback.png',
'mcms-roots/js/plupload/plupload.flash.swf',
'mcms-roots/js/plupload/plupload.full.min.js',
'mcms-roots/js/plupload/plupload.silverlight.xap',
'mcms-roots/js/swfupload/modules',
'mcms-roots/js/swfupload/swfupload.swf',
	// 4.9.2
	'mcms-roots/js/mediaelement/lang',
	'mcms-roots/js/mediaelement/lang/ca.js',
	'mcms-roots/js/mediaelement/lang/cs.js',
	'mcms-roots/js/mediaelement/lang/de.js',
	'mcms-roots/js/mediaelement/lang/es.js',
	'mcms-roots/js/mediaelement/lang/fa.js',
	'mcms-roots/js/mediaelement/lang/fr.js',
	'mcms-roots/js/mediaelement/lang/hr.js',
	'mcms-roots/js/mediaelement/lang/hu.js',
	'mcms-roots/js/mediaelement/lang/it.js',
	'mcms-roots/js/mediaelement/lang/ja.js',
	'mcms-roots/js/mediaelement/lang/ko.js',
	'mcms-roots/js/mediaelement/lang/nl.js',
	'mcms-roots/js/mediaelement/lang/pl.js',
	'mcms-roots/js/mediaelement/lang/pt.js',
	'mcms-roots/js/mediaelement/lang/ro.js',
	'mcms-roots/js/mediaelement/lang/ru.js',
	'mcms-roots/js/mediaelement/lang/sk.js',
	'mcms-roots/js/mediaelement/lang/sv.js',
	'mcms-roots/js/mediaelement/lang/uk.js',
	'mcms-roots/js/mediaelement/lang/zh-cn.js',
	'mcms-roots/js/mediaelement/lang/zh.js',
	'mcms-roots/js/mediaelement/mediaelement-flash-audio-ogg.swf',
	'mcms-roots/js/mediaelement/mediaelement-flash-audio.swf',
	'mcms-roots/js/mediaelement/mediaelement-flash-video-hls.swf',
	'mcms-roots/js/mediaelement/mediaelement-flash-video-mdash.swf',
	'mcms-roots/js/mediaelement/mediaelement-flash-video.swf',
	'mcms-roots/js/mediaelement/renderers/dailymotion.js',
	'mcms-roots/js/mediaelement/renderers/dailymotion.min.js',
	'mcms-roots/js/mediaelement/renderers/facebook.js',
	'mcms-roots/js/mediaelement/renderers/facebook.min.js',
	'mcms-roots/js/mediaelement/renderers/soundcloud.js',
	'mcms-roots/js/mediaelement/renderers/soundcloud.min.js',
	'mcms-roots/js/mediaelement/renderers/twitch.js',
	'mcms-roots/js/mediaelement/renderers/twitch.min.js',
);

/**
 * Stores new files in mcms-plugins to copy
 *
 * The contents of this array indicate any new bundled modules/myskins which
 * should be installed with the MandarinCMS Upgrade. These items will not be
 * re-installed in future upgrades, this behaviour is controlled by the
 * introduced version present here being older than the current installed version.
 *
 * The content of this array should follow the following format:
 * Filename (relative to mcms-plugins) => Introduced version
 * Directories should be noted by suffixing it with a trailing slash (/)
 *
 * @since 3.2.0
 * @since 4.7.0 New myskins were not automatically installed for 4.4-4.6 on
 *              upgrade. New myskins are now installed again. To disable new
 *              myskins from being installed on upgrade, explicitly define
 *              CORE_UPGRADE_SKIP_NEW_BUNDLED as false.
 * @global array $_new_bundled_files
 * @var array
 * @name $_new_bundled_files
 */
global $_new_bundled_files;

$_new_bundled_files = array(
	'myskins/razorleaf/' => '4.7',
);

/**
 * Upgrades the core of MandarinCMS.
 *
 * This will create a .maintenance file at the base of the MandarinCMS directory
 * to ensure that people can not access the web site, when the files are being
 * copied to their locations.
 *
 * The files in the `$_old_files` list will be removed and the new files
 * copied from the zip file after the database is upgraded.
 *
 * The files in the `$_new_bundled_files` list will be added to the installation
 * if the version is greater than or equal to the old version being upgraded.
 *
 * The steps for the upgrader for after the new release is downloaded and
 * unzipped is:
 *   1. Test unzipped location for select files to ensure that unzipped worked.
 *   2. Create the .maintenance file in current MandarinCMS base.
 *   3. Copy new MandarinCMS directory over old MandarinCMS files.
 *   4. Upgrade MandarinCMS to new version.
 *     4.1. Copy all files/folders other than mcms-plugins
 *     4.2. Copy any language files to MCMS_LANG_DIR (which may differ from MCMS_CONTENT_DIR
 *     4.3. Copy any new bundled myskins/modules to their respective locations
 *   5. Delete new MandarinCMS directory path.
 *   6. Delete .maintenance file.
 *   7. Remove old files.
 *   8. Delete 'update_core' option.
 *
 * There are several areas of failure. For instance if PHP times out before step
 * 6, then you will not be able to access any portion of your site. Also, since
 * the upgrade will not continue where it left off, you will not be able to
 * automatically remove old files and remove the 'update_core' option. This
 * isn't that bad.
 *
 * If the copy of the new MandarinCMS over the old fails, then the worse is that
 * the new MandarinCMS directory will remain.
 *
 * If it is assumed that every file will be copied over, including modules and
 * myskins, then if you edit the default myskin, you should rename it, so that
 * your changes remain.
 *
 * @since 2.7.0
 *
 * @global MCMS_Filesystem_Base $mcms_filesystem
 * @global array              $_old_files
 * @global array              $_new_bundled_files
 * @global mcmsdb               $mcmsdb
 * @global string             $mcms_version
 * @global string             $required_php_version
 * @global string             $required_mysql_version
 *
 * @param string $from New release unzipped path.
 * @param string $to   Path to old MandarinCMS installation.
 * @return MCMS_Error|null MCMS_Error on failure, null on success.
 */
function update_core($from, $to) {
	global $mcms_filesystem, $_old_files, $_new_bundled_files, $mcmsdb;

	@set_time_limit( 300 );

	/**
	 * Filters feedback messages displayed during the core update process.
	 *
	 * The filter is first evaluated after the zip file for the latest version
	 * has been downloaded and unzipped. It is evaluated five more times during
	 * the process:
	 *
	 * 1. Before MandarinCMS begins the core upgrade process.
	 * 2. Before Maintenance Mode is enabled.
	 * 3. Before MandarinCMS begins copying over the necessary files.
	 * 4. Before Maintenance Mode is disabled.
	 * 5. Before the database is upgraded.
	 *
	 * @since 2.5.0
	 *
	 * @param string $feedback The core update feedback messages.
	 */
	apply_filters( 'update_feedback', __( 'Verifying the unpacked files&#8230;' ) );

	// Sanity check the unzipped distribution.
	$distro = '';
	$roots = array( '/mandarincms/', '/mandarincms-mu/' );
	foreach ( $roots as $root ) {
		if ( $mcms_filesystem->exists( $from . $root . 'readme.html' ) && $mcms_filesystem->exists( $from . $root . 'mcms-roots/version.php' ) ) {
			$distro = $root;
			break;
		}
	}
	if ( ! $distro ) {
		$mcms_filesystem->delete( $from, true );
		return new MCMS_Error( 'insane_distro', __('The update could not be unpacked') );
	}


	/*
	 * Import $mcms_version, $required_php_version, and $required_mysql_version from the new version.
	 * DO NOT globalise any variables imported from `version-current.php` in this function.
	 *
	 * BC Note: $mcms_filesystem->mcms_content_dir() returned unslashed pre-2.8
	 */
	$versions_file = trailingslashit( $mcms_filesystem->mcms_content_dir() ) . 'upgrade/version-current.php';
	if ( ! $mcms_filesystem->copy( $from . $distro . 'mcms-roots/version.php', $versions_file ) ) {
		$mcms_filesystem->delete( $from, true );
		return new MCMS_Error( 'copy_failed_for_version_file', __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' ), 'mcms-roots/version.php' );
	}

	$mcms_filesystem->chmod( $versions_file, FS_CHMOD_FILE );
	require( MCMS_CONTENT_DIR . '/upgrade/version-current.php' );
	$mcms_filesystem->delete( $versions_file );

	$php_version    = phpversion();
	$mysql_version  = $mcmsdb->db_version();
	$old_mcms_version = $GLOBALS['mcms_version']; // The version of MandarinCMS we're updating from
	$development_build = ( false !== strpos( $old_mcms_version . $mcms_version, '-' )  ); // a dash in the version indicates a Development release
	$php_compat     = version_compare( $php_version, $required_php_version, '>=' );
	if ( file_exists( MCMS_CONTENT_DIR . '/db.php' ) && empty( $mcmsdb->is_mysql ) )
		$mysql_compat = true;
	else
		$mysql_compat = version_compare( $mysql_version, $required_mysql_version, '>=' );

	if ( !$mysql_compat || !$php_compat )
		$mcms_filesystem->delete($from, true);

	if ( !$mysql_compat && !$php_compat )
		return new MCMS_Error( 'php_mysql_not_compatible', sprintf( __('The update cannot be installed because MandarinCMS %1$s requires PHP version %2$s or higher and MySQL version %3$s or higher. You are running PHP version %4$s and MySQL version %5$s.'), $mcms_version, $required_php_version, $required_mysql_version, $php_version, $mysql_version ) );
	elseif ( !$php_compat )
		return new MCMS_Error( 'php_not_compatible', sprintf( __('The update cannot be installed because MandarinCMS %1$s requires PHP version %2$s or higher. You are running version %3$s.'), $mcms_version, $required_php_version, $php_version ) );
	elseif ( !$mysql_compat )
		return new MCMS_Error( 'mysql_not_compatible', sprintf( __('The update cannot be installed because MandarinCMS %1$s requires MySQL version %2$s or higher. You are running version %3$s.'), $mcms_version, $required_mysql_version, $mysql_version ) );

	/** This filter is documented in mcms-admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Preparing to install the latest version&#8230;' ) );

	// Don't copy mcms-plugins, we'll deal with that below
	// We also copy version.php last so failed updates report their old version
	$skip = array( 'mcms-plugins', 'mcms-roots/version.php' );
	$check_is_writable = array();

	// Check to see which files don't really need updating - only available for 3.7 and higher
	if ( function_exists( 'get_core_checksums' ) ) {
		// Find the local version of the working directory
		$working_dir_local = MCMS_CONTENT_DIR . '/upgrade/' . basename( $from ) . $distro;

		$checksums = get_core_checksums( $mcms_version, isset( $mcms_local_package ) ? $mcms_local_package : 'en_US' );
		if ( is_array( $checksums ) && isset( $checksums[ $mcms_version ] ) )
			$checksums = $checksums[ $mcms_version ]; // Compat code for 3.7-beta2
		if ( is_array( $checksums ) ) {
			foreach ( $checksums as $file => $checksum ) {
				if ( 'mcms-plugins' == substr( $file, 0, 10 ) )
					continue;
				if ( ! file_exists( BASED_TREE_URI . $file ) )
					continue;
				if ( ! file_exists( $working_dir_local . $file ) )
					continue;
				if ( '.' === dirname( $file ) && in_array( pathinfo( $file, PATHINFO_EXTENSION ), array( 'html', 'txt' ) ) )
					continue;
				if ( md5_file( BASED_TREE_URI . $file ) === $checksum )
					$skip[] = $file;
				else
					$check_is_writable[ $file ] = BASED_TREE_URI . $file;
			}
		}
	}

	// If we're using the direct method, we can predict write failures that are due to permissions.
	if ( $check_is_writable && 'direct' === $mcms_filesystem->method ) {
		$files_writable = array_filter( $check_is_writable, array( $mcms_filesystem, 'is_writable' ) );
		if ( $files_writable !== $check_is_writable ) {
			$files_not_writable = array_diff_key( $check_is_writable, $files_writable );
			foreach ( $files_not_writable as $relative_file_not_writable => $file_not_writable ) {
				// If the writable check failed, chmod file to 0644 and try again, same as copy_dir().
				$mcms_filesystem->chmod( $file_not_writable, FS_CHMOD_FILE );
				if ( $mcms_filesystem->is_writable( $file_not_writable ) )
					unset( $files_not_writable[ $relative_file_not_writable ] );
			}

			// Store package-relative paths (the key) of non-writable files in the MCMS_Error object.
			$error_data = version_compare( $old_mcms_version, '3.7-beta2', '>' ) ? array_keys( $files_not_writable ) : '';

			if ( $files_not_writable )
				return new MCMS_Error( 'files_not_writable', __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' ), implode( ', ', $error_data ) );
		}
	}

	/** This filter is documented in mcms-admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Enabling Maintenance mode&#8230;' ) );
	// Create maintenance file to signal that we are upgrading
	$maintenance_string = '<?php $upgrading = ' . time() . '; ?>';
	$maintenance_file = $to . '.maintenance';
	$mcms_filesystem->delete($maintenance_file);
	$mcms_filesystem->put_contents($maintenance_file, $maintenance_string, FS_CHMOD_FILE);

	/** This filter is documented in mcms-admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Copying the required files&#8230;' ) );
	// Copy new versions of MCMS files into place.
	$result = _copy_dir( $from . $distro, $to, $skip );
	if ( is_mcms_error( $result ) )
		$result = new MCMS_Error( $result->get_error_code(), $result->get_error_message(), substr( $result->get_error_data(), strlen( $to ) ) );

	// Since we know the core files have copied over, we can now copy the version file
	if ( ! is_mcms_error( $result ) ) {
		if ( ! $mcms_filesystem->copy( $from . $distro . 'mcms-roots/version.php', $to . 'mcms-roots/version.php', true /* overwrite */ ) ) {
			$mcms_filesystem->delete( $from, true );
			$result = new MCMS_Error( 'copy_failed_for_version_file', __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' ), 'mcms-roots/version.php' );
		}
		$mcms_filesystem->chmod( $to . 'mcms-roots/version.php', FS_CHMOD_FILE );
	}

	// Check to make sure everything copied correctly, ignoring the contents of mcms-plugins
	$skip = array( 'mcms-plugins' );
	$failed = array();
	if ( isset( $checksums ) && is_array( $checksums ) ) {
		foreach ( $checksums as $file => $checksum ) {
			if ( 'mcms-plugins' == substr( $file, 0, 10 ) )
				continue;
			if ( ! file_exists( $working_dir_local . $file ) )
				continue;
			if ( '.' === dirname( $file ) && in_array( pathinfo( $file, PATHINFO_EXTENSION ), array( 'html', 'txt' ) ) ) {
				$skip[] = $file;
				continue;
			}
			if ( file_exists( BASED_TREE_URI . $file ) && md5_file( BASED_TREE_URI . $file ) == $checksum )
				$skip[] = $file;
			else
				$failed[] = $file;
		}
	}

	// Some files didn't copy properly
	if ( ! empty( $failed ) ) {
		$total_size = 0;
		foreach ( $failed as $file ) {
			if ( file_exists( $working_dir_local . $file ) )
				$total_size += filesize( $working_dir_local . $file );
		}

		// If we don't have enough free space, it isn't worth trying again.
		// Unlikely to be hit due to the check in unzip_file().
		$available_space = @disk_free_space( BASED_TREE_URI );
		if ( $available_space && $total_size >= $available_space ) {
			$result = new MCMS_Error( 'disk_full', __( 'There is not enough free disk space to complete the update.' ) );
		} else {
			$result = _copy_dir( $from . $distro, $to, $skip );
			if ( is_mcms_error( $result ) )
				$result = new MCMS_Error( $result->get_error_code() . '_retry', $result->get_error_message(), substr( $result->get_error_data(), strlen( $to ) ) );
		}
	}

	// Custom Content Directory needs updating now.
	// Copy Languages
	if ( !is_mcms_error($result) && $mcms_filesystem->is_dir($from . $distro . 'mcms-plugins/languages') ) {
		if ( MCMS_LANG_DIR != BASED_TREE_URI . MCMSINC . '/languages' || @is_dir(MCMS_LANG_DIR) )
			$lang_dir = MCMS_LANG_DIR;
		else
			$lang_dir = MCMS_CONTENT_DIR . '/languages';

		if ( !@is_dir($lang_dir) && 0 === strpos($lang_dir, BASED_TREE_URI) ) { // Check the language directory exists first
			$mcms_filesystem->mkdir($to . str_replace(BASED_TREE_URI, '', $lang_dir), FS_CHMOD_DIR); // If it's within the BASED_TREE_URI we can handle it here, otherwise they're out of luck.
			clearstatcache(); // for FTP, Need to clear the stat cache
		}

		if ( @is_dir($lang_dir) ) {
			$mcms_lang_dir = $mcms_filesystem->find_folder($lang_dir);
			if ( $mcms_lang_dir ) {
				$result = copy_dir($from . $distro . 'mcms-plugins/languages/', $mcms_lang_dir);
				if ( is_mcms_error( $result ) )
					$result = new MCMS_Error( $result->get_error_code() . '_languages', $result->get_error_message(), substr( $result->get_error_data(), strlen( $mcms_lang_dir ) ) );
			}
		}
	}

	/** This filter is documented in mcms-admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Disabling Maintenance mode&#8230;' ) );
	// Remove maintenance file, we're done with potential site-breaking changes
	$mcms_filesystem->delete( $maintenance_file );

	// 3.5 -> 3.5+ - an empty twentytwelve directory was created upon upgrade to 3.5 for some users, preventing installation of Twenty Twelve.
	if ( '3.5' == $old_mcms_version ) {
		if ( is_dir( MCMS_CONTENT_DIR . '/myskins/twentytwelve' ) && ! file_exists( MCMS_CONTENT_DIR . '/myskins/twentytwelve/style.css' )  ) {
			$mcms_filesystem->delete( $mcms_filesystem->mcms_myskins_dir() . 'twentytwelve/' );
		}
	}

	// Copy New bundled modules & myskins
	// This gives us the ability to install new modules & myskins bundled with future versions of MandarinCMS whilst avoiding the re-install upon upgrade issue.
	// $development_build controls us overwriting bundled myskins and modules when a non-stable release is being updated
	if ( !is_mcms_error($result) && ( ! defined('CORE_UPGRADE_SKIP_NEW_BUNDLED') || ! CORE_UPGRADE_SKIP_NEW_BUNDLED ) ) {
		foreach ( (array) $_new_bundled_files as $file => $introduced_version ) {
			// If a $development_build or if $introduced version is greater than what the site was previously running
			if ( $development_build || version_compare( $introduced_version, $old_mcms_version, '>' ) ) {
				$directory = ('/' == $file[ strlen($file)-1 ]);
				list($type, $filename) = explode('/', $file, 2);

				// Check to see if the bundled items exist before attempting to copy them
				if ( ! $mcms_filesystem->exists( $from . $distro . 'mcms-plugins/' . $file ) )
					continue;

				if ( 'modules' == $type )
					$dest = $mcms_filesystem->mcms_modules_dir();
				elseif ( 'myskins' == $type )
					$dest = trailingslashit($mcms_filesystem->mcms_myskins_dir()); // Back-compat, ::mcms_myskins_dir() did not return trailingslash'd pre-3.2
				else
					continue;

				if ( ! $directory ) {
					if ( ! $development_build && $mcms_filesystem->exists( $dest . $filename ) )
						continue;

					if ( ! $mcms_filesystem->copy($from . $distro . 'mcms-plugins/' . $file, $dest . $filename, FS_CHMOD_FILE) )
						$result = new MCMS_Error( "copy_failed_for_new_bundled_$type", __( 'Could not copy file.' ), $dest . $filename );
				} else {
					if ( ! $development_build && $mcms_filesystem->is_dir( $dest . $filename ) )
						continue;

					$mcms_filesystem->mkdir($dest . $filename, FS_CHMOD_DIR);
					$_result = copy_dir( $from . $distro . 'mcms-plugins/' . $file, $dest . $filename);

					// If a error occurs partway through this final step, keep the error flowing through, but keep process going.
					if ( is_mcms_error( $_result ) ) {
						if ( ! is_mcms_error( $result ) )
							$result = new MCMS_Error;
						$result->add( $_result->get_error_code() . "_$type", $_result->get_error_message(), substr( $_result->get_error_data(), strlen( $dest ) ) );
					}
				}
			}
		} //end foreach
	}

	// Handle $result error from the above blocks
	if ( is_mcms_error($result) ) {
		$mcms_filesystem->delete($from, true);
		return $result;
	}

	// Remove old files
	foreach ( $_old_files as $old_file ) {
		$old_file = $to . $old_file;
		if ( !$mcms_filesystem->exists($old_file) )
			continue;

		// If the file isn't deleted, try writing an empty string to the file instead.
		if ( ! $mcms_filesystem->delete( $old_file, true ) && $mcms_filesystem->is_file( $old_file ) ) {
			$mcms_filesystem->put_contents( $old_file, '' );
		}
	}

	// Remove any Genericons example.html's from the filesystem
	_upgrade_422_remove_genericons();

	// Remove the REST API module if its version is Beta 4 or lower
	_upgrade_440_force_deactivate_incompatible_modules();

	// Upgrade DB with separate request
	/** This filter is documented in mcms-admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Upgrading database&#8230;' ) );
	$db_upgrade_url = admin_url('upgrade.php?step=upgrade_db');
	mcms_remote_post($db_upgrade_url, array('timeout' => 60));

	// Clear the cache to prevent an update_option() from saving a stale db_version to the cache
	mcms_cache_flush();
	// (Not all cache back ends listen to 'flush')
	mcms_cache_delete( 'alloptions', 'options' );

	// Remove working directory
	$mcms_filesystem->delete($from, true);

	// Force refresh of update information
	if ( function_exists('delete_site_transient') )
		delete_site_transient('update_core');
	else
		delete_option('update_core');

	/**
	 * Fires after MandarinCMS core has been successfully updated.
	 *
	 * @since 3.3.0
	 *
	 * @param string $mcms_version The current MandarinCMS version.
	 */
	do_action( '_core_updated_successfully', $mcms_version );

	// Clear the option that blocks auto updates after failures, now that we've been successful.
	if ( function_exists( 'delete_site_option' ) )
		delete_site_option( 'auto_core_update_failed' );

	return $mcms_version;
}

/**
 * Copies a directory from one location to another via the MandarinCMS Filesystem Abstraction.
 * Assumes that MCMS_Filesystem() has already been called and setup.
 *
 * This is a temporary function for the 3.1 -> 3.2 upgrade, as well as for those upgrading to
 * 3.7+
 *
 * @ignore
 * @since 3.2.0
 * @since 3.7.0 Updated not to use a regular expression for the skip list
 * @see copy_dir()
 *
 * @global MCMS_Filesystem_Base $mcms_filesystem
 *
 * @param string $from     source directory
 * @param string $to       destination directory
 * @param array $skip_list a list of files/folders to skip copying
 * @return mixed MCMS_Error on failure, True on success.
 */
function _copy_dir($from, $to, $skip_list = array() ) {
	global $mcms_filesystem;

	$dirlist = $mcms_filesystem->dirlist($from);

	$from = trailingslashit($from);
	$to = trailingslashit($to);

	foreach ( (array) $dirlist as $filename => $fileinfo ) {
		if ( in_array( $filename, $skip_list ) )
			continue;

		if ( 'f' == $fileinfo['type'] ) {
			if ( ! $mcms_filesystem->copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ) {
				// If copy failed, chmod file to 0644 and try again.
				$mcms_filesystem->chmod( $to . $filename, FS_CHMOD_FILE );
				if ( ! $mcms_filesystem->copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) )
					return new MCMS_Error( 'copy_failed__copy_dir', __( 'Could not copy file.' ), $to . $filename );
			}
		} elseif ( 'd' == $fileinfo['type'] ) {
			if ( !$mcms_filesystem->is_dir($to . $filename) ) {
				if ( !$mcms_filesystem->mkdir($to . $filename, FS_CHMOD_DIR) )
					return new MCMS_Error( 'mkdir_failed__copy_dir', __( 'Could not create directory.' ), $to . $filename );
			}

			/*
			 * Generate the $sub_skip_list for the subdirectory as a sub-set
			 * of the existing $skip_list.
			 */
			$sub_skip_list = array();
			foreach ( $skip_list as $skip_item ) {
				if ( 0 === strpos( $skip_item, $filename . '/' ) )
					$sub_skip_list[] = preg_replace( '!^' . preg_quote( $filename, '!' ) . '/!i', '', $skip_item );
			}

			$result = _copy_dir($from . $filename, $to . $filename, $sub_skip_list);
			if ( is_mcms_error($result) )
				return $result;
		}
	}
	return true;
}

/**
 * Redirect to the About MandarinCMS page after a successful upgrade.
 *
 * This function is only needed when the existing installation is older than 3.4.0.
 *
 * @since 3.3.0
 *
 * @global string $mcms_version
 * @global string $pagenow
 * @global string $action
 *
 * @param string $new_version
 */
function _redirect_to_about_mandarincms( $new_version ) {
	global $mcms_version, $pagenow, $action;

	if ( version_compare( $mcms_version, '3.4-RC1', '>=' ) )
		return;

	// Ensure we only run this on the update-core.php page. The Core_Upgrader may be used in other contexts.
	if ( 'update-core.php' != $pagenow )
		return;

 	if ( 'do-core-upgrade' != $action && 'do-core-reinstall' != $action )
 		return;

	// Load the updated default text localization domain for new strings.
	load_default_textdomain();

	// See do_core_upgrade()
	show_message( __('MandarinCMS updated successfully') );

	// self_admin_url() won't exist when upgrading from <= 3.0, so relative URLs are intentional.
	show_message( '<span class="hide-if-no-js">' . sprintf( __( 'Welcome to MandarinCMS %1$s. You will be redirected to the About MandarinCMS screen. If not, click <a href="%2$s">here</a>.' ), $new_version, 'about.php?updated' ) . '</span>' );
	show_message( '<span class="hide-if-js">' . sprintf( __( 'Welcome to MandarinCMS %1$s. <a href="%2$s">Learn more</a>.' ), $new_version, 'about.php?updated' ) . '</span>' );
	echo '</div>';
	?>
<script type="text/javascript">
window.location = 'about.php?updated';
</script>
	<?php

	// Include admin-footer.php and exit.
	include(BASED_TREE_URI . 'mcms-admin/admin-footer.php');
	exit();
}

/**
 * Cleans up Genericons example files.
 *
 * @since 4.2.2
 *
 * @global array              $mcms_myskin_directories
 * @global MCMS_Filesystem_Base $mcms_filesystem
 */
function _upgrade_422_remove_genericons() {
	global $mcms_myskin_directories, $mcms_filesystem;

	// A list of the affected files using the filesystem absolute paths.
	$affected_files = array();

	// MySkins
	foreach ( $mcms_myskin_directories as $directory ) {
		$affected_myskin_files = _upgrade_422_find_genericons_files_in_folder( $directory );
		$affected_files       = array_merge( $affected_files, $affected_myskin_files );
	}

	// Modules
	$affected_module_files = _upgrade_422_find_genericons_files_in_folder( MCMS_PLUGIN_DIR );
	$affected_files        = array_merge( $affected_files, $affected_module_files );

	foreach ( $affected_files as $file ) {
		$gen_dir = $mcms_filesystem->find_folder( trailingslashit( dirname( $file ) ) );
		if ( empty( $gen_dir ) ) {
			continue;
		}

		// The path when the file is accessed via MCMS_Filesystem may differ in the case of FTP
		$remote_file = $gen_dir . basename( $file );

		if ( ! $mcms_filesystem->exists( $remote_file ) ) {
			continue;
		}

		if ( ! $mcms_filesystem->delete( $remote_file, false, 'f' ) ) {
			$mcms_filesystem->put_contents( $remote_file, '' );
		}
	}
}

/**
 * Recursively find Genericons example files in a given folder.
 *
 * @ignore
 * @since 4.2.2
 *
 * @param string $directory Directory path. Expects trailingslashed.
 * @return array
 */
function _upgrade_422_find_genericons_files_in_folder( $directory ) {
	$directory = trailingslashit( $directory );
	$files     = array();

	if ( file_exists( "{$directory}example.html" ) && false !== strpos( file_get_contents( "{$directory}example.html" ), '<title>Genericons</title>' ) ) {
		$files[] = "{$directory}example.html";
	}

	$dirs = glob( $directory . '*', GLOB_ONLYDIR );
	if ( $dirs ) {
		foreach ( $dirs as $dir ) {
			$files = array_merge( $files, _upgrade_422_find_genericons_files_in_folder( $dir ) );
		}
	}

	return $files;
}

/**
 * @ignore
 * @since 4.4.0
 */
function _upgrade_440_force_deactivate_incompatible_modules() {
	if ( defined( 'REST_API_VERSION' ) && version_compare( REST_API_VERSION, '2.0-beta4', '<=' ) ) {
		deactivate_modules( array( 'rest-api/module.php' ), true );
	}
}
