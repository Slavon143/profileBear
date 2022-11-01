<?php
/**
 * Fusion MegaMenu Functions
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Don't duplicate me!
if ( ! class_exists( 'Avada_Megamenu_Framework' ) ) {

	/**
	 * Main Avada_Megamenu_Framework Class
	 */
	class Avada_Megamenu_Framework {

		/**
		 * The theme info object.
		 *
		 * @static
		 * @access public
		 * @var object
		 */
		public static $theme_info;

		/**
		 * Array of objects.
		 *
		 * @static
		 * @access public
		 * @var mixed
		 */
		public static $classes;

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {

			self::$theme_info = wp_get_theme();

			add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'register_stylesheets' ] );

			do_action( 'fusion_init' );

			self::$classes['menus'] = new Avada_Megamenu();

			// Add the first level menu style dropdown to the menu fields.
			add_action( 'wp_nav_menu_item_custom_fields', [ $this, 'add_menu_button_fields' ], 10, 5 );

			// Add the mega menu custom fields to the menu fields.
			if ( Avada()->settings->get( 'disable_megamenu' ) ) {
				add_filter( 'avada_menu_options', [ $this, 'add_megamenu_fields' ], 20, 4 );
			}

			// Add the menu arrow highlights.
			add_filter( 'avada_menu_arrow_hightlight', [ $this, 'add_menu_arrow_highlight' ], 10, 2 );

			// Add special menu items meta box.
			add_action( 'admin_head-nav-menus.php', [ $this, 'add_special_links_meta_box' ] );

			// AutoComplete field ajax.
			add_action( 'wp_ajax_avada_mega_menu_autocomplete', [ $this, 'get_autocomplete' ] );

		}

		/**
		 * Register megamenu javascript assets.
		 *
		 * @since  3.4
		 * @access public
		 * @param string $hook The hook we're currently on.
		 * @return void
		 */
		public function register_scripts( $hook ) {
			if ( 'nav-menus.php' === $hook ) {

				// Scripts.
				wp_enqueue_media();
				wp_register_script( 'avada-megamenu', Avada::$template_dir_url . '/assets/admin/js/mega-menu.js', [], self::$theme_info->get( 'Version' ), false );
				wp_enqueue_script( 'avada-megamenu' );
				wp_localize_script(
					'avada-megamenu',
					'AvadaMegaMenuVars',
					[
						'nonce'        => wp_create_nonce( 'avada_mega_menu_nonce' ),
						'text_loading' => __( 'Loading...', 'Avada' ),
					]
				);
			}
		}

		/**
		 * Enqueue megamenu stylesheets
		 *
		 * @since  3.4
		 * @access public
		 * @param string $hook The hook we're currently on.
		 * @return void
		 */
		public function register_stylesheets( $hook ) {
			if ( 'nav-menus.php' === $hook ) {
				wp_enqueue_style( 'avada-megamenu', Avada::$template_dir_url . '/assets/admin/css/mega-menu.css', false, self::$theme_info->get( 'Version' ) );
			}
		}

		/**
		 * Adds the menu button fields.
		 *
		 * @static
		 * @access public
		 * @since 6.0.0
		 * @return array.
		 */
		public static function menu_options_map() {
			return [
				'megamenu-style'                        => [
					'id'          => 'megamenu-style',
					'label'       => esc_attr__( 'Menu First Level Style', 'Avada' ),
					'choices'     => [
						''                     => esc_attr__( 'Default Style', 'Avada' ),
						'fusion-button-small'  => esc_attr__( 'Button Small', 'Avada' ),
						'fusion-button-medium' => esc_attr__( 'Button Medium', 'Avada' ),
						'fusion-button-large'  => esc_attr__( 'Button Large', 'Avada' ),
						'fusion-button-xlarge' => esc_attr__( 'Button xLarge', 'Avada' ),
					],
					'description' => esc_attr__( 'Select to use normal text (default) for the parent level menu item, or a button. Button styles are controlled in Global Options > Avada Builder Elements.', 'Avada' ),
					'type'        => 'select',
					'default'     => '',
					'save_id'     => 'fusion_menu_style',
				],
				'megamenu-icon'                         => [
					'id'          => 'megamenu-icon',
					'label'       => esc_attr__( 'Icon Select', 'Avada' ),
					'description' => esc_attr__( 'Select an icon for your menu item. For top-level menu items, icon styles can be controlled in Global Options > Menu > Main Menu Icons.', 'Avada' ),
					'type'        => 'iconpicker',
					'default'     => '',
				],
				'megamenu-icononly'                     => [
					'id'          => 'megamenu-icononly',
					'label'       => esc_attr__( 'Icon/Thumbnail Only', 'Avada' ),
					'description' => esc_attr__( 'Turn on to only show the icon/image thumbnail while hiding the menu text. Important: this does not apply to the mobile menu.', 'Avada' ),
					'type'        => 'radio-buttonset',
					'default'     => 'off',
					'choices'     => [
						'icononly' => esc_attr__( 'On', 'Avada' ),
						'off'      => esc_attr__( 'Off', 'Avada' ),
					],
					'save_id'     => 'fusion_menu_icononly',
				],
				'megamenu-highlight-label'              => [
					'id'          => 'megamenu-highlight-label',
					'label'       => esc_attr__( 'Menu Highlight Label', 'Avada' ),
					'description' => esc_attr__( 'Set the highlight label for menu item.', 'Avada' ),
					'type'        => 'text',
					'save_id'     => 'fusion_highlight_label',
				],
				'megamenu-highlight-label-background'   => [
					'id'          => 'megamenu-highlight-label-background',
					/* translators: "<span>" tags. */
					'label'       => sprintf( esc_html__( '%1$sMenu Highlight Label Background Color%2$s%3$sCart Counter Background Color%4$s', 'Avada' ), '<span class="fusion-menu-default-text">', '</span>', '<span class="fusion-menu-cart-text">', '</span>' ),
					/* translators: %1$s: <span> tag. %2$s: "TGlobal Options" link. %3$s: <span> tag. %4$s: <span> tag. %5$s: <span> tag. */
					'description' => sprintf( esc_html__( '%1$sSet the highlight label background color. To set a border radius, visit %2$s and modify the Menu Highlight Label Radius option.%3$s%4$s Set cart counter background color.%5$s', 'Avada' ), '<span class="fusion-menu-default-text">', '<a href="' . esc_url_raw( admin_url( 'themes.php?page=avada_options#main_nav_highlight_radius' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Global Options', 'Avada' ) . '</a>', '</span>', '<span class="fusion-menu-cart-text">', '</span>' ),
					'type'        => 'color-alpha',
					'save_id'     => 'fusion_highlight_label_background',
					'dependency'  => [
						[
							'field'      => 'megamenu-highlight-label',
							'value'      => '',
							'comparison' => '!=',
						],
					],
				],
				'megamenu-highlight-label-color'        => [
					'id'          => 'megamenu-highlight-label-color',
					/* translators: "<span>" tags. */
					'label'       => sprintf( esc_html__( '%1$sMenu Highlight Label Text Color%2$s%3$sCart Counter Text Color%4$s', 'Avada' ), '<span class="fusion-menu-default-text">', '</span>', '<span class="fusion-menu-cart-text">', '</span>' ),
					/* translators: "<span>" tags. */
					'description' => sprintf( esc_html__( '%1$sSet the highlight label text color.%2$s%3$sSet the cart counter text color.%4$s', 'Avada' ), '<span class="fusion-menu-default-text">', '</span>', '<span class="fusion-menu-cart-text">', '</span>' ),
					'type'        => 'color',
					'save_id'     => 'fusion_highlight_label_color',
					'dependency'  => [
						[
							'field'      => 'megamenu-highlight-label',
							'value'      => '',
							'comparison' => '!=',
						],
					],
				],
				'megamenu-highlight-label-border-color' => [
					'id'          => 'megamenu-highlight-label-border-color',
					/* translators: "<span>" tags. */
					'label'       => sprintf( esc_html__( '%1$sMenu Highlight Label Border Color%2$s%3$sCart Counter Border Color %4$s', 'Avada' ), '<span class="fusion-menu-default-text">', '</span>', '<span class="fusion-menu-cart-text">', '</span>' ),
					/* translators: "<span>" tags. */
					'description' => sprintf( esc_html__( '%1$sSet the highlight label border color.%2$s%3$sSet the cart counter border color.%4$s', 'Avada' ), '<span class="fusion-menu-default-text">', '</span>', '<span class="fusion-menu-cart-text">', '</span>' ),
					'type'        => 'color',
					'save_id'     => 'fusion_highlight_label_border_color',
					'dependency'  => [
						[
							'field'      => 'megamenu-highlight-label',
							'value'      => '',
							'comparison' => '!=',
						],
					],
				],
				'megamenu-modal'                        => [
					'id'          => 'megamenu-modal',
					'label'       => esc_attr__( 'Modal Window Anchor', 'Avada' ),
					'description' => esc_attr__( 'Add the class name of the modal window you want to open on menu item click.', 'Avada' ),
					'type'        => 'text',
				],
				'megamenu-background-image'             => [
					'id'          => 'megamenu-background-image',
					'label'       => esc_html__( 'Mega Menu / Flyout Menu Background Image', 'Avada' ),
					'description' => __( 'Select an image for the mega menu or flyout menu background.<br /><strong>Mega Menu:</strong> In case of mega menu, if left empty, the Main Menu Dropdown Background Color will be used. Each mega menu column can have its own background image, or you can have one image that spreads across the entire mega menu width.<br /><strong>Flyout Menu:</strong> When used in the flyout menu, the image will be shown full screen when hovering the corresponding menu item.', 'Avada' ),
					'type'        => 'media',
					'dependency'  => [
						[
							'field'      => 'depth',
							'value'      => '3',
							'comparison' => '<',
						],
					],
				],
			];
		}

		/**
		 * Adds Avada special menu items meta box.
		 *
		 * @since  7.0
		 * @access public
		 * @return void
		 */
		public function add_special_links_meta_box() {
			add_meta_box( 'avada_special_items_nav_link', __( 'Avada Special Menu Items', 'Avada' ), [ $this, 'special_menut_items' ], 'nav-menus', 'side', 'low' );
		}

		/**
		 * Outputs contents of Avada special menu items meta box.
		 *
		 * @since  7.0
		 * @access public
		 * @return void
		 */
		public function special_menut_items() {
			?>
			<div class="avada-special-menu-items-note"><?php _e( '<strong>IMPORTANT NOTE:</strong> These items only work in Avada Builder Menu element.', 'Avada' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<div id="avada-special-menu-items" class="posttypediv">
				<div id="tabs-panel-avada-special-menu-items" class="tabs-panel tabs-panel-active">
					<ul id="avada-special-menu-items-checklist" class="categorychecklist form-no-clear">
						<?php
						$endpoints = [
							'#fusion-search'               => esc_html__( 'Search', 'Avada' ),
							'#fusion-sliding-bar-toggle'   => esc_html__( 'Sliding Bar Toggle', 'Avada' ),
							'#awb-off-canvas-menu-trigger' => esc_html__( 'Off Canvas Toggle', 'Avada' ),
						];
						if ( class_exists( 'WooCommerce' ) ) {
							$endpoints['#fusion-woo-cart']       = esc_html__( 'WooCommerce Cart', 'Avada' );
							$endpoints['#fusion-woo-my-account'] = esc_html__( 'WooCommerce My Account', 'Avada' );
						}
						$i = -1;
						foreach ( $endpoints as $key => $value ) :
							?>
							<li>
								<label class="menu-item-title">
									<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-object-id]" value="<?php echo esc_attr( $i ); ?>" /> <?php echo esc_html( $value ); ?>
								</label>
								<input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-type]" value="custom" />
								<input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-title]" value="<?php echo esc_attr( $value ); ?>" />
								<input type="hidden" class="menu-item-url" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-url]" value="<?php echo esc_attr( $key ); ?>" />
								<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-classes]" />
							</li>
							<?php
							$i--;
						endforeach;
						?>
					</ul>
				</div>
				<p class="button-controls wp-clearfix" data-items-type="avada-special-menu-items">
					<span class="list-controls hide-if-no-js">
						<input type="checkbox" id="avada-special-menu-items-tab" class="select-all">
						<label for="avada-special-menu-items-tab"><?php esc_html_e( 'Select all', 'Avada' ); ?></label>
					</span>
					<span class="add-to-menu">
						<button type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to menu', 'Avada' ); ?>" name="add-post-type-menu-item" id="submit-avada-special-menu-items"><?php esc_html_e( 'Add to menu', 'Avada' ); ?></button>
						<span class="spinner"></span>
					</span>
				</p>
			</div>
			<?php
		}

		/**
		 * Adds CSS to hide URL for special link menu.
		 *
		 * @since  7.0
		 * @access public
		 * @param int $item_id The menu-item's ID.
		 * @return void
		 */
		public function hide_url_field( $item_id ) {
			?>
			<style type="text/css">
				#menu-item-settings-<?php echo esc_attr( $item_id ); ?> p.field-url {
					display: none;
				}
			</style>
			<?php
		}

		/**
		 * Adds the menu button fields.
		 *
		 * @static
		 * @access public
		 * @since 7.0.0
		 * @param array  $fields   Existing fields.
		 * @param string $item_url The menu item's URL.
		 * @param int    $depth    The item's depth.
		 * @return array.
		 */
		public static function special_link_options_map( $fields, $item_url, $depth ) {

			$off_canvas_field_type = 'autocomplete';
			$off_canvas_items      = [ '' => __( 'Select Off Canvas', 'Avada' ) ];

			if ( 25 > wp_count_posts( 'awb_off_canvas' )->publish ) {
				$off_canvas_field_type = 'select';
				$off_canvas_items      = class_exists( 'AWB_Off_Canvas_Front_End' ) ? AWB_Off_Canvas_Front_End()->get_available_items() : [];
			}

			$custom_fields = [
				'fusion-megamenu-special-link-note'    => [
					'id'          => 'fusion-megamenu-special-link-note',
					'label'       => esc_attr__( 'Important Note', 'Avada' ),
					'description' => esc_attr__( 'Avada Special Menu Items can only be used as top-level items. Please move the item to top-level to see the available options.', 'Avada' ),
					'type'        => 'note',
					'save_id'     => 'fusion_special_link_note',
					'dependency'  => [
						[
							'field'      => 'depth',
							'value'      => '1',
							'comparison' => '>',
						],
					],
				],
				'fusion-megamenu-special-link'         => [
					'id'          => 'fusion-megamenu-special-link',
					'label'       => esc_attr__( 'Special Link Type', 'Avada' ),
					'description' => esc_attr__( 'Select to add a special link type.', 'Avada' ),
					'type'        => 'hidden',
					'value'       => 0 === $depth ? ltrim( $item_url, '#' ) : '',
					'save_id'     => 'fusion_special_link',
				],
				'megamenu-show-woo-cart-counter'       => [
					'id'          => 'megamenu-show-woo-cart-counter',
					'label'       => esc_attr__( 'Show WooCommerce Cart Counter', 'Avada' ),
					'description' => esc_attr__( 'Turn on to show the cart products counter.', 'Avada' ),
					'type'        => 'radio-buttonset',
					'default'     => 'no',
					'choices'     => [
						'yes' => esc_attr__( 'On', 'Avada' ),
						'no'  => esc_attr__( 'Off', 'Avada' ),
					],
					'save_id'     => 'fusion_show_woo_cart_counter',
					'dependency'  => [
						[
							'field'      => 'fusion-megamenu-special-link',
							'value'      => 'fusion-woo-cart',
							'comparison' => '==',
						],
					],
				],
				'megamenu-cart-counter-display'        => [
					'id'          => 'megamenu-cart-counter-display',
					'label'       => esc_html__( 'Cart Counter Display', 'Avada' ),
					'description' => esc_html__( 'Select the display style you would like for the cart counter.  Badge display will be smaller and positioned on the top corner of the menu item.  Note: badge styling will only be used in the menu element.', 'Avada' ),
					'save_id'     => 'fusion_cart_counter_display',
					'type'        => 'radio-buttonset',
					'default'     => 'inline',
					'choices'     => [
						'inline' => esc_attr__( 'Inline', 'Avada' ),
						'badge'  => esc_attr__( 'Badge', 'Avada' ),
					],
					'dependency'  => [
						[
							'field'      => 'fusion-megamenu-special-link',
							'value'      => 'fusion-woo-cart',
							'comparison' => '==',
						],
						[
							'field'      => 'megamenu-show-woo-cart-counter',
							'value'      => 'yes',
							'comparison' => '==',
						],
					],
				],
				'megamenu-show-empty-woo-cart-counter' => [
					'id'          => 'megamenu-show-empty-woo-cart-counter',
					'label'       => esc_attr__( 'Show WooCommerce Empty Cart Counter', 'Avada' ),
					'description' => esc_attr__( 'Turn on to show the cart counter when cart is empty.', 'Avada' ),
					'type'        => 'radio-buttonset',
					'default'     => 'yes',
					'choices'     => [
						'yes' => esc_attr__( 'On', 'Avada' ),
						'no'  => esc_attr__( 'Off', 'Avada' ),
					],
					'save_id'     => 'fusion_show_empty_woo_cart_counter',
					'dependency'  => [
						[
							'field'      => 'fusion-megamenu-special-link',
							'value'      => 'fusion-woo-cart',
							'comparison' => '==',
						],
						[
							'field'      => 'megamenu-show-woo-cart-counter',
							'value'      => 'yes',
							'comparison' => '==',
						],
					],
				],
				'megamenu-show-woo-cart-contents'      => [
					'id'          => 'megamenu-show-woo-cart-contents',
					'label'       => esc_attr__( 'Show WooCommerce Cart Contents Dropdown', 'Avada' ),
					'description' => esc_attr__( 'Turn on to show the cart contents dropdown.', 'Avada' ),
					'type'        => 'radio-buttonset',
					'default'     => 'no',
					'choices'     => [
						'yes' => esc_attr__( 'On', 'Avada' ),
						'no'  => esc_attr__( 'Off', 'Avada' ),
					],
					'save_id'     => 'fusion_show_woo_cart_contents',
					'dependency'  => [
						[
							'field'      => 'fusion-megamenu-special-link',
							'value'      => 'fusion-woo-cart',
							'comparison' => '==',
						],
					],
				],
				'megamenu-searchform-mode'             => [
					'id'          => 'megamenu-searchform-mode',
					'label'       => esc_html__( 'Search-Form Mode', 'Avada' ),
					'description' => esc_html__( 'Select how your search-form will be displayed.', 'Avada' ),
					'type'        => 'radio-buttonset',
					'default'     => 'inline',
					'choices'     => [
						'inline'   => esc_html__( 'Inline', 'Avada' ),
						'dropdown' => esc_html__( 'Dropdown', 'Avada' ),
						'overlay'  => esc_html__( 'Overlay', 'Avada' ),
					],
					'save_id'     => 'fusion_searchform_mode',
					'dependency'  => [
						[
							'field'      => 'fusion-megamenu-special-link',
							'value'      => 'fusion-search',
							'comparison' => '==',
						],
					],
				],
				'megamenu-off-canvas-id'               => [
					'id'          => 'megamenu-off-canvas-id',
					'label'       => esc_html__( 'Off Canvas', 'Avada' ),
					'description' => esc_html__( 'Select off canvas to show when click on menu item.', 'Avada' ),
					'type'        => $off_canvas_field_type,
					'choices'     => $off_canvas_items,
					'post_type'   => 'awb_off_canvas',
					'save_id'     => 'fusion_off_canvas_id',
					'dependency'  => [
						[
							'field'      => 'fusion-megamenu-special-link',
							'value'      => 'awb-off-canvas-menu-trigger',
							'comparison' => '==',
						],
					],
				],
			];

			return array_merge( $custom_fields, $fields );
		}

		/**
		 * Adds the megamenu fields.
		 *
		 * @access public
		 * @since 6.0.0
		 * @param array $fields Current fields map.
		 * @return array.
		 */
		public function add_megamenu_fields( $fields ) {
			global $wp_registered_sidebars;
			$sidebars = [
				'0' => esc_attr__( 'Select Widget Area', 'Avada' ),
			];
			if ( ! empty( $wp_registered_sidebars ) && is_array( $wp_registered_sidebars ) ) {
				foreach ( $wp_registered_sidebars as $sidebar ) {
								$sidebars[ esc_attr( $sidebar['id'] ) ] = esc_attr( $sidebar['name'] );
				}
			}

			$mega_fields = [
				'megamenu-status'      => [
					'id'          => 'megamenu-status',
					'label'       => esc_html__( 'Avada Mega Menu', 'Avada' ),
					'choices'     => [
						'enabled' => esc_attr__( 'On', 'Avada' ),
						'off'     => esc_attr__( 'Off', 'Avada' ),
					],
					'description' => esc_html__( 'Turn on to enable the mega menu.  Note this will only work for the main menu.', 'Avada' ),
					'type'        => 'radio-buttonset',
					'default'     => 'off',
					'dependency'  => [
						[
							'field'      => 'depth',
							'value'      => '1',
							'comparison' => '==',
						],
					],
				],
				'megamenu-thumbnail'   => [
					'id'          => 'megamenu-thumbnail',
					'label'       => esc_html__( 'Mega Menu Thumbnail', 'Avada' ),
					'description' => esc_html__( 'Select an image to use as a thumbnail for the menu item. For top-level items, the size of the thumbnail can be controlled in Global Options > Menu > Main Menu Icons.', 'Avada' ),
					'type'        => 'media',
					'dependency'  => [
						[
							'field'      => 'parent_megamenu-status',
							'value'      => 'enabled',
							'comparison' => '==',
						],
					],
				],
				'megamenu-width'       => [
					'id'          => 'megamenu-width',
					'label'       => esc_attr__( 'Mega Menu Wrapper Width', 'Avada' ),
					'choices'     => [
						'fullwidth' => esc_attr__( 'Max Width', 'Avada' ),
						'off'       => esc_attr__( 'Fit Content', 'Avada' ),
					],
					/* translators: %s: "Global Options" link. */
					'description' => sprintf( esc_attr__( 'Controls the width of mega menu. In case of max width, it is taken from the site width option in %s. Note this overrides the column width option.', 'Avada' ), '<a href="' . esc_url_raw( admin_url( 'themes.php?page=avada_options#megamenu_width' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Global Options', 'Avada' ) . '</a>' ),
					'type'        => 'radio-buttonset',
					'default'     => 'off',
					'dependency'  => [
						[
							'field'      => 'depth',
							'value'      => '1',
							'comparison' => '==',
						],
						[
							'field'      => 'megamenu-status',
							'value'      => 'enabled',
							'comparison' => '==',
						],
					],
				],
				'megamenu-columns'     => [
					'id'          => 'megamenu-columns',
					'label'       => esc_attr__( 'Mega Menu Number of Columns', 'Avada' ),
					'choices'     => [
						'auto' => esc_attr__( 'Auto', 'Avada' ),
						'1'    => '1',
						'2'    => '2',
						'3'    => '3',
						'4'    => '4',
						'5'    => '5',
						'6'    => '6',
					],
					'description' => esc_attr__( 'Select the number of columns you want to use.', 'Avada' ),
					'type'        => 'select',
					'default'     => 'auto',
					'dependency'  => [
						[
							'field'      => 'depth',
							'value'      => '1',
							'comparison' => '==',
						],
						[
							'field'      => 'megamenu-status',
							'value'      => 'enabled',
							'comparison' => '==',
						],
					],
				],
				'megamenu-columnwidth' => [
					'id'          => 'megamenu-columnwidth',
					'label'       => esc_attr__( 'Mega Menu Column Width', 'Avada' ),
					'description' => esc_attr__( 'Set the width of the column. In percentage, ex 60%.', 'Avada' ),
					'type'        => 'text',
					'dependency'  => [
						[
							'field'      => 'depth',
							'value'      => '2',
							'comparison' => '==',
						],
						[
							'field'      => 'parent_megamenu-status',
							'value'      => 'enabled',
							'comparison' => '==',
						],
						[
							'field'      => 'parent_megamenu-width',
							'value'      => 'fullwidth',
							'comparison' => '!=',
						],
					],
				],
				'megamenu-title'       => [
					'id'          => 'megamenu-title',
					'label'       => esc_attr__( 'Mega Menu Column Title', 'Avada' ),
					'choices'     => [
						'enabled'  => esc_attr__( 'On', 'Avada' ),
						'disabled' => esc_attr__( 'Off', 'Avada' ),
					],
					'description' => esc_attr__( 'Turn on to display item as linked column title. Turn off to display item as normal mega menu entry.', 'Avada' ),
					'type'        => 'radio-buttonset',
					'default'     => 'enabled',
					'dependency'  => [
						[
							'field'      => 'depth',
							'value'      => '2',
							'comparison' => '==',
						],
						[
							'field'      => 'parent_megamenu-status',
							'value'      => 'enabled',
							'comparison' => '==',
						],
					],
				],
				'megamenu-widgetarea'  => [
					'id'          => 'megamenu-widgetarea',
					'label'       => esc_attr__( 'Mega Menu Widget Area', 'Avada' ),
					'choices'     => $sidebars,
					'description' => esc_attr__( 'Select a widget area to be used as the content for the column.', 'Avada' ),
					'type'        => 'select',
					'default'     => '0',
					'dependency'  => [
						[
							'field'      => 'depth',
							'value'      => '1',
							'comparison' => '>',
						],
						[
							'field'      => 'depth',
							'value'      => '4',
							'comparison' => '<',
						],
						[
							'field'      => 'parent_megamenu-status',
							'value'      => 'enabled',
							'comparison' => '==',
						],
					],
				],
			];
			return array_merge( $fields, $mega_fields );
		}

		/**
		 * Adds the markup for the options.
		 *
		 * @access public
		 * @since 6.0.0
		 * @param string $item_id The ID of the menu item.
		 * @param object $item    The menu item object.
		 * @param int    $depth   The menu item depth (starts at 0).
		 * @return void.
		 */
		public function parse_options( $item_id, $item, $depth ) {
			$fields   = self::menu_options_map();
			$specials = [ '#fusion-woo-cart', '#fusion-woo-my-account', '#fusion-search', '#fusion-sliding-bar-toggle', '#awb-off-canvas-menu-trigger' ];

			// Add special-links options.
			if ( isset( $item->object ) && 'custom' === $item->object && in_array( $item->url, $specials, true ) ) {
				$fields = self::special_link_options_map( $fields, $item->url, $depth );

				// Do not show URL field for special links options.
				$this->hide_url_field( $item->ID );
			}

			$fields = apply_filters( 'avada_menu_options', $fields );

			if ( is_array( $fields ) ) {
				foreach ( $fields as $field ) {

					// Defaults.
					$field['id']          = isset( $field['id'] ) ? $field['id'] : '';
					$field['label']       = isset( $field['label'] ) ? $field['label'] : '';
					$field['choices']     = isset( $field['choices'] ) ? $field['choices'] : [];
					$field['description'] = isset( $field['description'] ) ? $field['description'] : '';
					$field['default']     = isset( $field['default'] ) ? $field['default'] : '';
					$field['dependency']  = isset( $field['dependency'] ) ? $field['dependency'] : [];
					$field['save_id']     = isset( $field['save_id'] ) ? $field['save_id'] : 'fusion_' . str_replace( '-', '_', $field['id'] );

					if ( isset( $field['type'] ) ) {
						switch ( $field['type'] ) {

							case 'note':
								$this->note( $field['id'], $field['label'], $field['description'], $field['dependency'], $item_id, $item, $field['save_id'] );
								break;
							case 'text':
								$this->text( $field['id'], $field['label'], $field['description'], $field['dependency'], $item_id, $item, $field['save_id'] );
								break;
							case 'hidden':
								$this->hidden( $field['id'], $item_id, $item, $field['value'] );
								break;
							case 'radio-buttonset':
								$this->radio_buttonset( $field['id'], $field['label'], $field['choices'], $field['description'], $field['default'], $field['dependency'], $item_id, $item, $field['save_id'] );
								break;
							case 'select':
								$this->select( $field['id'], $field['label'], $field['choices'], $field['description'], $field['default'], $field['dependency'], $item_id, $item, $field['save_id'] );
								break;
							case 'color-alpha':
								$this->color_alpha( $field['id'], $field['label'], $field['description'], $field['default'], $field['dependency'], $item_id, $item, $field['save_id'] );
								break;
							case 'color':
								$this->color( $field['id'], $field['label'], $field['description'], $field['default'], $field['dependency'], $item_id, $item, $field['save_id'] );
								break;
							case 'media':
								$this->media( $field['id'], $field['label'], $field['description'], $field['dependency'], $item_id, $item, $field['save_id'] );
								break;
							case 'iconpicker':
								$this->iconpicker( $field['id'], $field['label'], $field['description'], $field['dependency'], $item_id, $item, $field['save_id'] );
								break;
							case 'autocomplete':
								$post_type = isset( $field['post_type'] ) ? $field['post_type'] : '';
								$this->autocomplete( $field['id'], $field['label'], $post_type, $field['description'], $field['default'], $field['dependency'], $item_id, $item, $field['save_id'] );
								break;

						}
					}
				}
			}
		}

		/**
		 * Note control.
		 *
		 * @access public
		 * @since 7.1
		 * @param string $id         The ID.
		 * @param string $label      The label.
		 * @param string $desc       The description.
		 * @param array  $dependency The dependencies array.
		 * @param string $item_id    The ID of the menu item.
		 * @param object $item       The menu item object.
		 * @param string $save_id    The save ID if it is different from ID.
		 */
		public function note( $id, $label, $desc = '', $dependency = [], $item_id = 0, $item = null, $save_id = '' ) {
			?>
			<div class="fusion-builder-option field-<?php echo esc_attr( $id ); ?>">
				<div class="avada-special-menu-items-note">
					<strong><?php echo esc_html( $label ); ?>:</strong> <span><?php echo esc_html( $desc ); ?></span>
				</div>
			</div>
			<?php
		}

		/**
		 * Text controls.
		 *
		 * @access public
		 * @since 6.0.0
		 * @param string $id         The ID.
		 * @param string $label      The label.
		 * @param string $desc       The description.
		 * @param array  $dependency The dependencies array.
		 * @param string $item_id    The ID of the menu item.
		 * @param object $item       The menu item object.
		 * @param string $save_id    The save ID if it is different from ID.
		 */
		public function text( $id, $label, $desc = '', $dependency = [], $item_id = 0, $item = null, $save_id = '' ) {
			?>
			<div class="fusion-builder-option field-<?php echo esc_attr( $id ); ?>">
				<div class="option-details">
					<h3><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
					<p class="description"><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</div>
				<div class="option-field fusion-builder-option-container">
					<input type="text" id="edit-menu-item-<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $item_id ); ?>" class="edit-menu-item-<?php echo esc_attr( $id ); ?>" name="menu-item-fusion-<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->{ $save_id } ); ?>" />
				</div>
			</div>
			<?php
		}

		/**
		 * Hidden controls.
		 *
		 * @access public
		 * @since 7.0.0
		 * @param string $id      The ID.
		 * @param string $item_id The ID of the menu item.
		 * @param object $item    The menu item object.
		 * @param string $value   The save ID if it is different from ID.
		 */
		public function hidden( $id, $item_id, $item, $value ) {
			?>
			<input type="hidden" id="edit-menu-item-<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $item_id ); ?>" class="edit-menu-item-<?php echo esc_attr( $id ); ?> <?php echo esc_attr( $id ); ?>" name="menu-item-<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
			<?php
		}

		/**
		 * Radio button set field.
		 *
		 * @access public
		 * @since 6.0.0
		 * @param string           $id         ID of input field.
		 * @param string           $label      Label of field.
		 * @param array            $options    Options to select from.
		 * @param string           $desc       Description of field.
		 * @param string|int|float $default    The default value.
		 * @param array            $dependency The dependencies array.
		 * @param string           $item_id    The ID of the menu item.
		 * @param object           $item       The menu item object.
		 * @param string           $save_id    The save ID if it is different from ID.
		 */
		public function radio_buttonset( $id, $label, $options, $desc = '', $default = '', $dependency = [], $item_id = 0, $item = null, $save_id = '' ) {
			?>
			<div class="fusion-builder-option field-<?php echo esc_attr( $id ); ?>">
				<div class="option-details">
					<h3><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
					<p class="description"><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</div>
				<div class="option-field fusion-builder-option-container avada-buttonset">
					<div class="fusion-form-radio-button-set ui-buttonset edit-menu-item-<?php echo esc_attr( $id ); ?>">
						<input type="hidden" id="edit-menu-item-fusion-<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $item_id ); ?>" name="menu-item-fusion-<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->{ $save_id } ); ?>" class="button-set-value" />
						<?php foreach ( $options as $value => $label ) : ?>
							<?php $value_check = ( '' !== $item->{ $save_id } ) ? $item->{ $save_id } : $default; ?>
							<a href="#" class="ui-button buttonset-item<?php echo ( $value === $value_check ) ? ' ui-state-active' : ''; ?>" data-value="<?php echo esc_attr( $value ); ?>"><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Select field.
		 *
		 * @access public
		 * @since 6.0.0
		 * @param string           $id         ID of input field.
		 * @param string           $label      Label of field.
		 * @param array            $options    Options to select from.
		 * @param string           $desc       Description of field.
		 * @param string|int|float $default    The default value.
		 * @param array            $dependency The dependencies array.
		 * @param string           $item_id    The ID of the menu item.
		 * @param object           $item       The menu item object.
		 * @param string           $save_id    The save ID if it is different from ID.
		 */
		public function select( $id, $label, $options, $desc = '', $default = '', $dependency = [], $item_id = 0, $item = null, $save_id = '' ) {
			?>
			<div class="fusion-builder-option field-<?php echo esc_attr( $id ); ?>">
				<div class="option-details">
					<h3><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
					<p class="description"><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</div>
				<div class="option-field fusion-builder-option-container">
					<select id="edit-menu-item-<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $item_id ); ?>" class="widefat code edit-menu-item-<?php echo esc_attr( $id ); ?>" name="menu-item-fusion-<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $item_id ); ?>]">
						<?php foreach ( $options as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $item->{ $save_id }, $value ); ?>><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<?php
		}

		/**
		 * Icon field.
		 *
		 * @access public
		 * @since 6.0.0
		 * @param string $id         ID of input field.
		 * @param string $label      Label of field.
		 * @param string $desc       Description of field.
		 * @param array  $dependency The dependencies array.
		 * @param string $item_id    The ID of the menu item.
		 * @param object $item       The menu item object.
		 * @param string $save_id    The save ID if it is different from ID.
		 */
		public function iconpicker( $id, $label, $desc = '', $dependency = [], $item_id = 0, $item = null, $save_id = '' ) {
			?>
			<div class="fusion-builder-option field-<?php echo esc_attr( $id ); ?>">
				<div class="option-details">
					<h3><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
					<p class="description"><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</div>
				<div class="option-field fusion-builder-option-container fusion-iconpicker">
					<span class="add-custom-icons">
						<a href="<?php echo esc_url( admin_url( '/post-new.php?post_type=fusion_icons' ) ); ?>" target="_blank" class="fusiona-plus" title="<?php echo esc_attr_e( 'Add Custom Icon Set', 'fusion-builder' ); ?>"></a>
					</span>
					<input type="text" class="fusion-icon-search" placeholder="<?php esc_attr_e( 'Search Icons', 'Avada' ); ?>" />
					<span class="input-icon fusiona-search"></span>
					<div class="icon_select_container"></div>
					<input type="hidden" id="edit-menu-item-<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $item_id ); ?>" class="fusion-iconpicker-input" name="menu-item-fusion-<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->{ $save_id } ); ?>" />
				</div>
			</div>
			<?php
		}

		/**
		 * Color alpha field.
		 *
		 * @access public
		 * @since 6.0.0
		 * @param string           $id         ID of input field.
		 * @param string           $label      Label of field.
		 * @param string           $desc       Description of field.
		 * @param string|int|float $default    The default value.
		 * @param array            $dependency The dependencies array.
		 * @param string           $item_id    The ID of the menu item.
		 * @param object           $item       The menu item object.
		 * @param string           $save_id    The save ID if it is different from ID.
		 */
		public function color_alpha( $id, $label, $desc = '', $default = '', $dependency = [], $item_id = 0, $item = null, $save_id = '' ) {
			?>
			<div class="fusion-builder-option field-<?php echo esc_attr( $id ); ?>">
				<div class="option-details">
					<h3><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
					<p class="description"><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</div>
				<div class="option-field fusion-builder-option-container pyre_field avada-color colorpickeralpha">
					<input type="text" id="edit-menu-item-<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $item_id ); ?>" class="edit-menu-item-<?php echo esc_attr( $id ); ?> fusion-builder-color-picker-hex color-picker" data-alpha="true" name="menu-item-fusion-<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->{ $save_id } ); ?>" />
				</div>
			</div>
			<?php
		}

		/**
		 * Color field.
		 * Alias of color-alpha.
		 *
		 * @access public
		 * @since 6.0.0
		 * @param string           $id         ID of input field.
		 * @param string           $label      Label of field.
		 * @param string           $desc       Description of field.
		 * @param string|int|float $default    The default value.
		 * @param array            $dependency The dependencies array.
		 * @param string           $item_id    The ID of the menu item.
		 * @param object           $item       The menu item object.
		 * @param string           $save_id    The save ID if it is different from ID.
		 */
		public function color( $id, $label, $desc = '', $default = '', $dependency = [], $item_id = 0, $item = null, $save_id = '' ) {
			$this->color_alpha( $id, $label, $desc, $default, $dependency, $item_id, $item, $save_id );
		}

		/**
		 * Media field.
		 *
		 * @access public
		 * @since 6.0.0
		 * @param string $id         ID of input field.
		 * @param string $label      Label of field.
		 * @param string $desc       Description of field.
		 * @param array  $dependency The dependencies array.
		 * @param string $item_id    The ID of the menu item.
		 * @param object $item       The menu item object.
		 * @param string $save_id    The save ID if it is different from ID.
		 */
		public function media( $id, $label, $desc = '', $dependency = [], $item_id = 0, $item = null, $save_id = '' ) {
			$media_id = str_replace( 'megamenu-', '', $id );
			?>
			<div class="fusion-builder-option field-<?php echo esc_attr( $id ); ?>">
				<div class="option-details">
					<h3><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
					<p class="description"><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</div>
				<div class="option-field fusion-builder-option-container">
					<div class="fusion-upload-image<?php echo ( isset( $item->{ $save_id } ) && '' !== $item->{ $save_id } ) ? ' fusion-image-set' : ''; ?>">
						<input type="hidden" id="edit-menu-item-<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $item_id ); ?>" class="regular-text fusion-builder-upload-field" name="menu-item-fusion-<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->{ $save_id } ); ?>" />
						<?php
						$thumbnail_id = isset( $item->fusion_megamenu_thumbnail ) ? $item->fusion_megamenu_thumbnail_id : 0;
						if ( ! $thumbnail_id && isset( $item->fusion_megamenu_thumbnail ) && '' !== $item->fusion_megamenu_thumbnail ) {
							$thumbnail_id = Fusion_Images::get_attachment_id_from_url( $item->fusion_megamenu_thumbnail );
						}
						?>
						<input type="hidden" id="edit-menu-item-<?php echo esc_attr( $id ); ?>-id-<?php echo esc_attr( $item_id ); ?>" class="regular-text fusion-builder-upload-field" name="menu-item-fusion-<?php echo esc_attr( $id ); ?>-id[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $thumbnail_id ); ?>" />
						<div class="fusion-builder-upload-preview">
							<img src="<?php echo esc_attr( $item->{ $save_id } ); ?>" id="fusion-media-img-<?php echo esc_attr( $media_id ); ?>-<?php echo esc_attr( $item_id ); ?>" class="fusion-<?php echo esc_attr( $id ); ?>-image" <?php echo ( trim( $item->fusion_megamenu_thumbnail ) ) ? 'style="display:inline;"' : ''; ?>" />
						</div>
						<input type='button' data-id="<?php echo esc_attr( $media_id ); ?>-<?php echo esc_attr( $item_id ); ?>" class='button-upload fusion-builder-upload-button avada-edit-button' data-type="image" value="<?php esc_attr_e( 'Edit', 'Avada' ); ?>" />
						<input type="button" data-id="<?php echo esc_attr( $media_id ); ?>-<?php echo esc_attr( $item_id ); ?>" class="upload-image-remove avada-remove-button" value="<?php esc_attr_e( 'Remove', 'Avada' ); ?>"  />
						<input type='button' data-id="<?php echo esc_attr( $media_id ); ?>-<?php echo esc_attr( $item_id ); ?>" class='button-upload fusion-builder-upload-button avada-upload-button' data-type="image" value="<?php esc_attr_e( 'Upload Image', 'Avada' ); ?>" />
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Adds the menu button fields.
		 *
		 * @access public
		 * @param string $item_id The ID of the menu item.
		 * @param object $item    The menu item object.
		 * @param int    $depth   The depth of the current item in the menu.
		 * @param array  $args    Menu arguments.
		 * @param int    $id      Menu ID.
		 * @return void.
		 */
		public function add_menu_button_fields( $item_id, $item, $depth, $args, $id ) {
			$name = 'menu-item-fusion-megamenu-style';
			?>
			<div class="fusion-menu-options-container">
				<a class="button button-primary button-large fusion-menu-option-trigger" href="#"><?php esc_html_e( 'Avada Menu Options', 'Avada' ); ?></a>
				<div class="fusion_builder_modal_overlay" style="display:none"></div>
				<div id="fusion-menu-options-<?php echo esc_attr( $item_id ); ?>" class="fusion-options-holder fusion-builder-modal-settings-container" style="display:none">
					<div class="fusion-builder-modal-container fusion_builder_module_settings">
						<div class="fusion-builder-modal-top-container">
							<h2><?php esc_html_e( 'Avada Menu Options', 'Avada' ); ?></h2>
							<div class="fusion-builder-modal-close fusiona-plus2"></div>
						</div>
						<div class="fusion-builder-modal-bottom-container">
							<a href="#" class="fusion-builder-modal-save" ><span><?php esc_html_e( 'Save', 'Avada' ); ?></span></a>
							<a href="#" class="fusion-builder-modal-close" ><span><?php esc_html_e( 'Cancel', 'Avada' ); ?></span></a>
						</div>
						<div class="fusion-builder-main-settings fusion-builder-main-settings-full">
							<div class="fusion-builder-module-settings">
								<?php $this->parse_options( $item_id, $item, $depth ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Adds the menu arrow light to main menu top level items.
		 *
		 * @since 5.3
		 * @access public
		 * @param string $title The menu item title markup.
		 * @param bool   $has_children Whether the menu item has children or not.
		 * @return string The extended title markup, including the menu arrow highlight.
		 */
		public function add_menu_arrow_highlight( $title, $has_children = false ) {
			$menu_highlight_style = Avada()->settings->get( 'menu_highlight_style' );
			$header_layout        = Avada()->settings->get( 'header_layout' );
			$svg                  = '';

			if ( 'arrow' === $menu_highlight_style && 'v6' !== $header_layout ) {
				$header_position = fusion_get_option( 'header_position' );
				$svg_height      = Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'height' ) );
				$svg_height_int  = intval( $svg_height );
				$svg_width       = Fusion_Sanitize::size( Avada()->settings->get( 'menu_arrow_size', 'width' ) );
				$svg_width_int   = intval( $svg_width );
				$svg_bg          = 'fill="' . Fusion_Sanitize::color( fusion_get_option( 'header_bg_color' ) ) . '"';
				$svg_border      = '';

				$header_2_3_border = ( 'v2' === $header_layout || 'v3' === $header_layout );
				$header_4_5_border = ( ( 'v4' === $header_layout || 'v5' === $header_layout ) && 1 === Fusion_Color::new_color( Fusion_Sanitize::color( fusion_get_option( 'header_bg_color' ) ) )->alpha );

				if ( 'top' !== $header_position || $header_2_3_border || $header_4_5_border ) {
					$svg_border = 'class="header_border_color_stroke" stroke-width="1"';
				}

				if ( 'left' === $header_position ) {
					$svg = '<span class="fusion-arrow-svg"><svg height="' . $svg_height . '" width="' . $svg_width . '">
						<path d="M0 0 L' . $svg_width_int . ' ' . ( $svg_height_int / 2 ) . ' L0 ' . $svg_height_int . ' Z" ' . $svg_bg . ' ' . $svg_border . '/>
						</svg></span>';
				} elseif ( 'right' === $header_position ) {
					$svg = '<span class="fusion-arrow-svg"><svg height="' . $svg_height . '" width="' . $svg_width . '">
					<path d="M' . $svg_width_int . ' 0 L0 ' . ( $svg_height_int / 2 ) . ' L' . $svg_width_int . ' ' . $svg_height_int . ' Z" ' . $svg_bg . ' ' . $svg_border . '/>
					</svg></span>';
				} elseif ( 'top' === $header_position ) {
					$svg = '<span class="fusion-arrow-svg"><svg height="' . $svg_height . '" width="' . $svg_width . '">
					<path d="M0 0 L' . ( $svg_width_int / 2 ) . ' ' . $svg_height_int . ' L' . $svg_width_int . ' 0 Z" ' . $svg_bg . ' ' . $svg_border . '/>
					</svg></span>';
				}

				// Add svg markup for dropdown.
				if ( $has_children ) {
					$svg_bg = 'fill="' . Fusion_Sanitize::color( Avada()->settings->get( 'menu_sub_bg_color' ) ) . '"';
					if ( 'top' === $header_position ) {
						$dropdownsvg = '<span class="fusion-dropdown-svg"><svg height="' . $svg_height . '" width="' . $svg_width . '">
						<path d="M0 ' . $svg_height_int . ' L' . ( $svg_width_int / 2 ) . ' 0 L' . $svg_width_int . ' ' . $svg_height_int . ' Z" ' . $svg_bg . '/>
						</svg></span>';
					} elseif ( 'left' === $header_position ) {
						$dropdownsvg = '<span class="fusion-dropdown-svg"><svg height="' . $svg_height . '" width="' . $svg_width . '">
						<path d="M' . $svg_width_int . ' 0 L0 ' . ( $svg_height_int / 2 ) . ' L' . $svg_width_int . ' ' . $svg_height_int . ' Z" ' . $svg_bg . '/>
						</svg></span>';
					} elseif ( 'right' === $header_position ) {
						$dropdownsvg = '<span class="fusion-dropdown-svg"><svg height="' . $svg_height . '" width="' . $svg_width . '">
						<path d="M0 0 L' . $svg_width_int . ' ' . ( $svg_height_int / 2 ) . ' L0 ' . $svg_height_int . ' Z" ' . $svg_bg . '/>
						</svg></span>';
					}
					$svg = $svg . $dropdownsvg;
				}
			}

			return $title . $svg;
		}

		/**
		 * Autocomplete controls.
		 *
		 * @access public
		 * @since 7.6
		 * @param string $id         The ID.
		 * @param string $label      The label.
		 * @param string $post_type  The post type.
		 * @param string $desc       The description.
		 * @param string $default    The default value.
		 * @param array  $dependency The dependencies array.
		 * @param string $item_id    The ID of the menu item.
		 * @param object $item       The menu item object.
		 * @param string $save_id    The save ID if it is different from ID.
		 * @param string $placeholder    Search text.
		 */
		public function autocomplete( $id, $label, $post_type = '', $desc = '', $default = '', $dependency = [], $item_id = 0, $item = null, $save_id = '', $placeholder = '' ) {
			$placeholder = $placeholder ? $placeholder : __( 'Search ...', 'Avada' );

			$value        = $item->{ $save_id };
			$name         = '' !== $value ? wp_strip_all_tags( get_the_title( $value ) ) : '';
			$search_class = '' !== $value ? 'hidden' : '';
			?>
			<div class="fusion-builder-option field-<?php echo esc_attr( $id ); ?>">
				<div class="option-details">
					<h3><?php echo $label; // phpcs:ignore WordPress.Security.EscapeOutput ?></h3>
					<p class="description"><?php echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</div>
				<div class="option-field fusion-builder-option-container autocomplete-container">
					<div class="fusion-autocomplete-wrap">
						<div class="selected-holder">
							<?php
							if ( '' !== $name ) {
								echo '<div class="item">' . $name . '<span class="remove fusiona-plus2"></span></div>'; // phpcs:ignore
							}
							?>
						</div>
						<input type="text" class="fusion-autocomplete-search <?php echo esc_attr( $search_class ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" data-post-type="<?php echo esc_attr( $post_type ); ?>" />
						<input type="hidden" id="edit-menu-item-<?php echo esc_attr( $id ); ?>-<?php echo esc_attr( $item_id ); ?>" class="edit-menu-item-<?php echo esc_attr( $id ); ?>  autocomplete-saved-value" name="menu-item-fusion-<?php echo esc_attr( $id ); ?>[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
						<div class="autocomplete-ajax-results"></div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Autocomplete ajax.
		 *
		 * @access public
		 * @since 7.6
		 */
		public function get_autocomplete() {
			check_ajax_referer( 'avada_mega_menu_nonce', 'nonce' );

			$keyword   = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
			$post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : 'post';

			ob_start();

			if ( '' !== $keyword ) {
				$args  = [
					'post_type'           => $post_type,
					'ignore_sticky_posts' => 1,
					'posts_per_page'      => -1, // phpcs:ignore WPThemeReview.CoreFunctionality.PostsPerPage.posts_per_page_posts_per_page
					's'                   => $keyword,
				];
				$query = new WP_Query( $args );
				if ( $query->have_posts() ) {
					echo '<div class="results">';
					while ( $query->have_posts() ) {
						$query->the_post();
						?>
								<a href="#" data-id="<?php echo esc_attr( get_the_ID() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
						<?php
					}
					echo '</div>';
				} else {
					echo esc_html__( 'Sorry, no posts matched your criteria.', 'Avada' );
				}
			}
			$content = ob_get_clean();

			$return_data = [
				'code' => 200,
				'html' => $content,
			];

			echo wp_json_encode( $return_data );
			wp_die();
		}
	}
}
/* Omit closing PHP tag to avoid "Headers already sent" issues. */
