<?php
/**
 * Widget Class.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada Core
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Widget class.
 */
class Fusion_Widget_Vertical_Menu extends WP_Widget {

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		$widget_ops  = [
			'classname'   => 'avada_vertical_menu',
			'description' => __( 'This widget replaces the Side Navigation Template.', 'fusion-core' ),
		];
		$control_ops = [
			'id_base' => 'avada-vertical-menu-widget',
		];
		parent::__construct( 'avada-vertical-menu-widget', __( 'Avada: Vertical Menu', 'fusion-core' ), $widget_ops, $control_ops );

		$this->enqueue_script();

	}

	/**
	 * Echoes the widget content.
	 *
	 * @access public
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );

		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput

		if ( $title ) {
			echo $before_title . $title . $after_title; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		$widget_id_escaped = is_numeric( $args['widget_id'] ) ? 'avada-vertical-menu-widget-' . esc_attr( $args['widget_id'] ) . '-nav' : esc_attr( $args['widget_id'] ) . '-nav'; // phpcs:ignore WordPress.Security.EscapeOutput

		// Dynamic Styles.
		$style  = '<style>';
		$style .= '#fusion-vertical-menu-widget-' . $widget_id_escaped . ' ul.menu li a {'; // phpcs:ignore WordPress.Security.EscapeOutput
		$style .= 'font-size:' . ( FusionCore_Plugin::$fusion_library_exists ? esc_attr( Fusion_Sanitize::size( $instance['font_size'] ) ) : esc_attr( $instance['font_size'] ) ) . ';';
		$style .= '}';

		$style .= '</style>';

		echo $style; // phpcs:ignore WordPress.Security.EscapeOutput

		$nav_type = $instance['nav_type'];

		if ( ! isset( $instance['fusion_divider_color'] ) ) {
			if ( isset( $instance['border_color'] ) ) {
				$instance['fusion_divider_color'] = $instance['border_color'];
			} else {
				$instance['fusion_divider_color'] = '';
			}
		}

		$widget_border_class = ( '' === $instance['fusion_divider_color'] ? 'no-border' : '' );

		if ( 'custom_menu' === $nav_type ) {
			// Get menu.
			$nav_menu = ! empty( $instance['nav_menu'] ) ? wp_get_nav_menu_object( $instance['nav_menu'] ) : false;

			if ( ! $nav_menu ) {
				echo $after_widget; // phpcs:ignore WordPress.Security.EscapeOutput

				return;
			}

			$link_before = '<span class="arrow"></span><span class="link-text">';
			$link_after  = '</span>';

			if ( ( 'left' === $instance['layout'] && ! is_rtl() ) || ( 'right' === $instance['layout'] && is_rtl() ) ) {
				$link_before = '<span class="link-text">';
				$link_after  = '</span><span class="arrow"></span>';
			}

			$nav_menu_args = [
				'fallback_cb'     => '',
				'menu'            => $nav_menu,
				'container_class' => 'fusion-vertical-menu-widget', // Do not delete, needed for menu icons.
				'container'       => false,
				'item_spacing'    => 'discard',
				'link_before'     => $link_before,
				'link_after'      => $link_after,
			];

			$nav_menu_args = apply_filters( 'avada_vertical_menu_widget_custom_args', $nav_menu_args );

			$aria_label = __( 'Secondary navigation', 'fusion-core' );
			if ( isset( $instance['title'] ) ) {
				/* translators: The widget name. */
				$aria_label = sprintf( __( 'Secondary Navigation: %s', 'fusion-core' ), $instance['title'] );
			}

			echo '<nav id="fusion-vertical-menu-widget-' . $widget_id_escaped . '" class="fusion-vertical-menu-widget fusion-menu ' . esc_attr( $instance['behavior'] ) . ' ' . esc_attr( $instance['layout'] ) . ' ' . esc_attr( $widget_border_class ) . '" aria-label="' . esc_attr( $aria_label ) . '">'; // phpcs:ignore WordPress.Security.EscapeOutput
			add_filter( 'nav_menu_item_title', [ $this, 'nav_menu_item_title' ], 10, 4 );
			wp_nav_menu( $nav_menu_args );
			remove_filter( 'nav_menu_item_title', [ $this, 'nav_menu_item_title' ] );
			echo '</nav>';

		} elseif ( 'vertical_menu' === $nav_type ) {
			// Get page.
			$parent_page = ( ! empty( $instance['parent_page'] ) || '0' != $instance['parent_page'] ) ? $instance['parent_page'] : false; // phpcs:ignore WordPress.PHP.StrictComparisons

			if ( ! $parent_page ) {
				if (
					( function_exists( 'fusion_doing_ajax' ) && fusion_doing_ajax() ) || // Widget loaded from a change in the live editor.
					( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) // Initial load on the live-editor.
				) {
					echo '<div class="fusion-builder-placeholder">';
					esc_html_e( 'For the vertical layout to work, the page needs to be set as part of the WordPress parent/child hierarchy.', 'fusion-core' );
					echo '</div>';
				}
				echo $after_widget; // phpcs:ignore WordPress.Security.EscapeOutput

				return;
			}

			$html  = '<nav class="fusion-vertical-menu-widget fusion-menu ' . $instance['behavior'] . ' ' . $instance['layout'] . ' ' . $widget_border_class . '" id="' . $widget_id_escaped . '">';
			$html .= '<ul class="menu">';
			$html .= ( is_page( $parent_page ) ) ? '<li class="current_page_item">' : '<li>';
			$html .= '<a href="' . get_permalink( $parent_page ) . '" title="' . esc_html__( 'Back to Parent Page', 'fusion-core' ) . '">' . get_the_title( $parent_page ) . '</a></li>';

			$link_before = '<span class="arrow"></span><span class="link-text">';
			$link_after  = '</span>';

			if ( ( 'left' === $instance['layout'] && ! is_rtl() ) || ( 'right' === $instance['layout'] && is_rtl() ) ) {
				$link_before = '<span class="link-text">';
				$link_after  = '</span><span class="arrow"></span>';
			}

			$html .= wp_list_pages(
				apply_filters(
					'avada_vertical_menu_widget_vertical_args',
					[
						'title_li'    => '',
						'child_of'    => $parent_page,
						'link_before' => $link_before,
						'link_after'  => $link_after,
						'echo'        => 0,
					]
				)
			);

			$html .= '</ul></nav>';

			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput

		}

		echo $after_widget; // phpcs:ignore WordPress.Security.EscapeOutput

	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * This function should check that `$new_instance` is set correctly. The newly-calculated
	 * value of `$instance` should be returned. If false is returned, the instance won't be
	 * saved/updated.
	 *
	 * @access public
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']       = isset( $new_instance['title'] ) ? $new_instance['title'] : '';
		$instance['nav_type']    = isset( $new_instance['nav_type'] ) ? $new_instance['nav_type'] : '';
		$instance['nav_menu']    = isset( $new_instance['nav_menu'] ) ? $new_instance['nav_menu'] : '';
		$instance['parent_page'] = isset( $new_instance['parent_page'] ) ? $new_instance['parent_page'] : '';
		$instance['behavior']    = isset( $new_instance['behavior'] ) ? $new_instance['behavior'] : '';
		$instance['layout']      = isset( $new_instance['layout'] ) ? $new_instance['layout'] : '';
		$instance['font_size']   = isset( $new_instance['font_size'] ) ? $new_instance['font_size'] : '';

		unset( $instance['border_color'] );

		return $instance;

	}

	/**
	 * Get array of pages which have got children.
	 *
	 * @access public
	 * @return array Array of all pages which have got chidlren.
	 */
	public function get_pages_with_children() {
		$args = [
			'parent'      => -1,
			'post_type'   => 'page',
			'post_status' => 'publish',
		];

		$pages   = get_pages( $args );
		$pages   = array_filter( $pages, [ $this, 'exclude_parents' ] );
		$parents = [];

		foreach ( $pages as $page ) {
			$parents[ $page->post_parent ] = get_the_title( $page->post_parent );
		}

		return $parents;
	}

	/**
	 * Callback function for array_filter in get_pages_with_children method.
	 *
	 * This method chcecks if current pages array index has got parent set.
	 *
	 * @access public
	 * @param array $element Current array element.
	 * @return bool whether got parent or not.
	 */
	public function exclude_parents( $element ) {
		return isset( $element->post_parent ) && 0 !== $element->post_parent;
	}

	/**
	 * Enqueues script.
	 *
	 * @access public
	 * @return void
	 */
	public function enqueue_script() {
		if ( class_exists( 'Fusion_Dynamic_JS' ) ) {
			Fusion_Dynamic_JS::enqueue_script(
				'avada-vertical-menu-widget',
				FusionCore_Plugin::$js_folder_url . '/fusion-vertical-menu-widget.js',
				FusionCore_Plugin::$js_folder_path . '/fusion-vertical-menu-widget.js',
				[ 'jquery' ],
				FUSION_CORE_VERSION,
				true
			);
		}
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @access public
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {

		$defaults = [
			'title'       => '',
			'nav_type'    => 'custom',
			'nav_menu'    => '',
			'parent_page' => '',
			'behavior'    => 'hover',
			'layout'      => 'left',
			'font_size'   => '14px',
		];
		$instance = wp_parse_args( (array) $instance, $defaults );

		// Get menus.
		$menus    = wp_get_nav_menus();
		$nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : '';

		// Get pages.
		$pages       = $this->get_pages_with_children();
		$parent_page = isset( $instance['parent_page'] ) ? $instance['parent_page'] : '';
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p class="fusion-vetical-menu-widget-selection">
			<label for="<?php echo esc_attr( $this->get_field_id( 'nav_type' ) ); ?>"><?php esc_attr_e( 'Menu Type:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'nav_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'nav_type' ) ); ?>" class="widefat" style="width:100%;" onchange="setFusionVerticalMenuWidgetDependencies('<?php echo esc_attr( $this->get_field_id( 'nav_type' ) ); ?>')">
				<option value="custom_menu" <?php echo ( 'custom_menu' === $instance['nav_type'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Custom Menu', 'fusion-core' ); ?></option>
				<option value="vertical_menu" <?php echo ( 'vertical_menu' === $instance['nav_type'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Vertical Menu', 'fusion-core' ); ?></option>
			</select>
			<small><?php echo esc_attr_e( 'Choose if a custom menu or the classic side navigation (vertical menu option) should be displayed.', 'fusion-core' ); ?></small>
		</p>

		<p class="fusion-vetical-menu-selection">
			<label for="<?php echo esc_attr( $this->get_field_id( 'nav_menu' ) ); ?>"><?php esc_attr_e( 'Select Menu:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'nav_menu' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'nav_menu' ) ); ?>" class="widefat" style="width:100%;">
				<option value="0">&mdash; <?php esc_attr_e( 'Select', 'fusion-core' ); ?> &mdash;</option>
				<?php foreach ( $menus as $menu ) : ?>
					<option value="<?php echo esc_attr( $menu->slug ); ?>" <?php selected( $nav_menu, $menu->slug ); ?>>
						<?php echo esc_html( $menu->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>

		<p class="fusion-vetical-menu-parent-selection">
			<label for="<?php echo esc_attr( $this->get_field_id( 'parent_page' ) ); ?>"><?php esc_attr_e( 'Parent Page:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'parent_page' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'parent_page' ) ); ?>" class="widefat" style="width:100%;">
				<option value="0">&mdash; <?php esc_attr_e( 'Select', 'fusion-core' ); ?> &mdash;</option>
				<?php while ( $page = current( $pages ) ) : // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition ?>
					<option value="<?php echo esc_attr( key( $pages ) ); ?>" <?php selected( $parent_page, key( $pages ) ); ?>>
						<?php echo esc_html( $page ); ?>
					</option>
					<?php next( $pages ); ?>
				<?php endwhile; ?>
			</select>
		</p>

		<p class="fusion-vetical-menu-behavior-selection">
			<label for="<?php echo esc_attr( $this->get_field_id( 'behavior' ) ); ?>"><?php esc_attr_e( 'Behavior:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'behavior' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'behavior' ) ); ?>" class="widefat" style="width:100%;">
				<option value="hover" <?php echo ( 'hover' === $instance['behavior'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Hover', 'fusion-core' ); ?></option>
				<option value="click" <?php echo ( 'click' === $instance['behavior'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Click', 'fusion-core' ); ?></option>
			</select>
		</p>

		<p class="fusion-vetical-menu-widget-layout">
			<label for="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>"><?php esc_attr_e( 'Layout:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>" class="widefat" style="width:100%;">
				<option value="left" <?php echo ( 'left' === $instance['layout'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Left', 'fusion-core' ); ?></option>
				<option value="right" <?php echo ( 'right' === $instance['layout'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Right', 'fusion-core' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'font_size' ) ); ?>"><?php esc_attr_e( 'Font Size:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'font_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'font_size' ) ); ?>" value="<?php echo esc_attr( $instance['font_size'] ); ?>" />
		</p>

		<script type="text/javascript">
			jQuery( document ).ready( function() {
				setFusionVerticalMenuWidgetDependencies( '<?php echo esc_attr( $this->get_field_id( 'nav_type' ) ); ?>' );
			} );
			function setFusionVerticalMenuWidgetDependencies ( id ) {
				var selection = jQuery( '#' + id ).val();

				switch ( selection ) {
					case 'custom_menu':
						jQuery( '#' + id ).parent().parent().find( '.fusion-vetical-menu-parent-selection' ).hide();
						jQuery( '#' + id ).parent().parent().find( '.fusion-vetical-menu-selection' ).show();
					break;
					case 'vertical_menu':
						jQuery( '#' + id ).parent().parent().find( '.fusion-vetical-menu-selection' ).hide();
						jQuery( '#' + id ).parent().parent().find( '.fusion-vetical-menu-parent-selection' ).show();
					break;
				}
			}
		</script>
		<?php

	}

	/**
	 * Filters a menu item's title.
	 *
	 * @access public
	 * @since 5.7
	 * @param string   $title The menu item's title.
	 * @param WP_Post  $item  The current menu item.
	 * @param stdClass $args  An object of wp_nav_menu() arguments.
	 * @param int      $depth Depth of menu item. Used for padding.
	 */
	public function nav_menu_item_title( $title, $item, $args, $depth ) {

		// Make sure the filter only gets added to the vertical-menu widget and not all menus.
		$args_array = (array) $args;

		if ( isset( $args_array['container_class'] ) && false === strpos( $args_array['container_class'], 'fusion-vertical-menu-widget' ) ) {
			return $title;
		}

		// Determine if item has an icon.
		$has_icon = ( isset( $item->fusion_megamenu_icon ) && ! empty( $item->fusion_megamenu_icon ) );

		// Build the icon's markup.
		$icon = ( $has_icon ) ? '<span class="' . esc_attr( $item->fusion_megamenu_icon ) . '"></span>' : '';

		// In RTL languages append the icon, otherwise append it.
		return ( is_rtl() ) ? $title . ' ' . $icon : $icon . ' ' . $title;
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
