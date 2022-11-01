<?php
/**
 * The main navwalker.
 *
 * @author    ThemeFusion
 * @copyright (c) Copyright by ThemeFusion
 * @link      https://theme-fusion.com
 * @package   Fusion-Library
 * @since     3.0
 */

/**
 * The main navwalker.
 */
class Fusion_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Do we use default styling or a button?
	 *
	 * @access  private
	 * @var string
	 */
	private $menu_style = '';

	/**
	 * Are we currently rendering a mega menu?
	 *
	 * @access  private
	 * @var string
	 */
	private $menu_megamenu_status = '';

	/**
	 * Use full width mega menu?
	 *
	 * @access  private
	 * @var string
	 */
	private $menu_megamenu_width = '';

	/**
	 * How many columns should the mega menu have?
	 *
	 * @access  private
	 * @var int
	 */
	private $num_of_columns = 0;

	/**
	 * Mega menu allow for 6 columns at max.
	 *
	 * @access  private
	 * @var int
	 */
	private $max_num_of_columns = 6;

	/**
	 * Total number of columns for a single megamenu?
	 *
	 * @access  private
	 * @var int
	 */
	private $total_num_of_columns = 0;

	/**
	 * Total number of columns for a single megamenu?
	 *
	 * @access  private
	 * @var int
	 */
	private $total_num_of_widgets = 0;

	/**
	 * Number of rows in the mega menu.
	 *
	 * @access  private
	 * @var int
	 */
	private $num_of_rows = 1;

	/**
	 * Holds number of columns per row.
	 *
	 * @access  private
	 * @var array
	 */
	private $submenu_matrix = [];

	/**
	 * How large is the width of a column?
	 *
	 * @access  private
	 * @var int|string
	 */
	private $menu_megamenu_columnwidth = 0;

	/**
	 * How large is the width of each row?
	 *
	 * @access  private
	 * @var array
	 */
	private $menu_megamenu_rowwidth_matrix = [];

	/**
	 * How large is the overall width of a column?
	 *
	 * @access private
	 * @var string
	 */
	private $menu_megamenu_maxwidth = '';

	/**
	 * Should a colum title be displayed?
	 *
	 * @access  private
	 * @var string
	 */
	private $menu_megamenu_title = '';

	/**
	 * Should one column be a widget area?
	 *
	 * @access  private
	 * @var string
	 */
	private $menu_megamenu_widget_area = '';

	/**
	 * Does the item have an icon?
	 *
	 * @access  private
	 * @var string
	 */
	private $menu_megamenu_icon = '';

	/**
	 * Does the item have a thumbnail?
	 *
	 * @access  private
	 * @var string
	 */
	private $menu_megamenu_thumbnail = '';


	/**
	 * Does the item have a background image?
	 *
	 * @access  private
	 * @var string
	 */
	private $menu_megamenu_background_image = '';

	/**
	 * Number of top level menu items.
	 *
	 * @since 5.7
	 * @access private
	 * @var init
	 */
	private $top_level_menu_items_count = 0;

	/**
	 * Middle logo menu breaking point
	 *
	 * @access  private
	 * @var init
	 */
	private $middle_logo_menu_break_point = null;

	/**
	 * Middle logo menu number of top level items displayed
	 *
	 * @access  private
	 * @var init
	 */
	private $no_of_top_level_items_displayed = 0;

	/**
	 * Holds the markup of the flyout menu background images.
	 *
	 * @since 5.7
	 * @access private
	 * @var string
	 */
	private $flyout_menu_bg_markup = '';

	/**
	 * Hold menu args..
	 *
	 * @since 6.0
	 * @access public
	 * @var string
	 */
	public $args = [];

	/**
	 * Holds info if previous column was a 100% column.
	 *
	 * @since 5.9.1
	 * @access private
	 * @var bool
	 */
	private $previous_column_was_100_percent = false;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 7.0
	 * @param array $args The nav arguments.
	 */
	public function __construct( $args = [] ) {
		$this->args = array_merge(
			[
				'header_layout'                   => fusion_get_option( 'header_layout' ),
				'header_position'                 => fusion_get_option( 'header_position' ),
				'megamenu_width'                  => fusion_get_option( 'megamenu_width' ),
				'side_header_width'               => fusion_get_option( 'side_header_width' ),
				'megamenu_interior_content_width' => fusion_get_option( 'megamenu_interior_content_width' ),
				'site_width'                      => fusion_get_option( 'site_width' ),
				'megamenu_max_width'              => fusion_get_option( 'megamenu_max_width' ),
				'menu_icon_position'              => fusion_get_option( 'menu_icon_position' ),
				'menu_display_dropdown_indicator' => fusion_get_option( 'menu_display_dropdown_indicator' ),
				'menu_highlight_style'            => fusion_get_option( 'menu_highlight_style' ),
				'main_nav_search_icon'            => fusion_get_option( 'main_nav_search_icon' ),
				'woocommerce_cart_link_main_nav'  => fusion_get_option( 'woocommerce_cart_link_main_nav' ),
				'woocommerce_acc_link_main_nav'   => fusion_get_option( 'woocommerce_acc_link_main_nav' ),
				'slidingbar_toggle_style'         => fusion_get_option( 'slidingbar_toggle_style' ),
				'slidingbar_widgets'              => fusion_get_option( 'slidingbar_widgets' ),
				'disable_megamenu'                => fusion_get_option( 'disable_megamenu' ),
				'button_type'                     => fusion_get_option( 'button_type' ),
				'woocommerce_acc_link_main_nav'   => fusion_get_option( 'woocommerce_acc_link_main_nav' ),
				'logo_url'                        => fusion_get_option( 'logo', 'url' ),
				'logo_retina_url'                 => fusion_get_option( 'logo_retina', 'url' ),
				'disable_highlight_arrows'        => false,
				'fb_menu_element'                 => false,
				'submenu_mode'                    => 'dropdown',
				'transition_type'                 => '',
				'expand_method'                   => 'hover',
				'lazy_load'                       => 'avada' === fusion_get_option( 'lazy_load' ),
			],
			$args
		);
	}

	/**
	 * Start level.
	 *
	 * @see Walker::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth Depth of page. Used for padding.
	 * @param  array  $args Not used.
	 */
	public function start_lvl( &$output, $depth = 0, $args = [] ) {

		if ( 0 === $depth && 'enabled' === $this->menu_megamenu_status ) {
			$output .= '{first_level}';
			$output .= '<div class="fusion-megamenu-holder' . ( $this->args['lazy_load'] ? ' lazyload' : '' ) . '" {megamenu_final_width}><ul class="fusion-megamenu{megamenu_border}{megamenu_interior_width}>';
		} elseif ( 2 <= $depth && 'enabled' === $this->menu_megamenu_status ) {
			$output .= '<ul class="sub-menu deep-level">';
		} else {
			$output .= '<ul class="sub-menu">';
		}

	}


	/**
	 * End level.
	 *
	 * @see Walker::end_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth Depth of page. Used for padding.
	 * @param  array  $args Not used.
	 */
	public function end_lvl( &$output, $depth = 0, $args = [] ) {

		$header_layout = $this->args['header_layout'];

		if ( 0 === $depth && 'enabled' === $this->menu_megamenu_status ) {

			$output .= '</ul></div><div style="clear:both;"></div></div></div>';

			$col_span = ' col-span-' . $this->max_num_of_columns * 2;
			if ( $this->total_num_of_columns < $this->max_num_of_columns ) {
				$col_span = ' col-span-' . $this->total_num_of_columns * 2;
			}

			$total_column_width_per_row = [];
			foreach ( $this->menu_megamenu_rowwidth_matrix as $row => $columns ) {
				$total_column_width_per_row[ $row ] = array_sum( $columns );
			}
			$max_row_width = max( $total_column_width_per_row );

			$megamenu_interior_width = '"';

			// Set overall width of megamenu.
			$megamenu_width = $this->args['megamenu_width'];
			if ( 'viewport_width' === $megamenu_width ) {
				if ( 'top' !== $this->args['header_position'] ) {
					$site_header_width            = (int) $this->args['side_header_width'];
					$this->menu_megamenu_maxwidth = 'calc(100vw - ' . $site_header_width . 'px)';
					$wrapper_width                = 'calc(' . $max_row_width . ' * (100vw - ' . $site_header_width . 'px))';
				} else {
					$this->menu_megamenu_maxwidth = '100vw';
					$wrapper_width                = ( $max_row_width * 100 ) . 'vw';

					if ( 'site_width' === $this->args['megamenu_interior_content_width'] && 'fullwidth' === $this->menu_megamenu_width ) {
						$megamenu_interior_width = ' fusion-megamenu-sitewidth" style="margin: 0 auto;width: 100%;max-width: ' . str_replace( '%', 'vw', $this->args['site_width'] ) . ';"';
					}
				}
			} elseif ( 'site_width' === $megamenu_width ) {
				$this->menu_megamenu_maxwidth = str_replace( '%', 'vw', $this->args['site_width'] );

				if ( false === strpos( $this->menu_megamenu_maxwidth, 'calc' ) ) {
					$wrapper_width = ( $max_row_width * Fusion_Sanitize::number( $this->menu_megamenu_maxwidth ) ) . Fusion_Sanitize::get_unit( $this->menu_megamenu_maxwidth );
				} else {
					$wrapper_width = 'calc(' . $max_row_width . ' * (' . str_replace( [ 'calc(', ')' ], [ '', '' ], $this->menu_megamenu_maxwidth ) . '))';
				}
			} else {
				$this->menu_megamenu_maxwidth = (int) $this->args['megamenu_max_width'] . 'px';
				$wrapper_width                = ( $max_row_width * (int) $this->menu_megamenu_maxwidth ) . 'px';
			}

			if ( 'fullwidth' === $this->menu_megamenu_width ) {
				$col_span = ' col-span-12 fusion-megamenu-fullwidth';
				if ( 'site_width' === $megamenu_width ) {
					$col_span .= ' fusion-megamenu-width-site-width';
				}

				// Overall megamenu wrapper width in px is max width for fullwidth megamenu.
				$wrapper_width = $this->menu_megamenu_maxwidth;
			}

			$background_image = '';
			$data_bg          = '';
			if ( ! empty( $this->menu_megamenu_background_image ) ) {
				if ( $this->args['lazy_load'] ) {
					$data_bg = ' data-bg="' . $this->menu_megamenu_background_image . '"';
				} else {
					$background_image .= ';background-image: url(' . $this->menu_megamenu_background_image . ');';
				}
			}

			$output = str_replace( '{first_level}', '<div class="fusion-megamenu-wrapper {fusion_columns} columns-' . $this->total_num_of_columns . $col_span . '"><div class="row">', $output );
			$output = str_replace( '{megamenu_final_width}', 'style="width:' . $wrapper_width . $background_image . ';"' . $data_bg . ' data-width="' . $wrapper_width . '"', $output );
			$output = str_replace( '{megamenu_interior_width}', $megamenu_interior_width, $output );
			$output = str_replace( '{fusion_all_widgets}', $this->total_num_of_widgets === $this->total_num_of_columns ? 'fusion-has-all-widgets' : '', $output );

			$replacement = ( $this->total_num_of_columns > $this->max_num_of_columns ) ? ' fusion-megamenu-border' : '';
			$output      = str_replace( '{megamenu_border}', $replacement, $output );

			foreach ( $this->submenu_matrix as $row => $columns ) {

				$layout_columns = 12 / $columns;
				$layout_columns = ( 5 === $columns ) ? 2 : $layout_columns;

				$replacement  = 'fusion-megamenu-row-columns-' . $columns;
				$replacement .= ( ( $row - 1 ) * $this->max_num_of_columns + $columns < $this->total_num_of_columns ) ? ' fusion-megamenu-border' : '';

				$output = str_replace( '{row_number_' . $row . '}', $replacement, $output );

				$replacement = ( count( $this->submenu_matrix ) === $row ) ? '' : 'fusion-megamenu-border';
				$output      = str_replace( '{force_row_border_' . $row . '}', $replacement, $output );

				$output = str_replace( '{current_row_' . $row . '}', 'fusion-megamenu-columns-' . $columns . ' col-lg-' . $layout_columns . ' col-md-' . $layout_columns . ' col-sm-' . $layout_columns, $output );
				$output = str_replace( '{fusion_columns}', 'fusion-columns-' . $columns . ' columns-per-row-' . $columns, $output );
			}

			foreach ( $this->menu_megamenu_rowwidth_matrix as $row => $columns ) {
				foreach ( $columns as $column => $column_width ) {
					$weighted_width = ( 100 / $max_row_width * $column_width ) . '%';
					$output         = str_replace( '{column_width_' . $row . '_' . $column . '}', $weighted_width, $output );
				}
			}
		} else {
			$output .= '</ul>';
		}
	}

	/**
	 * Start element.
	 *
	 * @see Walker::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int    $depth Depth of menu item. Used for padding.
	 * @param array  $args The arguments.
	 * @param int    $id Menu item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) {

		$item_output          = '';
		$class_columns        = '';
		$menu_highlight_label = '';

		$is_rtl                          = is_rtl();
		$header_layout                   = $this->args['header_layout'];
		$header_position                 = $this->args['header_position'];
		$menu_icon_position              = $this->args['menu_icon_position'];
		$menu_display_dropdown_indicator = $this->args['menu_display_dropdown_indicator'];
		$fb_menu_element                 = $this->args['fb_menu_element'];
		$expand_method                   = $this->args['expand_method'];
		$menu_highlight_style            = $this->args['menu_highlight_style'];
		$transition_type                 = $this->args['transition_type'];

		/**
		 * Filters the arguments for a single nav menu item.
		 *
		 * @since 4.4.0
		 *
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param WP_Post  $item  Menu item data object.
		 * @param int      $depth Depth of menu item. Used for padding.
		 */
		$args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

		if ( null === $item->menu_item_parent ) {
			$item->menu_item_parent = '0';
		}

		if ( ! $this->top_level_menu_items_count ) {

			if ( 'v6' === $header_layout || 'v7' === $header_layout || ( $fb_menu_element && 'flyout' === $this->args['submenu_mode'] ) ) {
				$menu_elements = wp_get_nav_menu_items(
					$args->menu,
					[
						'meta_key'   => '_menu_item_menu_item_parent', // phpcs:ignore WordPress.DB.SlowDBQuery
						'meta_value' => '0', // phpcs:ignore WordPress.DB.SlowDBQuery
					]
				);

				// Array of menu item types we don't consider as 'top level' menu items.
				$exclude_menu_items = apply_filters( 'fusion_exclude_top_menu_items', [ 'wpml_ls_menu_item' ] );

				if ( is_array( $menu_elements ) ) {

					foreach ( $menu_elements as $key => $menu_element ) {
						if ( isset( $menu_element->type ) && in_array( $menu_element->type, $exclude_menu_items, true ) ) {
							unset( $menu_elements[ $key ] );
						}
					}

					$this->top_level_menu_items_count = count( $menu_elements );

					foreach ( $menu_elements as $menu_element ) {
						if ( null === $menu_element->menu_item_parent ) {
							$menu_element->menu_item_parent = '0';
						}
					}
				}
			}

			if ( 'v6' === $header_layout || 'v7' === $header_layout ) {
				$is_search_icon_enabled      = (int) $this->args['main_nav_search_icon'];
				$is_cart_icon_enabled        = (int) $this->args['woocommerce_cart_link_main_nav'];
				$is_my_account_menu_enabled  = (int) $this->args['woocommerce_acc_link_main_nav'];
				$is_sliding_bar_icon_enabled = (int) 'menu' === $this->args['slidingbar_toggle_style'] && $this->args['slidingbar_widgets'];

				if ( 'v7' === $header_layout ) {
					$this->top_level_menu_items_count += $is_search_icon_enabled + $is_cart_icon_enabled + $is_my_account_menu_enabled + $is_sliding_bar_icon_enabled;

					if ( 0 === $this->top_level_menu_items_count ) {
						$this->middle_logo_menu_break_point = $this->top_level_menu_items_count / 2;
					} else {
						$this->middle_logo_menu_break_point = ceil( $this->top_level_menu_items_count / 2 );
						if ( $is_search_icon_enabled || $is_cart_icon_enabled || $is_sliding_bar_icon_enabled ) {
							$this->middle_logo_menu_break_point = floor( $this->top_level_menu_items_count / 2 );
						}
					}

					$this->middle_logo_menu_break_point = apply_filters( 'avada_middle_logo_menu_break_point', $this->middle_logo_menu_break_point );
				}
			}
		}

		// Set some vars.
		$meta_data   = get_post_meta( $item->ID );
		$fusion_meta = ! empty( $meta_data['_menu_item_fusion_megamenu'][0] ) ? maybe_unserialize( $meta_data['_menu_item_fusion_megamenu'][0] ) : [];

		$fusion_meta = apply_filters( 'avada_menu_meta', $fusion_meta, $item->ID );

		// Skip special links if old menu setup.
		if ( ! $fb_menu_element && isset( $fusion_meta['special_link'] ) && '' !== $fusion_meta['special_link'] ) {
			return '';
		}

		$this->menu_style          = isset( $fusion_meta['style'] ) ? $fusion_meta['style'] : '';
		$this->menu_megamenu_icon  = isset( $fusion_meta['icon'] ) ? $fusion_meta['icon'] : '';
		$this->menu_megamenu_modal = isset( $fusion_meta['modal'] ) ? $fusion_meta['modal'] : '';
		$this->menu_title_only     = isset( $fusion_meta['icononly'] ) ? $fusion_meta['icononly'] : '';

		$this->fusion_highlight_label              = isset( $fusion_meta['highlight_label'] ) ? $fusion_meta['highlight_label'] : '';
		$this->fusion_highlight_label_background   = isset( $fusion_meta['highlight_label_background'] ) ? $fusion_meta['highlight_label_background'] : '';
		$this->fusion_highlight_label_color        = isset( $fusion_meta['highlight_label_color'] ) ? $fusion_meta['highlight_label_color'] : '';
		$this->fusion_highlight_label_border_color = isset( $fusion_meta['highlight_label_border_color'] ) ? $fusion_meta['highlight_label_border_color'] : '';

		// Add the bg image markup for flyout menu items.
		if ( ( 0 === $depth && 'v6' === $header_layout || ( $fb_menu_element && 'flyout' === $this->args['submenu_mode'] ) ) && isset( $fusion_meta['background_image'] ) && '' !== $fusion_meta['background_image'] ) {
			if ( $this->args['lazy_load'] ) {
				$this->flyout_menu_bg_markup .= '<div id="item-bg-' . $item->ID . '" class="fusion-flyout-menu-item-bg lazyload" data-bg="' . $fusion_meta['background_image'] . '"></div>';
			} else {
				$this->flyout_menu_bg_markup .= '<div id="item-bg-' . $item->ID . '" class="fusion-flyout-menu-item-bg" style="background-image:url(' . $fusion_meta['background_image'] . ');"></div>';
			}
		}

		if ( ! empty( $item->fusion_highlight_label ) ) {

			$highlight_style = '';

			if ( ! empty( $item->fusion_highlight_label_background ) ) {
				$highlight_style .= 'background-color:' . $item->fusion_highlight_label_background . ';';
			}

			if ( ! empty( $item->fusion_highlight_label_border_color ) ) {
				$highlight_style .= 'border-color:' . $item->fusion_highlight_label_border_color . ';';
			}

			if ( ! empty( $item->fusion_highlight_label_color ) ) {
				$highlight_style .= 'color:' . $item->fusion_highlight_label_color . ';';
			}

			$menu_highlight_label = '<span class="fusion-menu-highlight-label" style="' . esc_attr( $highlight_style ) . '">' . esc_html( $item->fusion_highlight_label ) . '</span>';
		}

		// Megamenu is enabled.
		if ( $this->args['disable_megamenu'] && 'top_navigation' !== $args->theme_location ) {
			if ( 0 === $depth ) {
				$this->menu_megamenu_status = isset( $fusion_meta['status'] ) ? $fusion_meta['status'] : 'disabled';
				$this->menu_megamenu_width  = isset( $fusion_meta['width'] ) ? $fusion_meta['width'] : '';
				$allowed_columns            = isset( $fusion_meta['columns'] ) ? $fusion_meta['columns'] : '';
				if ( 'auto' !== $allowed_columns ) {
					$this->max_num_of_columns = (int) $allowed_columns;
				}
				$this->num_of_columns                = 0;
				$this->total_num_of_columns          = 0;
				$this->total_num_of_widgets          = 0;
				$this->num_of_rows                   = 1;
				$this->menu_megamenu_rowwidth_matrix = [];
				$this->menu_megamenu_rowwidth_matrix[ $this->num_of_rows ] = [];

				$this->menu_megamenu_background_image = isset( $fusion_meta['background_image'] ) ? $fusion_meta['background_image'] : '';
			} elseif ( 1 === $depth ) {
				$megamenu_column_background_image = isset( $fusion_meta['background_image'] ) ? $fusion_meta['background_image'] : '';
			}

			$this->menu_megamenu_title      = isset( $fusion_meta['title'] ) ? $fusion_meta['title'] : '';
			$this->menu_megamenu_widgetarea = isset( $fusion_meta['widgetarea'] ) ? $fusion_meta['widgetarea'] : '';

			if ( ! empty( $fusion_meta['thumbnail'] ) ) {
				$thumbnail_id = isset( $fusion_meta['thumbnail_id'] ) ? $fusion_meta['thumbnail_id'] : 0;

				$thumbnail_data = fusion_library()->images->get_attachment_data_by_helper( $thumbnail_id, $fusion_meta['thumbnail'] );

				if ( $thumbnail_data ) {
					$this->menu_megamenu_thumbnail = '<img src="' . $thumbnail_data['url'] . '" alt="' . $thumbnail_data['alt'] . '" title="' . $thumbnail_data['title'] . '">';
				} else {
					$this->menu_megamenu_thumbnail = '<img src="' . $fusion_meta['thumbnail'] . '">';
				}
			} else {
				$this->menu_megamenu_thumbnail = '';
			}

			// Megamenu is disabled.
		} else {
			$this->menu_megamenu_status = 'disabled';
		}

		// We are inside a megamenu.
		if ( 1 === $depth && 'enabled' === $this->menu_megamenu_status ) {

			if ( isset( $fusion_meta['columnwidth'] ) && $fusion_meta['columnwidth'] ) {
				$this->menu_megamenu_columnwidth = floatval( $fusion_meta['columnwidth'] ) . '%';
			} else {
				$this->menu_megamenu_columnwidth = '16.6666%';
				if ( 'fullwidth' === $this->menu_megamenu_width && $this->max_num_of_columns ) {
					$this->menu_megamenu_columnwidth = 100 / $this->max_num_of_columns . '%';
				} elseif ( 1 === $this->max_num_of_columns ) {
					$this->menu_megamenu_columnwidth = '100%';
				}
			}

			$this->num_of_columns++;
			$this->total_num_of_columns++;

			// Check if we need to start a new row.
			if ( $this->num_of_columns > $this->max_num_of_columns || $this->previous_column_was_100_percent ) {
				$this->num_of_columns = 1;
				$this->num_of_rows++;
				$force_row_border = '';

				if ( $this->previous_column_was_100_percent ) {
					$this->previous_column_was_100_percent = false;
					$force_row_border                      = ' {force_row_border_' . $this->num_of_rows . '}';
				}

				$output .= '</ul><ul class="fusion-megamenu fusion-megamenu-row-' . $this->num_of_rows . ' {row_number_' . $this->num_of_rows . '}' . $force_row_border . '{megamenu_interior_width}>';
			}

			$this->menu_megamenu_rowwidth_matrix[ $this->num_of_rows ][ $this->num_of_columns ] = floatval( $this->menu_megamenu_columnwidth ) / 100;

			if ( isset( $fusion_meta['columnwidth'] ) && '100%' === $this->menu_megamenu_columnwidth && 'fullwidth' !== $this->menu_megamenu_width ) {
				$this->previous_column_was_100_percent = true;
			}

			$this->submenu_matrix[ $this->num_of_rows ] = $this->num_of_columns;

			if ( $this->max_num_of_columns < $this->num_of_columns ) {
				$this->max_num_of_columns = $this->num_of_columns;
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );

			/**
			 * Filters a menu item's title.
			 *
			 * @since 4.4.0
			 *
			 * @param string   $title The menu item's title.
			 * @param WP_Post  $item  The current menu item.
			 * @param stdClass $args  An object of wp_nav_menu() arguments.
			 * @param int      $depth Depth of menu item. Used for padding.
			 */
			$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

			if ( ! ( ( empty( $item->url ) || '#' === $item->url || 'http://' === $item->url ) && 'disabled' === $this->menu_megamenu_title ) ) {
				$heading      = do_shortcode( $title );
				$link         = '<span class="awb-justify-title">';
				$link_closing = '</span>';
				$target       = '';
				$link_class   = '';

				if ( ! empty( $item->url ) && '#' !== $item->url && 'http://' !== $item->url ) {
					$link_class = 'awb-justify-title';

					if ( ! empty( $item->target ) ) {
						$target = ' target="' . $item->target . '"';
					}
					if ( 'disabled' === $this->menu_megamenu_title ) {
						$link_class .= ' fusion-megamenu-title-disabled';

						$link         = '<a class="' . $link_class . '" href="' . $item->url . '"' . $target . '><span>';
						$link_closing = '</span></a>';                      
					} else {
						$link         = '<a class="' . $link_class . '" href="' . $item->url . '"' . $target . '>';
						$link_closing = '</a>';
					}

					if ( $this->menu_megamenu_widgetarea && is_active_sidebar( $this->menu_megamenu_widgetarea ) ) {
						$this->total_num_of_widgets--;
					}
				}

				// Check if we need to set an image.
				$title_enhance = '';
				if ( ! empty( $this->menu_megamenu_thumbnail ) ) {
					$title_enhance = '<span class="fusion-megamenu-icon fusion-megamenu-thumbnail">' . $this->menu_megamenu_thumbnail . '</span>';
				} elseif ( ! empty( $this->menu_megamenu_icon ) ) {
					$title_enhance = '<span class="fusion-megamenu-icon"><i class="glyphicon ' . fusion_font_awesome_name_handler( $this->menu_megamenu_icon ) . '" aria-hidden="true"></i></span>';
				} elseif ( 'disabled' === $this->menu_megamenu_title ) {
					$title_enhance = '<span class="fusion-megamenu-bullet"></span>';
				}

				$heading         = $link . $title_enhance . $title . $menu_highlight_label . $link_closing;
				$menu_icon_right = ( ( ! $is_rtl && 'right' === $menu_icon_position ) || ( $is_rtl && 'left' === $menu_icon_position ) );
				// If we have an icon or thumbnail and the position is not left, then change order.
				if ( 0 === $depth && ( ! empty( $this->menu_megamenu_icon ) || ! empty( $this->menu_megamenu_thumbnail ) ) && $menu_icon_right ) {
					$heading = $link . $title . $title_enhance . $link_closing;
				}
				if ( 'disabled' !== $this->menu_megamenu_title ) {
					$item_output .= "<div class='fusion-megamenu-title'>" . $heading . '</div>';
				} else {
					$item_output .= $heading;
				}
			}

			if ( $this->menu_megamenu_widgetarea && is_active_sidebar( $this->menu_megamenu_widgetarea ) ) {
				$this->total_num_of_widgets++;
				ob_start();
				dynamic_sidebar( $this->menu_megamenu_widgetarea );
				$item_output .= '<div class="fusion-megamenu-widgets-container second-level-widget">' . ob_get_clean() . '</div>';
			}

			$class_columns = ' {current_row_' . $this->num_of_rows . '}';

		} elseif ( 2 === $depth && 'enabled' === $this->menu_megamenu_status && $this->menu_megamenu_widgetarea ) {

			if ( is_active_sidebar( $this->menu_megamenu_widgetarea ) ) {
				ob_start();
				dynamic_sidebar( $this->menu_megamenu_widgetarea );
				$item_output .= '<div class="fusion-megamenu-widgets-container third-level-widget">' . ob_get_clean() . '</div>';
			}
		} else {

			$atts = [
				'title'  => ! empty( $item->attr_title ) ? esc_attr( $item->attr_title ) : '',
				'target' => ! empty( $item->target ) ? esc_attr( $item->target ) : '',
				'rel'    => ! empty( $item->xfn ) ? esc_attr( $item->xfn ) : '',
				'href'   => ! empty( $item->url ) ? esc_attr( $item->url ) : '',
				'class'  => [],
			];

			// Add off canvas menu item.
			if ( isset( $fusion_meta['special_link'] ) && 'awb-off-canvas-menu-trigger' === $fusion_meta['special_link'] && class_exists( 'AWB_Off_Canvas_Front_End' ) && ! empty( $fusion_meta['off_canvas_id'] ) && class_exists( 'AWB_Off_Canvas' ) && false !== AWB_Off_Canvas::is_enabled() ) {
				AWB_Off_Canvas_Front_End::add_off_canvas_to_stack( $fusion_meta['off_canvas_id'] );
				$atts['href']    = '#awb-oc__' . $fusion_meta['off_canvas_id'];
				$atts['class'][] = 'awb-oc-menu-item-link';
			}
			if ( 'v7' === $header_layout && '0' === $item->menu_item_parent ) {
				$atts['class'][] = 'fusion-top-level-link';
			}

			if ( 'icononly' === $this->menu_title_only && 0 === $depth ) {
				$atts['class'][] = 'fusion-icon-only-link';
			}

			if ( ( ! empty( $this->menu_megamenu_icon ) || ! empty( $this->menu_megamenu_thumbnail ) || $item->description ) && ! $this->menu_style && 0 === $depth ) {
				$atts['class'][] = 'fusion-flex-link';
				if ( 'top' === $menu_icon_position || 'bottom' === $menu_icon_position ) {
					$atts['class'][] = 'fusion-flex-column';
				}
			}

			$atts['class'][] = 'fusion-' . $menu_highlight_style . '-highlight';

			if ( ! empty( $menu_highlight_label ) ) {
				$atts['class'][] = 'fusion-has-highlight-label';
			}

			if ( 0 === $depth && $item->description ) {
				$atts['class'][] = 'fusion-has-description';
			}

			if ( '_blank' === $atts['target'] ) {
				$atts['rel'] = ( ( $atts['rel'] ) ? $atts['rel'] . ' noopener noreferrer' : 'noopener noreferrer' );
			}

			if ( '' !== $this->menu_megamenu_modal ) {
				$atts['data-toggle'] = 'modal';
				$atts['data-target'] = '.' . $this->menu_megamenu_modal;
			}

			$atts['class'] = implode( ' ', $atts['class'] );

			/**
			 * Filters the HTML attributes applied to a menu item's anchor element.
			 *
			 * @since 3.6.0
			 * @since 4.1.0 The `$depth` parameter was added.
			 *
			 * @param array $atts {
			 *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
			 *
			 *     @type string $title  Title attribute.
			 *     @type string $target Target attribute.
			 *     @type string $rel    The rel attribute.
			 *     @type string $href   The href attribute.
			 * }
			 * @param WP_Post  $item  The current menu item.
			 * @param stdClass $args  An object of wp_nav_menu() arguments.
			 * @param int      $depth Depth of menu item. Used for padding.
			 */
			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}

			$item_output .= $args->before . '<a ' . $attributes . '>';

			// For right side header add the caret icon at the beginning.
			if ( $args->has_children && ( ( 'parent' === $menu_display_dropdown_indicator && 0 === $depth ) || 'parent_child' === $menu_display_dropdown_indicator ) && 'v6' !== $header_layout && ( 'right' === $header_position && ! $is_rtl || 'left' === $header_position && $is_rtl ) && ! $this->menu_style ) {
				$item_output .= ' <span class="fusion-caret"><i class="fusion-dropdown-indicator" aria-hidden="true"></i></span>';
			}

			// Check if we need to set an image.
			$icon_wrapper_class = 'fusion-megamenu-icon';

			if ( 0 === $depth ) {
				if ( $is_rtl && 'left' === $this->args['menu_icon_position'] ) {
					$icon_wrapper_class .= ' fusion-megamenu-icon-left';
				}

				if ( $this->menu_style ) {
					$icon_wrapper_class = ( $is_rtl ) ? 'button-icon-divider-right' : 'button-icon-divider-left';
				}
			}

			$icon = '';

			// If its a side header, make sure icons are fixed size.
			if ( ! empty( $this->menu_megamenu_icon ) && 'top' !== $header_position ) {
				$this->menu_megamenu_icon .= ' fa-fw';
			}
			if ( ! empty( $this->menu_megamenu_thumbnail ) && 'enabled' === $this->menu_megamenu_status ) {
				$icon = '<span class="' . $icon_wrapper_class . ' fusion-megamenu-image">' . $this->menu_megamenu_thumbnail . '</span>';
			} elseif ( ! empty( $this->menu_megamenu_icon ) ) {
				$icon = '<span class="' . $icon_wrapper_class . '"><i class="glyphicon ' . fusion_font_awesome_name_handler( $this->menu_megamenu_icon ) . '" aria-hidden="true"></i></span>';
			} elseif ( 0 !== $depth && 'enabled' === $this->menu_megamenu_status ) {
				$icon = '<span class="fusion-megamenu-bullet"></span>';
			}
			if ( isset( $fusion_meta['special_link'] ) && 'awb-off-canvas-menu-trigger' === $fusion_meta['special_link'] && class_exists( 'AWB_Off_Canvas_Front_End' ) && ! empty( $fusion_meta['off_canvas_id'] ) && class_exists( 'AWB_Off_Canvas' ) && false !== AWB_Off_Canvas::is_enabled() ) {
				$icon .= '' !== $icon ? '<span class="fusion-megamenu-icon awb-oc-close-icon"></span>' : '';
			}

			$classes = '';
			// Check if we have a menu button.
			if ( 0 === $depth ) {
				$classes = 'menu-text';
				if ( $this->menu_style ) {
					$classes .= ' fusion-button button-default ' . str_replace( 'fusion-', '', $this->menu_style );
					// Button should have 3D effect.
					if ( '3d' === $this->args['button_type'] ) {
						$classes .= ' button-3d';
					}
				}
			}

			$title = $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;

			/**
			 * Filters a menu item's title.
			 *
			 * @since 4.4.0
			 *
			 * @param string   $title The menu item's title.
			 * @param WP_Post  $item  The current menu item.
			 * @param stdClass $args  An object of wp_nav_menu() arguments.
			 * @param int      $depth Depth of menu item. Used for padding.
			 */
			$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

			// If we are top level, not using a button and have a description, then add that to the title.
			if ( $item->description && ( 0 === $depth || true === $this->args['fb_menu_element'] ) && ! $this->menu_style ) {
				$title .= '<span class="fusion-menu-description">' . $item->description . '</span>';
			}

			if ( ! empty( $menu_highlight_label ) ) {
				$title .= $menu_highlight_label;
			}

			if ( false !== strpos( $icon, 'button-icon-divider-left' ) ) {
				$title = '<span class="fusion-button-text-left">' . $title . '</span>';
			} elseif ( false !== strpos( $icon, 'button-icon-divider-right' ) ) {
				$title = '<span class="fusion-button-text-right">' . $title . '</span>';
			} elseif ( 'icononly' === $this->menu_title_only && 0 === $depth ) {
				$title = '<span class="menu-title">' . $title . '</span>';
			}

			// SVG creation for menu item hover/active.
			if ( ! $this->args['disable_highlight_arrows'] && 0 === $depth && ( ! $this->menu_style || $args->has_children ) ) {
				$title = apply_filters( 'avada_menu_arrow_hightlight', $title, $args->has_children );
			}

			$menu_icon_right = ( ( ! $is_rtl && 'right' === $menu_icon_position ) || ( $is_rtl && 'left' === $menu_icon_position ) );

			$opening_span = ( $classes ) ? '<span class="' . $classes . '">' : '<span>';

			// If we have an icon or thumbnail and the position is not left, then change order.
			if (
				( ! empty( $this->menu_megamenu_icon ) || ! empty( $this->menu_megamenu_thumbnail ) ) && ( $menu_icon_right || 'bottom' === $menu_icon_position ) && ! $this->menu_style && 0 === $depth ) {
				$item_output = $item_output . $opening_span . $title . '</span>' . $icon;
			} elseif ( $this->menu_style || 0 !== $depth ) {
				$item_output = $item_output . $opening_span . $icon . $title . '</span>';
			} else {
				$item_output = $item_output . $icon . $opening_span . $title . '</span>';
			}

			// For top header and left side header add the caret icon at the end.
			if ( $args->has_children && ( ( 'parent' === $menu_display_dropdown_indicator && 0 === $depth ) || 'parent_child' === $menu_display_dropdown_indicator ) && 'v6' !== $header_layout && ( ( 'right' !== $header_position && ! $is_rtl ) || ( 'left' !== $header_position && $is_rtl ) ) && ( ! $this->menu_style || $fb_menu_element ) ) {
				if ( $fb_menu_element ) {
					$item_output .= 'hover' === $expand_method ? '<span class="fusion-open-nav-submenu"></span>' : '';
					$item_output .= '</a>' . $args->after;
					/* Translators: The menu item title. */
					$item_output .= '<button type="button" aria-label="' . esc_attr( sprintf( __( 'Open submenu of %s', 'fusion-builder' ), $item->title ) ) . '" aria-expanded="false" class="fusion-open-nav-submenu fusion-open-nav-submenu-on-click" onclick="fusionNavClickExpandSubmenuBtn(this);"></button>';
				} else {
					$item_output .= ' <span class="fusion-caret"><i class="fusion-dropdown-indicator" aria-hidden="true"></i></span>';
					$item_output .= '</a>' . $args->after;
				}
			} else {
				$item_output .= '</a>' . $args->after;
			}

			if ( isset( $fusion_meta['special_link'] ) && 'fusion-woo-cart' === $fusion_meta['special_link'] && class_exists( 'WooCommerce' ) ) {

				// Construct menu item title.
				$woo_item_title = '<span class="menu-text">' . esc_html( $item->title );
				if ( $item->description ) {
					$woo_item_title .= '<span class="fusion-menu-description">' . esc_html( $item->description ) . '</span>';
				}
				$woo_item_title    .= '</span>';
				$woo_item_icon      = '';
				$show_counter       = ( isset( $fusion_meta['show_woo_cart_counter'] ) && 'yes' === $fusion_meta['show_woo_cart_counter'] ) ? true : false;
				$show_empty_counter = ( ! isset( $fusion_meta['show_empty_woo_cart_counter'] ) || 'yes' === $fusion_meta['show_empty_woo_cart_counter'] ) ? true : false;
				$counter_type       = ( ! isset( $fusion_meta['cart_counter_display'] ) ) ? 'inline' : $fusion_meta['cart_counter_display'];
				$show_cart_contents = ( isset( $fusion_meta['show_woo_cart_contents'] ) && 'yes' === $fusion_meta['show_woo_cart_contents'] ) ? true : false;
				$counter_style      = '';

				if ( '' !== $fusion_meta['icon'] ) {
					$woo_item_icon = '<span class="fusion-megamenu-icon"><i class="glyphicon ' . fusion_font_awesome_name_handler( $this->menu_megamenu_icon ) . '" aria-hidden="true"></i></span>';

					if ( $menu_icon_right || 'bottom' === $menu_icon_position ) {
						$woo_item_title = $woo_item_title . $woo_item_icon;
					} else {
						$woo_item_title = $woo_item_icon . $woo_item_title;
					}
				}

				$woo_item_after_title_inside = '';
				$woo_item_after_title        = '';
				if ( true === $show_cart_contents && ( 'parent' === $menu_display_dropdown_indicator || 'parent_child' === $menu_display_dropdown_indicator ) ) {
					if ( 'hover' === $expand_method ) {
						$woo_item_after_title_inside = '<span class="fusion-open-nav-submenu"></span>';
					} else {
						$woo_item_after_title .= '<button type="button" aria-label="' . esc_attr__( 'Show Cart Contents', 'fusion-builder' ) . '" aria-expanded="false" class="fusion-open-nav-submenu fusion-open-nav-submenu-on-click" onclick="fusionNavClickExpandSubmenuBtn(this);"></button>';
					}
				}

				if ( $show_counter ) {

					if ( ! empty( $item->fusion_highlight_label_background ) ) {
						$counter_style .= 'background-color:' . $item->fusion_highlight_label_background . ';';
					}

					if ( ! empty( $item->fusion_highlight_label_border_color ) ) {
						$counter_style .= 'border-color:' . $item->fusion_highlight_label_border_color . ';';
					}

					if ( ! empty( $item->fusion_highlight_label_color ) ) {
						$counter_style .= 'color:' . $item->fusion_highlight_label_color . ';';
					}
				}

				$item_output = fusion_menu_element_add_woo_cart_to_widget_html(
					[
						'link_classes'       => $atts['class'],
						'text_title'         => $woo_item_title,
						'after_title_inside' => $woo_item_after_title_inside,
						'after_title'        => $woo_item_after_title,
						'show_counter'       => $show_counter,
						'counter_style'      => $counter_style,
					]
				);

				$item->classes = isset( $item->classes ) ? (array) $item->classes : [];

				if ( isset( $fusion_meta['show_woo_cart_contents'] ) && 'yes' === $fusion_meta['show_woo_cart_contents'] ) {

					$item_output .= avada_menu_element_woo_cart();

					if ( is_object( WC()->cart ) && 0 < WC()->cart->get_cart_contents_count() ) {
						$item->classes[] = 'menu-item-has-children';
					} else {
						$item->classes[] = 'empty-cart';
					}
				}

				// Set menu item classes.
				$item->classes[] = 'fusion-widget-cart';
				$item->classes[] = 'fusion-menu-cart';
				$item->classes[] = 'avada-main-menu-cart';

				if ( false === $show_empty_counter ) {
					$item->classes[] = 'fusion-menu-cart-hide-empty-counter';
				}

				if ( 'badge' === $counter_type ) {
					$item->classes[] = 'fusion-counter-badge';
				}

				if ( ! $show_counter && is_object( WC()->cart ) && 0 < WC()->cart->get_cart_contents_count() ) {
					$item->classes[] = ' fusion-active-cart-icon';
				}
			}

			if ( isset( $fusion_meta['special_link'] ) && 'fusion-woo-my-account' === $fusion_meta['special_link'] && class_exists( 'WooCommerce' ) ) {

				// Construct menu item title.
				$woo_item_title = '<span class="menu-text">' . esc_html( $item->title );
				if ( $item->description ) {
					$woo_item_title .= '<span class="fusion-menu-description">' . esc_html( $item->description ) . '</span>';
				}
				$woo_item_title .= '</span>';

				$woo_item_icon = '';

				if ( '' !== $fusion_meta['icon'] ) {
					$woo_item_icon = '<span class="fusion-megamenu-icon"><i class="glyphicon ' . fusion_font_awesome_name_handler( $this->menu_megamenu_icon ) . '" aria-hidden="true"></i></span>';

					if ( $menu_icon_right || 'bottom' === $menu_icon_position ) {
						$woo_item_title = $woo_item_title . $woo_item_icon;
					} else {
						$woo_item_title = $woo_item_icon . $woo_item_title;
					}
				}

				$menu_id = '';
				if ( $args->menu_id ) {
					$menu_id = $args->menu_id;
				} elseif ( is_string( $args->menu ) ) {
					$menu_id = uniqid( $args->menu . '-' );
				} elseif ( isset( $args->menu->slug ) ) {
					$menu_id = uniqid( $args->menu->slug . '-' );
				}

				$woo_args = [
					'menu_item_content'    => $woo_item_title,
					'link_classes'         => $atts['class'],
					'after_content_inside' => '',
					'after_content'        => '',
					'menu_id'              => $menu_id,
				];

				if ( 'parent' === $menu_display_dropdown_indicator || 'parent_child' === $menu_display_dropdown_indicator ) {
					if ( 'hover' === $expand_method ) {
						$woo_args['after_content_inside'] = '<span class="fusion-open-nav-submenu"></span>';
					} else {
						$woo_args['after_content'] = '<button type="button" aria-label="' . esc_attr__( 'Open Profile Submenu', 'fusion-builder' ) . '" aria-expanded="false" class="fusion-open-nav-submenu fusion-open-nav-submenu-on-click" onclick="fusionNavClickExpandSubmenuBtn(this);"></button>';
					}
				}

				$item_output = avada_menu_element_add_login_box_to_nav( $woo_args );

				// Set menu item classes.
				$item->classes = isset( $item->classes ) ? (array) $item->classes : [];

				if ( is_account_page() ) {
					$item->classes[] = 'current-menu-item';
					$item->classes[] = 'current_page_item';
				}
				$item->classes[] = 'fusion-dropdown-menu';
				$item->classes[] = 'menu-item-has-children';
				$item->classes[] = 'avada-menu-login-box';
			}

			if ( isset( $fusion_meta['special_link'] ) && 'fusion-search' === $fusion_meta['special_link'] ) {
				$fusion_meta['searchform_mode'] = isset( $fusion_meta['searchform_mode'] ) ? $fusion_meta['searchform_mode'] : 'inline';
				$item_title_esc                 = esc_attr( $item->title );
				$icon_only_class                = '';

				if ( 'icononly' === $this->menu_title_only ) {
					$icon_only_class = ' fusion-icon-only-link';
				}

				switch ( $fusion_meta['searchform_mode'] ) {
					case 'dropdown':
						$item->classes   = isset( $item->classes ) ? (array) $item->classes : [];
						$item->classes[] = 'menu-item-has-children';
						$item->classes[] = 'custom-menu-search';
						$item->classes[] = 'custom-menu-search-dropdown';

						$item_output = '<a class="fusion-main-menu-icon' . $icon_only_class . '" href="#" aria-label="' . $item_title_esc . '" data-title="' . $item_title_esc . '" title="' . $item_title_esc . '">';
						if ( 'icononly' !== $this->menu_title_only ) {
							$item_output .= '<span class="menu-title">' . $item->title;
							if ( $item->description ) {
								$item_output .= '<span class="fusion-menu-description">' . esc_html( $item->description ) . '</span>';
							}
							$item_output .= '</span>';
						}

						if ( ! empty( $this->menu_megamenu_icon ) ) {
							$item_output .= '<span class="fusion-megamenu-icon"><i class="glyphicon ' . fusion_font_awesome_name_handler( $this->menu_megamenu_icon ) . '" aria-hidden="true"></i></span>';
						}

						$item_output .= '</a>';
						$item_output .= '<button type="button" aria-label="' . esc_attr__( 'Expand Search', 'fusion-builder' ) . '" aria-expanded="false" class="fusion-open-nav-submenu fusion-open-nav-submenu-on-click" onclick="fusionNavClickExpandSubmenuBtn(this);"></button>';
						$item_output .= '<ul class="sub-menu fusion-menu-searchform-dropdown"><li>' . get_search_form( false ) . '</li></ul>';

						break;

					case 'overlay':
						$item->classes   = isset( $item->classes ) ? (array) $item->classes : [];
						$item->classes[] = 'custom-menu-search';
						$item->classes[] = 'custom-menu-search-overlay';

						$item_output = '<a class="fusion-main-menu-icon fusion-menu-icon-search trigger-overlay' . $icon_only_class . '" href="#" aria-label="' . $item_title_esc . '" data-title="' . $item_title_esc . '" title="' . $item_title_esc . '" role="button" aria-expanded="false"></a>';

						$searchform_markup  = get_search_form( false );
						$searchform_markup .= '<div class="fusion-search-spacer"></div>';
						$searchform_markup .= '<a href="#" role="button" aria-label="' . esc_attr__( 'Close Search', 'Avada' ) . '" class="fusion-close-search"></a>';

						if ( class_exists( 'FusionSC_Menu' ) ) {
							FusionSC_Menu::$overlay_search_markup .= '<div class="fusion-overlay-search">' . $searchform_markup . '</div>';
						}
						// This is here for mobile menus. DO NOT REMOVE.
						$item_output .= '<div class="fusion-menu-form-inline">' . $searchform_markup . '</div>';
						break;

					default:
						$item->classes[] = 'custom-menu-search-inline';
						$item_output     = '<div class="fusion-menu-form-inline">' . get_search_form( false ) . '</div>';
				}
			}

			if ( isset( $fusion_meta['special_link'] ) && 'fusion-sliding-bar-toggle' === $fusion_meta['special_link'] ) {
				$item->classes[]   = 'fusion-custom-menu-item';
				$item->classes[]   = 'fusion-main-menu-sliding-bar fusion-flyout-sliding-bar-toggle';
				$sliding_bar_label = esc_attr__( 'Toggle Sliding Bar', 'Avada' );

				// Construct menu item title.
				$slidingbar_title = '<span class="menu-text">' . esc_html( $item->title );
				if ( $item->description ) {
					$slidingbar_title .= '<span class="fusion-menu-description">' . esc_html( $item->description ) . '</span>';
				}
				$slidingbar_title .= '</span>';
				$slidingbar_icon   = '';

				if ( '' !== $fusion_meta['icon'] ) {
					$slidingbar_icon = '<span class="fusion-megamenu-icon"><i class="glyphicon ' . fusion_font_awesome_name_handler( $this->menu_megamenu_icon ) . '" aria-hidden="true"></i></span>';

					if ( $menu_icon_right || 'bottom' === $menu_icon_position ) {
						$slidingbar_title = $slidingbar_title . $slidingbar_icon;
					} else {
						$slidingbar_title = $slidingbar_icon . $slidingbar_title;
					}
				}

				$atts['title']      = $sliding_bar_label;
				$atts['href']       = '#';
				$atts['class']     .= ' fusion-main-menu-icon awb-icon-sliding-bar';
				$atts['aria-label'] = $sliding_bar_label;
				$atts['data-title'] = $sliding_bar_label;
				unset( $atts['target'] );
				unset( $atts['rel'] );

				$attributes = '';
				foreach ( $atts as $attr => $value ) {
					if ( ! empty( $value ) ) {
						$value       = esc_attr( $value );
						$attributes .= ' ' . $attr . '="' . $value . '"';
					}
				}

				$item_output = '<a ' . $attributes . '>' . $slidingbar_title . '</a>';
			}
		}

		// Check if we need to apply a divider.
		if ( 'enabled' !== $this->menu_megamenu_status && ( ( 0 === strcasecmp( $item->attr_title, 'divider' ) ) || ( 0 === strcasecmp( $item->title, 'divider' ) ) ) ) {

			$output .= '<li role="presentation" class="divider">';

		} else {

			$class_names       = '';
			$column_width      = '';
			$style             = '';
			$custom_class_data = '';
			$classes           = empty( $item->classes ) ? [] : (array) $item->classes;
			$data_bg           = '';
			$classes[]         = 'menu-item-' . $item->ID;

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );

			if ( 0 === $depth && $args->has_children ) {
				$class_names .= ( 'enabled' === $this->menu_megamenu_status ) ? ' fusion-megamenu-menu' : ' fusion-dropdown-menu';
				$class_names .= ( 'enabled' === $this->menu_megamenu_status ) ? ' {fusion_all_widgets}' : '';
			}

			if ( 0 === $depth && $this->menu_style ) {
				$class_names .= ' fusion-menu-item-button';
			}

			// Add class to last element in flyout menus.
			if ( 0 === $depth && ( 'v6' === $header_layout && ! $this->args['woocommerce_acc_link_main_nav'] || ( $fb_menu_element && 'flyout' === $this->args['submenu_mode'] ) ) && $this->top_level_menu_items_count === $this->no_of_top_level_items_displayed + 1 ) {
				$class_names .= ' fusion-flyout-menu-item-last';
			}

			if ( $fb_menu_element && 0 === $depth && 'flyout' === $this->args['submenu_mode'] && ( empty( $item->url ) || '#' === $item->url || 'http://' === $item->url ) ) {
				$class_names .= ' awb-flyout-top-level-no-link';
			}

			if ( 1 === $depth ) {

				if ( 'enabled' === $this->menu_megamenu_status ) {
					$class_names .= ' fusion-megamenu-submenu';

					if ( 'disabled' === $this->menu_megamenu_title ) {
						$class_names .= ' fusion-megamenu-submenu-notitle';
					}
					if ( isset( $item->url ) && '#' !== $item->url && '' !== $item->url ) {
						$class_names .= ' menu-item-has-link';
					}
					if ( ! empty( $megamenu_column_background_image ) ) {
						if ( $this->args['lazy_load'] ) {
							$class_names .= ' lazyload';
							$data_bg      = ' data-bg="' . $megamenu_column_background_image . '"';
						} else {
							$style .= 'background-image: url(' . $megamenu_column_background_image . ');';
						}
					}

					if ( 'fullwidth' !== $this->menu_megamenu_width ) {
						$style .= 'width:{column_width_' . $this->num_of_rows . '_' . $this->num_of_columns . '};';
					}
				} else {
					$class_names .= ' fusion-dropdown-submenu';
				}
			}

			if ( isset( $item->classes[0] ) && ! empty( $item->classes[0] ) ) {
				$custom_class_data = ' data-classes="' . $item->classes[0] . '"';
			}

			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . $class_columns . '"' : '';

			$style = $style ? ' style="' . esc_attr( $style ) . '"' : '';

			$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args );
			$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

			$data_id = ( 0 === $depth || ( $fb_menu_element && 'flyout' === $this->args['submenu_mode'] && isset( $fusion_meta['background_image'] ) && '' !== $fusion_meta['background_image'] ) ) ? ' data-item-id="' . $item->ID . '"' : '';

			$output .= '<li ' . $id . ' ' . $class_names . ' ' . $column_width . $custom_class_data . $style . $data_id . $data_bg . '>';

			if ( $fb_menu_element ) {
				$output .= '<span class="background-default transition-' . $transition_type . '"></span><span class="background-active transition-' . $transition_type . '"></span>';
			}

			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}

	/**
	 * End Element.
	 *
	 * @see Walker::end_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Page data object. Not used.
	 * @param int    $depth Depth of page. Not Used.
	 * @param  array  $args Not used.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = [] ) {
		$output .= '</li>';

		if ( null === $item->menu_item_parent ) {
			$item->menu_item_parent = '0';
		}

		if ( '0' === $item->menu_item_parent ) {
			$this->no_of_top_level_items_displayed++;
		}
		$fb_menu_element = $this->args['fb_menu_element'];

		// Add the bg image markup for flyout menu items.
		if ( 0 === $depth && ( 'v6' === $this->args['header_layout'] || ( $fb_menu_element && 'flyout' === $this->args['submenu_mode'] ) ) && $this->flyout_menu_bg_markup && $this->top_level_menu_items_count === $this->no_of_top_level_items_displayed ) {
			$output .= '<li class="fusion-flyout-menu-backgrounds">' . $this->flyout_menu_bg_markup . '</li>';
		}

		if ( 'v7' === $this->args['header_layout'] && ( '' !== $this->args['logo_url'] || '' !== $this->args['logo_retina_url'] ) && 'top' === $this->args['header_position'] && $this->middle_logo_menu_break_point == $this->no_of_top_level_items_displayed && '0' === $item->menu_item_parent ) { // phpcs:ignore WordPress.PHP.StrictComparisons
			ob_start();
			get_template_part( 'templates/logo' );
			$output .= ob_get_clean();
		}
	}

	/**
	 * Traverse elements to create list from elements.
	 *
	 * Display one element if the element doesn't have any children otherwise,
	 * display the element and its children. Will only traverse up to the max
	 * depth and no ignore elements under that depth.
	 *
	 * This method shouldn't be called directly, use the walk() method instead.
	 *
	 * @see Walker::start_el()
	 * @since 2.5.0
	 *
	 * @param object $element Data object.
	 * @param array  $children_elements List of elements to continue traversing.
	 * @param int    $max_depth Max depth to traverse.
	 * @param int    $depth Depth of current element.
	 * @param array  $args The arguments.
	 * @param string $output Passed by reference. Used to append additional content.
	 * @return null Null on failure with no changes to parameters.
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( ! $element ) {
			return;
		}

		$id_field = $this->db_fields['id'];

		// Display this element.
		if ( is_object( $args[0] ) ) {
			$args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
		}

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

	/**
	 * Menu Fallback
	 * =============
	 * If this function is assigned to the wp_nav_menu's fallback_cb variable
	 * and a manu has not been assigned to the theme location in the WordPress
	 * menu manager the function with display nothing to a non-logged in user,
	 * and will add a link to the WordPress menu manager if logged in as an admin.
	 *
	 * @param array $args passed from the wp_nav_menu function.
	 */
	public static function fallback( $args ) {
		if ( current_user_can( 'manage_options' ) ) {
			return null;
		}
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
