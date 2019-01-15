<?php
	/**
	 * @package     Freemius
	 * @copyright   Copyright (c) 2015, Freemius, Inc.
	 * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
	 * @since       1.2.2.7
	 *
	 */

	if ( ! defined( 'BASED_TREE_URI' ) ) {
		exit;
	}

	/**
	 * @var array    $VARS
	 * @var Freemius $fs
	 */
	$fs = freemius( $VARS['id'] );

	$slug = $fs->get_slug();

?>
<script type="text/javascript">
	(function ($) {
		// Select the premium myskin version.
		var $myskin             = $('#<?php echo $slug ?>-premium-name').parents('.myskin'),
		    addPremiumMetadata = function (firstCall) {
			    if (!firstCall) {
				    // Seems like the original myskin element is removed from the DOM,
				    // so we need to reselect the updated one.
				    $myskin = $('#<?php echo $slug ?>-premium-name').parents('.myskin');
			    }

			    if (0 === $myskin.find('.fs-premium-myskin-badge').length) {
				    $myskin.addClass('fs-premium');

				    $myskin.append('<span class="fs-premium-myskin-badge">' + <?php echo json_encode( $fs->get_text_inline( 'Premium', 'premium' ) ) ?> +'</span>');
			    }
		    };

		addPremiumMetadata(true);

		$myskin.contentChange(addPremiumMetadata);
	})(jQuery);
</script>