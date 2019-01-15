<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

require_once 'vc-grids-functions.php';
if ( ! class_exists( 'VcGridsCommon' ) ) {
	abstract class VcGridsCommon {

		protected static $basicGrid;
		protected static $masonryGrid;
		protected static $masonryMediaGrid;
		protected static $mediaGrid;
		protected static $gridCommon;
		protected static $btn3Params;
		protected static $gridColsList;

		protected static function initData() {

			self::$btn3Params = vc_map_integrate_shortcode( 'vc_btn', 'btn_', __( 'Load More Button', 'rl_conductor' ), array(
				'exclude' => array(
					'link',
					'css',
					'el_class',
					'css_animation',
				),
			), array(
				'element' => 'style',
				'value' => array( 'load-more' ),
			) );
			foreach ( self::$btn3Params as $key => $value ) {
				if ( 'btn_title' == $value['param_name'] ) {
					self::$btn3Params[ $key ]['value'] = __( 'Load more', 'rl_conductor' );
				} else if ( 'btn_color' == $value['param_name'] ) {
					self::$btn3Params[ $key ]['std'] = 'blue';
				} else if ( 'btn_style' == $value['param_name'] ) {
					self::$btn3Params[ $key ]['std'] = 'flat';
				}
			}

			// Grid column list
			self::$gridColsList = array(
				array(
					'label' => '6',
					'value' => 2,
				),
				array(
					'label' => '4',
					'value' => 3,
				),
				array(
					'label' => '3',
					'value' => 4,
				),
				array(
					'label' => '2',
					'value' => 6,
				),
				array(
					'label' => '1',
					'value' => 12,
				),
			);
		}

		// Basic Grid Common Settings
		public static function getBasicAtts() {

			if ( self::$basicGrid ) {
				return self::$basicGrid;
			}

			if ( is_null( self::$btn3Params ) && is_null( self::$gridColsList ) ) {
				self::initData();
			}

			$postTypes = get_post_types( array() );
			$postTypesList = array();
			$excludedPostTypes = array(
				'revision',
				'nav_menu_item',
				'vc_grid_item',
			);
			if ( is_array( $postTypes ) && ! empty( $postTypes ) ) {
				foreach ( $postTypes as $postType ) {
					if ( ! in_array( $postType, $excludedPostTypes ) ) {
						$label = ucfirst( $postType );
						$postTypesList[] = array(
							$postType,
							$label,
						);
					}
				}
			}
			$postTypesList[] = array(
				'custom',
				__( 'Custom query', 'rl_conductor' ),
			);
			$postTypesList[] = array(
				'ids',
				__( 'List of IDs', 'rl_conductor' ),
			);

			$taxonomiesForFilter = array();

			if ( 'vc_edit_form' === vc_post_param( 'action' ) ) {
				$vcTaxonomiesTypes = vc_taxonomies_types();
				if ( is_array( $vcTaxonomiesTypes ) && ! empty( $vcTaxonomiesTypes ) ) {
					foreach ( $vcTaxonomiesTypes as $t => $data ) {
						if ( 'post_format' !== $t && is_object( $data ) ) {
							$taxonomiesForFilter[ $data->labels->name . '(' . $t . ')' ] = $t;
						}
					}
				}
			}

			self::$basicGrid = array_merge( array(
				array(
					'type' => 'dropdown',
					'heading' => __( 'Data source', 'rl_conductor' ),
					'param_name' => 'post_type',
					'value' => $postTypesList,
					'save_always' => true,
					'description' => __( 'Select content type for your grid.', 'rl_conductor' ),
					'admin_label' => true,
				),
				array(
					'type' => 'autocomplete',
					'heading' => __( 'Include only', 'rl_conductor' ),
					'param_name' => 'include',
					'description' => __( 'Add posts, pages, etc. by title.', 'rl_conductor' ),
					'settings' => array(
						'multiple' => true,
						'sortable' => true,
						'groups' => true,
					),
					'dependency' => array(
						'element' => 'post_type',
						'value' => array( 'ids' ),
					),
				),
				// Custom query tab
				array(
					'type' => 'textarea_safe',
					'heading' => __( 'Custom query', 'rl_conductor' ),
					'param_name' => 'custom_query',
					'description' => __( 'Build custom query according to <a href="http://codex.mandarincms.com/Function_Reference/query_posts">MandarinCMS Codex</a>.', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'post_type',
						'value' => array( 'custom' ),
					),
				),
				array(
					'type' => 'autocomplete',
					'heading' => __( 'Narrow data source', 'rl_conductor' ),
					'param_name' => 'taxonomies',
					'settings' => array(
						'multiple' => true,
						'min_length' => 1,
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
					),
					'param_holder_class' => 'vc_not-for-custom',
					'description' => __( 'Enter categories, tags or custom taxonomies.', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'post_type',
						'value_not_equal_to' => array(
							'ids',
							'custom',
						),
					),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Total items', 'rl_conductor' ),
					'param_name' => 'max_items',
					'value' => 10,
					// default value
					'param_holder_class' => 'vc_not-for-custom',
					'description' => __( 'Set max limit for items in grid or enter -1 to display all (limited to 1000).', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'post_type',
						'value_not_equal_to' => array(
							'ids',
							'custom',
						),
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Display Style', 'rl_conductor' ),
					'param_name' => 'style',
					'value' => array(
						__( 'Show all', 'rl_conductor' ) => 'all',
						__( 'Load more button', 'rl_conductor' ) => 'load-more',
						__( 'Lazy loading', 'rl_conductor' ) => 'lazy',
						__( 'Pagination', 'rl_conductor' ) => 'pagination',
					),
					'dependency' => array(
						'element' => 'post_type',
						'value_not_equal_to' => array( 'custom' ),
					),
					'edit_field_class' => 'vc_col-sm-6',
					'description' => __( 'Select display style for grid.', 'rl_conductor' ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Items per page', 'rl_conductor' ),
					'param_name' => 'items_per_page',
					'description' => __( 'Number of items to show per page.', 'rl_conductor' ),
					'value' => '10',
					'dependency' => array(
						'element' => 'style',
						'value' => array(
							'lazy',
							'load-more',
							'pagination',
						),
					),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Show filter', 'rl_conductor' ),
					'param_name' => 'show_filter',
					'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
					'description' => __( 'Append filter to grid.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Grid elements per row', 'rl_conductor' ),
					'param_name' => 'element_width',
					'value' => self::$gridColsList,
					'std' => '4',
					'edit_field_class' => 'vc_col-sm-6',
					'description' => __( 'Select number of single grid elements per row.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Gap', 'rl_conductor' ),
					'param_name' => 'gap',
					'value' => array(
						'0px' => '0',
						'1px' => '1',
						'2px' => '2',
						'3px' => '3',
						'4px' => '4',
						'5px' => '5',
						'10px' => '10',
						'15px' => '15',
						'20px' => '20',
						'25px' => '25',
						'30px' => '30',
						'35px' => '35',
					),
					'std' => '30',
					'description' => __( 'Select gap between grid elements.', 'rl_conductor' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				// Data settings
				array(
					'type' => 'dropdown',
					'heading' => __( 'Order by', 'rl_conductor' ),
					'param_name' => 'orderby',
					'value' => array(
						__( 'Date', 'rl_conductor' ) => 'date',
						__( 'Order by post ID', 'rl_conductor' ) => 'ID',
						__( 'Author', 'rl_conductor' ) => 'author',
						__( 'Title', 'rl_conductor' ) => 'title',
						__( 'Last modified date', 'rl_conductor' ) => 'modified',
						__( 'Post/page parent ID', 'rl_conductor' ) => 'parent',
						__( 'Number of comments', 'rl_conductor' ) => 'comment_count',
						__( 'Menu order/Page Order', 'rl_conductor' ) => 'menu_order',
						__( 'Meta value', 'rl_conductor' ) => 'meta_value',
						__( 'Meta value number', 'rl_conductor' ) => 'meta_value_num',
						__( 'Random order', 'rl_conductor' ) => 'rand',
					),
					'description' => __( 'Select order type. If "Meta value" or "Meta value Number" is chosen then meta key is required.', 'rl_conductor' ),
					'group' => __( 'Data Settings', 'rl_conductor' ),
					'param_holder_class' => 'vc_grid-data-type-not-ids',
					'dependency' => array(
						'element' => 'post_type',
						'value_not_equal_to' => array(
							'ids',
							'custom',
						),
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Sort order', 'rl_conductor' ),
					'param_name' => 'order',
					'group' => __( 'Data Settings', 'rl_conductor' ),
					'value' => array(
						__( 'Descending', 'rl_conductor' ) => 'DESC',
						__( 'Ascending', 'rl_conductor' ) => 'ASC',
					),
					'param_holder_class' => 'vc_grid-data-type-not-ids',
					'description' => __( 'Select sorting order.', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'post_type',
						'value_not_equal_to' => array(
							'ids',
							'custom',
						),
					),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Meta key', 'rl_conductor' ),
					'param_name' => 'meta_key',
					'description' => __( 'Input meta key for grid ordering.', 'rl_conductor' ),
					'group' => __( 'Data Settings', 'rl_conductor' ),
					'param_holder_class' => 'vc_grid-data-type-not-ids',
					'dependency' => array(
						'element' => 'orderby',
						'value' => array(
							'meta_value',
							'meta_value_num',
						),
					),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Offset', 'rl_conductor' ),
					'param_name' => 'offset',
					'description' => __( 'Number of grid elements to displace or pass over.', 'rl_conductor' ),
					'group' => __( 'Data Settings', 'rl_conductor' ),
					'param_holder_class' => 'vc_grid-data-type-not-ids',
					'dependency' => array(
						'element' => 'post_type',
						'value_not_equal_to' => array(
							'ids',
							'custom',
						),
					),
				),
				array(
					'type' => 'autocomplete',
					'heading' => __( 'Exclude', 'rl_conductor' ),
					'param_name' => 'exclude',
					'description' => __( 'Exclude posts, pages, etc. by title.', 'rl_conductor' ),
					'group' => __( 'Data Settings', 'rl_conductor' ),
					'settings' => array(
						'multiple' => true,
					),
					'param_holder_class' => 'vc_grid-data-type-not-ids',
					'dependency' => array(
						'element' => 'post_type',
						'value_not_equal_to' => array(
							'ids',
							'custom',
						),
						'callback' => 'vc_grid_exclude_dependency_callback',
					),
				),
				//Filter tab
				array(
					'type' => 'dropdown',
					'heading' => __( 'Filter by', 'rl_conductor' ),
					'param_name' => 'filter_source',
					'value' => $taxonomiesForFilter,
					'group' => __( 'Filter', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'show_filter',
						'value' => array( 'yes' ),
					),
					'save_always' => true,
					'description' => __( 'Select filter source.', 'rl_conductor' ),
				),
				array(
					'type' => 'autocomplete',
					'heading' => __( 'Exclude from filter list', 'rl_conductor' ),
					'param_name' => 'exclude_filter',
					'settings' => array(
						'multiple' => true,
						// is multiple values allowed? default false
						'min_length' => 1,
						// min length to start search -> default 2
						'groups' => true,
						// In UI show results grouped by groups, default false
						'unique_values' => true,
						// In UI show results except selected. NB! You should manually check values in backend, default false
						'display_inline' => true,
						// In UI show results inline view, default false (each value in own line)
						'delay' => 500,
						// delay for search. default 500
						'auto_focus' => true,
						// auto focus input, default true
					),
					'description' => __( 'Enter categories, tags won\'t be shown in the filters list', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'show_filter',
						'value' => array( 'yes' ),
						'callback' => 'vcGridFilterExcludeCallBack',
					),
					'group' => __( 'Filter', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Style', 'rl_conductor' ),
					'param_name' => 'filter_style',
					'value' => array(
						__( 'Rounded', 'rl_conductor' ) => 'default',
						__( 'Less Rounded', 'rl_conductor' ) => 'default-less-rounded',
						__( 'Border', 'rl_conductor' ) => 'bordered',
						__( 'Rounded Border', 'rl_conductor' ) => 'bordered-rounded',
						__( 'Less Rounded Border', 'rl_conductor' ) => 'bordered-rounded-less',
						__( 'Filled', 'rl_conductor' ) => 'filled',
						__( 'Rounded Filled', 'rl_conductor' ) => 'filled-rounded',
						__( 'Dropdown', 'rl_conductor' ) => 'dropdown',
					),
					'dependency' => array(
						'element' => 'show_filter',
						'value' => array( 'yes' ),
					),
					'group' => __( 'Filter', 'rl_conductor' ),
					'description' => __( 'Select filter display style.', 'rl_conductor' ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Default title', 'rl_conductor' ),
					'param_name' => 'filter_default_title',
					'value' => __( 'All', 'rl_conductor' ),
					'description' => __( 'Enter default title for filter option display (empty: "All").', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'show_filter',
						'value' => array( 'yes' ),
					),
					'group' => __( 'Filter', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Alignment', 'rl_conductor' ),
					'param_name' => 'filter_align',
					'value' => array(
						__( 'Center', 'rl_conductor' ) => 'center',
						__( 'Left', 'rl_conductor' ) => 'left',
						__( 'Right', 'rl_conductor' ) => 'right',
					),
					'dependency' => array(
						'element' => 'show_filter',
						'value' => array( 'yes' ),
					),
					'group' => __( 'Filter', 'rl_conductor' ),
					'description' => __( 'Select filter alignment.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Color', 'rl_conductor' ),
					'param_name' => 'filter_color',
					'value' => getVcShared( 'colors' ),
					'std' => 'grey',
					'param_holder_class' => 'vc_colored-dropdown',
					'dependency' => array(
						'element' => 'show_filter',
						'value' => array( 'yes' ),
					),
					'group' => __( 'Filter', 'rl_conductor' ),
					'description' => __( 'Select filter color.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Filter size', 'rl_conductor' ),
					'param_name' => 'filter_size',
					'value' => getVcShared( 'sizes' ),
					'std' => 'md',
					'description' => __( 'Select filter size.', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'show_filter',
						'value' => array( 'yes' ),
					),
					'group' => __( 'Filter', 'rl_conductor' ),
				),
				// moved to the end
				// Paging controls
				array(
					'type' => 'dropdown',
					'heading' => __( 'Arrows design', 'rl_conductor' ),
					'param_name' => 'arrows_design',
					'value' => array(
						__( 'None', 'rl_conductor' ) => 'none',
						__( 'Simple', 'rl_conductor' ) => 'vc_arrow-icon-arrow_01_left',
						__( 'Simple Circle Border', 'rl_conductor' ) => 'vc_arrow-icon-arrow_02_left',
						__( 'Simple Circle', 'rl_conductor' ) => 'vc_arrow-icon-arrow_03_left',
						__( 'Simple Square', 'rl_conductor' ) => 'vc_arrow-icon-arrow_09_left',
						__( 'Simple Square Rounded', 'rl_conductor' ) => 'vc_arrow-icon-arrow_12_left',
						__( 'Simple Rounded', 'rl_conductor' ) => 'vc_arrow-icon-arrow_11_left',
						__( 'Rounded', 'rl_conductor' ) => 'vc_arrow-icon-arrow_04_left',
						__( 'Rounded Circle Border', 'rl_conductor' ) => 'vc_arrow-icon-arrow_05_left',
						__( 'Rounded Circle', 'rl_conductor' ) => 'vc_arrow-icon-arrow_06_left',
						__( 'Rounded Square', 'rl_conductor' ) => 'vc_arrow-icon-arrow_10_left',
						__( 'Simple Arrow', 'rl_conductor' ) => 'vc_arrow-icon-arrow_08_left',
						__( 'Simple Rounded Arrow', 'rl_conductor' ) => 'vc_arrow-icon-arrow_07_left',

					),
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'pagination' ),
					),
					'description' => __( 'Select design for arrows.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Arrows position', 'rl_conductor' ),
					'param_name' => 'arrows_position',
					'value' => array(
						__( 'Inside Wrapper', 'rl_conductor' ) => 'inside',
						__( 'Outside Wrapper', 'rl_conductor' ) => 'outside',
					),
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'arrows_design',
						'value_not_equal_to' => array( 'none' ),
						// New dependency
					),
					'description' => __( 'Arrows will be displayed inside or outside grid.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Arrows color', 'rl_conductor' ),
					'param_name' => 'arrows_color',
					'value' => getVcShared( 'colors' ),
					'param_holder_class' => 'vc_colored-dropdown',
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'arrows_design',
						'value_not_equal_to' => array( 'none' ),
						// New dependency
					),
					'description' => __( 'Select color for arrows.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Pagination style', 'rl_conductor' ),
					'param_name' => 'paging_design',
					'value' => array(
						__( 'None', 'rl_conductor' ) => 'none',
						__( 'Square Dots', 'rl_conductor' ) => 'square_dots',
						__( 'Radio Dots', 'rl_conductor' ) => 'radio_dots',
						__( 'Point Dots', 'rl_conductor' ) => 'point_dots',
						__( 'Fill Square Dots', 'rl_conductor' ) => 'fill_square_dots',
						__( 'Rounded Fill Square Dots', 'rl_conductor' ) => 'round_fill_square_dots',
						__( 'Pagination Default', 'rl_conductor' ) => 'pagination_default',
						__( 'Outline Default Dark', 'rl_conductor' ) => 'pagination_default_dark',
						__( 'Outline Default Light', 'rl_conductor' ) => 'pagination_default_light',
						__( 'Pagination Rounded', 'rl_conductor' ) => 'pagination_rounded',
						__( 'Outline Rounded Dark', 'rl_conductor' ) => 'pagination_rounded_dark',
						__( 'Outline Rounded Light', 'rl_conductor' ) => 'pagination_rounded_light',
						__( 'Pagination Square', 'rl_conductor' ) => 'pagination_square',
						__( 'Outline Square Dark', 'rl_conductor' ) => 'pagination_square_dark',
						__( 'Outline Square Light', 'rl_conductor' ) => 'pagination_square_light',
						__( 'Pagination Rounded Square', 'rl_conductor' ) => 'pagination_rounded_square',
						__( 'Outline Rounded Square Dark', 'rl_conductor' ) => 'pagination_rounded_square_dark',
						__( 'Outline Rounded Square Light', 'rl_conductor' ) => 'pagination_rounded_square_light',
						__( 'Stripes Dark', 'rl_conductor' ) => 'pagination_stripes_dark',
						__( 'Stripes Light', 'rl_conductor' ) => 'pagination_stripes_light',
					),
					'std' => 'radio_dots',
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'pagination' ),
					),
					'description' => __( 'Select pagination style.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Pagination color', 'rl_conductor' ),
					'param_name' => 'paging_color',
					'value' => getVcShared( 'colors' ),
					'std' => 'grey',
					'param_holder_class' => 'vc_colored-dropdown',
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'paging_design',
						'value_not_equal_to' => array( 'none' ),
						// New dependency
					),
					'description' => __( 'Select pagination color.', 'rl_conductor' ),
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Loop pages?', 'rl_conductor' ),
					'param_name' => 'loop',
					'description' => __( 'Allow items to be repeated in infinite loop (carousel).', 'rl_conductor' ),
					'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'pagination' ),
					),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Autoplay delay', 'rl_conductor' ),
					'param_name' => 'autoplay',
					'value' => '-1',
					'description' => __( 'Enter value in seconds. Set -1 to disable autoplay.', 'rl_conductor' ),
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'pagination' ),
					),
				),
				array(
					'type' => 'animation_style',
					'heading' => __( 'Animation In', 'rl_conductor' ),
					'param_name' => 'paging_animation_in',
					'group' => __( 'Pagination', 'rl_conductor' ),
					'settings' => array(
						'type' => array(
							'in',
							'other',
						),
					),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'pagination' ),
					),
					'description' => __( 'Select "animation in" for page transition.', 'rl_conductor' ),
				),
				array(
					'type' => 'animation_style',
					'heading' => __( 'Animation Out', 'rl_conductor' ),
					'param_name' => 'paging_animation_out',
					'group' => __( 'Pagination', 'rl_conductor' ),
					'settings' => array(
						'type' => array( 'out' ),
					),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'pagination' ),
					),
					'description' => __( 'Select "animation out" for page transition.', 'rl_conductor' ),
				),
				array(
					'type' => 'vc_grid_item',
					'heading' => __( 'Grid element template', 'rl_conductor' ),
					'param_name' => 'item',
					'description' => sprintf( __( '%sCreate new%s template or %smodify selected%s. Predefined templates will be cloned.', 'rl_conductor' ), '<a href="' . esc_url( admin_url( 'post-new.php?post_type=vc_grid_item' ) ) . '" target="_blank">', '</a>', '<a href="#" target="_blank" data-vc-grid-item="edit_link">', '</a>' ),
					'group' => __( 'Item Design', 'rl_conductor' ),
					'value' => 'none',
				),
				array(
					'type' => 'vc_grid_id',
					'param_name' => 'grid_id',
				),
				array(
					'type' => 'animation_style',
					'heading' => __( 'Initial loading animation', 'rl_conductor' ),
					'param_name' => 'initial_loading_animation',
					'value' => 'fadeIn',
					'settings' => array(
						'type' => array(
							'in',
							'other',
						),
					),
					'description' => __( 'Select initial loading animation for grid element.', 'rl_conductor' ),
				),
				array(
					'type' => 'el_id',
					'heading' => __( 'Element ID', 'rl_conductor' ),
					'param_name' => 'el_id',
					'description' => sprintf( __( 'Enter element ID (Note: make sure it is unique and valid according to <a href="%s" target="_blank">w3c specification</a>).', 'rl_conductor' ), 'http://www.w3schools.com/tags/att_global_id.asp' ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Extra class name', 'rl_conductor' ),
					'param_name' => 'el_class',
					'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', 'rl_conductor' ),
					'param_name' => 'css',
					'group' => __( 'Design Options', 'rl_conductor' ),
				),

				// Load more btn
				array(
					'type' => 'hidden',
					'heading' => __( 'Button style', 'rl_conductor' ),
					'param_name' => 'button_style',
					'value' => '',
					'param_holder_class' => 'vc_colored-dropdown',
					'group' => __( 'Load More Button', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'load-more' ),
					),
					'description' => __( 'Select button style.', 'rl_conductor' ),
				),
				array(
					'type' => 'hidden',
					'heading' => __( 'Button color', 'rl_conductor' ),
					'param_name' => 'button_color',
					'value' => '',
					'param_holder_class' => 'vc_colored-dropdown',
					'group' => __( 'Load More Button', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'load-more' ),
					),
					'description' => __( 'Select button color.', 'rl_conductor' ),
				),
				array(
					'type' => 'hidden',
					'heading' => __( 'Button size', 'rl_conductor' ),
					'param_name' => 'button_size',
					'value' => '',
					'description' => __( 'Select button size.', 'rl_conductor' ),
					'group' => __( 'Load More Button', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'load-more' ),
					),
				),
			), self::$btn3Params );
			self::$basicGrid = array_merge( self::$basicGrid );

			return self::$basicGrid;
		}

		// Media grid common settings
		public static function getMediaCommonAtts() {

			if ( self::$mediaGrid ) {
				return self::$mediaGrid;
			}

			if ( is_null( self::$btn3Params ) && is_null( self::$gridColsList ) ) {
				self::initData();
			}

			self::$mediaGrid = array_merge( array(
				array(
					'type' => 'attach_images',
					'heading' => __( 'Images', 'rl_conductor' ),
					'param_name' => 'include',
					'description' => __( 'Select images from media library.', 'rl_conductor' ),

				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Display Style', 'rl_conductor' ),
					'param_name' => 'style',
					'value' => array(
						__( 'Show all', 'rl_conductor' ) => 'all',
						__( 'Load more button', 'rl_conductor' ) => 'load-more',
						__( 'Lazy loading', 'rl_conductor' ) => 'lazy',
						__( 'Pagination', 'rl_conductor' ) => 'pagination',
					),
					'dependency' => array(
						'element' => 'post_type',
						'value_not_equal_to' => array( 'custom' ),
					),
					'edit_field_class' => 'vc_col-sm-6',
					'description' => __( 'Select display style for grid.', 'rl_conductor' ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Items per page', 'rl_conductor' ),
					'param_name' => 'items_per_page',
					'description' => __( 'Number of items to show per page.', 'rl_conductor' ),
					'value' => '10',
					'dependency' => array(
						'element' => 'style',
						'value' => array(
							'lazy',
							'load-more',
							'pagination',
						),
					),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Grid elements per row', 'rl_conductor' ),
					'param_name' => 'element_width',
					'value' => self::$gridColsList,
					'std' => '4',
					'edit_field_class' => 'vc_col-sm-6',
					'description' => __( 'Select number of single grid elements per row.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Gap', 'rl_conductor' ),
					'param_name' => 'gap',
					'value' => array(
						'0px' => '0',
						'1px' => '1',
						'2px' => '2',
						'3px' => '3',
						'4px' => '4',
						'5px' => '5',
						'10px' => '10',
						'15px' => '15',
						'20px' => '20',
						'25px' => '25',
						'30px' => '30',
						'35px' => '35',
					),
					'std' => '5',
					'description' => __( 'Select gap between grid elements.', 'rl_conductor' ),
					'edit_field_class' => 'vc_col-sm-6',
				),
				array(
					'type' => 'hidden',
					'heading' => __( 'Button style', 'rl_conductor' ),
					'param_name' => 'button_style',
					'value' => '',
					'param_holder_class' => 'vc_colored-dropdown',
					'group' => __( 'Load More Button', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'load-more' ),
					),
					'description' => __( 'Select button style.', 'rl_conductor' ),
				),
				array(
					'type' => 'hidden',
					'heading' => __( 'Button color', 'rl_conductor' ),
					'param_name' => 'button_color',
					'value' => '',
					'param_holder_class' => 'vc_colored-dropdown',
					'group' => __( 'Load More Button', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'load-more' ),
					),
					'description' => __( 'Select button color.', 'rl_conductor' ),
				),
				array(
					'type' => 'hidden',
					'heading' => __( 'Button size', 'rl_conductor' ),
					'param_name' => 'button_size',
					'value' => '',
					'description' => __( 'Select button size.', 'rl_conductor' ),
					'group' => __( 'Load More Button', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'load-more' ),
					),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Arrows design', 'rl_conductor' ),
					'param_name' => 'arrows_design',
					'value' => array(
						__( 'None', 'rl_conductor' ) => 'none',
						__( 'Simple', 'rl_conductor' ) => 'vc_arrow-icon-arrow_01_left',
						__( 'Simple Circle Border', 'rl_conductor' ) => 'vc_arrow-icon-arrow_02_left',
						__( 'Simple Circle', 'rl_conductor' ) => 'vc_arrow-icon-arrow_03_left',
						__( 'Simple Square', 'rl_conductor' ) => 'vc_arrow-icon-arrow_09_left',
						__( 'Simple Square Rounded', 'rl_conductor' ) => 'vc_arrow-icon-arrow_12_left',
						__( 'Simple Rounded', 'rl_conductor' ) => 'vc_arrow-icon-arrow_11_left',
						__( 'Rounded', 'rl_conductor' ) => 'vc_arrow-icon-arrow_04_left',
						__( 'Rounded Circle Border', 'rl_conductor' ) => 'vc_arrow-icon-arrow_05_left',
						__( 'Rounded Circle', 'rl_conductor' ) => 'vc_arrow-icon-arrow_06_left',
						__( 'Rounded Square', 'rl_conductor' ) => 'vc_arrow-icon-arrow_10_left',
						__( 'Simple Arrow', 'rl_conductor' ) => 'vc_arrow-icon-arrow_08_left',
						__( 'Simple Rounded Arrow', 'rl_conductor' ) => 'vc_arrow-icon-arrow_07_left',

					),
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'pagination' ),
					),
					'description' => __( 'Select design for arrows.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Arrows position', 'rl_conductor' ),
					'param_name' => 'arrows_position',
					'value' => array(
						__( 'Inside Wrapper', 'rl_conductor' ) => 'inside',
						__( 'Outside Wrapper', 'rl_conductor' ) => 'outside',
					),
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'arrows_design',
						'value_not_equal_to' => array( 'none' ),
						// New dependency
					),
					'description' => __( 'Arrows will be displayed inside or outside grid.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Arrows color', 'rl_conductor' ),
					'param_name' => 'arrows_color',
					'value' => getVcShared( 'colors' ),
					'param_holder_class' => 'vc_colored-dropdown',
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'arrows_design',
						'value_not_equal_to' => array( 'none' ),
						// New dependency
					),
					'description' => __( 'Select color for arrows.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Pagination style', 'rl_conductor' ),
					'param_name' => 'paging_design',
					'value' => array(
						__( 'None', 'rl_conductor' ) => 'none',
						__( 'Square Dots', 'rl_conductor' ) => 'square_dots',
						__( 'Radio Dots', 'rl_conductor' ) => 'radio_dots',
						__( 'Point Dots', 'rl_conductor' ) => 'point_dots',
						__( 'Fill Square Dots', 'rl_conductor' ) => 'fill_square_dots',
						__( 'Rounded Fill Square Dots', 'rl_conductor' ) => 'round_fill_square_dots',
						__( 'Pagination Default', 'rl_conductor' ) => 'pagination_default',
						__( 'Outline Default Dark', 'rl_conductor' ) => 'pagination_default_dark',
						__( 'Outline Default Light', 'rl_conductor' ) => 'pagination_default_light',
						__( 'Pagination Rounded', 'rl_conductor' ) => 'pagination_rounded',
						__( 'Outline Rounded Dark', 'rl_conductor' ) => 'pagination_rounded_dark',
						__( 'Outline Rounded Light', 'rl_conductor' ) => 'pagination_rounded_light',
						__( 'Pagination Square', 'rl_conductor' ) => 'pagination_square',
						__( 'Outline Square Dark', 'rl_conductor' ) => 'pagination_square_dark',
						__( 'Outline Square Light', 'rl_conductor' ) => 'pagination_square_light',
						__( 'Pagination Rounded Square', 'rl_conductor' ) => 'pagination_rounded_square',
						__( 'Outline Rounded Square Dark', 'rl_conductor' ) => 'pagination_rounded_square_dark',
						__( 'Outline Rounded Square Light', 'rl_conductor' ) => 'pagination_rounded_square_light',
						__( 'Stripes Dark', 'rl_conductor' ) => 'pagination_stripes_dark',
						__( 'Stripes Light', 'rl_conductor' ) => 'pagination_stripes_light',
					),
					'std' => 'radio_dots',
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'pagination' ),
					),
					'description' => __( 'Select pagination style.', 'rl_conductor' ),
				),
				array(
					'type' => 'dropdown',
					'heading' => __( 'Pagination color', 'rl_conductor' ),
					'param_name' => 'paging_color',
					'value' => getVcShared( 'colors' ),
					'std' => 'grey',
					'param_holder_class' => 'vc_colored-dropdown',
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'paging_design',
						'value_not_equal_to' => array( 'none' ),
						// New dependency
					),
					'description' => __( 'Select pagination color.', 'rl_conductor' ),
				),
				array(
					'type' => 'checkbox',
					'heading' => __( 'Loop pages?', 'rl_conductor' ),
					'param_name' => 'loop',
					'description' => __( 'Allow items to be repeated in infinite loop (carousel).', 'rl_conductor' ),
					'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'pagination' ),
					),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Autoplay delay', 'rl_conductor' ),
					'param_name' => 'autoplay',
					'value' => '-1',
					'description' => __( 'Enter value in seconds. Set -1 to disable autoplay.', 'rl_conductor' ),
					'group' => __( 'Pagination', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'pagination' ),
					),
				),
				array(
					'type' => 'animation_style',
					'heading' => __( 'Animation In', 'rl_conductor' ),
					'param_name' => 'paging_animation_in',
					'group' => __( 'Pagination', 'rl_conductor' ),
					'settings' => array(
						'type' => array(
							'in',
							'other',
						),
					),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'pagination' ),
					),
					'description' => __( 'Select "animation in" for page transition.', 'rl_conductor' ),
				),
				array(
					'type' => 'animation_style',
					'heading' => __( 'Animation Out', 'rl_conductor' ),
					'param_name' => 'paging_animation_out',
					'group' => __( 'Pagination', 'rl_conductor' ),
					'settings' => array(
						'type' => array( 'out' ),
					),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'pagination' ),
					),
					'description' => __( 'Select "animation out" for page transition.', 'rl_conductor' ),
				),
				array(
					'type' => 'vc_grid_item',
					'heading' => __( 'Grid element template', 'rl_conductor' ),
					'param_name' => 'item',
					'description' => sprintf( __( '%sCreate new%s template or %smodify selected%s. Predefined templates will be cloned.', 'rl_conductor' ), '<a href="' . esc_url( admin_url( 'post-new.php?post_type=vc_grid_item' ) ) . '" target="_blank">', '</a>', '<a href="#" target="_blank" data-vc-grid-item="edit_link">', '</a>' ),
					'group' => __( 'Item Design', 'rl_conductor' ),
					'value' => 'mediaGrid_Default',
				),
				array(
					'type' => 'vc_grid_id',
					'param_name' => 'grid_id',
				),
				array(
					'type' => 'el_id',
					'heading' => __( 'Element ID', 'rl_conductor' ),
					'param_name' => 'el_id',
					'description' => sprintf( __( 'Enter element ID (Note: make sure it is unique and valid according to <a href="%s" target="_blank">w3c specification</a>).', 'rl_conductor' ), 'http://www.w3schools.com/tags/att_global_id.asp' ),
				),
				array(
					'type' => 'textfield',
					'heading' => __( 'Extra class name', 'rl_conductor' ),
					'param_name' => 'el_class',
					'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
				),
				array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', 'rl_conductor' ),
					'param_name' => 'css',
					'group' => __( 'Design Options', 'rl_conductor' ),
				),
			), self::$btn3Params, array(
				// Load more btn bc
				array(
					'type' => 'hidden',
					'heading' => __( 'Button style', 'rl_conductor' ),
					'param_name' => 'button_style',
					'value' => '',
					'param_holder_class' => 'vc_colored-dropdown',
					'group' => __( 'Load More Button', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'load-more' ),
					),
					'description' => __( 'Select button style.', 'rl_conductor' ),
				),
				array(
					'type' => 'hidden',
					'heading' => __( 'Button color', 'rl_conductor' ),
					'param_name' => 'button_color',
					'value' => '',
					'param_holder_class' => 'vc_colored-dropdown',
					'group' => __( 'Load More Button', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'load-more' ),
					),
					'description' => __( 'Select button color.', 'rl_conductor' ),
				),
				array(
					'type' => 'hidden',
					'heading' => __( 'Button size', 'rl_conductor' ),
					'param_name' => 'button_size',
					'value' => '',
					'description' => __( 'Select button size.', 'rl_conductor' ),
					'group' => __( 'Load More Button', 'rl_conductor' ),
					'dependency' => array(
						'element' => 'style',
						'value' => array( 'load-more' ),
					),
				),
				array(
					'type' => 'animation_style',
					'heading' => __( 'Initial loading animation', 'rl_conductor' ),
					'param_name' => 'initial_loading_animation',
					'value' => 'fadeIn',
					'settings' => array(
						'type' => array(
							'in',
							'other',
						),
					),
					'description' => __( 'Select initial loading animation for grid element.', 'rl_conductor' ),
				),
			) );

			self::$mediaGrid = array_merge( self::$mediaGrid );

			return self::$mediaGrid;
		}

		public static function getMasonryCommonAtts() {

			if ( self::$masonryGrid ) {
				return self::$masonryGrid;
			}

			$gridParams = self::getBasicAtts();

			self::$masonryGrid = $gridParams;
			$style = self::arraySearch( self::$masonryGrid, 'param_name', 'style' );
			unset( self::$masonryGrid[ $style ]['value'][ __( 'Pagination', 'rl_conductor' ) ] );

			$animation = self::arraySearch( self::$masonryGrid, 'param_name', 'initial_loading_animation' );
			$masonryAnimation = array(
				'type' => 'dropdown',
				'heading' => __( 'Initial loading animation', 'rl_conductor' ),
				'param_name' => 'initial_loading_animation',
				'value' => array(
					__( 'None', 'rl_conductor' ) => 'none',
					__( 'Default', 'rl_conductor' ) => 'zoomIn',
					__( 'Fade In', 'rl_conductor' ) => 'fadeIn',
				),
				'std' => 'zoomIn',
				'description' => __( 'Select initial loading animation for grid element.', 'rl_conductor' ),
			);
			// unset( self::$masonryGrid[$animation] );
			self::$masonryGrid[ $animation ] = $masonryAnimation;

			while ( $key = self::arraySearch( self::$masonryGrid, 'group', __( 'Pagination', 'rl_conductor' ) ) ) {
				unset( self::$masonryGrid[ $key ] );
			}

			$vcGridItem = self::arraySearch( self::$masonryGrid, 'param_name', 'item' );
			self::$masonryGrid[ $vcGridItem ]['value'] = 'masonryGrid_Default';

			self::$masonryGrid = array_merge( self::$masonryGrid );

			return array_merge( self::$masonryGrid );
		}

		public static function getMasonryMediaCommonAtts() {

			if ( self::$masonryMediaGrid ) {
				return self::$masonryMediaGrid;
			}

			$mediaGridParams = self::getMediaCommonAtts();

			self::$masonryMediaGrid = $mediaGridParams;

			while ( $key = self::arraySearch( self::$masonryMediaGrid, 'group', __( 'Pagination', 'rl_conductor' ) ) ) {
				unset( self::$masonryMediaGrid[ $key ] );
			}

			$vcGridItem = self::arraySearch( self::$masonryMediaGrid, 'param_name', 'item' );
			self::$masonryMediaGrid[ $vcGridItem ]['value'] = 'masonryMedia_Default';

			$style = self::arraySearch( self::$masonryMediaGrid, 'param_name', 'style' );

			unset( self::$masonryMediaGrid[ $style ]['value'][ __( 'Pagination', 'rl_conductor' ) ] );

			$animation = self::arraySearch( self::$masonryMediaGrid, 'param_name', 'initial_loading_animation' );
			$masonryAnimation = array(
				'type' => 'dropdown',
				'heading' => __( 'Initial loading animation', 'rl_conductor' ),
				'param_name' => 'initial_loading_animation',
				'value' => array(
					__( 'None', 'rl_conductor' ) => 'none',
					__( 'Default', 'rl_conductor' ) => 'zoomIn',
					__( 'Fade In', 'rl_conductor' ) => 'fadeIn',
				),
				'std' => 'zoomIn',
				'settings' => array(
					'type' => array(
						'in',
						'other',
					),
				),
				'description' => __( 'Select initial loading animation for grid element.', 'rl_conductor' ),
			);
			self::$masonryMediaGrid[ $animation ] = $masonryAnimation;

			self::$masonryMediaGrid = array_merge( self::$masonryMediaGrid );

			return array_merge( self::$masonryMediaGrid );
		}

		// Function to search array
		public static function arraySearch( $array, $column, $value ) {
			if ( ! is_array( $array ) ) {
				return false;
			}
			foreach ( $array as $key => $innerArray ) {
				$exists = isset( $innerArray[ $column ] ) && $innerArray[ $column ] == $value;
				if ( $exists ) {
					return $key;
				}
			}

			return false;
		}
	} // class ends
}
