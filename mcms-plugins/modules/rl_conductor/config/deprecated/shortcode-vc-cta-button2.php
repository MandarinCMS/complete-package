<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

return array(
	'name' => __( 'Old Call to Action Button', 'rl_conductor' ) . ' 2',
	'base' => 'vc_cta_button2',
	'icon' => 'icon-mcmsb-call-to-action',
	'deprecated' => '4.5',
	'content_element' => false,
	'category' => array( __( 'Content', 'rl_conductor' ) ),
	'description' => __( 'Catch visitors attention with CTA block', 'rl_conductor' ),
	'params' => array(
		array(
			'type' => 'textfield',
			'heading' => __( 'Heading', 'rl_conductor' ),
			'admin_label' => true,
			'param_name' => 'h2',
			'value' => __( 'Hey! I am first heading line feel free to change me', 'rl_conductor' ),
			'description' => __( 'Enter text for heading line.', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Subheading', 'rl_conductor' ),
			'param_name' => 'h4',
			'value' => '',
			'description' => __( 'Enter text for subheading line.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Shape', 'rl_conductor' ),
			'param_name' => 'style',
			'value' => getVcShared( 'cta styles' ),
			'description' => __( 'Select display shape and style.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Width', 'rl_conductor' ),
			'param_name' => 'el_width',
			'value' => getVcShared( 'cta widths' ),
			'description' => __( 'Select element width (percentage).', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Text alignment', 'rl_conductor' ),
			'param_name' => 'txt_align',
			'value' => getVcShared( 'text align' ),
			'description' => __( 'Select text alignment in "Call to Action" block.', 'rl_conductor' ),
		),
		array(
			'type' => 'colorpicker',
			'heading' => __( 'Background color', 'rl_conductor' ),
			'param_name' => 'accent_color',
			'description' => __( 'Select background color.', 'rl_conductor' ),
		),
		array(
			'type' => 'textarea_html',
			'heading' => __( 'Text', 'rl_conductor' ),
			'param_name' => 'content',
			'value' => __( 'I am promo text. Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'rl_conductor' ),
		),
		array(
			'type' => 'vc_link',
			'heading' => __( 'URL (Link)', 'rl_conductor' ),
			'param_name' => 'link',
			'description' => __( 'Add link to button (Important: adding link automatically adds button).', 'rl_conductor' ),
		),
		array(
			'type' => 'textfield',
			'heading' => __( 'Text on the button', 'rl_conductor' ),
			'param_name' => 'title',
			'value' => __( 'Text on the button', 'rl_conductor' ),
			'description' => __( 'Add text on the button.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Shape', 'rl_conductor' ),
			'param_name' => 'btn_style',
			'value' => getVcShared( 'button styles' ),
			'description' => __( 'Select button display style and shape.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Color', 'rl_conductor' ),
			'param_name' => 'color',
			'value' => getVcShared( 'colors' ),
			'description' => __( 'Select button color.', 'rl_conductor' ),
			'param_holder_class' => 'vc_colored-dropdown',
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Size', 'rl_conductor' ),
			'param_name' => 'size',
			'value' => getVcShared( 'sizes' ),
			'std' => 'md',
			'description' => __( 'Select button size.', 'rl_conductor' ),
		),
		array(
			'type' => 'dropdown',
			'heading' => __( 'Button position', 'rl_conductor' ),
			'param_name' => 'position',
			'value' => array(
				__( 'Right', 'rl_conductor' ) => 'right',
				__( 'Left', 'rl_conductor' ) => 'left',
				__( 'Bottom', 'rl_conductor' ) => 'bottom',
			),
			'description' => __( 'Select button alignment.', 'rl_conductor' ),
		),
		vc_map_add_css_animation(),
		array(
			'type' => 'textfield',
			'heading' => __( 'Extra class name', 'rl_conductor' ),
			'param_name' => 'el_class',
			'description' => __( 'Style particular content element differently - add a class name and refer to it in custom CSS.', 'rl_conductor' ),
		),
	),
);
