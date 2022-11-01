<?php
/**
 * Modifications for WooCommerce variations.
 *
 * @author     ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class to apply woocommerce variations.
 *
 * @since 7.2
 */
class Avada_Woocommerce_Variations {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 7.2
	 * @var object
	 */
	private static $instance;

	/**
	 * Array of taxonomies.
	 *
	 * @access private
	 * @since 7.2
	 * @var object
	 */
	public $taxonomies = null;

	/**
	 * Class constructor.
	 *
	 * @since 7.2
	 * @access private
	 */
	private function __construct() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 7.2
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new Avada_Woocommerce_Variations();
		}
		return self::$instance;
	}

	/**
	 * Init.
	 *
	 * @static
	 * @access public
	 * @since 7.2
	 */
	public function init() {
		if ( class_exists( 'WooCommerce' ) && ( '0' !== Avada()->settings->get( 'woocommerce_variations' ) ) ) {
			add_filter( 'product_attributes_type_selector', [ $this, 'add_attribute_types' ] );
			add_action( 'fusion_tax_meta_allowed_screens', [ $this, 'add_taxonomy_options' ] );
			add_filter( 'before_avada_taxonomy_map', [ $this, 'term_options' ] );
			add_filter( 'woocommerce_dropdown_variation_attribute_options_html', [ $this, 'variation_markup' ], 10, 2 );
			add_action( 'wp', [ $this, 'enqueue_assets' ] );
			add_action( 'woocommerce_product_option_terms', [ $this, 'option_terms' ], 20, 3 );
			add_filter( 'woocommerce_layered_nav_term_html', [ $this, 'layered_nav_list_links' ], 10, 4 );
		}
	}

	/**
	 * Function that gets called at 'admin_init' action.
	 *
	 * @static
	 * @access public
	 * @since 7.5
	 */
	public function admin_init() {
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if ( $attribute_taxonomies ) {
			foreach ( $attribute_taxonomies as $tax ) {
				$product_attr = wc_attribute_taxonomy_name( $tax->attribute_name );
				add_filter( 'manage_edit-' . $product_attr . '_columns', [ $this, 'add_custom_attribute_type_preview' ] );
				add_filter( 'manage_' . $product_attr . '_custom_column', [ $this, 'create_preview_column_for_custom_attribute_type' ], 10, 3 );
			}
		}
	}

	/**
	 * Allow selection of terms on product page.
	 *
	 * @access public
	 * @since 7.2
	 * @param object $attribute_taxonomy Taxonomy object.
	 * @param int    $i                  Number.
	 * @param object $attribute          Attributes object.
	 * @return void
	 */
	public function option_terms( $attribute_taxonomy, $i, $attribute = '' ) {
		if ( false !== strpos( $attribute_taxonomy->attribute_type, 'avada_' ) ) {
			?>
			<select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'woocommerce' ); ?>" class="multiselect attribute_values wc-enhanced-select" name="attribute_values[<?php echo esc_attr( $i ); ?>][]">
			<?php
			$args      = [
				'orderby'    => ! empty( $attribute_taxonomy->attribute_orderby ) ? $attribute_taxonomy->attribute_orderby : 'name',
				'hide_empty' => 0,
			];
			$all_terms = get_terms( $attribute->get_taxonomy(), apply_filters( 'woocommerce_product_attribute_terms', $args ) );
			if ( $all_terms ) {
				foreach ( $all_terms as $term ) {
					$options = $attribute->get_options();
					$options = ! empty( $options ) ? $options : [];
					echo '<option value="' . esc_attr( $term->term_id ) . '"' . wc_selected( $term->term_id, $options ) . '>' . esc_html( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
			?>
		</select>
		<button class="button plus select_all_attributes"><?php esc_html_e( 'Select all', 'woocommerce' ); ?></button>
		<button class="button minus select_no_attributes"><?php esc_html_e( 'Select none', 'woocommerce' ); ?></button>
			<?php
		}
	}

	/**
	 * Enqueue variation assets.
	 *
	 * @access public
	 * @since 7.2
	 */
	public function enqueue_assets() {
		$version          = Avada::get_theme_version();
		$js_folder_suffix = AVADA_DEV_MODE ? '/assets/js' : '/assets/min/js';
		$js_folder_url    = Avada::$template_dir_url . $js_folder_suffix;
		$js_folder_path   = Avada::$template_dir_path . $js_folder_suffix;

		Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-variations.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-variations.min.css' );
		Fusion_Dynamic_JS::enqueue_script(
			'avada-woo-product-variations',
			$js_folder_url . '/general/avada-woo-variations.js',
			$js_folder_path . '/general/avada-woo-variations.js',
			[ 'jquery' ],
			$version,
			true
		);
	}

	/**
	 * Works out attrybute type.
	 *
	 * @access public
	 * @param string $attribute Attribute name.
	 * @return mixed Attribute type or false if not custom.
	 * @since 7.2
	 */
	public function custom_attribute_type( $attribute = '' ) {
		if ( null === $this->taxonomies ) {
			$this->set_taxonomies();
		}

		$attribute = str_replace( 'pa_', '', $attribute );

		foreach ( $this->taxonomies as $taxonomy ) {
			if ( str_replace( 'pa_', '', $taxonomy->attribute_name === $attribute ) ) {
				return $taxonomy->attribute_type;
			}
		}

		return false;
	}

	/**
	 * Output different markup for attibute type.
	 *
	 * @access public
	 * @param string $html HTML markup.
	 * @param array  $args Variation arguments.
	 * @since 7.2
	 */
	public function variation_markup( $html = '', $args = [] ) {

		// Get attribute type if it is custom.
		$custom_attribute_type = apply_filters( 'avada_custom_variation', $this->custom_attribute_type( $args['attribute'] ) );

		// Not custom, no need to change the markup.
		if ( ! $custom_attribute_type ) {
			return $html;
		}

		// Get selected value.
		if ( false === $args['selected'] && $args['attribute'] && $args['product'] instanceof WC_Product ) {
			$selected_key = 'attribute_' . sanitize_title( $args['attribute'] );
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$args['selected'] = isset( $_REQUEST[ $selected_key ] ) ? wc_clean( wp_unslash( $_REQUEST[ $selected_key ] ) ) : $args['product']->get_variation_default_attribute( $args['attribute'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		$options               = $args['options'];
		$product               = $args['product'];
		$attribute             = $args['attribute'];
		$name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
		$id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
		$class                 = $args['class'];
		$show_option_none      = (bool) $args['show_option_none'];
		$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' ); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[ $attribute ];
		}

		$custom_attribute_type = str_replace( 'avada_', '', $custom_attribute_type );
		$html                  = '<div class="avada-select-wrapper" data-type="' . esc_attr( $custom_attribute_type ) . '">' . $html;
		if ( ! empty( $options ) ) {
			if ( $product && taxonomy_exists( $attribute ) ) {
				// Get terms if this is a taxonomy - ordered. We need the names too.
				$terms = wc_get_product_terms(
					$product->get_id(),
					$attribute,
					[
						'fields' => 'all',
					]
				);

				foreach ( $terms as $term ) {
					if ( in_array( $term->slug, $options, true ) ) {

						$args = [
							'id'       => $term->term_id,
							'value'    => $term->slug,
							'name'     => $name,
							'selected' => $args['selected'],
							'title'    => apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product ),
						];
						if ( 'color' === $custom_attribute_type ) {
							$html .= $this->color_select( $args );
						} elseif ( 'image' === $custom_attribute_type ) {
							$html .= $this->image_select( $args );
						} else {
							$html .= $this->button_select( $args );
						}
					}
				}
			} else {
				foreach ( $options as $option ) {
					$args = [
						'id'       => 0,
						'value'    => $option,
						'name'     => $name,
						'selected' => $args['selected'],
						'title'    => apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product ),
					];
					if ( 'color' === $custom_attribute_type ) {
						$html .= $this->color_select( $args );
					} elseif ( 'image' === $custom_attribute_type ) {
						$html .= $this->image_select( $args );
					} else {
						$html .= $this->button_select( $args );
					}
				}
			}
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Markup for a color variation.
	 *
	 * @access public
	 * @param array $args Color field arguments.
	 * @since 7.2
	 */
	public function color_select( $args = [] ) {
		$color   = $args['id'] ? fusion_data()->term_meta( $args['id'] )->get( 'attribute_color' ) : '#ddd';
		$id      = esc_attr( $args['name'] . '_' . $args['value'] );
		$checked = sanitize_title( $args['value'] ) === sanitize_title( $args['selected'] ) ? ' data-checked="true" ' : '';

		$html = '<a href="#" class="avada-color-select" role="button" tabindex="0" aria-label="' . esc_html( $args['title'] ) . '" title="' . esc_html( $args['title'] ) . '" data-value="' . esc_attr( $args['value'] ) . '"' . $checked . '><span style="background-color: ' . esc_attr( $color ) . '"></span></a>';
		return $html;
	}

	/**
	 * Markup for a image variation.
	 *
	 * @access public
	 * @param array $args Image field arguments.
	 * @since 7.2
	 */
	public function image_select( $args = [] ) {
		$image   = $args['id'] ? fusion_data()->term_meta( $args['id'] )->get( 'attribute_image' ) : '';
		$id      = esc_attr( $args['name'] . '_' . $args['value'] );
		$checked = sanitize_title( $args['value'] ) === sanitize_title( $args['selected'] ) ? ' data-checked="true" ' : '';

		$image_id     = isset( $image['id'] ) ? $image['id'] : '';
		$image_url    = isset( $image['url'] ) ? $image['url'] : '';
		$image_size   = isset( $image['size'] ) ? $image['size'] : 'full';
		$image_data   = fusion_library()->images->get_attachment_data_by_helper( $image_id, $image_url );
		$image_output = wp_get_attachment_image( $image_data['id'], $image_size );

		$html = '<a href="#" class="avada-image-select" tabindex="0" aria-label="' . esc_html( $args['title'] ) . '" title="' . esc_html( $args['title'] ) . '" data-value="' . esc_attr( $args['value'] ) . '"' . $checked . '>' . $image_output . '</a>';

		return $html;
	}

	/**
	 * Markup for a button variation.
	 *
	 * @access public
	 * @param array $args Image field arguments.
	 * @since 7.2
	 */
	public function button_select( $args = [] ) {
		$id      = esc_attr( $args['name'] . '_' . $args['value'] );
		$checked = sanitize_title( $args['value'] ) === sanitize_title( $args['selected'] ) ? ' data-checked="true" ' : '';

		$html = '<a href="#" class="avada-button-select" tabindex="0" data-value="' . esc_attr( $args['value'] ) . '"' . $checked . '>' . esc_html( $args['title'] ) . '</a>';

		return $html;
	}

	/**
	 * Adds term option for the attributes.
	 *
	 * @access public
	 * @param array $sections Sections to add options to.
	 * @since 7.2
	 */
	public function term_options( $sections = [] ) {
		$taxonomy = false;
		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
		if ( isset( $_GET['taxonomy'] ) ) {
			$taxonomy = wp_unslash( $_GET['taxonomy'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		if ( isset( $_POST ) && ! empty( $_POST ) && isset( $_POST['taxonomy'] ) ) {
			$taxonomy = wp_unslash( $_POST['taxonomy'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing

		if ( ! $taxonomy ) {
			return $sections;
		}

		$taxonomy = sanitize_title( wp_unslash( $taxonomy ) );
		$type     = $this->custom_attribute_type( $taxonomy );

		if ( ! $type ) {
			return $sections;
		}

		if ( 'avada_button' === $type ) {
			return false;
		}

		$sections['taxonomy_options'] = [
			'label'  => __( 'Avada Product Attribute Options', 'fusion-builder' ),
			'id'     => 'taxonomy_options',
			'class'  => 'avada-tax-heading avada-tax-heading-edit',
			'icon'   => 'fusiona-page-options',
			'fields' => [
				'fusion_tax_heading' => [
					'id'    => 'fusion_tax_heading',
					'label' => __( 'Avada Product Attribute Options', 'fusion-builder' ),
					'class' => 'avada-tax-heading avada-tax-heading-edit',
					'type'  => 'header',
				],
			],
		];

		if ( 'avada_image' === $type ) {
			$sections['taxonomy_options']['fields']['attribute_image'] = [
				'id'          => 'attribute_image',
				'label'       => __( 'Image', 'fusion-builder' ),
				'description' => esc_html__( 'Select an image to use for the attribute.', 'fusion-builder' ),
				'type'        => 'media',
				'location'    => 'TAXO',
			];
		} elseif ( 'avada_color' === $type ) {
			$sections['taxonomy_options']['fields']['attribute_color'] = [
				'id'          => 'attribute_color',
				'label'       => __( 'Color', 'fusion-builder' ),
				'description' => esc_html__( 'Controls the color preview for the attribute. Hex code or rgba value, ex: #000.', 'fusion-builder' ),
				'type'        => 'color-alpha',
				'location'    => 'TAXO',
			];
		}

		return $sections;
	}

	/**
	 * Sets taxonomies we want to add options to.
	 *
	 * @access public
	 * @param array $taxonomies Taxonomies to add options to.
	 * @since 7.2
	 */
	public function add_taxonomy_options( $taxonomies = [] ) {
		if ( null === $this->taxonomies ) {
			$this->set_taxonomies();
		}

		foreach ( (array) $this->taxonomies as $taxonomy ) {
			$taxonomies[] = 'pa_' . $taxonomy->attribute_name;
		}
		return $taxonomies;
	}

	/**
	 * Sets taxonomies we want to add options to.
	 *
	 * @access public
	 * @since 7.2
	 */
	public function set_taxonomies() {
		$this->taxonomies = [];
		if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {

			$attribute_taxonomies = wc_get_attribute_taxonomies();
			if ( ! $attribute_taxonomies ) {
				return;
			}

			foreach ( $attribute_taxonomies as $taxonomy ) {
				if ( false !== strpos( $taxonomy->attribute_type, 'avada_' ) ) {
					$this->taxonomies[] = $taxonomy;
				}
			}
		}
	}

	/**
	 * Adds new attribute types to the selection.
	 *
	 * @static
	 * @access public
	 * @param array $attributes Attributes array.
	 * @since 7.2
	 */
	public function add_attribute_types( $attributes = [] ) {
		$attributes['avada_color']  = __( 'Avada Color', 'fusion-builder' );
		$attributes['avada_image']  = __( 'Avada Image', 'fusion-builder' );
		$attributes['avada_button'] = __( 'Avada Button', 'fusion-builder' );
		return $attributes;
	}

	/**
	 * Add columns for custom attribute types in the WooCommerce attributes table, if needed.
	 *
	 * @param array $columns The previous columns.
	 * @return array The new columns.
	 * @since 7.2
	 */
	public function add_custom_attribute_type_preview( $columns ) {

		if ( ! wp_doing_ajax() ) {
			$screen = get_current_screen();
			if ( ! $screen instanceof WP_Screen ) {
				return $columns;
			}
			$taxonomy_slug = $screen->taxonomy;
		} else {
			// We are in AJAX, try to determine the taxonomy from http referer.
			$params         = [];
			$url            = wp_get_referer();
			$url_components = wp_parse_url( $url );

			if ( ! is_array( $url_components ) && ! isset( $url_components['query'] ) ) {
				return $columns;
			}

			parse_str( $url_components['query'], $params );
			$taxonomy_slug = '';
			if ( isset( $params['taxonomy'] ) ) {
				$taxonomy_slug = $params['taxonomy'];
			}
		}

		if ( ! $taxonomy_slug ) {
			return $columns;
		}

		$attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy_slug );

		if ( ! $attribute_id ) {
			return $columns;
		}

		$attribute = wc_get_attribute( $attribute_id );
		if ( ! $attribute ) {
			return $columns;
		}
		$type = $attribute->type;

		if ( 'avada_color' === $type ) {
			$new_column = [ 'avada_color' => '' ];
			$columns    = array_merge( array_slice( $columns, 0, 2, true ), $new_column, array_slice( $columns, 2, null, true ) );
		}

		if ( 'avada_image' === $type ) {
			$new_column = [ 'avada_image' => '' ];
			$columns    = array_merge( array_slice( $columns, 0, 2, true ), $new_column, array_slice( $columns, 2, null, true ) );
		}

		return $columns;
	}

	/**
	 * Create the preview columns, for custom avada attributes types.
	 *
	 * @access public
	 * @since 7.7 
	 * @param string     $content The previews content.
	 * @param string     $column_id The column Id.
	 * @param int|string $term_id The term Id.
	 * @return string The content.
	 */
	public function create_preview_column_for_custom_attribute_type( $content, $column_id, $term_id ) {
		if ( 'avada_color' === $column_id ) {
			$color = fusion_data()->term_meta( $term_id )->get( 'attribute_color' );
			if ( ! $color ) {
				$color = 'transparent';
			}
			return '<div class="avada-color-column-preview" style="background-color:' . $color . '"></div>';
		}

		if ( 'avada_image' === $column_id ) {
			$image = fusion_data()->term_meta( intval( $term_id ) )->get( 'attribute_image' );

			$image_id = '';
			if ( is_array( $image ) && isset( $image['id'] ) ) {
				$image_id = $image['id'];
			}

			$image_url = '';
			if ( ! empty( $image_id ) ) {
				$image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );

				if ( ! is_string( $image_url ) ) {
					$image_url = '';
				}
			}

			return '<img class="avada-image-column-preview" src="' . esc_attr( $image_url ) . '" alt="">';
		}

		return $content;
	}

	/**
	 * Filters the link markup for the WooCommerce layered nav list widgets.
	 *
	 * @access public
	 * @since 7.7
	 * @param string $term_html HTML of the term link.
	 * @param object $term The term object.
	 * @param string $link The link URL.
	 * @param string $count Number of products matching the attribute.
	 * @return string The content.
	 */
	public function layered_nav_list_links( $term_html, $term, $link, $count ) {
		$attribute = wc_get_attribute( wc_attribute_taxonomy_id_by_name( $term->taxonomy ) );
		$attr_type = $attribute->type;

		switch ( $attr_type ) {
			case 'avada_color':
				$color     = fusion_data()->term_meta( $term->term_id )->get( 'attribute_color' );
				$term_html = '<a class="awb-woo-attr" rel="nofollow" href="' . esc_url( $link ) . '"><span class="awb-woo-attr-wrapper"><span class="avada-color-select"><span style="background-color: ' . esc_attr( $color ) . '"></span></span><span class="awb-woo-attr-name">' . esc_html( $term->name ) . '</span></span><span class="awb-woo-attr-count">(' . esc_html( $count ) . ')</span></a>';
				break;
			case 'avada_image':
				$image        = fusion_data()->term_meta( $term->term_id )->get( 'attribute_image' );
				$image_id     = isset( $image['id'] ) ? $image['id'] : '';
				$image_url    = isset( $image['url'] ) ? $image['url'] : '';
				$image_size   = isset( $image['size'] ) ? $image['size'] : 'full';
				$image_data   = fusion_library()->images->get_attachment_data_by_helper( $image_id, $image_url );
				$image_output = is_array( $image_data ) ? wp_get_attachment_image( $image_data['id'], $image_size ) : '';
				$term_html    = '<a class="awb-woo-attr" rel="nofollow" href="' . esc_url( $link ) . '"><span class="awb-woo-attr-wrapper"><span class="avada-image-select">' . $image_output . '</span><span class="awb-woo-attr-name">' . esc_html( $term->name ) . '</span></span><span class="awb-woo-attr-count">(' . esc_html( $count ) . ')</span></a>';              
				break;
			case 'avada_button':
				$term_html = '<a class="awb-woo-attr" rel="nofollow" href="' . esc_url( $link ) . '"><span class="awb-woo-attr-wrapper"><span class="avada-button-select">' . esc_html( $term->name ) . '</span></span><span class="awb-woo-attr-count">(' . esc_html( $count ) . ')</span></a>';
				break;
		}

		return $term_html;
	}
}

/**
 * Instantiates the Avada_Woocommerce_Variations class.
 * Make sure the class is properly set-up.
 *
 * @since object 7.2
 * @return object Avada_Woocommerce_Variations
 */
function Avada_Woocommerce_Variations() { // phpcs:ignore WordPress.NamingConventions
	return Avada_Woocommerce_Variations::get_instance();
}
Avada_Woocommerce_Variations();
