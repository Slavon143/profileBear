<?php
/**
 * Avada Options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      4.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * WooCommerce settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_woocommerce( $sections ) {
	$fusion_settings = awb_get_fusion_settings();

	// Check if we have a global header override.
	$has_global_header = false;
	if ( class_exists( 'Fusion_Template_Builder' ) ) {
		$default_layout    = Fusion_Template_Builder::get_default_layout();
		$has_global_header = isset( $default_layout['data']['template_terms'] ) && isset( $default_layout['data']['template_terms']['header'] ) && $default_layout['data']['template_terms']['header'];
	}

	$body_typography = $fusion_settings->get( 'body_typography' );

	$sections['woocommerce'] = ( Avada::$is_updating || class_exists( 'WooCommerce' ) ) ? [
		'label'    => esc_html__( 'WooCommerce', 'Avada' ),
		'id'       => 'heading_woocommerce',
		'priority' => 26,
		'icon'     => 'el-icon-shopping-cart',
		'alt_icon' => 'fusiona-cart',
		'fields'   => [
			'general_woocommerce_options_subsection' => [
				'label'       => esc_html__( 'General WooCommerce', 'Avada' ),
				'description' => '',
				'id'          => 'general_woocommerce_options_subsection',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'woo_items' => [
						'label'       => esc_html__( 'WooCommerce Number of Products per Page', 'Avada' ),
						'description' => esc_html__( 'Controls the number of products that display per page. ', 'Avada' ),
						'id'          => 'woo_items',
						'default'     => '12',
						'type'        => 'slider',
						'choices'     => [
							'min'  => '1',
							'max'  => '50',
							'step' => '1',
						],
					],
					'woocommerce_shop_page_columns' => [
						'label'           => esc_html__( 'WooCommerce Number of Product Columns', 'Avada' ),
						'description'     => esc_html__( 'Controls the number of columns for the main shop page.', 'Avada' ),
						'id'              => 'woocommerce_shop_page_columns',
						'default'         => 4,
						'type'            => 'slider',
						'choices'     => [
							'min'  => 1,
							'max'  => 6,
							'step' => 1,
						],
						'update_callback' => [
							[
								'condition' => 'is_shop',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'woocommerce_related_columns' => [
						'label'           => esc_html__( 'WooCommerce Related/Up-Sell/Cross-Sell Product Number of Columns', 'Avada' ),
						'description'     => esc_html__( 'Controls the number of columns for the related and up-sell products on single posts and cross-sells on cart page.', 'Avada' ),
						'id'              => 'woocommerce_related_columns',
						'default'         => 4,
						'type'            => 'slider',
						'choices'     => [
							'min'  => 0,
							'max'  => 6,
							'step' => 1,
						],
						'transport'   => 'refresh',
						'output'      => [
							[
								'element'       => 'body',
								'function'      => 'attr',
								'attr'          => 'class',
								'value_pattern' => 'fusion-woo-related-columns-$',
								'remove_attrs'  => [ 'fusion-woo-related-columns-1', 'fusion-woo-related-columns-2', 'fusion-woo-related-columns-3', 'fusion-woo-related-columns-4', 'fusion-woo-related-columns-5', 'fusion-woo-related-columns-6' ],
							],
						],
					],
					'woocommerce_archive_page_columns' => [
						'label'           => esc_html__( 'WooCommerce Archive Number of Product Columns', 'Avada' ),
						'description'     => esc_html__( 'Controls the number of columns for the archive pages.', 'Avada' ),
						'id'              => 'woocommerce_archive_page_columns',
						'default'         => 3,
						'type'            => 'slider',
						'choices'     => [
							'min'  => 1,
							'max'  => 6,
							'step' => 1,
						],
					],
					'woocommerce_archive_grid_column_spacing'             => [
						'label'           => esc_html__( 'Column Spacing', 'Avada' ),
						'description'     => esc_html__( 'Controls the column spacing between products on WooCommerce product archives.', 'Avada' ),
						'id'              => 'woocommerce_archive_grid_column_spacing',
						'default'         => '40',
						'type'            => 'slider',
						'choices'         => [
							'min'  => '0',
							'step' => '1',
							'max'  => '300',
							'edit' => 'yes',
						],
						'css_vars'        => [
							[
								'name'          => '--woocommerce_archive_grid_column_spacing',
								'value_pattern' => '$px',
							],
						],
					],
					'woocommerce_product_images_layout' => [
						'label'       => esc_html__( 'WooCommerce Product Images Layout', 'Avada' ),
						'description' => esc_html__( 'Set the layout for your product images.', 'Avada' ),
						'id'          => 'woocommerce_product_images_layout',
						'type'        => 'radio-buttonset',
						'default'     => 'avada',
						'choices'     => [
							'avada'       => esc_html__( 'Avada', 'Avada' ),
							'woocommerce' => esc_html__( 'WooCommerce', 'Avada' ),
						],
					],
					'woocommerce_product_images_zoom' => [
						'label'           => esc_html__( 'WooCommerce Product Images Zoom', 'Avada' ),
						'description'     => __( 'Turn on to enable the WooCommerce product images zoom feature. <strong>IMPORTANT NOTE:</strong> Every product image you use must be larger than the product images container for zoom to work correctly. <a href="https://theme-fusion.com/documentation/avada/woocommerce-single-product-gallery/" target="_blank">See this post for more information.</a>', 'Avada' ),
						'id'              => 'woocommerce_product_images_zoom',
						'default'         => '1',
						'type'            => 'switch',
					],
					'woocommerce_single_gallery_size' => [
						'label'       => esc_html__( 'WooCommerce Product Images Width', 'Avada' ),
						'description' => __( 'Controls the width of the single product page image gallery. For the image gallery zoom feature to work, the images you upload must be larger than the gallery size you select for this option. <strong>IMPORTANT NOTE:</strong> When this option is changed, you may need to adjust the Single Product Image size setting in WooCommerce Settings to make sure that one is larger and also regenerate thumbnails. <a href="https://theme-fusion.com/documentation/avada/woocommerce-single-product-gallery/" target="_blank">See this post for more information.</a><br/>', 'Avada' ),
						'id'          => 'woocommerce_single_gallery_size',
						'default'     => '500px',
						'type'        => 'dimension',
						'choices'     => [ 'px' ],
						'css_vars'    => [
							[
								'name'    => '--woocommerce_single_gallery_size',
								'element' => '.avada-product-images-global .woocommerce-product-gallery',
							],
						],
						'output'      => [
							[
								'element'       => '.ltr .product .summary.entry-summary',
								'property'      => 'margin-left',
								'value_pattern' => 'calc($ + 30px)',
							],
							[
								'element'       => '.rtl .product .summary.entry-summary',
								'property'      => 'margin-right',
								'value_pattern' => 'calc($ + 30px)',
							],
						],
					],
					'woocommerce_gallery_thumbnail_width' => [
						'label'           => esc_html__( 'WooCommerce Product Images Thumbnail Width', 'Avada' ),
						'description'     => __( 'Controls the natural image width of product page image gallery thumbnails. <strong>IMPORTANT NOTE:</strong> When this option is changed, you need to make sure to regenerate thumbnails. <a href="https://theme-fusion.com/documentation/avada/woocommerce-single-product-gallery/" target="_blank">See this post for more information.</a><br/>', 'Avada' ),
						'id'              => 'woocommerce_gallery_thumbnail_width',
						'default'         => 200,
						'type'            => 'slider',
						'choices'         => [
							'min'  => 50,
							'max'  => 400,
							'step' => 1,
						],
						'update_callback' => [
							[
								'condition' => 'is_product',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'woocommerce_product_images_thumbnail_position' => [
						'label'       => esc_html__( 'WooCommerce Product Images Thumbnail Position', 'Avada' ),
						'description' => esc_html__( 'Set the position of the product page image gallery thumbnails with respect to the main gallery images.', 'Avada' ),
						'id'          => 'woocommerce_product_images_thumbnail_position',
						'type'        => 'radio-buttonset',
						'default'     => 'bottom',
						'choices'     => [
							'top'    => esc_html__( 'Top', 'fusion-builder' ),
							'right'  => esc_html__( 'Right', 'fusion-builder' ),
							'bottom' => esc_html__( 'Bottom', 'fusion-builder' ),
							'left'   => esc_html__( 'Left', 'fusion-builder' ),
						],
						'required'        => [
							[
								'setting'  => 'woocommerce_product_images_layout',
								'operator' => '==',
								'value'    => 'avada',
							],
						],
					],
					'woocommerce_product_images_thumbnail_column_width' => [
						'label'           => esc_html__( 'WooCommerce Product Images Thumbnail Column Width', 'Avada' ),
						'description'     => esc_html__( 'Controls the width of the left/right column of product images thumbnails as a percentage of the full gallery width.', 'Avada' ),
						'id'              => 'woocommerce_product_images_thumbnail_column_width',
						'default'         => 15,
						'type'            => 'slider',
						'choices'         => [
							'min'  => 1,
							'max'  => 100,
							'step' => 1,
						],
						'css_vars'    => [
							[
								'name'          => '--woocommerce_product_images_thumbnail_column_width',
								'element'       => '.avada-product-images-global .flex-control-thumbs',
								'value_pattern' => '$%',
							],
						],
						'class'           => 'fusion-gutter-and-or-and',
						'required'        => [
							[
								'setting'  => 'woocommerce_product_images_layout',
								'operator' => '==',
								'value'    => 'avada',
							],
							[
								'setting'  => 'woocommerce_product_images_thumbnail_position',
								'operator' => '==',
								'value'    => 'right',
							],
							[
								'setting'  => 'woocommerce_product_images_layout',
								'operator' => '==',
								'value'    => 'avada',
							],
							[
								'setting'  => 'woocommerce_product_images_thumbnail_position',
								'operator' => '==',
								'value'    => 'left',
							],
						],
						'update_callback' => [
							[
								'condition' => 'is_product',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'woocommerce_gallery_thumbnail_columns' => [
						'label'           => esc_html__( 'WooCommerce Product Images Thumbnails Columns', 'Avada' ),
						'description'     => esc_html__( 'Controls the number of columns of the single product page image gallery thumbnails. In order to avoid blurry thumbnails, make sure the "WooCommerce Product Images Thumbnails Width" option above is large enough. It has to be at least "WooCommerce Product Images Width" option divided by this number of columns.', 'Avada' ),
						'id'              => 'woocommerce_gallery_thumbnail_columns',
						'default'         => 4,
						'type'            => 'slider',
						'choices'         => [
							'min'  => 1,
							'max'  => 6,
							'step' => 1,
						],
						'class'           => 'fusion-gutter-and-or-and-or',
						'required'        => [
							[
								'setting'  => 'woocommerce_product_images_layout',
								'operator' => '==',
								'value'    => 'avada',
							],
							[
								'setting'  => 'woocommerce_product_images_thumbnail_position',
								'operator' => '==',
								'value'    => 'top',
							],
							[
								'setting'  => 'woocommerce_product_images_layout',
								'operator' => '==',
								'value'    => 'avada',
							],
							[
								'setting'  => 'woocommerce_product_images_thumbnail_position',
								'operator' => '==',
								'value'    => 'bottom',
							],
							[
								'setting'  => 'woocommerce_product_images_layout',
								'operator' => '==',
								'value'    => 'woocommerce',
							],
						],
						'update_callback' => [
							[
								'condition' => 'is_product',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'woocommerce_enable_quick_view' => [
						'label'           => esc_html__( 'WooCommerce Product Quick View', 'Avada' ),
						'description'     => esc_html__( 'Turn on to enable product quick view for products.', 'Avada' ),
						'id'              => 'woocommerce_enable_quick_view',
						'default'         => '0',
						'type'            => 'switch',
					],
					'woocommerce_variations' => [
						'label'           => esc_html__( 'WooCommerce Product Variation Swatches', 'Avada' ),
						'description'     => esc_html__( 'Turn on to enable color, button and image variation types.', 'Avada' ),
						'id'              => 'woocommerce_variations',
						'default'         => '1',
						'type'            => 'switch',
					],
					'woocommerce_avada_ordering' => [
						'label'           => esc_html__( 'WooCommerce Shop Page Ordering Boxes', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the ordering boxes on the shop page.', 'Avada' ),
						'id'              => 'woocommerce_avada_ordering',
						'default'         => '1',
						'type'            => 'switch',
					],
					'woocommerce_disable_crossfade_effect' => [
						'label'           => esc_html__( 'WooCommerce Shop Page Crossfade Image Effect', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the product crossfade image effect on the shop page.', 'Avada' ),
						'id'              => 'woocommerce_disable_crossfade_effect',
						'default'         => '1',
						'type'            => 'switch',
					],
					'woocommerce_one_page_checkout' => [
						'label'           => esc_html__( 'WooCommerce One Page Checkout', 'Avada' ),
						'description'     => esc_html__( 'Turn on to use the one page checkout template.', 'Avada' ),
						'id'              => 'woocommerce_one_page_checkout',
						'default'         => '0',
						'type'            => 'switch',
						'update_callback' => [
							[
								'condition' => 'is_checkout',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'woocommerce_enable_order_notes' => [
						'label'           => esc_html__( 'WooCommerce Order Notes on Checkout', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the order notes on the checkout page.', 'Avada' ),
						'id'              => 'woocommerce_enable_order_notes',
						'default'         => '1',
						'type'            => 'switch',
						'update_callback' => [
							[
								'condition' => 'is_checkout',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'woocommerce_acc_link_main_nav' => [
						'label'           => esc_html__( 'WooCommerce My Account Link in Main Menu', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the "My Account" link in the main menu. Not compatible with Ubermenu.', 'Avada' ),
						'id'              => 'woocommerce_acc_link_main_nav',
						'default'         => '0',
						'hidden'          =>  $has_global_header,
						'type'            => 'switch',
						'class'       => 'fusion-or-gutter',
						// Partial refresh for the header.
						'partial_refresh' => [
							'header_content_woocommerce_acc_link_main_nav_remove_before_hook' => [
								'selector'            => '.avada-hook-before-header-wrapper',
								'container_inclusive' => true,
								'render_callback'     => '__return_null',
							],
							'header_content_woocommerce_acc_link_main_nav_remove_after_hook' => [
								'selector'            => '.avada-hook-after-header-wrapper',
								'container_inclusive' => true,
								'render_callback'     => '__return_null',
							],
							'header_content_woocommerce_acc_link_main_nav' => [
								'selector'              => '.fusion-header-wrapper',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
								'success_trigger_event' => 'header-rendered',
							],
						],
					],
					'woocommerce_cart_link_main_nav' => [
						'label'           => esc_html__( 'WooCommerce Cart Icon in Main Menu', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the cart icon in the main menu. Not compatible with Ubermenu.', 'Avada' ),
						'id'              => 'woocommerce_cart_link_main_nav',
						'default'         => '1',
						'hidden'          =>  $has_global_header,
						'type'            => 'switch',
						// Partial refresh for the header.
						'partial_refresh' => [
							'header_content_woocommerce_cart_link_main_nav_remove_before_hook' => [
								'selector'            => '.avada-hook-before-header-wrapper',
								'container_inclusive' => true,
								'render_callback'     => '__return_null',
							],
							'header_content_woocommerce_cart_link_main_nav_remove_after_hook' => [
								'selector'            => '.avada-hook-after-header-wrapper',
								'container_inclusive' => true,
								'render_callback'     => '__return_null',
							],
							'header_content_woocommerce_cart_link_main_nav' => [
								'selector'              => '.fusion-header-wrapper',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
								'success_trigger_event' => 'header-rendered',
							],
						],
					],
					'woocommerce_cart_counter' => [
						'label'       => esc_html__( 'WooCommerce Menu Cart Icon Counter', 'Avada' ),
						'description' => esc_html__( 'Turn on to display the WooCommerce cart counter circle.', 'Avada' ),
						'id'          => 'woocommerce_cart_counter',
						'default'     => '0',
						'hidden'      =>  $has_global_header,
						'type'        => 'switch',
						// Partial refresh for the header.
						'partial_refresh' => [
							'header_content_woocommerce_cart_counter_remove_before_hook' => [
								'selector'            => '.avada-hook-before-header-wrapper',
								'container_inclusive' => true,
								'render_callback'     => '__return_null',
							],
							'header_content_woocommerce_cart_counter_remove_after_hook' => [
								'selector'            => '.avada-hook-after-header-wrapper',
								'container_inclusive' => true,
								'render_callback'     => '__return_null',
							],
							'header_content_woocommerce_cart_counter' => [
								'selector'              => '.fusion-header-wrapper',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
								'success_trigger_event' => 'header-rendered',
							],
						],
					],
					'woocommerce_acc_link_top_nav' => [
						'label'           => esc_html__( 'WooCommerce My Account Link in Secondary Menu', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the "My Account" link in the secondary menu. Not compatible with Ubermenu.', 'Avada' ),
						'id'              => 'woocommerce_acc_link_top_nav',
						'default'         => '1',
						'type'            => 'switch',
						'class'           => 'fusion-or-gutter',
						'required'        => [
							[
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'top',
							],
							[
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							],
							[
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							],
							[
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							],
							[
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							],
						],
						// Partial refresh for the header.
						'partial_refresh' => [
							'header_content_woocommerce_acc_link_top_nav_remove_before_hook' => [
								'selector'            => '.avada-hook-before-header-wrapper',
								'container_inclusive' => true,
								'render_callback'     => '__return_null',
							],
							'header_content_woocommerce_acc_link_top_nav_remove_after_hook' => [
								'selector'            => '.avada-hook-after-header-wrapper',
								'container_inclusive' => true,
								'render_callback'     => '__return_null',
							],
							'header_content_woocommerce_acc_link_top_nav' => [
								'selector'              => '.fusion-header-wrapper',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
								'success_trigger_event' => 'header-rendered',
							],
						],
					],
					'woocommerce_cart_link_top_nav' => [
						'label'           => esc_html__( 'WooCommerce Cart Icon in Secondary Menu', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the cart icon in the secondary menu. Not compatible with Ubermenu.', 'Avada' ),
						'id'              => 'woocommerce_cart_link_top_nav',
						'default'         => '1',
						'type'            => 'switch',
						'class'           => 'fusion-or-gutter',
						'required'        => [
							[
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'top',
							],
							[
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v2',
							],
							[
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v3',
							],
							[
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v4',
							],
							[
								'setting'  => 'header_layout',
								'operator' => '=',
								'value'    => 'v5',
							],
						],
						// Partial refresh for the header.
						'partial_refresh' => [
							'header_content_woocommerce_cart_link_top_nav_remove_before_hook' => [
								'selector'            => '.avada-hook-before-header-wrapper',
								'container_inclusive' => true,
								'render_callback'     => '__return_null',
							],
							'header_content_woocommerce_cart_link_top_nav_remove_after_hook' => [
								'selector'            => '.avada-hook-after-header-wrapper',
								'container_inclusive' => true,
								'render_callback'     => '__return_null',
							],
							'header_content_woocommerce_cart_link_top_nav' => [
								'selector'              => '.fusion-header-wrapper',
								'container_inclusive'   => true,
								'render_callback'       => [ 'Avada_Partial_Refresh_Callbacks', 'header' ],
								'success_trigger_event' => 'header-rendered',
							],
						],
					],
					'woocommerce_social_links' => [
						'label'           => esc_html__( 'WooCommerce Social Icons', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the social icons on single product posts.', 'Avada' ),
						'id'              => 'woocommerce_social_links',
						'default'         => '1',
						'type'            => 'switch',
						'update_callback' => [
							[
								'condition' => 'is_product',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'woocommerce_toggle_grid_list' => [
						'label'           => esc_html__( 'WooCommerce Product Grid / List View', 'Avada' ),
						'description'     => esc_html__( 'Turn on to display the grid/list toggle on the main shop page and archive shop pages.', 'Avada' ),
						'id'              => 'woocommerce_toggle_grid_list',
						'default'         => '1',
						'type'            => 'switch',
					],
					'woocommerce_product_view' => [
						'type'        => 'radio-buttonset',
						'label'       => esc_html__( 'WooCommerce Product Default View', 'Avada' ),
						'description' => esc_html__( 'Sets the default product view for shop page and product archive pages.', 'Avada' ),
						'id'          => 'woocommerce_product_view',
						'default'     => 'grid',
						'choices'     => [
							'grid' => esc_html__( 'Grid', 'Avada' ),
							'list' => esc_html__( 'List', 'Avada' ),
						],
					],
					'woo_acc_msg_1' => [
						'label'           => esc_html__( 'WooCommerce Account Area Message 1', 'Avada' ),
						'description'     => esc_html__( 'Controls the text that displays in the first message box on the account page.', 'Avada' ),
						'id'              => 'woo_acc_msg_1',
						'default'         => 'Need Assistance? Call customer service at 888-555-5555.',
						'type'            => 'textarea',
						'partial_refresh' => [
							'partial_woo_acc_msg_1' => [
								'selector'            => '.avada-myaccount-user',
								'container_inclusive' => true,
								'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'wc_top_user_container' ],
							],
						],
					],
					'woo_acc_msg_2' => [
						'label'           => esc_html__( 'WooCommerce Account Area Message 2', 'Avada' ),
						'description'     => esc_html__( 'Controls the text that displays in the second message box on the account page.', 'Avada' ),
						'id'              => 'woo_acc_msg_2',
						'default'         => 'E-mail them at info@yourshop.com',
						'type'            => 'textarea',
						'partial_refresh' => [
							'partial_woo_acc_msg_2' => [
								'selector'            => '.avada-myaccount-user',
								'container_inclusive' => true,
								'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'wc_top_user_container' ],
							],
						],
					],
				],
			],
			'woocommerce_styling_options_subsection' => [
				'label'       => esc_html__( 'WooCommerce Styling', 'Avada' ),
				'description' => '',
				'id'          => 'woocommerce_styling_options_subsection',
				'icon'        => true,
				'type'        => 'sub-section',
				'fields'      => [
					'woocommerce_product_box_design' => [
						'type'        => 'radio-buttonset',
						'label'       => esc_html__( 'WooCommerce Product Box Design', 'Avada' ),
						'description' => esc_html__( 'Controls the design of the product boxes.', 'Avada' ),
						'id'          => 'woocommerce_product_box_design',
						'default'     => 'classic',
						'choices'     => [
							'classic' => esc_html__( 'Classic', 'Avada' ),
							'clean'   => esc_html__( 'Clean', 'Avada' ),
						],
					],
					'woocommerce_product_box_content_padding' => [
						'label'       => esc_html__( 'WooCommerce Product Box Content Padding', 'Avada' ),
						'description' => esc_html__( 'Controls the top/right/bottom/left padding of the products contents.', 'Avada' ),
						'id'          => 'woocommerce_product_box_content_padding',
						'choices'     => [
							'top'    => true,
							'bottom' => true,
							'left'   => true,
							'right'  => true,
						],
						'default'     => [
							'top'    => '20px',
							'bottom' => '15px',
							'left'   => '15px',
							'right'  => '15px',
						],
						'type'        => 'spacing',
						'css_vars'    => [
							[
								'name'   => '--woocommerce_product_box_content_padding-top',
								'choice' => 'top',
							],
							[
								'name'   => '--woocommerce_product_box_content_padding-bottom',
								'choice' => 'bottom',
							],
							[
								'name'   => '--woocommerce_product_box_content_padding-left',
								'choice' => 'left',
							],
							[
								'name'   => '--woocommerce_product_box_content_padding-right',
								'choice' => 'right',
							],
						],
					],
					'product_width_100' => [
						'label'       => esc_html__( '100% Width Page', 'Avada' ),
						'description' => esc_html__( 'Turn on to display product posts at 100% browser width according to the window size. Turn off to follow site width.', 'Avada' ),
						'id'          => 'product_width_100',
						'default'     => '0',
						'type'        => 'switch',
						'update_callback' => [
							[
								'condition' => 'is_product',
								'operator'  => '===',
								'value'     => true,
							],
						],
					],
					'woocommerce_equal_heights' => [
						'label'       => esc_html__( 'Equal Heights', 'Avada' ),
						'description' => esc_html__( 'Turn on to display grid boxes with equal heights per row.', 'Avada' ),
						'id'          => 'woocommerce_equal_heights',
						'default'     => 0,
						'type'        => 'switch',
						'output'      => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ '', 'true' ],
										'element'   => 'body',
										'className' => 'fusion-woocommerce-equal-heights',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'woocommerce_product_tab_design' => [
						'label'           => esc_html__( 'WooCommerce Tab Design', 'Avada' ),
						'description'     => esc_html__( 'Controls the design of all WooCommerce tabs.', 'Avada' ),
						'id'              => 'woocommerce_product_tab_design',
						'default'         => 'vertical',
						'type'            => 'radio-buttonset',
						'choices'     => [
							'horizontal' => esc_html__( 'Horizontal Tabs', 'Avada' ),
							'vertical'   => esc_html__( 'Vertical Tabs', 'Avada' ),
						],
						'output'      => [
							[
								'element'       => 'body',
								'function'      => 'attr',
								'attr'          => 'class',
								'value_pattern' => 'woo-tabs-$',
								'remove_attrs'  => [ 'woo-tabs-vertical', 'woo-tabs-horizontal' ],
							],
						],
					],
					'qty_size'                    => [
						'label'       => esc_html__( 'WooCommerce Quantity Input Size', 'Avada' ),
						'description' => esc_html__( 'Controls the width and height of the quantity input field.', 'Avada' ),
						'id'          => 'qty_size',
						'units'       => false,
						'default'     => [
							'width'  => '38px',
							'height' => '36px',
						],
						'type'        => 'dimensions',
						'css_vars'    => [
							[
								'name'   => '--qty_size-width',
								'choice' => 'width',
							],
							[
								'name'   => '--qty_size-height',
								'choice' => 'height',
							],
						],
					],
					'qty_font_size' => [
						'label'           => esc_html__( 'WooCommerce Quantity Font Size', 'Avada' ),
						'description'     => esc_html__( 'Controls the font size of the quantity input field.', 'Avada' ),
						'id'              => 'qty_font_size',
						'type'            => 'dimension',
						'default'         => '14px',
						'css_vars'        => [
							[
								'name' => '--qty_font_size',
							],
						],
					],
					'qty_bg_color' => [
						'label'           => esc_html__( 'WooCommerce Quantity Box Background Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the background color of the WooCommerce quantity box.', 'Avada' ),
						'id'              => 'qty_bg_color',
						'default'         => 'var(--awb-color2)',
						'type'            => 'color-alpha',
						'css_vars'        => [
							[
								'name' => '--qty_bg_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'qty_bg_hover_color' => [
						'label'           => esc_html__( 'WooCommerce Quantity Box Hover Background Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the hover color of the WooCommerce quantity box.', 'Avada' ),
						'id'              => 'qty_bg_hover_color',
						'default'         => 'var(--awb-color1)',
						'type'            => 'color-alpha',
						'css_vars'        => [
							[
								'name' => '--qty_bg_hover_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'woo_dropdown_bg_color' => [
						'label'           => esc_html__( 'WooCommerce Order Dropdown Background Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the background color of the WooCommerce order dropdowns.', 'Avada' ),
						'id'              => 'woo_dropdown_bg_color',
						'default'         => 'var(--awb-color2)',
						'type'            => 'color-alpha',
						'css_vars'        => [
							[
								'name' => '--woo_dropdown_bg_color',
								'callback' => [ 'sanitize_color' ],
							],
							[
								'name'     => '--woo_dropdown_bg_color-1l',
								'callback' => [ 'lightness_adjust', 0.15 ],
							],
						],
					],
					'woo_dropdown_text_color' => [
						'label'           => esc_html__( 'WooCommerce Order Dropdown Text Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the color of the text and icons in the WooCommerce order dropdowns.', 'Avada' ),
						'id'              => 'woo_dropdown_text_color',
						'default'         => 'var(--awb-color8)',
						'type'            => 'color-alpha',
						'css_vars'        => [
							[
								'name' => '--woo_dropdown_text_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'woo_dropdown_border_color' => [
						'label'           => esc_html__( 'WooCommerce Order Dropdown Border Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the border color in the WooCommerce order dropdowns.', 'Avada' ),
						'id'              => 'woo_dropdown_border_color',
						'default'         => 'var(--awb-color3)',
						'type'            => 'color-alpha',
						'css_vars'        => [
							[
								'name' => '--woo_dropdown_border_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'woo_cart_bg_color' => [
						'label'           => esc_html__( 'WooCommerce Cart Menu Background Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the bottom section background color of the WooCommerce cart dropdown.', 'Avada' ),
						'id'              => 'woo_cart_bg_color',
						'default'         => 'var(--awb-color2)',
						'type'            => 'color-alpha',
						'class'           => 'fusion-or-gutter',
						'required'        => [
							[
								'setting'  => 'header_position',
								'operator' => '!=',
								'value'    => 'top',
							],
							[
								'setting'  => 'header_layout',
								'operator' => '!=',
								'value'    => 'v6',
							],
						],
						'css_vars'        => [
							[
								'name' => '--woo_cart_bg_color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'woo_icon_font_size' => [
						'label'           => esc_html__( 'WooCommerce Icon Font Size', 'Avada' ),
						'description'     => esc_html__( 'Controls the font size of the WooCommerce icons.', 'Avada' ),
						'id'              => 'woo_icon_font_size',
						'type'            => 'dimension',
						'default'         => '12px',
						'css_vars'        => [
							[
								'name' => '--woo_icon_font_size',
							],
						],
					],
					'woo_sale_badge_section'             => [
						'label' => esc_html__( 'Sale Badge', 'Avada' ),
						'id'    => 'woo_sale_badge_section',
						'icon'  => true,
						'type'  => 'info',
					],
					'woo_sale_badge_shape' => [
						'label'           => esc_html__( 'WooCommerce Sale Badge Shape', 'Avada' ),
						'description'     => esc_html__( 'Controls the shape of the sale badge.', 'Avada' ),
						'id'              => 'woo_sale_badge_shape',
						'type'            => 'radio-buttonset',
						'default'     => 'circle',
						'choices'     => [
							'rectangle' => esc_html__( 'Rectangle', 'Avada' ),
							'circle'    => esc_html__( 'Circle', 'Avada' ),
						],
						'output'      => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ 'circle', '===' ],
										'element'   => 'body',
										'className' => 'woo-sale-badge-circle',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'woo_sale_badge_bg_color' => [
						'label'           => esc_html__( 'WooCommerce Sale Badge Background Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the background color of the WooCommerce sale badge.', 'Avada' ),
						'id'              => 'woo_sale_badge_bg_color',
						'default'         => 'var(--awb-color5)',
						'type'            => 'color-alpha',
						'css_vars'        => [
							[
								'name' => '--fusion-woo-sale-badge-background-color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'woo_sale_badge_text_color' => [
						'label'           => esc_html__( 'WooCommerce Sale Badge Text Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the text color of the WooCommerce sale badge.', 'Avada' ),
						'id'              => 'woo_sale_badge_text_color',
						'default'         => 'var(--awb-color1)',
						'type'            => 'color-alpha',
						'css_vars'        => [
							[
								'name' => '--fusion-woo-sale-badge-text-color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'woo_sale_badge_text_size' => [
						'label'           => esc_html__( 'WooCommerce Sale Badge Text Size', 'Avada' ),
						'description'     => esc_html__( 'Controls the font size of the WooCommerce sale badge text.', 'Avada' ),
						'id'              => 'woo_sale_badge_text_size',
						'type'            => 'dimension',
						'default'         => isset( $body_typography['font-size'] ) ? $body_typography['font-size'] : '16px',
						'css_vars'        => [
							[
								'name' => '--fusion-woo-sale-badge-text-size',
							],
						],
					],
					'woo_sale_badge_padding' => [
						'label'       => esc_html__( 'WooCommerce Sale Badge Padding', 'Avada' ),
						'description' => esc_html__( 'Controls the top/right/bottom/left padding of the sale badge.', 'Avada' ),
						'id'          => 'woo_sale_badge_padding',
						'choices'     => [
							'top'    => true,
							'bottom' => true,
							'left'   => true,
							'right'  => true,
						],
						'default'     => [
							'top'    => '0.5em',
							'bottom' => '0.5em',
							'left'   => '0.5em',
							'right'  => '0.5em',
						],
						'type'        => 'spacing',
						'css_vars'    => [
							[
								'name'   => '--fusion-woo-sale-badge-padding-top',
								'choice' => 'top',
							],
							[
								'name'   => '--fusion-woo-sale-badge-padding-bottom',
								'choice' => 'bottom',
							],
							[
								'name'   => '--fusion-woo-sale-badge-padding-left',
								'choice' => 'left',
							],
							[
								'name'   => '--fusion-woo-sale-badge-padding-right',
								'choice' => 'right',
							],
						],
						'transport'   => 'postMessage',
						'required'        => [
							[
								'setting'  => 'woo_sale_badge_shape',
								'operator' => '!=',
								'value'    => 'circle',
							],
						],
					],
					'woo_sale_badge_border_radius'        => [
						'type'        => 'border_radius',
						'label'       => esc_html__( 'WooCommerce Sale Badge Border Radius', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the border radius of sale badge.', 'Avada' ),
						'id'          => 'woo_sale_badge_border_radius',
						'choices'     => [
							'top_left'     => true,
							'top_right'    => true,
							'bottom_right' => true,
							'bottom_left'  => true,
							'units'        => [ 'px', '%', 'em' ],
						],
						'default'     => [
							'top_left'     => '50%',
							'top_right'    => '50%',
							'bottom_right' => '50%',
							'bottom_left'  => '50%',
						],
						'css_vars'    => [
							[
								'name'    => '--fusion-woo-sale-badge-border-top-left-radius',
								'choice'  => 'top_left',
								'element' => 'body',
							],
							[
								'name'    => '--fusion-woo-sale-badge-border-top-right-radius',
								'choice'  => 'top_right',
								'element' => 'body',
							],
							[
								'name'    => '--fusion-woo-sale-badge-border-bottom-right-radius',
								'choice'  => 'bottom_right',
								'element' => 'body',
							],
							[
								'name'    => '--fusion-woo-sale-badge-border-bottom-left-radius',
								'choice'  => 'bottom_left',
								'element' => 'body',
							],
						],

						// Could update variable here, but does not look necessary as set inline.
						'transport'   => 'postMessage',
						'required'        => [
							[
								'setting'  => 'woo_sale_badge_shape',
								'operator' => '!=',
								'value'    => 'circle',
							],
						],
					],
					'woo_sale_badge_border_width'                 => [
						'label'       => esc_html__( 'WooCommerce Sale Badge Border Size', 'Avada' ),
						'description' => esc_html__( 'Controls the border size of the sale badge.', 'Avada' ),
						'id'          => 'woo_sale_badge_border_width',
						'choices'     => [
							'top'    => true,
							'bottom' => true,
							'left'   => true,
							'right'  => true,
						],
						'default'     => [
							'top'    => '0px',
							'bottom' => '0px',
							'left'   => '0px',
							'right'  => '0px',
						],
						'type'        => 'spacing',
						'css_vars'    => [
							[
								'name'   => '--fusion-woo-sale-badge-width-top',
								'choice' => 'top',
							],
							[
								'name'   => '--fusion-woo-sale-badge-width-bottom',
								'choice' => 'bottom',
							],
							[
								'name'   => '--fusion-woo-sale-badge-width-left',
								'choice' => 'left',
							],
							[
								'name'   => '--fusion-woo-sale-badge-width-right',
								'choice' => 'right',
							],
						],
					],
					'woo_sale_badge_border_color'                 => [
						'label'           => esc_html__( 'WooCommerce Sale Badge Border Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the border color of the sale badge', 'Avada' ),
						'id'              => 'woo_sale_badge_border_color',
						'default'         => '',
						'type'            => 'color-alpha',
						'css_vars'        => [
							[
								'name'     => '--fusion-woo-sale-badge-border-color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'woo_sale_badge_text' => [
						'label'           => esc_html__( 'WooCommerce Sale Badge Text', 'Avada' ),
						'description'     => esc_html__( '[percentage] and [value] placeholders can be used to display product discount as percentage or as a value, ex: [percentage] Off!', 'Avada' ),
						'id'              => 'woo_sale_badge_text',
						'default'         => __( 'Sale!', 'Avada' ),
						'type'            => 'text',
					],
					'woo_outofstock_badge_section'             => [
						'label' => esc_html__( 'Out Of Stock Badge', 'Avada' ),
						'id'    => 'woo_outofstock_badge_section',
						'icon'  => true,
						'type'  => 'info',
					],
					'woo_outofstock_badge_shape' => [
						'label'           => esc_html__( 'WooCommerce Out Of Stock Badge Shape', 'Avada' ),
						'description'     => esc_html__( 'Controls the shape of the out of stock badge.', 'Avada' ),
						'id'              => 'woo_outofstock_badge_shape',
						'type'            => 'radio-buttonset',
						'default'     => 'top_bar',
						'choices'     => [
							'top_bar' => esc_html__( 'Top Bar', 'Avada' ),
							'rectangle' => esc_html__( 'Rectangle', 'Avada' ),
							'circle'    => esc_html__( 'Circle', 'Avada' ),
						],
						'output'      => [
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ 'circle', '===' ],
										'element'   => 'body',
										'className' => 'woo-outofstock-badge-circle',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ 'rectangle', '===' ],
										'element'   => 'body',
										'className' => 'woo-outofstock-badge-rectangle',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
							[
								'element'           => 'helperElement',
								'property'          => 'dummy',
								'callback'          => [
									'toggle_class',
									[
										'condition' => [ 'top_bar', '===' ],
										'element'   => 'body',
										'className' => 'woo-outofstock-badge-top_bar',
									],
								],
								'sanitize_callback' => '__return_empty_string',
							],
						],
					],
					'woo_outofstock_badge_bg_color' => [
						'label'           => esc_html__( 'WooCommerce Out of Stock Badge Background Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the background color of the WooCommerce out of stock badge.', 'Avada' ),
						'id'              => 'woo_outofstock_badge_bg_color',
						'default'         => 'rgba(210, 0, 0, 0.9)',
						'type'            => 'color-alpha',
						'css_vars'        => [
							[
								'name' => '--fusion-woo-outofstock-badge-background-color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'woo_outofstock_badge_text_color' => [
						'label'           => esc_html__( 'WooCommerce Out of Stock Badge Text Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the text color of the WooCommerce out of stock badge.', 'Avada' ),
						'id'              => 'woo_outofstock_badge_text_color',
						'default'         => 'var(--awb-color1)',
						'type'            => 'color-alpha',
						'css_vars'        => [
							[
								'name' => '--fusion-woo-outofstock-badge-text-color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'woo_outofstock_badge_text_size' => [
						'label'           => esc_html__( 'WooCommerce Out of Stock Badge Text Size', 'Avada' ),
						'description'     => esc_html__( 'Controls the font size of the WooCommerce out of stock badge text.', 'Avada' ),
						'id'              => 'woo_outofstock_badge_text_size',
						'type'            => 'dimension',
						'default'         => '13px',
						'css_vars'        => [
							[
								'name' => '--fusion-woo-outofstock-badge-text-size',
							],
						],
					],
					'woo_outofstock_badge_padding' => [
						'label'       => esc_html__( 'WooCommerce Out Of Stock Badge Padding', 'Avada' ),
						'description' => esc_html__( 'Controls the top/right/bottom/left padding of the out of stock badge.', 'Avada' ),
						'id'          => 'woo_outofstock_badge_padding',
						'choices'     => [
							'top'    => true,
							'bottom' => true,
							'left'   => true,
							'right'  => true,
						],
						'default'     => [
							'top'    => '0.45em',
							'bottom' => '0.45em',
							'left'   => '0',
							'right'  => '0',
						],
						'type'        => 'spacing',
						'css_vars'    => [
							[
								'name'   => '--fusion-woo-outofstock-badge-padding-top',
								'choice' => 'top',
							],
							[
								'name'   => '--fusion-woo-outofstock-badge-padding-bottom',
								'choice' => 'bottom',
							],
							[
								'name'   => '--fusion-woo-outofstock-badge-padding-left',
								'choice' => 'left',
							],
							[
								'name'   => '--fusion-woo-outofstock-badge-padding-right',
								'choice' => 'right',
							],
						],
						'transport'   => 'postMessage',
						'required'        => [
							[
								'setting'  => 'woo_outofstock_badge_shape',
								'operator' => '!=',
								'value'    => 'circle',
							],
						],
					],
					'woo_outofstock_badge_border_radius'        => [
						'type'        => 'border_radius',
						'label'       => esc_html__( 'WooCommerce Out Of Stock Badge Border Radius', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the border radius of out of stock badge.', 'Avada' ),
						'id'          => 'woo_outofstock_badge_border_radius',
						'choices'     => [
							'top_left'     => true,
							'top_right'    => true,
							'bottom_right' => true,
							'bottom_left'  => true,
							'units'        => [ 'px', '%', 'em' ],
						],
						'default'     => [
							'top_left'     => '0px',
							'top_right'    => '0px',
							'bottom_right' => '0px',
							'bottom_left'  => '0px',
						],
						'css_vars'    => [
							[
								'name'    => '--fusion-woo-outofstock-badge-border-top-left-radius',
								'choice'  => 'top_left',
								'element' => 'body',
							],
							[
								'name'    => '--fusion-woo-outofstock-badge-border-top-right-radius',
								'choice'  => 'top_right',
								'element' => 'body',
							],
							[
								'name'    => '--fusion-woo-outofstock-badge-border-bottom-right-radius',
								'choice'  => 'bottom_right',
								'element' => 'body',
							],
							[
								'name'    => '--fusion-woo-outofstock-badge-border-bottom-left-radius',
								'choice'  => 'bottom_left',
								'element' => 'body',
							],
						],

						// Could update variable here, but does not look necessary as set inline.
						'transport'   => 'postMessage',
						'required'        => [
							[
								'setting'  => 'woo_outofstock_badge_shape',
								'operator' => '!=',
								'value'    => 'circle',
							],
						],
					],
					'woo_outofstock_badge_border_width'                 => [
						'label'       => esc_html__( 'WooCommerce Out Of Stock Badge Border Size', 'Avada' ),
						'description' => esc_html__( 'Controls the border size of the out of stock badge.', 'Avada' ),
						'id'          => 'woo_outofstock_badge_border_width',
						'choices'     => [
							'top'    => true,
							'bottom' => true,
							'left'   => true,
							'right'  => true,
						],
						'default'     => [
							'top'    => '0px',
							'bottom' => '0px',
							'left'   => '0px',
							'right'  => '0px',
						],
						'type'        => 'spacing',
						'css_vars'    => [
							[
								'name'   => '--fusion-woo-outofstock-badge-width-top',
								'choice' => 'top',
							],
							[
								'name'   => '--fusion-woo-outofstock-badge-width-bottom',
								'choice' => 'bottom',
							],
							[
								'name'   => '--fusion-woo-outofstock-badge-width-left',
								'choice' => 'left',
							],
							[
								'name'   => '--fusion-woo-outofstock-badge-width-right',
								'choice' => 'right',
							],
						],
					],
					'woo_outofstock_badge_border_color'                 => [
						'label'           => esc_html__( 'WooCommerce Out Of Stock Badge Border Color', 'Avada' ),
						'description'     => esc_html__( 'Controls the border color of the out of stock badge', 'Avada' ),
						'id'              => 'woo_outofstock_badge_border_color',
						'default'         => '',
						'type'            => 'color-alpha',
						'css_vars'        => [
							[
								'name'     => '--fusion-woo-outofstock-badge-border-color',
								'callback' => [ 'sanitize_color' ],
							],
						],
					],
					'woo_outofstock_badge_text' => [
						'label'           => esc_html__( 'WooCommerce Out Of Stock Badge Text', 'Avada' ),
						'description'     => esc_html__( 'Default value is: Out of stock', 'Avada' ),
						'id'              => 'woo_outofstock_badge_text',
						'default'         => __( 'Out of stock', 'Avada' ),
						'type'            => 'text',
					],
				],
			],
		],
	 ] : [];

	return $sections;

}
