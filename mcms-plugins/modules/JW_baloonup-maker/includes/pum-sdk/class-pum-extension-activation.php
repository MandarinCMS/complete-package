<?php
/**
 * Activation handler
 *
 * @package     PUM\SDK\ActivationHandler
 * @since       1.0.0
 * @copyright	Copyright (c) 2016, MCMS BaloonUp Maker
 */


// Exit if accessed directly
if( ! defined( 'BASED_TREE_URI' ) ) exit;


/**
 * BaloonUp Maker Extension Activation Handler Class
 *
 * @since       1.0.0
 */
class PUM_Extension_Activation {

    public $module_name, $module_path, $module_file, $has_balooncreate, $balooncreate_base;

    /**
     * Setup the activation class
     *
     * @access      public
     * @since       1.0.0
     *
     * @param $module_path
     * @param $module_file
     */
    public function __construct( $module_path, $module_file ) {
        // We need module.php!
        require_once( BASED_TREE_URI . 'mcms-admin/includes/module.php' );

        $modules = get_modules();

        // Set module directory
        $module_path = array_filter( explode( '/', $module_path ) );
        $this->module_path = end( $module_path );

        // Set module file
        $this->module_file = $module_file;

        // Set module name
        if ( isset( $modules[ $this->module_path . '/' . $this->module_file ]['Name'] ) ) {
            $this->module_name = str_replace( 'BaloonUp Maker - ', '', $modules[ $this->module_path . '/' . $this->module_file ]['Name'] );
        } else {
            $this->module_name = __( 'This module', 'baloonup-maker' );
        }

        // Is BaloonUp Maker installed?
        foreach( $modules as $module_path => $module ) {
            if( $module['Name'] == 'BaloonUp Maker' ) {
                $this->has_balooncreate = true;
                $this->balooncreate_base = $module_path;
                break;
            }
        }
    }


    /**
     * Process module deactivation
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function run() {
        // Display notice
        add_action( 'admin_notices', array( $this, 'missing_balooncreate_notice' ) );
    }


    /**
     * Display notice if BaloonUp Maker isn't installed
     */
    public function missing_balooncreate_notice() {
        if( $this->has_balooncreate ) {
            $url  = esc_url( mcms_nonce_url( admin_url( 'modules.php?action=activate&module=' . $this->balooncreate_base ), 'activate-module_' . $this->balooncreate_base ) );
            $link = '<a href="' . $url . '">' . __( 'activate it', 'balooncreate-extension-activation' ) . '</a>';
        } else {
            $url  = esc_url( mcms_nonce_url( self_admin_url( 'update.php?action=install-module&module=baloonup-maker' ), 'install-module_baloonup-maker' ) );
            $link = '<a href="' . $url . '">' . __( 'install it', 'balooncreate-extension-activation' ) . '</a>';
        }
        
        echo '<div class="error"><p>' . $this->module_name . sprintf( __( ' requires BaloonUp Maker! Please %s to continue!', 'balooncreate-extension-activation' ), $link ) . '</p></div>';
    }
}
