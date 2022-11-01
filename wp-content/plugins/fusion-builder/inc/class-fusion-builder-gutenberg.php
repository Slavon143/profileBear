<?php
/**
 * Avada Builder Gutenberg compatibility class.
 *
 * @package Avada-Builder
 * @since 1.7
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Avada Builder Gutenberg compatibility class.
 *
 * @since 1.7
 */
class Fusion_Builder_Gutenberg {

	/**
	 * Function-name to check for Gutenberg block-editing.
	 *
	 * @access private
	 * @since 1.7.2
	 * @var string
	 */
	private $block_editor_check_function = '';

	/**
	 * Class constructor.
	 *
	 * @since 1.7
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'init' ], 10 );
	}

	/**
	 * Class init.
	 *
	 * @since 1.7
	 * @access public
	 * @return void
	 */
	public function init() {
		global $typenow, $pagenow;

		if ( function_exists( 'use_block_editor_for_post' ) && ! defined( 'GUTENBERG_VERSION' ) ) {
			$this->block_editor_check_function = 'use_block_editor_for_post';
		} elseif ( function_exists( 'gutenberg_can_edit_post' ) && defined( 'GUTENBERG_VERSION' ) ) {
			$this->block_editor_check_function = 'gutenberg_can_edit_post';
		}

		if ( ! function_exists( $this->block_editor_check_function ) ) {
			return;
		}

		$post_type = $typenow;
		if ( 'edit.php' === $pagenow && '' === $typenow ) {
			$post_type = 'post';
		}

		if ( is_admin() ) {

			if ( $this->is_fb_enabled( $post_type ) ) {

				// Alter the add new dropdown.
				add_action( 'admin_print_footer_scripts-edit.php', [ $this, 'edit_dropdown' ], 10 );
			}

			if ( $this->is_fb_enabled( $post_type ) || 'admin-ajax.php' === $pagenow ) {

				// Add Gutenberg edit link.
				add_filter( 'page_row_actions', [ $this, 'add_edit_link' ], 10, 2 );
				add_filter( 'post_row_actions', [ $this, 'add_edit_link' ], 10, 2 );

				// Update edit link if required.
				add_filter( 'get_edit_post_link', [ $this, 'update_edit_link' ], 10, 3 );
			}
		}

		add_action( 'admin_print_footer_scripts-post-new.php', [ $this, 'adopt_to_builder' ], 10 );
		add_action( 'admin_print_footer_scripts-post.php', [ $this, 'adopt_to_builder' ], 10 );

		// Make sure G only loads with get variable if FB is new default.
		add_filter( $this->block_editor_check_function, [ $this, 'replace_gutenberg' ], 99, 2 );
	}

