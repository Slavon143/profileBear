<?php
/**
 * This class contains static functions
 * that contain collections of data.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * A collection of data.
 *
 * @since 1.0.0
 */
class Fusion_Data {

	/**
	 * Returns an array of all the social icons included in the core fusion font.
	 *
	 * @static
	 * @access public
	 * @param  bool $custom If we want a custom icon entry.
	 * @param  bool $colors If we want to get the colors.
	 * @return  array
	 */
	public static function fusion_social_icons( $custom = true, $colors = false ) {

		$networks = [
			'blogger'    => [
				'label' => 'Blogger',
				'color' => '#f57d00',
			],
			'deviantart' => [
				'label' => 'Deviantart',
				'color' => '#4dc47d',
			],
			'discord'    => [
				'label' => 'Discord',
				'color' => '#26262B',
			],
			'digg'       => [
				'label' => 'Digg',
				'color' => '#000000',
			],
			'dribbble'   => [
				'label' => 'Dribbble',
				'color' => '#ea4c89',
			],
			'dropbox'    => [
				'label' => 'Dropbox',
				'color' => '#007ee5',
			],
			'facebook'   => [
				'label' => 'Facebook',
				'color' => '#3b5998',
			],
			'flickr'     => [
				'label' => 'Flickr',
				'color' => '#0063dc',
			],
			'forrst'     => [
				'label' => 'Forrst',
				'color' => '#5b9a68',
			],
			'instagram'  => [
				'label' => 'Instagram',
				'color' => '#3f729b',
			],
			'linkedin'   => [
				'label' => 'LinkedIn',
				'color' => '#0077b5',
			],
			'myspace'    => [
				'label' => 'Myspace',
				'color' => '#000000',
			],
			'paypal'     => [
				'label' => 'Paypal',
				'color' => '#003087',
			],
			'pinterest'  => [
				'label' => 'Pinterest',
				'color' => '#bd081c',
			],
			'reddit'     => [
				'label' => 'Reddit',
				'color' => '#ff4500',
			],
			'rss'        => [
				'label' => 'RSS',
				'color' => '#f26522',
			],
			'skype'      => [
				'label' => 'Skype',
				'color' => '#00aff0',
			],
			'soundcloud' => [
				'label' => 'Soundcloud',
				'color' => '#ff8800',
			],
			'spotify'    => [
				'label' => 'Spotify',
				'color' => '#2ebd59',
			],
			'teams'      => [
				'label' => 'Teams',
				'color' => '#505AC9',
			],
			'telegram'   => [
				'label' => 'Telegram',
				'color' => '#0088cc',
			],
			'tiktok'     => [
				'label' => 'Tiktok',
				'color' => '#010101',
			],
			'tumblr'     => [
				'label' => 'Tumblr',
				'color' => '#35465c',
			],
			'twitter'    => [
				'label' => 'Twitter',
				'color' => '#55acee',
			],
			'twitch'     => [
				'label' => 'Twitch',
				'color' => '#6441a5',
			],
			'vimeo'      => [
				'label' => 'Vimeo',
				'color' => '#1ab7ea',
			],
			'vk'         => [
				'label' => 'VK',
				'color' => '#45668e',
			],
			'wechat'     => [
				'label' => 'WeChat',
				'color' => '#7bb22e',
			],
			'whatsapp'   => [
				'label' => 'WhatsApp',
				'color' => '#77e878',
			],
			'xing'       => [
				'label' => 'Xing',
				'color' => '#026466',
			],
			'yahoo'      => [
				'label' => 'Yahoo',
				'color' => '#410093',
			],
			'yelp'       => [
				'label' => 'Yelp',
				'color' => '#af0606',
			],
			'youtube'    => [
				'label' => 'Youtube',
				'color' => '#cd201f',
			],
			'email'      => [
				'label' => esc_html__( 'Email Address', 'fusion-builder' ),
				'color' => '#000000',
			],
			'phone'      => [
				'label' => esc_html__( 'Phone', 'fusion-builder' ),
				'color' => '#000000',
			],
		];

		// Add a "custom" entry.
		if ( $custom ) {
			$networks['custom'] = [
				'label' => esc_html__( 'Custom', 'fusion-builder' ),
				'color' => '',
			];
		}

		if ( ! $colors ) {
			$simple_networks = [];
			foreach ( $networks as $network_id => $network_args ) {
				$simple_networks[ $network_id ] = $network_args['label'];
			}
			$networks = $simple_networks;
		}

		return $networks;

	}

