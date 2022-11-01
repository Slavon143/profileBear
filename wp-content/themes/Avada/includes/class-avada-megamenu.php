<?php
/**
 * Manipulate mega-menus.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      3.4.0
 */

// Don't duplicate me!
if ( ! class_exists( 'Avada_Megamenu' ) ) {

	/**
	 * Class to manipulate menus.
	 */
	class Avada_Megamenu extends Avada_Megamenu_Framework {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			if ( ! is_customize_preview() ) {
				add_action( 'wp_update_nav_menu_item', [ $this, 'save_custom_menu_style_fields' ], 10, 3 );
			}
			add_filter( 'wp_setup_nav_menu_item', [ $this, 'add_menu_style_data_to_menu' ] );
			add_filter( 'wp_edit_nav_menu_walker', [ $this, 'add_custom_fields' ] );
			add_action( 'wp_update_nav_menu', [ $this, 'update_nav_menu' ] );
		}

		/**
		 * Function to replace normal edit nav walker for fusion core mega menus.
		 *
		 * @return string Class name of new navwalker
		 */
		public function add_custom_fields() {
			return 'Avada_Nav_Walker_Megamenu';
		}

		/**
		 * Add the custom megamenu fields menu item data to fields in database.
		 *
		 * @access public
		 * @param string|int $menu_id         The menu ID.
		 * @param string|int $menu_item_db_id The menu ID from the db.
		 * @param array      $args            The arguments array.
		 * @return void
		 */
		public function save_custom_menu_style_fields( $menu_id, $menu_item_db_id, $args ) {

			// If this is a front-end save, exit early.
			if ( isset( $_POST ) && isset( $_POST['custom'] ) ) { // phpcs:ignore WordPress.Security
				return;
			}

			$meta_data  = get_post_meta( $menu_item_db_id );
			$avada_meta = ! empty( $meta_data['_menu_item_fusion_megamenu'][0] ) ? maybe_unserialize( $meta_data['_menu_item_fusion_megamenu'][0] ) : [];

			$field_name_suffix = [
				'icon',
				'icononly',
				'modal',
				'highlight-label',
				'highlight-label-background',
				'highlight-label-color',
				'highlight-label-border-color',
				'special-link',
				'show-woo-cart-counter',
				'show-empty-woo-cart-counter',
				'cart-counter-display',
				'show-woo-cart-contents',
				'searchform-mode',
				'off-canvas-id',
			];
			if ( ! $args['menu-item-parent-id'] ) {
				$field_name_suffix = [
					'style',
					'icon',
					'icononly',
					'modal',
					'highlight-label',
					'highlight-label-background',
					'highlight-label-color',
					'highlight-label-border-color',
					'special-link',
					'show-woo-cart-counter',
					'show-empty-woo-cart-counter',
					'cart-counter-display',
					'show-woo-cart-contents',
					'searchform-mode',
					'off-canvas-id',
				];
			}

			if ( Avada()->settings->get( 'disable_megamenu' ) ) {

				$megamenu_field_name_suffix = [ 'title', 'widgetarea', 'columnwidth', 'thumbnail', 'thumbnail-id', 'background-image' ];

				if ( ! $args['menu-item-parent-id'] ) {
					$megamenu_field_name_suffix = [ 'status', 'width', 'columns', 'columnwidth', 'thumbnail', 'thumbnail-id', 'background-image' ];
				}

				$field_name_suffix = array_merge( $field_name_suffix, $megamenu_field_name_suffix );
			}

			foreach ( $field_name_suffix as $key ) {
				if ( ! isset( $_REQUEST[ 'menu-item-fusion-megamenu-' . $key ][ $menu_item_db_id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$_REQUEST[ 'menu-item-fusion-megamenu-' . $key ][ $menu_item_db_id ] = '';
				}
				$avada_meta[ str_replace( '-', '_', $key ) ] = sanitize_text_field( wp_unslash( $_REQUEST[ 'menu-item-fusion-megamenu-' . $key ][ $menu_item_db_id ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}

			update_post_meta( $menu_item_db_id, '_menu_item_fusion_megamenu', $avada_meta );
		}

		/**
		 * Additional actions to run when the menu is saved.
		 *
		 * @access public
		 * @since 7.0.0
		 * @return void
		 */
		public function update_nav_menu() {
			wp_cache_delete( 'avada_woo_nav_items', 'avada' );
		}

		/**
		 * Add custom megamenu fields data to the menu.
		 *
		 * @access public
		 * @param object $menu_item A single menu item.
		 * @return object The menu item.
		 */
		public function add_menu_style_data_to_menu( $menu_item ) {

			$meta_data  = get_post_meta( $menu_item->ID );
			$avada_meta = ! empty( $meta_data['_menu_item_fusion_megamenu'][0] ) ? maybe_unserialize( $meta_data['_menu_item_fusion_megamenu'][0] ) : [];
			$avada_meta = apply_filters( 'avada_menu_meta', $avada_meta, $menu_item->ID );

			if ( ! $menu_item->menu_item_parent ) {
				$menu_item->fusion_menu_style = isset( $avada_meta['style'] ) ? $avada_meta['style'] : '';
			}

			$menu_item->fusion_menu_icononly  = isset( $avada_meta['icononly'] ) ? $avada_meta['icononly'] : '';
			$menu_item->fusion_megamenu_icon  = isset( $avada_meta['icon'] ) ? $avada_meta['icon'] : '';
			$menu_item->fusion_megamenu_modal = isset( $avada_meta['modal'] ) ? $avada_meta['modal'] : '';

			$menu_item->fusion_special_link                = isset( $avada_meta['special_link'] ) ? $avada_meta['special_link'] : '';
			$menu_item->fusion_show_woo_cart_counter       = isset( $avada_meta['show_woo_cart_counter'] ) ? $avada_meta['show_woo_cart_counter'] : 'no';
			$menu_item->fusion_show_empty_woo_cart_counter = isset( $avada_meta['show_empty_woo_cart_counter'] ) ? $avada_meta['show_empty_woo_cart_counter'] : 'yes';
			$menu_item->fusion_cart_counter_display        = isset( $avada_meta['cart_counter_display'] ) ? $avada_meta['cart_counter_display'] : 'inline';
			$menu_item->fusion_show_woo_cart_contents      = isset( $avada_meta['show_woo_cart_contents'] ) ? $avada_meta['show_woo_cart_contents'] : 'no';
			$menu_item->fusion_searchform_mode             = isset( $avada_meta['searchform_mode'] ) ? $avada_meta['searchform_mode'] : 'inline';
			$menu_item->fusion_off_canvas_id               = isset( $avada_meta['off_canvas_id'] ) ? $avada_meta['off_canvas_id'] : '';

			$menu_item->fusion_highlight_label              = isset( $avada_meta['highlight_label'] ) ? $avada_meta['highlight_label'] : '';
			$menu_item->fusion_highlight_label_background   = isset( $avada_meta['highlight_label_background'] ) ? $avada_meta['highlight_label_background'] : '';
			$menu_item->fusion_highlight_label_color        = isset( $avada_meta['highlight_label_color'] ) ? $avada_meta['highlight_label_color'] : '';
			$menu_item->fusion_highlight_label_border_color = isset( $avada_meta['highlight_label_border_color'] ) ? $avada_meta['highlight_label_border_color'] : '';

			if ( Avada()->settings->get( 'disable_megamenu' ) ) {
				if ( ! $menu_item->menu_item_parent ) {
					$menu_item->fusion_megamenu_status  = isset( $avada_meta['status'] ) ? $avada_meta['status'] : 'disabled';
					$menu_item->fusion_megamenu_width   = isset( $avada_meta['width'] ) ? $avada_meta['width'] : '';
					$menu_item->fusion_megamenu_columns = isset( $avada_meta['columns'] ) ? $avada_meta['columns'] : '';
				} else {
					$menu_item->fusion_megamenu_title      = isset( $avada_meta['title'] ) ? $avada_meta['title'] : '';
					$menu_item->fusion_megamenu_widgetarea = isset( $avada_meta['widgetarea'] ) ? $avada_meta['widgetarea'] : '';
				}
				$menu_item->fusion_megamenu_columnwidth      = isset( $avada_meta['columnwidth'] ) ? $avada_meta['columnwidth'] : '';
				$menu_item->fusion_megamenu_thumbnail        = isset( $avada_meta['thumbnail'] ) ? $avada_meta['thumbnail'] : '';
				$menu_item->fusion_megamenu_background_image = isset( $avada_meta['background_image'] ) ? $avada_meta['background_image'] : '';
			}

			return $menu_item;

		}
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