	/**
	 * Adopts to the chosen builder. Will add FB button to Gutenberg and trigger FB activation.
	 *
	 * @since 1.7
	 * @access public
	 * @return void
	 */
	public function adopt_to_builder() {
		global $post_type, $post;

		if ( $this->is_fb_enabled( $post_type ) && is_object( $post ) ) {
			if ( isset( $_GET['fb-be-editor'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				?>
				<script type="text/javascript">
				jQuery( window ).on( 'load', function() {
					var builderToggle = jQuery( '#fusion_toggle_builder' );

					setTimeout( function() {
						if ( ! builderToggle.hasClass( 'fusion_builder_is_active' ) ) {
							builderToggle.trigger( 'click' );
						}
					}, 100 );
				} );
				</script>
				<?php
			} elseif ( isset( $_GET['gutenberg-editor'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$post_link = add_query_arg( 'fb-be-editor', '', get_edit_post_link( $post->ID, 'raw' ) );
				$button    = '<a href="' . $post_link . '" id="fusion_builder_switch" class="button button-primary button-large"><span class="fusion-builder-button-text">' . esc_html__( 'Edit With Avada Builder', 'fusion-builder' ) . '</span></a>';
				?>
				<script type="text/javascript">
				jQuery( window ).on( 'load', function() {
					var toolbar = jQuery( '.edit-post-header-toolbar' );

					if ( toolbar.length ) {
						toolbar.append( '<?php echo $button; // phpcs:ignore WordPress.Security.EscapeOutput ?>' );
					}
				} );
				</script>
				<?php
			}
		}
	}

	/**
	 * Checks if Gutenberg should be disabled.
	 *
	 * @since 1.7
	 * @access public
	 * @param bool    $use_block_editor Whether the post can be edited or not with Gutenberg.
	 * @param WP_Post $post             The post being checked.
	 * @return bool   Whether post should be edited or not with Gutenberg.
	 */
	public function replace_gutenberg( $use_block_editor, $post ) {
		global $post_type;

		if ( isset( $_GET['gutenberg-editor'] ) || ! $this->is_fb_enabled( $post_type ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $use_block_editor;
		}
		return false;
	}

	/**
	 * Add edit dropdown to the all posts/pages screens.
	 *
	 * @since 1.7
	 * @access public
	 * @return void
	 */
	public function edit_dropdown() {
		global $typenow;

		$post_type_check = $this->block_editor_check_function . '_type';

		if ( ! $post_type_check( $typenow ) ) {
			return;
		}

		$options       = get_option( 'fusion_builder_settings', [] );
		$builder_type  = isset( $options['enable_builder_ui_by_default'] ) ? $options['enable_builder_ui_by_default'] : 'backend';
		$edit          = 'post' !== $typenow ? 'post-new.php?post_type=' . $typenow : 'post-new.php';
		$fb_url        = add_query_arg( 'fb-be-editor', '', $edit );
		$gutenberg_url = add_query_arg( 'gutenberg-editor', '', $edit );
		$live_editor   = apply_filters( 'fusion_load_live_editor', true );
		$edit          = 'live' === $builder_type && $live_editor ? '#' : $edit;
		$class         = 'live' === $builder_type && $live_editor ? ' awb-default-post-live' : '';

		$page_title_action_template  = '<span id="fusion-split-page-title-action" class="fusion-split-page-title-action ' . $class . '">';
		$page_title_action_template .= '<a href="' . $edit . '">' . esc_html__( 'Add New', 'fusion-builder' ) . '</a>';
		$page_title_action_template .= '<span class="expander" tabindex="0" role="button" aria-haspopup="true" aria-label="' . esc_html__( 'Toggle editor selection menu', 'fusion-builder' ) . '"></span>';
		$page_title_action_template .= '<span class="dropdown">';
		$page_title_action_template .= '<a href="' . $fb_url . '">' . esc_html__( 'Avada Builder', 'fusion-builder' ) . '</a>';

		if ( $live_editor ) {
			$page_title_action_template .= '<a href="#" id="fusion-builder-live-create-post">' . esc_html__( 'Avada Live', 'fusion-builder' ) . '</a>';
		}

		$page_title_action_template .= '<a href="' . $gutenberg_url . '">' . esc_html__( 'Gutenberg Editor', 'fusion-builder' ) . '</a>';
		$page_title_action_template .= '</span>';
		$page_title_action_template .= '</span>';
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				jQuery( 'body' ).on ('click', '#fusion-builder-live-create-post, .awb-default-post-live a[href="#"]',  function( e ) {
					e.preventDefault();

					if ( jQuery( e.currentTarget ).is( '#fusion-builder-live-create-post' ) ) {
						jQuery( this ).addClass( 'sending' );
					} else {
						jQuery( this ).closest( '.fusion-split-page-title-action' ).addClass( 'sending' );
					}

					jQuery.ajax( {
						type: 'POST',
						url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
						dataType: 'JSON',
						data: {
							action: 'fusion_create_post',
							fusion_load_nonce: '<?php echo esc_html( wp_create_nonce( 'fusion_load_nonce' ) ); ?>',
							post_type: '<?php echo esc_html( $typenow ); ?>'
						},
						success: function( response ) {
							console.log( response.permalink );
							window.location = response.permalink + '&fb-edit=1';

						}
					} );
				} );

				var pageTitleAction = ( jQuery( '.split-page-title-action' ).length ) ? jQuery( '.split-page-title-action' ) : jQuery( '.page-title-action' ).first();

				pageTitleAction.before( '<?php echo $page_title_action_template; // phpcs:ignore WordPress.Security.EscapeOutput ?>' );
				pageTitleAction.remove();
				jQuery( '.fusion-split-page-title-action' ).find( '.expander' ).on( 'click', function( e ) {
					jQuery( this ).siblings( '.dropdown' ).toggleClass( 'visible' );
				} );
			} );
		</script>
		<style>
			.fusion-split-page-title-action {
				display: inline-flex;
				align-items: center;
				position: relative;
			}
			.fusion-split-page-title-action a,
			.fusion-split-page-title-action a:active,
			.fusion-split-page-title-action .expander {
				padding: 6px 10px;
				text-decoration: none;
				border: 1px solid #0071a1;
				border-radius: 0 2px 2px 0;;
				background: #f3f5f6;
				text-shadow: none;
				font-weight: 600;
				font-size: 13px;
				line-height: normal;
				color: #0071a1;
				cursor: pointer;
				outline: 0;
				box-shadow: none;
			}
			.fusion-split-page-title-action > a {
				display: inline-block;
				height: 30px;
				width: 95px;
				box-sizing: border-box;
			}
			.fusion-split-page-title-action .expander {
				display: inline-block;
				position: relative;
				margin-left: -2px;
				padding: 0;
				height: 30px;
				width: 31px;
				box-sizing: border-box;
				outline: none;
			}
			.fusion-split-page-title-action .expander:after {
				content: "\f140";
				font: 400 20px/.5 dashicons;
				speak: never;
				top: 50%;
				left: 50%;
				position: absolute;
				transform: translate(-50%, -50%);
				text-decoration: none !important;
			}
			.fusion-split-page-title-action .dropdown {
				display: none;
				width: 150px;
			}
			.fusion-split-page-title-action .dropdown.visible {
				display: block;
				position: absolute;
				top: 100%;
				z-index: 1;
			}
			.fusion-split-page-title-action .dropdown.visible a {
				display: block;
				top: 0;
				margin: -1px 0;
				padding-right: 9px;
			}
			.fusion-split-page-title-action .dropdown.visible #fusion-builder-live-create-post {
				padding-right: 9px;
			}
			.fusion-split-page-title-action a:hover,
			.fusion-split-page-title-action .expander:hover {
				background: #f1f1f1;
				border-color: #016087;
				color: #016087;
			}
			@keyframes rotate {
				0% {
					transform: rotate(0deg);
				}

				100% {
					transform: rotate(360deg);
				}
			}
			#fusion-builder-live-create-post {
				padding-right: 25px;
			}
			#fusion-builder-live-create-post.sending:after {
				opacity: 1;
			}
			#fusion-builder-live-create-post:after {
				content: '';
				position: absolute;
				top: 50%;
				right: 9px;
				margin-top: -4px;
				width: 5px;
				height: 5px;
				border: 3px solid;
				border-left-color: transparent;
				border-radius: 50%;
				opacity: 0;
				transition-duration: 0.5s;
				transition-property: opacity;
				animation-duration: 1s;
				animation-iteration-count: infinite;
				animation-name: rotate;
				animation-timing-function: linear;
			}
			.fusion-split-page-title-action.sending span.expander:after {
				opacity: 1;
				left: auto;
			}
			.fusion-split-page-title-action.sending .expander:after {
				content: '';
				position: absolute;
				top: 50%;
				right: 9px;
				margin-top: -4px;
				width: 5px;
				height: 5px;
				border: 3px solid;
				border-left-color: transparent;
				border-radius: 50%;
				opacity: 0;
				transition-duration: 0.5s;
				transition-property: opacity;
				animation-duration: 1s;
				animation-iteration-count: infinite;
				animation-name: rotate;
				animation-timing-function: linear;
			}