	/**
	 * Returns an array of old names for font-awesome icons
	 * and their new destinations on font-awesome.
	 *
	 * @static
	 * @access public
	 */
	public static function old_icons() {

		$icons = [
			'arrow'                  => 'angle-right',
			'asterik'                => 'asterisk',
			'cross'                  => 'times',
			'ban-circle'             => 'ban',
			'bar-chart'              => 'bar-chart-o',
			'beaker'                 => 'flask',
			'bell'                   => 'bell-o',
			'bell-alt'               => 'bell',
			'bitbucket-sign'         => 'bitbucket-square',
			'bookmark-empty'         => 'bookmark-o',
			'building'               => 'building-o',
			'calendar-empty'         => 'calendar-o',
			'check-empty'            => 'square-o',
			'check-minus'            => 'minus-square-o',
			'check-sign'             => 'check-square',
			'check'                  => 'check-square-o',
			'chevron-sign-down'      => 'chevron-circle-down',
			'chevron-sign-left'      => 'chevron-circle-left',
			'chevron-sign-right'     => 'chevron-circle-right',
			'chevron-sign-up'        => 'chevron-circle-up',
			'circle-arrow-down'      => 'arrow-circle-down',
			'circle-arrow-left'      => 'arrow-circle-left',
			'circle-arrow-right'     => 'arrow-circle-right',
			'circle-arrow-up'        => 'arrow-circle-up',
			'circle-blank'           => 'circle-o',
			'cny'                    => 'rub',
			'collapse-alt'           => 'minus-square-o',
			'collapse-top'           => 'caret-square-o-up',
			'collapse'               => 'caret-square-o-down',
			'comment-alt'            => 'comment-o',
			'comments-alt'           => 'comments-o',
			'copy'                   => 'files-o',
			'cut'                    => 'scissors',
			'dashboard'              => 'tachometer',
			'double-angle-down'      => 'angle-double-down',
			'double-angle-left'      => 'angle-double-left',
			'double-angle-right'     => 'angle-double-right',
			'double-angle-up'        => 'angle-double-up',
			'download'               => 'arrow-circle-o-down',
			'download-alt'           => 'download',
			'edit-sign'              => 'pencil-square',
			'edit'                   => 'pencil-square-o',
			'ellipsis-horizontal'    => 'ellipsis-h',
			'ellipsis-vertical'      => 'ellipsis-v',
			'envelope-alt'           => 'envelope-o',
			'exclamation-sign'       => 'exclamation-circle',
			'expand-alt'             => 'plus-square-o',
			'expand'                 => 'caret-square-o-right',
			'external-link-sign'     => 'external-link-square',
			'eye-close'              => 'eye-slash',
			'eye-open'               => 'eye',
			'facebook-sign'          => 'facebook-square',
			'facetime-video'         => 'video-camera',
			'file-alt'               => 'file-o',
			'file-text-alt'          => 'file-text-o',
			'flag-alt'               => 'flag-o',
			'folder-close-alt'       => 'folder-o',
			'folder-close'           => 'folder',
			'folder-open-alt'        => 'folder-open-o',
			'food'                   => 'cutlery',
			'frown'                  => 'frown-o',
			'fullscreen'             => 'arrows-alt',
			'github-sign'            => 'github-square',
			'group'                  => 'users',
			'h-sign'                 => 'h-square',
			'hand-down'              => 'hand-o-down',
			'hand-left'              => 'hand-o-left',
			'hand-right'             => 'hand-o-right',
			'hand-up'                => 'hand-o-up',
			'hdd'                    => 'hdd-o',
			'heart-empty'            => 'heart-o',
			'hospital'               => 'hospital-o',
			'indent-left'            => 'outdent',
			'indent-right'           => 'indent',
			'info-sign'              => 'info-circle',
			'keyboard'               => 'keyboard-o',
			'legal'                  => 'gavel',
			'lemon'                  => 'lemon-o',
			'lightbulb'              => 'lightbulb-o',
			'linkedin-sign'          => 'linkedin-square',
			'meh'                    => 'meh-o',
			'microphone-off'         => 'microphone-slash',
			'minus-sign-alt'         => 'minus-square',
			'minus-sign'             => 'minus-circle',
			'mobile-phone'           => 'mobile',
			'moon'                   => 'moon-o',
			'move'                   => 'arrows',
			'off'                    => 'power-off',
			'ok-circle'              => 'check-circle-o',
			'ok-sign'                => 'check-circle',
			'ok'                     => 'check',
			'paper-clip'             => 'paperclip',
			'paste'                  => 'clipboard',
			'phone-sign'             => 'phone-square',
			'picture'                => 'picture-o',
			'pinterest-sign'         => 'pinterest-square',
			'play-circle'            => 'play-circle-o',
			'play-sign'              => 'play-circle',
			'plus-sign-alt'          => 'plus-square',
			'plus-sign'              => 'plus-circle',
			'pushpin'                => 'thumb-tack',
			'question-sign'          => 'question-circle',
			'remove-circle'          => 'times-circle-o',
			'remove-sign'            => 'times-circle',
			'remove'                 => 'times',
			'reorder'                => 'bars',
			'resize-full'            => 'expand',
			'resize-horizontal'      => 'arrows-h',
			'resize-small'           => 'compress',
			'resize-vertical'        => 'arrows-v',
			'rss-sign'               => 'rss-square',
			'save'                   => 'floppy-o',
			'screenshot'             => 'crosshairs',
			'share-alt'              => 'share',
			'share-sign'             => 'share-square',
			'share'                  => 'share-square-o',
			'sign-blank'             => 'square',
			'signin'                 => 'sign-in',
			'signout'                => 'sign-out',
			'smile'                  => 'smile-o',
			'sort-by-alphabet-alt'   => 'sort-alpha-desc',
			'sort-by-alphabet'       => 'sort-alpha-asc',
			'sort-by-attributes-alt' => 'sort-amount-desc',
			'sort-by-attributes'     => 'sort-amount-asc',
			'sort-by-order-alt'      => 'sort-numeric-desc',
			'sort-by-order'          => 'sort-numeric-asc',
			'sort-down'              => 'sort-asc',
			'sort-up'                => 'sort-desc',
			'stackexchange'          => 'stack-overflow',
			'star-empty'             => 'star-o',
			'star-half-empty'        => 'star-half-o',
			'sun'                    => 'sun-o',
			'thumbs-down-alt'        => 'thumbs-o-down',
			'thumbs-up-alt'          => 'thumbs-o-up',
			'time'                   => 'clock-o',
			'trash'                  => 'trash-o',
			'tumblr-sign'            => 'tumblr-square',
			'twitter-sign'           => 'twitter-square',
			'unlink'                 => 'chain-broken',
			'upload'                 => 'arrow-circle-o-up',
			'upload-alt'             => 'upload',
			'warning-sign'           => 'exclamation-triangle',
			'xing-sign'              => 'xing-square',
			'youtube-sign'           => 'youtube-square',
			'zoom-in'                => 'search-plus',
			'zoom-out'               => 'search-minus',
		];

		return $icons;

	}

