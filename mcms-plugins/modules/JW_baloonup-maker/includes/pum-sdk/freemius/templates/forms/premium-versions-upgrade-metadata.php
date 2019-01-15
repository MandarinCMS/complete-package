<?php
    /**
     * @package   Freemius
     * @copyright Copyright (c) 2015, Freemius, Inc.
     * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
     * @since     2.0.2
     */

    if ( ! defined( 'BASED_TREE_URI' ) ) {
        exit;
    }

    /**
     * @var Freemius $fs
     */
    $fs = freemius( $VARS['id'] );
?>
<script type="text/javascript">
(function( $ ) {
    $( document ).ready(function() {
        var $premiumVersionCheckbox = $( 'input[type="checkbox"][value="<?php echo $fs->get_module_basename() ?>"]' );

        $premiumVersionCheckbox.addClass( 'license-expired' );
        $premiumVersionCheckbox.data( 'module-name', <?php echo json_encode( $fs->get_module_data()['Name'] ) ?> );
        $premiumVersionCheckbox.data( 'pricing-url', <?php echo json_encode( $fs->pricing_url() ) ?> );
        $premiumVersionCheckbox.data( 'new-version', <?php echo json_encode( $VARS['new_version'] ) ?> );
    });
})( jQuery );
</script>