		</style>
		<?php
	}

	/**
	 * Updates edit link based on auto activation preferences.
	 *
	 * @since 3.8
	 * @access public
	 * @param  string $url      The edit URL.
	 * @param  int    $id       The post ID.
	 * @param  string $context  The context.
	 *
	 * @return string  Updated edit URL.
	 */
	public function update_edit_link( $url, $id, $context ) {
		$options      = get_option( 'fusion_builder_settings', [] );
		$builder_type = isset( $options['enable_builder_ui_by_default'] ) ? $options['enable_builder_ui_by_default'] : 'backend';
		$live_editor  = apply_filters( 'fusion_load_live_editor', true );

		if ( 'live' === $builder_type && $live_editor && 'display' === $context ) {
			$url = add_query_arg( 'fb-edit', '1', get_permalink( $id ) );
		}

		return $url;
	}

	/**
	 * Adds specific Gutenberg edit link to the posts hover menu.
	 *
	 * @since 1.7
	 * @access public
	 * @param  array   $actions Post actions.
	 * @param  WP_Post $post    Edited post.
	 *
	 * @return array          Updated post actions.
	 */
	public function add_edit_link( $actions, $post ) {
		if ( ! function_exists( $this->block_editor_check_function ) || ( isset( $_GET['post_status'] ) && 'trash' === $_GET['post_status'] ) || $this->is_live_edit_disabled( $post ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $actions;
		}

		$edit_url      = get_edit_post_link( $post->ID, 'raw' );
		$options       = get_option( 'fusion_builder_settings', [] );
		$builder_type  = isset( $options['enable_builder_ui_by_default'] ) ? $options['enable_builder_ui_by_default'] : 'backend';
		$fb_live_url   = add_query_arg( 'fb-edit', '1', get_permalink( $post->ID ) );
		$gutenberg_url = add_query_arg( 'gutenberg-editor', '', $edit_url );
		$live_editor   = apply_filters( 'fusion_load_live_editor', true );
		$edit_action   = [];

		// Build the classic edit action. See also: WP_Posts_List_Table::handle_row_actions().
		$title = _draft_or_post_title( $post->ID );

		// If auto activation is set to backend builder.
		if ( $live_editor && 'backend' === $builder_type ) {
			$edit_action['fusion_builder_live'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( $fb_live_url ),
				esc_attr(
					sprintf(
						/* translators: %s: post title */
						__( 'Edit &#8220;%s&#8221; in Avada Live', 'fusion-builder' ),
						$title
					)
				),
				esc_html__( 'Avada Live', 'fusion-builder' )
			);
		}

		// If auto activation is set to live builder.
		if ( $edit_url && 'live' === $builder_type ) {
			$edit_action['fusion_builder_backend'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( $edit_url ),
				esc_attr(
					sprintf(
						/* translators: %s: post title */
						__( 'Edit &#8220;%s&#8221; in Back-end Builder', 'fusion-builder' ),
						$title
					)
				),
				esc_html__( 'Back-end Builder', 'fusion-builder' )
			);
		}

		$edit_action['gutenberg'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			esc_url( $gutenberg_url ),
			esc_attr(
				sprintf(
					/* translators: %s: post title */
					__( 'Edit &#8220;%s&#8221; in the Gutenberg editor', 'fusion-builder' ),
					$title
				)
			),
			esc_html__( 'Gutenberg Editor', 'fusion-builder' )
		);

		// Insert the Gutenberg Edit action after the Edit action.
		$actions_keys = array_keys( $actions );
		$edit_offset  = array_search( 'edit', $actions_keys, true );
		$actions      = array_merge(
			array_slice( $actions, 0, $edit_offset + 1 ),
			$edit_action,
			array_slice( $actions, $edit_offset + 1 )
		);

		return $actions;
	}

	/**
	 * Check if live editing should be available for the post type.
	 *
	 * @since 2.2
	 * @access public
	 * @param object $post Post to check.
	 * @return bool
	 */
	public function is_live_edit_disabled( $post ) {

		// Disabled post types.
		$disabled = [ 'fusion_icons' ];

		return isset( $post ) && in_array( $post->post_type, $disabled, true ) ? true : false;
	}

	/**
	 * Check if FB is activated for the post type.
	 *
	 * @since 1.7
	 * @access public
	 * @param string $post_type Post type to check.
	 * @return bool
	 */
	public function is_fb_enabled( $post_type ) {
		if ( $post_type ) {
			return in_array( $post_type, FusionBuilder::allowed_post_types(), true );
		}
		return false;
	}
}
