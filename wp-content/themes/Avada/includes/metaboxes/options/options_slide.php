<?php
/**
 * Slide Metabox options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<div class='pyre_metabox'>

	<?php

	$this->radio_buttonset(
		'type',
		esc_attr__( 'Background Type', 'Avada' ),
		[
			'image'             => esc_attr__( 'Image', 'Avada' ),
			'self-hosted-video' => esc_attr__( 'Self-Hosted Video', 'Avada' ),
			'youtube'           => esc_attr__( 'Youtube', 'Avada' ),
			'vimeo'             => esc_attr__( 'Vimeo', 'Avada' ),
		],
		esc_html__( 'Select an image or video slide. If using an image, please select the image in the "Featured Image" box on the right hand side.', 'Avada' )
	);
	?>

	<div class="video_settings" style="display: none;">

		<h2><?php esc_html_e( 'Video Options', 'Avada' ); ?></h2>

		<?php
		$this->text(
			'youtube_id',
			esc_attr__( 'Youtube Video ID', 'Avada' ),
			/* translators: %1$s: URL. %2$s: ID. */
			sprintf( esc_html__( 'For example the Video ID for %1$s is %2$s', 'Avada' ), 'https://www.youtube.com/watch?v=<strong>x3UjX7_Qvbc</strong>', '<strong>x3UjX7_Qvbc</strong>' )
		);
		$this->text(
			'vimeo_id',
			esc_attr__( 'Vimeo Video ID', 'Avada' ),
			/* translators: %1$s: URL. %2$s: ID. */
			sprintf( esc_html__( 'For example the Video ID for %1$s is %2$s', 'Avada' ), 'http://vimeo.com/<strong>75230326</strong>', '<strong>75230326</strong>' )
		);
		$this->upload(
			'mp4',
			esc_attr__( 'Video MP4 Upload', 'Avada' ),
			esc_html__( 'Add your MP4 video file. This format must be included to render your video with cross-browser compatibility. WebM and OGV are optional. Using videos in a 16:9 aspect ratio is recommended.', 'Avada' )
		);
		$this->upload(
			'webm',
			esc_attr__( 'Video WebM Upload', 'Avada' ),
			esc_html__( 'Add your WebM video file. This is optional, only MP4 is required to render your video with cross-browser compatibility. Using videos in a 16:9 aspect ratio is recommended.', 'Avada' )
		);
		$this->upload(
			'ogv',
			esc_attr__( 'Video OGV Upload', 'Avada' ),
			esc_html__( 'Add your OGV video file. This is optional, only MP4 is required to render your video with cross-browser compatibility. Using videos in a 16:9 aspect ratio is recommended.', 'Avada' )
		);
		$this->upload(
			'preview_image',
			esc_attr__( 'Video Preview Image', 'Avada' ),
			esc_html__( 'IMPORTANT: This field must be used for self hosted videos. Self hosted videos do not work correctly on mobile devices. The preview image will be seen in place of your video on older browsers or mobile devices.', 'Avada' )
		);
		$this->text(
			'aspect_ratio',
			esc_attr__( 'Video Aspect Ratio', 'Avada' ),
			esc_html__( 'The video will be resized to maintain this aspect ratio, this is to prevent the video from showing any black bars. Enter an aspect ratio here such as: "16:9", "4:3" or "16:10". The default is "16:9"', 'Avada' )
		);
		$this->select(
			'video_display',
			esc_attr__( 'Video Display Mode', 'Avada' ),
			[
				'cover'   => esc_attr__( 'Cover', 'Avada' ),
				'contain' => esc_attr__( 'Contain', 'Avada' ),
			],
			esc_html__( 'If set to cover, the video will fill the entire slider area.  If set to contain, the video will display such that both its width and its height can fit inside the slider area.', 'Avada' )
		);
		$this->color(
			'video_bg_color',
			esc_attr__( 'Video Color Overlay', 'Avada' ),
			__( 'Select a color to show over the video as an overlay.', 'Avada' ),
			true
		);
		$this->radio_buttonset(
			'mute_video',
			esc_attr__( 'Mute Video', 'Avada' ),
			[
				'yes' => esc_attr__( 'Yes', 'Avada' ),
				'no'  => esc_attr__( 'No', 'Avada' ),
			],
			''
		);
		$this->radio_buttonset(
			'autoplay_video',
			esc_attr__( 'Autoplay Video', 'Avada' ),
			[
				'yes' => esc_attr__( 'Yes', 'Avada' ),
				'no'  => esc_attr__( 'No', 'Avada' ),
			],
			''
		);
		$this->radio_buttonset(
			'loop_video',
			esc_attr__( 'Loop Video', 'Avada' ),
			[
				'yes' => esc_attr__( 'Yes', 'Avada' ),
				'no'  => esc_html__( 'No', 'Avada' ),
			],
			''
		);
		$this->radio_buttonset(
			'hide_video_controls',
			esc_attr__( 'Hide Video Controls', 'Avada' ),
			[
				'yes' => esc_attr__( 'Yes', 'Avada' ),
				'no'  => esc_attr__( 'No', 'Avada' ),
			],
			esc_html__( 'If this is set to yes, autoplay must be enabled, otherwise the video can\'t play. For YouTube and Vimeo videos, in order to ensure the controls are always fully visible, the Video Display Mode should be set to "contain".', 'Avada' )
		);
		?>

	</div>

	<h2><?php esc_html_e( 'Content Options', 'Avada' ); ?></h2>

	<?php

	$this->radio_buttonset(
		'content_alignment',
		esc_attr__( 'Content Alignment', 'Avada' ),
		[
			'left'   => esc_attr__( 'Left', 'Avada' ),
			'center' => esc_attr__( 'Center', 'Avada' ),
			'right'  => esc_attr__( 'Right', 'Avada' ),
		],
		esc_html__( 'Select how the heading, caption and buttons will be aligned.', 'Avada' )
	);
	$this->textarea(
		'heading',
		esc_attr__( 'Heading Area', 'Avada' ),
		esc_html__( 'Enter the heading for your slide. This field can take HTML markup and simple Avada Shortcodes that work inside a heading tag.', 'Avada' )
	);
	$this->select(
		'heading_separator',
		esc_attr__( 'Heading Separator', 'Avada' ),
		[
			'none'             => esc_attr__( 'None', 'Avada' ),
			'single solid'     => esc_attr__( 'Single Solid', 'Avada' ),
			'single dashed'    => esc_attr__( 'Single Dashed', 'Avada' ),
			'single dotted'    => esc_attr__( 'Single Dotted', 'Avada' ),
			'double solid'     => esc_attr__( 'Double Solid', 'Avada' ),
			'double dashed'    => esc_attr__( 'Double Dashed', 'Avada' ),
			'double dotted'    => esc_attr__( 'Double Dotted', 'Avada' ),
			'underline solid'  => esc_attr__( 'Underline Solid', 'Avada' ),
			'underline dashed' => esc_attr__( 'Underline Dashed', 'Avada' ),
			'underline dotted' => esc_attr__( 'Underline Dotted', 'Avada' ),
		],
		esc_html__( 'Choose the heading separator you want to use.', 'Avada' )
	);
	/* translators: default value. */
	$default = sprintf( esc_html__( 'Default: %s', 'Avada' ), 'H2' );
	$this->radio_buttonset(
		'heading_size',
		esc_attr__( 'Title Size', 'Avada' ),
		[
			'1'   => esc_attr__( 'H1', 'Avada' ),
			'2'   => esc_attr__( 'H2', 'Avada' ),
			'3'   => esc_attr__( 'H3', 'Avada' ),
			'4'   => esc_attr__( 'H4', 'Avada' ),
			'5'   => esc_attr__( 'H5', 'Avada' ),
			'6'   => esc_attr__( 'H6', 'Avada' ),
			'div' => esc_attr__( 'DIV', 'Avada' ),
		],
		/* translators: default value. */
		sprintf( esc_html__( 'Choose the title size you want to use. The size you choose will utilize the font family and letter spacing typography settings in Global Options for that specific size. Font size is set below.  %s', 'Avada' ), '<strong>' . $default . '</strong>' ),
		'2'
	);
	/* translators: default value. */
	$default = sprintf( esc_html__( 'Default: %s', 'Avada' ), '60' );
	$this->text(
		'heading_font_size',
		esc_attr__( 'Heading Font Size', 'Avada' ),
		/* translators: default value. */
		sprintf( esc_html__( 'Enter heading font size without px unit. In pixels, ex: 50 instead of 50px. %s', 'Avada' ), '<strong>' . $default . '</strong>' )
	);
	/* translators: default value. */
	$default = sprintf( esc_html__( 'Default: %s', 'Avada' ), '#fff' );
	$this->color(
		'heading_color',
		esc_attr__( 'Heading Color', 'Avada' ),
		/* translators: default value. */
		sprintf( esc_html__( 'Select a color for the heading font. %s', 'Avada' ), '<strong>' . $default . '</strong>' ),
		true
	);
	$this->radio_buttonset(
		'heading_bg',
		esc_attr__( 'Heading Background', 'Avada' ),
		[
			'yes' => esc_attr__( 'Yes', 'Avada' ),
			'no'  => esc_attr__( 'No', 'Avada' ),
		],
		esc_html__( 'Select this option if you would like a semi-transparent background behind your heading.', 'Avada' )
	);
	/* translators: default value. */
	$default = sprintf( esc_html__( 'Default: %s', 'Avada' ), '#000' );
	$this->color(
		'heading_bg_color',
		esc_html__( 'Heading Background Color', 'Avada' ),
		/* translators: default value. */
		sprintf( esc_html__( 'Select a color for the heading background. %s', 'Avada' ), '<strong>' . $default . '</strong>' ),
		true
	);
	$this->textarea(
		'caption',
		esc_attr__( 'Caption Area', 'Avada' ),
		esc_html__( 'Enter the caption for your slide. This field can take HTML markup and simple Avada Shortcodes that work inside a heading tag.', 'Avada' )
	);
	$this->select(
		'caption_separator',
		esc_attr__( 'Caption Separator', 'Avada' ),
		[
			'none'             => esc_attr__( 'None', 'Avada' ),
			'single solid'     => esc_attr__( 'Single Solid', 'Avada' ),
			'single dashed'    => esc_attr__( 'Single Dashed', 'Avada' ),
			'single dotted'    => esc_attr__( 'Single Dotted', 'Avada' ),
			'double solid'     => esc_attr__( 'Double Solid', 'Avada' ),
			'double dashed'    => esc_attr__( 'Double Dashed', 'Avada' ),
			'double dotted'    => esc_attr__( 'Double Dotted', 'Avada' ),
			'underline solid'  => esc_attr__( 'Underline Solid', 'Avada' ),
			'underline dashed' => esc_attr__( 'Underline Dashed', 'Avada' ),
			'underline dotted' => esc_attr__( 'Underline Dotted', 'Avada' ),
		],
		esc_html__( 'Choose the caption separator you want to use.', 'Avada' )
	);
	/* translators: default value. */
	$default = sprintf( esc_html__( 'Default: %s', 'Avada' ), 'H3' );
	$this->radio_buttonset(
		'caption_size',
		esc_attr__( 'Caption Size', 'Avada' ),
		[
			'1'   => esc_attr__( 'H1', 'Avada' ),
			'2'   => esc_attr__( 'H2', 'Avada' ),
			'3'   => esc_attr__( 'H3', 'Avada' ),
			'4'   => esc_attr__( 'H4', 'Avada' ),
			'5'   => esc_attr__( 'H5', 'Avada' ),
			'6'   => esc_attr__( 'H6', 'Avada' ),
			'div' => esc_attr__( 'DIV', 'Avada' ),
		],
		/* translators: default value. */
		sprintf( esc_html__( 'Choose the caption size you want to use. The size you choose will utilize the font family and letter spacing typography settings in Global Options for that specific size. Font size is set below. %s', 'Avada' ), '<strong>' . $default . '</strong>' ),
		'3'
	);
	/* translators: default value. */
	$default = sprintf( esc_html__( 'Default: %s', 'Avada' ), '24' );
	$this->text(
		'caption_font_size',
		esc_attr__( 'Caption Font Size', 'Avada' ),
		/* translators: default value. */
		sprintf( esc_html__( 'Enter caption font size without px unit. In pixels, ex: 24 instead of 24px. %s', 'Avada' ), '<strong>' . $default . '</strong>' )
	);
	/* translators: default value. */
	$default = sprintf( esc_html__( 'Default: %s', 'Avada' ), '#fff' );
	$this->color(
		'caption_color',
		esc_attr__( 'Caption Color', 'Avada' ),
		/* translators: default value. */
		sprintf( esc_html__( 'Select a color for the caption font. %s', 'Avada' ), '<strong>' . $default . '</strong>' ),
		true
	);
	$this->radio_buttonset(
		'caption_bg',
		esc_attr__( 'Caption Background', 'Avada' ),
		[
			'yes' => esc_attr__( 'Yes', 'Avada' ),
			'no'  => esc_attr__( 'No', 'Avada' ),
		],
		esc_html__( 'Select this option if you would like a semi-transparent background behind your caption.', 'Avada' )
	);
	/* translators: default value. */
	$default = sprintf( esc_html__( 'Default: %s', 'Avada' ), '#000' );
	$this->color(
		'caption_bg_color',
		esc_attr__( 'Caption Background Color', 'Avada' ),
		/* translators: default value. */
		sprintf( esc_html__( 'Select a color for the caption background. %s', 'Avada' ), '<strong>' . $default . '</strong>' ),
		true
	);
	?>

	<h2><?php esc_html_e( 'Link Options', 'Avada' ); ?></h2>

	<?php

	$this->radio_buttonset(
		'link_type',
		esc_attr__( 'Slide Link Type', 'Avada' ),
		[
			'button' => esc_attr__( 'Button', 'Avada' ),
			'full'   => esc_attr__( 'Full Slide', 'Avada' ),
		],
		esc_html__( 'Select how the slide will link.', 'Avada' )
	);
	$this->text(
		'slide_link',
		esc_attr__( 'Slide Link', 'Avada' ),
		esc_html__( 'Please enter your URL that will be used to link the full slide.', 'Avada' )
	);
	$this->radio_buttonset(
		'slide_target',
		esc_attr__( 'Open Slide Link In New Window', 'Avada' ),
		[
			'yes' => esc_html__( 'Yes', 'Avada' ),
			'no'  => esc_html__( 'No', 'Avada' ),
		]
	);
	$this->textarea(
		'button_1',
		esc_html__( 'Button #1', 'Avada' ) . '<br/><a href="https://theme-fusion.com/documentation/avada/elements/button-element/#params" target="_blank">' . esc_html__( 'Click here to view button option descriptions.', 'Avada' ) . '</a>',
		esc_html__( 'Adjust the button shortcode parameters for the first button.', 'Avada' ),
		'[fusion_button link="" text_transform="" title="" target="_self" link_attributes="" alignment="" modal="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" class="" id="" color="default" button_gradient_top_color="" button_gradient_bottom_color="" button_gradient_top_color_hover="" button_gradient_bottom_color_hover="" accent_color="" accent_hover_color="" type="" bevel_color="" border_width="" border_radius="" border_color="" border_hover_color="" size="" stretch="default" icon="" icon_position="left" icon_divider="no" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset=""]' . esc_html__( 'Button Text', 'Avada' ) . '[/fusion_button]'
	);
	$this->textarea(
		'button_2',
		esc_html__( 'Button #2', 'Avada' ) . '<br/><a href="https://theme-fusion.com/documentation/avada/elements/button-element/#params" target="_blank">' . esc_html__( 'Click here to view button option descriptions.', 'Avada' ) . '</a>',
		esc_html__( 'Adjust the button shortcode parameters for the second button.', 'Avada' ),
		'[fusion_button link="" text_transform="" title="" target="_self" link_attributes="" alignment="" modal="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" class="" id="" color="default" button_gradient_top_color="" button_gradient_bottom_color="" button_gradient_top_color_hover="" button_gradient_bottom_color_hover="" accent_color="" accent_hover_color="" type="" bevel_color="" border_width="" border_radius="" border_color="" border_hover_color="" size="" stretch="default" icon="" icon_position="left" icon_divider="no" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset=""]' . esc_html__( 'Button Text', 'Avada' ) . '[/fusion_button]'
	);
	?>

</div>
<div class="clear"></div>
