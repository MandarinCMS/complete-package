<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'vc_gitem_wocommerce' => array(
		'name' => __( 'WooCommerce field', 'rl_conductor' ),
		'base' => 'vc_gitem_wocommerce',
		'icon' => 'icon-mcmsb-woocommerce',
		'category' => __( 'Content', 'rl_conductor' ),
		'description' => __( 'Woocommerce', 'rl_conductor' ),
		'php_class_name' => 'Vc_Gitem_Woocommerce_Shortcode',
		'params' => array(

			array(
				'type' => 'dropdown',
				'heading' => __( 'Content type', 'rl_conductor' ),
				'param_name' => 'post_type',
				'value' => array(
					__( 'Product', 'rl_conductor' ) => 'product',
					__( 'Order', 'rl_conductor' ) => 'order',
				),
				'save_always' => true,
				'description' => __( 'Select Woo Commerce post type.', 'rl_conductor' ),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Product field name', 'rl_conductor' ),
				'param_name' => 'product_field_key',
				'value' => Vc_Vendor_Woocommerce::getProductsFieldsList(),
				'dependency' => array(
					'element' => 'post_type',
					'value' => array( 'product' ),
				),
				'save_always' => true,
				'description' => __( 'Select field from product.', 'rl_conductor' ),
			),
			array(
				'type' => 'textfield',
				'heading' => __( 'Product custom key', 'rl_conductor' ),
				'param_name' => 'product_custom_key',
				'description' => __( 'Enter custom key.', 'rl_conductor' ),
				'dependency' => array(
					'element' => 'product_field_key',
					'value' => array( '_custom_' ),
				),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Order fields', 'rl_conductor' ),
				'param_name' => 'order_field_key',
				'value' => Vc_Vendor_Woocommerce::getOrderFieldsList(),
				'dependency' => array(
					'element' => 'post_type',
					'value' => array( 'order' ),
				),
				'save_always' => true,
				'description' => __( 'Select field from order.', 'rl_conductor' ),
			),
			array(
				'type' => 'textfield',
				'heading' => __( 'Order custom key', 'rl_conductor' ),
				'param_name' => 'order_custom_key',
				'dependency' => array(
					'element' => 'order_field_key',
					'value' => array( '_custom_' ),
				),
				'description' => __( 'Enter custom key.', 'rl_conductor' ),
			),
			array(
				'type' => 'checkbox',
				'heading' => __( 'Show label', 'rl_conductor' ),
				'param_name' => 'show_label',
				'value' => array( __( 'Yes', 'rl_conductor' ) => 'yes' ),
				'save_always' => true,
				'description' => __( 'Enter label to display before key value.', 'rl_conductor' ),
			),
			array(
				'type' => 'dropdown',
				'heading' => __( 'Align', 'rl_conductor' ),
				'param_name' => 'align',
				'value' => array(
					__( 'left', 'rl_conductor' ) => 'left',
					__( 'right', 'rl_conductor' ) => 'right',
					__( 'center', 'rl_conductor' ) => 'center',
					__( 'justify', 'rl_conductor' ) => 'justify',
				),
				'save_always' => true,
				'description' => __( 'Select alignment.', 'rl_conductor' ),
			),
			array(
				'type' => 'textfield',
				'heading' => __( 'Extra class name', 'rl_conductor' ),
				'param_name' => 'el_class',
				'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
			),
		),
		'post_type' => Vc_Grid_Item_Editor::postType(),
	),
);