	/**
	 * Get an array of all standard fonts.
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function standard_fonts() {

		$standard_fonts = [
			'Arial, Helvetica, sans-serif'          => 'Arial, Helvetica, sans-serif',
			"'Arial Black', Gadget, sans-serif"     => "'Arial Black', Gadget, sans-serif",
			"'Bookman Old Style', serif"            => "'Bookman Old Style', serif",
			"'Comic Sans MS', cursive"              => "'Comic Sans MS', cursive",
			'Courier, monospace'                    => 'Courier, monospace',
			'Garamond, serif'                       => 'Garamond, serif',
			'Georgia, serif'                        => 'Georgia, serif',
			'Impact, Charcoal, sans-serif'          => 'Impact, Charcoal, sans-serif',
			"'Lucida Console', Monaco, monospace"   => "'Lucida Console', Monaco, monospace",
			"'Lucida Sans Unicode', 'Lucida Grande', sans-serif" => "'Lucida Sans Unicode', 'Lucida Grande', sans-serif",
			"'MS Sans Serif', Geneva, sans-serif"   => "'MS Sans Serif', Geneva, sans-serif",
			"'MS Serif', 'New York', sans-serif"    => "'MS Serif', 'New York', sans-serif",
			"'Palatino Linotype', 'Book Antiqua', Palatino, serif" => "'Palatino Linotype', 'Book Antiqua', Palatino, serif",
			'Tahoma, Geneva, sans-serif'            => 'Tahoma, Geneva, sans-serif',
			"'Times New Roman', Times, serif"       => "'Times New Roman', Times, serif",
			"'Trebuchet MS', Helvetica, sans-serif" => "'Trebuchet MS', Helvetica, sans-serif",
			'Verdana, Geneva, sans-serif'           => 'Verdana, Geneva, sans-serif',
		];

		return $standard_fonts;

	}

	/**
	 * Get an array of all font-weights.
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function font_weights() {

		$font_weights = [
			'100' => esc_html__( 'Thin (100)', 'fusion-builder' ),
			'200' => esc_html__( 'Extra Light (200)', 'fusion-builder' ),
			'300' => esc_html__( 'Light (300)', 'fusion-builder' ),
			'400' => esc_html__( 'Normal (400)', 'fusion-builder' ),
			'500' => esc_html__( 'Medium (500)', 'fusion-builder' ),
			'600' => esc_html__( 'Semi Bold (600)', 'fusion-builder' ),
			'700' => esc_html__( 'Bold (700)', 'fusion-builder' ),
			'800' => esc_html__( 'Bolder (800)', 'fusion-builder' ),
			'900' => esc_html__( 'Extra Bold (900)', 'fusion-builder' ),
		];

		return $font_weights;

	}

	/**
	 * Get an array of all available font subsets for the Google Fonts API.
	 *
	 * @static
	 * @access  public
	 * @return  array
	 */
	public static function font_subsets() {
		// Deprecated in Avada 7.0 - The google-fonts API changed
		// and no longer requires subsets.
		return [];
	}

}

/* Omit closing PHP tag to avoid 'Headers already sent' issues. */
