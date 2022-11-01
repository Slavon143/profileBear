<?php
/**
 * A class to add Avada menu to the admin toolbar.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      7.4
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A class to add Avada menu to the admin toolbar.
 */
class Avada_Admin_Bar {

	/**
	 * Construct or.
	 *
	 * @since 7.4
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_before_admin_bar_render', [ $this, 'add_wp_toolbar_menu' ] );
	}

	/**
	 * Create the admin toolbar menu items.
	 *
	 * @access public
	 * @since 7.4
	 * @return void
	 */
	public function add_wp_toolbar_menu() {
		global $wp_admin_bar, $fusion_settings;

		if ( current_user_can( 'edit_pages' ) ) {

			// Done for white label plugin.
			$avada_parent_menu_name  = __( 'Avada', 'Avada' );
			$avada_parent_menu_title = '<span class="ab-label">' . esc_html( $avada_parent_menu_name ) . '</span>';

			if ( ! is_admin() ) {
				$this->add_wp_toolbar_menu_item(
					apply_filters( 'avada_wpadminbar_menu_title', $avada_parent_menu_title ),
					false,
					admin_url( 'admin.php?page=avada' ),
					[
						'class' => 'avada-menu',
					],
					'avada'
				);
			}

			$this->add_wp_toolbar_menu_item( esc_html__( 'Global Options', 'Avada' ), 'avada', admin_url( 'themes.php?page=avada_options' ) );
			$this->add_wp_toolbar_menu_item( esc_html__( 'Websites', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-prebuilt-websites' ) );
			if ( class_exists( 'AWB_Studio' ) && AWB_Studio::is_studio_enabled() ) {
				$this->add_wp_toolbar_menu_item( esc_html__( 'Studio', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-studio' ) );
			}
			$this->add_wp_toolbar_menu_item( esc_html__( 'Layouts', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-layouts' ) );

			if ( class_exists( 'AWB_Off_Canvas' ) && AWB_Off_Canvas::is_enabled() ) {
				$this->add_wp_toolbar_menu_item( esc_html__( 'Off Canvas', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-off-canvas' ) );
			}

			$this->add_wp_toolbar_menu_item( esc_html__( 'Icons', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-icons' ) );

			if ( class_exists( 'Fusion_Form_Builder' ) && Fusion_Form_Builder::is_enabled() ) {
				$this->add_wp_toolbar_menu_item( esc_html__( 'Forms', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-forms' ) );
			}

			if ( $fusion_settings->get( 'status_fusion_slider' ) ) {
				$this->add_wp_toolbar_menu_item( esc_html__( 'Sliders', 'Avada' ), 'avada', admin_url( 'edit-tags.php?taxonomy=slide-page&post_type=slide' ) );
			}

			$this->add_wp_toolbar_menu_item( esc_html__( 'Library', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-library' ) );

			$this->add_wp_toolbar_menu_item( esc_html__( 'Patcher', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-patcher' ) );

			$this->add_wp_toolbar_menu_item( esc_html__( 'Plugins', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-plugins' ) );
			$this->add_wp_toolbar_menu_item( esc_html__( 'Status', 'Avada' ), 'avada', admin_url( 'admin.php?page=avada-status' ) );

			if ( AVADA_DEV_MODE ) {
				$on_click = 'jQuery.post( "' . esc_url( admin_url( 'admin-ajax.php' ) ) . '", { "action": "fusion_reset_all_caches" }, function() {alert("' . esc_html__( 'All Avada caches have been reset.', 'Avada' ) . '");} );';
				$this->add_wp_toolbar_menu_item(
					esc_html__( 'Reset Avada Caches', 'Avada' ),
					'avada',
					'#',
					[
						'onclick' => $on_click,
						'target'  => '_self',
					]
				);
			}
		}
	}

	/**
	 * Add the top-level menu item to the adminbar.
	 *
	 * @access public
	 * @since 7.4
	 * @param string       $title       The title.
	 * @param string|false $parent      The parent node.
	 * @param string       $href        Link URL.
	 * @param array        $custom_meta An array of custom meta to apply.
	 * @param string       $custom_id   A custom ID.
	 */
	public function add_wp_toolbar_menu_item( $title, $parent = false, $href = '', $custom_meta = [], $custom_id = '' ) {
		global $wp_admin_bar;

		if ( current_user_can( 'edit_pages' ) ) {
			if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
				return;
			}

			// Set custom ID.
			if ( $custom_id ) {
				$id = $custom_id;
			} else { // Generate ID based on $title.
				$id = strtolower( str_replace( ' ', '-', $title ) );
			}

			// Links from the current host will open in the current window.
			$meta = strpos( $href, site_url() ) !== false ? [] : [
				'target' => '_blank',
			]; // External links open in new tab/window.
			$meta = array_merge( $meta, $custom_meta );

			$wp_admin_bar->add_node(
				[
					'parent' => $parent,
					'id'     => $id,
					'title'  => $title,
					'href'   => $href,
					'meta'   => $meta,
				]
			);
		}

	}

	/**
	 * Modify the menu.
	 *
	 * @access public
	 * @since 3.8.0
	 * @return void
	 */
	public function edit_admin_menus() {
		global $submenu;

		// Change Avada to Dashboard.
		if ( current_user_can( 'switch_themes' ) ) {
			$submenu['avada'][0][0] = esc_html__( 'Dashboard', 'Avada' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}

		if ( isset( $submenu['themes.php'] ) && ! empty( $submenu['themes.php'] ) ) {
			foreach ( $submenu['themes.php'] as $key => $value ) {
				// Remove "Header" submenu.
				if ( isset( $value[2] ) && false !== strpos( $value[2], 'customize.php' ) && false !== strpos( $value[2], '=header_image' ) ) {
					unset( $submenu['themes.php'][ $key ] );
				}
				// Remove "Background" submenu.
				if ( isset( $value[2] ) && false !== strpos( $value[2], 'customize.php' ) && false !== strpos( $value[2], '=background_image' ) ) {
					unset( $submenu['themes.php'][ $key ] );
				}
			}

			// Reorder items in the array.
			$submenu['themes.php'] = array_values( $submenu['themes.php'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}

		// Remove TGMPA menu from Appearance.
		remove_submenu_page( 'themes.php', 'install-required-plugins' );

	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
