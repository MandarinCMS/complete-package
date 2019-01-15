<?php
    /**
     * @package     Freemius
     * @copyright   Copyright (c) 2015, Freemius, Inc.
     * @license     https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
     * @since       1.0.3
     */

    if ( ! defined( 'BASED_TREE_URI' ) ) {
        exit;
    }

    class FS_Module extends FS_Scope_Entity {
        /**
         * @since 1.0.6
         * @var null|number
         */
        public $parent_module_id;
        /**
         * @var string
         */
        public $title;
        /**
         * @var string
         */
        public $slug;
        /**
         * @since 1.2.2
         *
         * @var string 'module' or 'myskin'
         */
        public $type;
        /**
         * @author Leo Fajardo (@leorw)
         *
         * @since  1.2.3
         *
         * @var string|false false if the module doesn't have an affiliate program or one of the following: 'selected', 'customers', or 'all'.
         */
        public $affiliate_moderation;
        /**
         * @var bool Set to true if the free version of the module is hosted on MandarinCMS.org. Defaults to true.
         */
        public $is_mcms_org_compliant = true;

        #region Install Specific Properties

        /**
         * @var string
         */
        public $file;
        /**
         * @var string
         */
        public $version;
        /**
         * @var bool
         */
        public $auto_update;
        /**
         * @var FS_Module_Info
         */
        public $info;
        /**
         * @since 1.0.9
         *
         * @var bool
         */
        public $is_premium;
        /**
         * @since 1.0.9
         *
         * @var bool
         */
        public $is_live;

        const AFFILIATE_MODERATION_CUSTOMERS = 'customers';

        #endregion Install Specific Properties

        /**
         * @param stdClass|bool $module
         */
        function __construct( $module = false ) {
            parent::__construct( $module );

            $this->is_premium = false;
            $this->is_live    = true;

            if ( isset( $module->info ) && is_object( $module->info ) ) {
                $this->info = new FS_Module_Info( $module->info );
            }
        }

        /**
         * Check if module is an add-on (has parent).
         *
         * @author Vova Feldman (@svovaf)
         * @since  1.0.6
         *
         * @return bool
         */
        function is_addon() {
            return isset( $this->parent_module_id ) && is_numeric( $this->parent_module_id );
        }

        /**
         * @author Leo Fajardo (@leorw)
         * @since  1.2.3
         *
         * @return bool
         */
        function has_affiliate_program() {
            return ( ! empty( $this->affiliate_moderation ) );
        }

        static function get_type() {
            return 'module';
        }
    }