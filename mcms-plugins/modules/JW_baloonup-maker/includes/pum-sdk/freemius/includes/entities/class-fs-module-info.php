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

	class FS_Module_Info extends FS_Entity {
		public $module_id;
		public $description;
		public $short_description;
		public $banner_url;
		public $card_banner_url;
		public $selling_point_0;
		public $selling_point_1;
		public $selling_point_2;
		public $screenshots;

		/**
		 * @param stdClass|bool $module_info
		 */
		function __construct( $module_info = false ) {
			parent::__construct( $module_info );
		}

		static function get_type() {
			return 'module';
		}
	}