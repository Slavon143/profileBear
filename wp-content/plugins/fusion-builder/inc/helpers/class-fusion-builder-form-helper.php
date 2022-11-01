<?php
/**
 * Avada Builder Form Helper class.
 *
 * @package Avada-Builder
 * @since 3.1
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Form Helper class.
 *
 * @since 3.1
 */
class Fusion_Builder_Form_Helper {

	/**
	 * Class constructor.
	 *
	 * @since 3.1
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Returns the array of forms with their id.
	 *
	 * @since 3.1
	 * @param string $type The type of data to fetch.
	 * @return array
	 */
	public static function fusion_form_creator_form_list( $type = 'titles' ) {
		global $post;

		$form_list = [];
		$args      = [
			'post_type'      => 'fusion_form',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		];

		$original_post = $post;
		$forms_query   = fusion_cached_query( $args );

		// Check if there are forms available.
		if ( $forms_query->have_posts() ) {
			while ( $forms_query->have_posts() ) :
				$forms_query->the_post();

				$value            = 'permalinks' === $type ? get_the_permalink() : get_the_title();
				$id               = get_the_ID();
				$form_list[ $id ] = $value;
			endwhile;
		}

		wp_reset_postdata();
		$post = $original_post;

		return $form_list;
	}

	/**
	 * Retrieve the form post content and return shortcode content.
	 *
	 * @since 3.1
	 * @param integer $form_post_id Form post id to retrieve content from form CPT.
	 * @return string Form shortcode content from the post content.
	 */
	public static function fusion_get_form_post_content( $form_post_id ) {
		$shortcode_content = '';
		$args              = [];
		$form_post         = get_post( $form_post_id );

		if ( $form_post ) {
			if ( isset( $_GET['form_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$args['recaptcha'] = 'no';
			}

			return [
				'args'    => $args,
				'content' => $form_post->post_content,
				'css'     => get_post_meta( $form_post_id, '_fusion_builder_custom_css', true ),
			];
		}

		return false;
	}

	/**
	 * Returns all countris with their country code.
	 *
	 * @since 3.1
	 * @param string $country_code Country code to get states for.
	 * @return array Countries with country code.
	 */
	public static function fusion_form_get_states_for_country( $country_code ) {
		$states = include FUSION_BUILDER_PLUGIN_DIR . '/inc/i18n/states.php';
		if ( isset( $states[ $country_code ] ) ) {
			return apply_filters( 'form_creator_country_state_' . strtolower( $country_code ), $states[ $country_code ] );
		} else {
			return [
				'none' => __( 'No States Found', 'fusion-builder' ),
			];
		}
	}

	/**
	 * Returns all states.
	 *
	 * @since 3.1
	 * @return array states.
	 */
	public static function fusion_form_get_all_states() {
		return include FUSION_BUILDER_PLUGIN_DIR . '/inc/i18n/states.php';
	}

	/**
	 * Check if the provided user role can view the form.
	 *
	 * @access public
	 * @since 3.1
	 * @param array $user_roles User roles to check.
	 * @return bool
	 */
	public static function user_can_see_fusion_form( $user_roles ) {
		// Get current logged in user.
		$current_user = wp_get_current_user();

		return (
			empty( $user_roles ) ||
			( is_array( $user_roles ) && ! empty( array_intersect( $user_roles, $current_user->roles ) ) )
		);
	}

	/**
	 * Returns all countris with their country code.
	 *
	 * @since 3.1
	 * @return array Countries with country code.
	 */
	public static function fusion_form_get_all_countries() {
		return apply_filters( 'fusion_form_countries', include FUSION_BUILDER_PLUGIN_DIR . '/inc/ /countries.php' );
	}

	/**
	 * Get fusion form post meta.
	 *
	 * @access public
	 * @since 3.1
	 * @param string $id post id.
	 * @return array
	 */
	public static function fusion_form_get_form_meta( $id ) {

		$sitename = isset( $_SERVER['SERVER_NAME'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) ) : '';

		if ( 'www.' === substr( $sitename, 0, 4 ) ) {
			$sitename = substr( $sitename, 4 );
		}

		$form_meta = wp_parse_args(
			(array) fusion_data()->post_meta( $id )->get_all_meta(),
			[
				'form_type'                   => 'ajax',
				'form_actions'                => [],
				'method'                      => 'method',
				'tooltip_text_color'          => '#ffffff',
				'tooltip_background_color'    => '#333333',
				'field_margin'                => [
					'top'    => '15px',
					'bottom' => '15px',
				],
				'label_position'              => 'above',
				'form_input_height'           => '',
				'form_border_width'           => [
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'form_border_radius'          => '',
				'form_border_color'           => '',
				'form_bg_color'               => '',
				'form_text_color'             => '',
				'form_label_color'            => '',
				'form_placeholder_color'      => '',
				'member_only_form'            => 'no',
				'user_roles'                  => '',
				'recaptcha'                   => 'no',
				'privacy_store_ip_ua'         => 'no',
				'privacy_expiration_interval' => 48,
				'privacy_expiration_action'   => 'anonymize',
				'email'                       => get_option( 'admin_email' ),
				'email_from'                  => 'WordPress',
				'email_from_id'               => 'wordpress@' . $sitename,

				/* translators: The title. */
				'email_subject'               => sprintf( esc_html__( '%s - Form Submission Notification', 'fusion-builder' ), get_the_title( $id ) ),
				'email_subject_encode'        => 0,
				'nonce_method'                => 'ajax',
			]
		);

		return $form_meta;
	}

	/**
	 * Sets the global $fusion_form var and returns it.
	 *
	 * @static
	 * @access public
	 * @since 3.1
	 * @param int $id The form ID.
	 * @return array
	 */
	public static function fusion_form_set_form_data( $id ) {
		global $fusion_form;

		$fusion_form = [
			'id'           => false,
			'field_types'  => [],
			'form_meta'    => [],
			'field_labels' => [],
			'field_logics' => [],
			'form_number'  => false,
			'text_config'  => [],
		];

		$id = apply_filters( 'wpml_object_id', $id, 'fusion_form', true );

		// If form post exists.
		if ( false !== get_post_status( $id ) ) {
			$fusion_form = [
				'id'           => $id,
				'field_types'  => [],
				'form_meta'    => self::fusion_form_get_form_meta( $id ),
				'field_labels' => [],
				'field_logics' => [],
				'form_number'  => $id,
				'text_config'  => [],
			];
		}

		return $fusion_form;
	}

	/**
	 * Exception for Name <me@mail.com> from sanitize.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param String $str String to sanitize.
	 * @return String
	 */
	public static function fusion_form_sanitize( $str ) {
		if ( is_object( $str ) || is_array( $str ) ) {
			return '';
		}

		$str = (string) $str;

		$filtered = wp_check_invalid_utf8( $str );

		if ( false !== strpos( $filtered, '<' ) ) {
			// check if it contains email.
			preg_match( '/<(.*?)>/', $filtered, $matches );
			$tag_content = isset( $matches[1] ) ? $matches[1] : '';
			if ( ! is_email( $tag_content ) ) {
				$filtered = sanitize_textarea_field( $filtered );
			}
		} else {
			$filtered = sanitize_textarea_field( $filtered );
		}

		return $filtered;
	}

	/**
	 * Convert filed names to labels.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param String $str filed name.
	 * @return String
	 */
	public static function fusion_name_to_label( $str ) {
		if ( is_object( $str ) || is_array( $str ) ) {
			return '';
		}

		$str = (string) $str;

		$str = preg_replace( '/_|-/', ' ', $str );
		return ucwords( $str );
	}
}
