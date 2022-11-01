<?php
/**
 * Initializes an addional featured image for use in backend and frontend.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Fusion-Library
 * @since      1.1
 */

/**
 * Handles additional featured images.
 *
 * @since 1.1
 */
class Fusion_Featured_Image {

	/**
	 * The class arguments.
	 *
	 * @since 1.1
	 * @access private
	 * @var array
	 */
	private $args = [];

	/**
	 * The class defaults.
	 *
	 * @since 1.1
	 * @access private
	 * @var array
	 */
	private $defaults = [];

	/**
	 * Constructor.
	 *
	 * @since 1.1
	 * @access public
	 * @param array $args The arguments.
	 * @return void
	 */
	public function __construct( $args ) {

		$this->defaults = [
			'id'           => 'featured-image-2',
			'post_type'    => 'page',
			'name'         => esc_html__( 'Featured Image 2', 'Avada' ),
			'label_set'    => esc_html__( 'Set featured image 2', 'Avada' ),
			'label_remove' => esc_html__( 'Remove featured image 2', 'Avada' ),
		];

		$this->args                  = wp_parse_args( $args, $this->defaults );
		$this->args['metabox_id']    = $this->args['id'] . '_' . $this->args['post_type'];
		$this->args['post_meta_key'] = 'kd_' . $this->args['metabox_id'] . '_id';

		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );

		add_action( 'init', [ $this, 'init_info_meta_box' ] );

	}
	/**
	 * Init admin metabox for an features images info.
	 *
	 * @since 5.2.1
	 * @access public
	 * @return void
	 */
	public function init_info_meta_box() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box_info' ] );
	}

	/**
	 * Add admin metabox for an additional featured image.
	 *
	 * @since 1.1
	 * @access public
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box(
			$this->args['metabox_id'],
			$this->args['name'],
			[ $this, 'meta_box_content' ],
			$this->args['post_type'],
			'side',
			'low'
		);
	}

	/**
	 * Add admin metabox for an additional featured images info.
	 *
	 * @since 5.2.1
	 * @access public
	 * @return void
	 */
	public function add_meta_box_info() {
		add_meta_box(
			'fusion_featured_images_info',
			__( 'Featured images Info', 'Avada' ),
			[ $this, 'meta_box_info_content' ],
			$this->args['post_type'],
			'side',
			'low'
		);
	}

	/**
	 * Output the metabox content.
	 *
	 * @since 1.1
	 * @access public
	 * @global object $post
	 * @return void
	 */
	public function meta_box_content() {
		global $post;

		$image_id = fusion_data()->post_meta( $post->ID )->get( $this->args['post_meta_key'] );
		?>
		<div class="fusion-featured-image-meta-box">
			<p class="hide-if-no-js">
				<a aria-label="<?php echo esc_attr( $this->args['label_set'] ); ?>" href="#" id="<?php echo esc_attr( $this->args['id'] ); ?>" class="fusion_upload_button">
					<span class="fusion-set-featured-image" style="<?php echo ( ! $image_id ) ? '' : 'display:none;'; ?>">
						<?php echo esc_html( $this->args['label_set'] ); ?>
					</span>

					<?php if ( $image_id ) : ?>
						<?php
						echo wp_get_attachment_image(
							$image_id,
							[ 266, 266 ],
							false,
							[
								'class' => 'fusion-preview-image',
							]
						);
						?>
					<?php else : ?>
						<img class="fusion-preview-image" src="" style="display:none;">
					<?php endif; ?>
				</a>
				<input class="upload_field" id="<?php echo esc_attr( $this->args['post_meta_key'] ); ?>" name="<?php echo esc_attr( Fusion_Data_PostMeta::ROOT . '[' . $this->args['post_meta_key'] . ']' ); ?>" value="<?php echo esc_attr( $image_id ); ?>" type="hidden">
			</p>

			<p class="hide-if-no-js fusion-remove-featured-image" style="<?php echo ( ! $image_id ) ? 'display:none;' : ''; ?>">
				<a aria-label="<?php echo esc_attr( $this->args['label_remove'] ); ?>" href="#" id="<?php echo esc_attr( $this->args['id'] ); ?>" class="fusion-remove-image">
					<?php echo esc_html( $this->args['label_remove'] ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Output the metabox info content.
	 *
	 * @since 5.2.1
	 * @access public
	 * @global object $post
	 * @return void
	 */
	public function meta_box_info_content() {
		/* translators: The "Global Options" link. */
		echo sprintf( esc_html__( 'To control the amount of featured image boxes, visit %s.', 'Avada' ), '<a href="' . esc_url_raw( admin_url( 'themes.php?page=avada_options#posts_slideshow_number' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Global Options', 'Avada' ) . '</a>' );
	}

	/**
	 * Retrieve the ID of the featured image.
	 *
	 * @since 1.1
	 * @static
	 * @access public
	 * @global object $post
	 * @param string $image_id Internal ID of the featured image.
	 * @param string $post_type The post type of the post the featured image belongs to.
	 * @param int    $post_id A custom post ID.
	 * @return int The featured image ID.
	 */
	public static function get_featured_image_id( $image_id, $post_type = null, $post_id = null ) {
		global $post;

		if ( is_null( $post_type ) ) {
			$post_type = get_post_type();
		}

		if ( is_null( $post_id ) ) {
			$post_id = get_the_ID();
		}

		return apply_filters( 'wpml_object_id', fusion_data()->post_meta( $post_id )->get( 'kd_' . $image_id . '_' . $post_type . '_id' ), 'attachment', true );
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
