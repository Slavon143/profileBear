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
class Fusion_Widget_Social_Links extends WP_Widget {

	/**
	 * General settings of the widget.
	 *
	 * @access protected
	 * @since 4.0
	 * @var array
	 */
	protected $settings = [];

	/**
	 * Custom social icon array.
	 *
	 * @static
	 * @access public
	 * @since 5.0.0
	 * @var array
	 */
	public static $custom_icons = [];

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		$this->set_settings();
		$this->set_custom_icons();

		$widget_ops  = [
			'classname'   => 'social_links',
			'description' => __( 'Adds social media icons with custom links.', 'fusion-core' ),
		];
		$control_ops = [
			'id_base' => 'social_links-widget',
		];

		parent::__construct( 'social_links-widget', __( 'Avada: Social Links', 'fusion-core' ), $widget_ops, $control_ops );
	}

	/**
	 * Set settings for this widget,
	 * with a fallback to some sane defaults if no customized settings are saved in the database.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function set_settings() {
		$fusion_settings = class_exists( 'Fusion_Settings' ) ? awb_get_fusion_settings() : false;

		if ( $fusion_settings ) {
			$this->settings = [
				'social_media_icons'    => $fusion_settings->get( 'social_media_icons' ),
				'nofollow_social_links' => $fusion_settings->get( 'nofollow_social_links' ),
			];
		} else {
			$this->settings = [
				'social_media_icons'    => [],
				'nofollow_social_links' => '0',
			];
		}
	}

	/**
	 * Sets custom icons array variable.
	 *
	 * @static
	 * @access public
	 * @since  5.0.0
	 * @return  void
	 */
	public function set_custom_icons() {
		$theme_options_array = $this->settings['social_media_icons'];
		$custom_icons        = [];

		if ( is_array( $theme_options_array ) && array_key_exists( 'icon', $theme_options_array ) && is_array( $theme_options_array['icon'] ) ) {
			$custom_icon_indices = array_keys( $theme_options_array['icon'], 'custom', true );

			$i = 0;
			if ( isset( $custom_icon_indices ) && is_array( $custom_icon_indices ) ) {
				foreach ( $custom_icon_indices as $name => $index ) {
					$icon_key = 'custom_' . $index;
					$i++;
					$custom_icons[ $icon_key ] = [
						'label' => $theme_options_array['custom_title'][ $index ],
						'color' => '',
					];
				}
			}
		}
		self::$custom_icons = $custom_icons;
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

		if ( ! is_array( $instance ) ) {
			$instance = [];
		}

		$title              = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );
		$add_class          = '';
		$style              = '';
		$nofollow           = ( $this->settings['nofollow_social_links'] ) ? 'nofollow noopener noreferrer' : 'noopener noreferrer';
		$to_social_networks = $this->settings['social_media_icons'];

		if ( ! isset( $instance['tooltip_pos'] ) || '' === $instance['tooltip_pos'] ) {
			$instance['tooltip_pos'] = 'top';
		}

		if ( ! isset( $instance['icon_color'] ) || '' === $instance['icon_color'] ) {
			$instance['icon_color'] = '#bebdbd';
		}

		if ( ! isset( $instance['boxed_icon'] ) || '' === $instance['boxed_icon'] ) {
			$instance['boxed_icon'] = 'Yes';
		}

		if ( ! isset( $instance['boxed_color'] ) || '' === $instance['boxed_color'] ) {
			$instance['boxed_color'] = '#e8e8e8';
		}

		if ( ! isset( $instance['boxed_icon_radius'] ) || '' === $instance['boxed_icon_radius'] ) {
			$instance['boxed_icon_radius'] = '4px';
		}

		if ( ! isset( $instance['linktarget'] ) || '' === $instance['linktarget'] ) {
			$instance['linktarget'] = '_self';
		}

		if ( ! isset( $instance['color_type'] ) || '' === $instance['color_type'] ) {
			$instance['color_type'] = 'custom';
		}

		if ( isset( $instance['boxed_icon'] ) && isset( $instance['boxed_icon_radius'] ) && 'Yes' === $instance['boxed_icon'] && ( $instance['boxed_icon_radius'] || '0' === $instance['boxed_icon_radius'] ) ) {
			$instance['boxed_icon_radius'] = ( 'round' === $instance['boxed_icon_radius'] ) ? '50%' : $instance['boxed_icon_radius'];
			$style                        .= 'border-radius:' . $instance['boxed_icon_radius'] . ';';
		}

		if ( isset( $instance['boxed_icon'] ) && 'Yes' === $instance['boxed_icon'] && isset( $instance['boxed_icon_padding'] ) && isset( $instance['boxed_icon_padding'] ) ) {
			$style .= 'padding:' . $instance['boxed_icon_padding'] . ';';
		}

		if ( isset( $instance['boxed_icon'] ) && 'Yes' === $instance['boxed_icon'] ) {
			$add_class .= ' boxed-icons';
		}

		if ( ! isset( $instance['icons_font_size'] ) || '' === $instance['icons_font_size'] ) {
			$instance['icons_font_size'] = '16px';
		}

		$style .= 'font-size:' . $instance['icons_font_size'] . ';';

		$social_networks = [];
		foreach ( $instance as $name => $value ) {
			if ( false !== strpos( $name, '_link' ) && $value ) {
				$new_value = str_replace( '_link', '', $name );
				$new_value = ( 'facebook' === $new_value ) ? 'fb' : $new_value;

				$social_networks[ $name ] = $new_value;
			}
		}

		$social_networks_ordered = [];

		// Check TO Social Links, if not in $social_networks and use_to option is yes, then add.
		if ( isset( $instance['use_to'] ) && 'Yes' === $instance['use_to'] && isset( $to_social_networks ) && is_array( $to_social_networks ) ) {
			foreach ( $to_social_networks['icon'] as $key => $value ) {
				if ( empty( $value ) ) {
					continue;
				}
				$new_value = $value;
				$new_value = ( 'facebook' === $new_value ) ? 'fb' : $new_value;

				if ( ! in_array( $new_value, $social_networks, true ) && 'custom' !== $value ) {
					$social_networks[ $new_value . '_link' ] = $value;
					$instance[ $new_value . '_link' ]        = $to_social_networks['url'][ $key ];
				}
			}
		}

		if ( isset( $to_social_networks['fusionredux_repeater_data'] ) && $to_social_networks && 0 < count( $to_social_networks['fusionredux_repeater_data'] ) ) {
			// Loop through the set of social networks and order them
			// according to the Global Options > Social Media tab ordering.
			// Append those icons that are not set in Global Options at the end.
			foreach ( $social_networks as $name => $value ) {

				$compare_value = ( 'fb' === $value ) ? 'facebook' : $value;

				$social_network_position = array_search( $compare_value, $to_social_networks['icon'], true );
				if ( $social_network_position || 0 === $social_network_position ) {
					$social_networks_ordered[ $social_network_position ] = $name;
					unset( $social_networks[ $name ] );
				} else {
					$social_networks[ $name ] = $value . '_link';
				}
			}

			// Make sure all custom icons from Global Options > Social Media tab are included, if the widget option is set.
			if ( isset( $instance['show_custom'] ) && 'Yes' === $instance['show_custom'] ) {
				$custom_icon_indices = array_keys( $to_social_networks['icon'], 'custom', true );

				foreach ( $custom_icon_indices as $name => $index ) {

					$network_icon_height = $to_social_networks['custom_source'][ $index ]['height'];
					$network_icon_width  = $to_social_networks['custom_source'][ $index ]['width'];
					$network_link        = $to_social_networks['url'][ $index ];

					// Check if different URL is set for this custom icon in the widget.  If so, use that instead.
					$instance_key = 'custom_' . $index . '_link';
					if ( isset( $instance[ $instance_key ] ) && '' !== $instance[ $instance_key ] ) {
						$network_link = $instance[ $instance_key ];
					}

					$social_networks_ordered[ $index ] = [
						'network_name'        => $to_social_networks['custom_title'][ $index ],
						'network_icon'        => $to_social_networks['custom_source'][ $index ]['url'],
						'network_icon_height' => $network_icon_height,
						'network_icon_width'  => $network_icon_width,
						'network_icon_mark'   => isset( $to_social_networks['icon_mark'][ $index ] ) ? $to_social_networks['icon_mark'][ $index ] : '',
						'network_link'        => $network_link,
					];
				}

				foreach ( $social_networks_ordered as $index => $value ) {
					if ( is_integer( $index ) ) {
						unset( $social_networks[ 'custom_' . $index . '_link' ] );
					}
				}
			}
		}

		ksort( $social_networks_ordered );
		$social_networks_ordered = array_merge( $social_networks_ordered, $social_networks );

		$icon_colors     = [];
		$icon_colors_max = 1;

		if ( isset( $instance['icon_color'] ) && $instance['icon_color'] ) {
			$icon_colors     = explode( '|', $instance['icon_color'] );
			$icon_colors_max = count( $icon_colors );
		}

		$box_colors     = [];
		$box_colors_max = 1;

		if ( isset( $instance['boxed_color'] ) && $instance['boxed_color'] ) {
			$box_colors     = explode( '|', $instance['boxed_color'] );
			$box_colors_max = count( $box_colors );
		}

		echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput

		if ( $title ) {
			echo $before_title . $title . $after_title; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>

		<div class="fusion-social-networks<?php echo esc_attr( $add_class ); ?>">

			<div class="fusion-social-networks-wrapper">
				<?php $icon_color_count = 0; ?>
				<?php $box_color_count = 0; ?>
				<?php foreach ( $social_networks_ordered as $name => $value ) : ?>
					<?php
					if ( is_string( $value ) ) {
						if ( $to_social_networks && isset( $to_social_networks['fusionredux_repeater_data'] ) && 0 < count( $to_social_networks['fusionredux_repeater_data'] ) ) {
							$name = $value;
						}

						$value = str_replace( '_link', '', $value );

						if ( 'google' === $value ) {
							continue;
						}

						$value = ( 'fb' === $value ) ? 'facebook' : $value;
						$value = ( 'email' === $value ) ? 'mail' : $value;

						$tooltip = $value;
						$tooltip = ( 'linkedin' === $tooltip ) ? 'LinkedIn' : $tooltip;
					} else {
						$tooltip = $value['network_name'];
					}

					$icon_style = '';
					$box_style  = '';

					if ( 'brand' === $instance['color_type'] ) {
						// If not custom social icon.
						if ( is_string( $value ) && 0 !== strpos( $value, 'custom_' ) ) {
							// Get a list of all the available social networks.
							$social_icon_boxed_colors         = FusionCore_Plugin::$fusion_library_exists ? Fusion_Data::fusion_social_icons( false, true ) : [];
							$social_icon_boxed_colors['mail'] = [
								'label' => esc_html__( 'Email Address', 'fusion-core' ),
								'color' => '#000000',
							];

							if ( ! isset( $social_icon_boxed_colors[ $value ] ) ) {
								continue;
							}

							$color    = ( 'Yes' === $instance['boxed_icon'] ) ? '#ffffff' : $social_icon_boxed_colors[ $value ]['color'];
							$bg_color = ( 'Yes' === $instance['boxed_icon'] ) ? $social_icon_boxed_colors[ $value ]['color'] : '';

							$icon_style = 'color:' . $color . ';';
							$box_style  = 'background-color:' . $bg_color . ';border-color:' . $bg_color . ';';
						}
					} else {
						if ( isset( $icon_colors[ $icon_color_count ] ) && $icon_colors[ $icon_color_count ] ) {
							$icon_style = 'color:' . trim( $icon_colors[ $icon_color_count ] ) . ';';
						} elseif ( isset( $icon_colors[ ( $icon_colors_max - 1 ) ] ) ) {
							$icon_style = 'color:' . trim( $icon_colors[ ( $icon_colors_max - 1 ) ] ) . ';';
						}

						if ( isset( $instance['boxed_icon'] ) && 'Yes' === $instance['boxed_icon'] && isset( $box_colors[ $box_color_count ] ) && $box_colors[ $box_color_count ] ) {
							$box_style = 'background-color:' . trim( $box_colors[ $box_color_count ] ) . ';border-color:' . trim( $box_colors[ $box_color_count ] ) . ';';
						} elseif ( isset( $instance['boxed_icon'] ) && 'Yes' === $instance['boxed_icon'] && isset( $box_colors[ ( $box_colors_max - 1 ) ] ) && ( ! isset( $box_colors[ $box_color_count ] ) || ! $box_colors[ $box_color_count ] ) ) {
							$box_style = 'background-color:' . trim( $box_colors[ ( $box_colors_max - 1 ) ] ) . ';border-color:' . trim( $box_colors[ ( $box_colors_max - 1 ) ] ) . ';';
						}
					}

					$tooltip_params = ' ';
					if ( 'none' !== strtolower( $instance['tooltip_pos'] ) ) {
						$tooltip_params = ' data-placement="' . strtolower( $instance['tooltip_pos'] ) . '" data-title="' . ucwords( $tooltip ) . '" data-toggle="tooltip" data-original-title="" ';
					}
					$tooltip = ucwords( $tooltip );
					if ( 'youtube' === strtolower( $tooltip ) ) {
						$tooltip = 'YouTube';
					}
					?>
					<?php if ( is_string( $value ) ) : ?>
						<?php if ( 'mail' === $value ) : ?>
							<?php if ( apply_filters( 'fusion_disable_antispambot', false ) ) : ?>
								<?php $instance[ $name ] = 'mailto:' . $instance[ $name ]; ?>
							<?php else : ?>
								<?php $instance[ $name ] = 'mailto:' . antispambot( $instance[ $name ] ); ?>
							<?php endif; ?>
						<?php endif; ?>

						<?php if ( 'phone' === $value ) : ?>
							<?php $instance[ $name ] = 'tel:' . esc_attr( $instance[ $name ] ); ?>
						<?php endif; ?>

						<?php if ( isset( $instance[ $name ] ) ) : ?>
							<?php if ( 'skype' === $value || 'whatsapp' === $value || 'wechat' === $value ) : ?>
								<?php $social_link = esc_attr( $instance[ $name ] ); ?>
							<?php else : ?>
								<?php $social_link = esc_url( $instance[ $name ] ); ?>
							<?php endif; ?>
							<a class="fusion-social-network-icon fusion-tooltip fusion-<?php echo esc_attr( $value ); ?> awb-icon-<?php echo esc_attr( $value ); ?>" href="<?php echo esc_attr( $social_link ); ?>" <?php echo $tooltip_params; // phpcs:ignore WordPress.Security.EscapeOutput ?> title="<?php echo esc_attr( $tooltip ); ?>" aria-label="<?php echo esc_attr( ucwords( $tooltip ) ); ?>" rel="<?php echo esc_attr( $nofollow ); ?>" target="<?php echo esc_attr( $instance['linktarget'] ); ?>" style="<?php echo esc_attr( $style . $icon_style . $box_style ); ?>"></a>
						<?php endif; ?>
					<?php else : ?>
						<?php if ( 'mail' === $value ) : ?>
							<?php if ( apply_filters( 'fusion_disable_antispambot', false ) ) : ?>
								<?php $value['network_link'] = 'mailto:' . $value['network_link']; ?>
							<?php else : ?>
								<?php $value['network_link'] = 'mailto:' . antispambot( $value['network_link'] ); ?>
							<?php endif; ?>
						<?php endif; ?>

						<?php if ( '' !== $value['network_icon_mark'] ) : ?>
							<a class="fusion-social-network-icon fusion-tooltip <?php echo esc_attr( str_replace( 'fusion-prefix-', '', $value['network_icon_mark'] ) ); ?>" target="<?php echo esc_attr( $instance['linktarget'] ); ?>" href="<?php echo esc_url_raw( $value['network_link'] ); ?>" title="<?php echo esc_attr( $tooltip ); ?>" aria-label="<?php echo esc_attr( ucwords( $tooltip ) ); ?>" rel="<?php esc_attr( $nofollow ); ?>" <?php echo $tooltip_params; // phpcs:ignore WordPress.Security.EscapeOutput ?> style="<?php echo esc_attr( $style . $icon_style . $box_style ); ?>"></a>						
						<?php else : ?>
							<a class="fusion-social-network-icon fusion-tooltip" target="<?php echo esc_attr( $instance['linktarget'] ); ?>" href="<?php echo esc_url_raw( $value['network_link'] ); ?>" rel="<?php esc_attr( $nofollow ); ?>" <?php echo $tooltip_params; // phpcs:ignore WordPress.Security.EscapeOutput ?> title="" style="<?php echo esc_attr( $style . $box_style ); ?>"><img src="<?php echo esc_url_raw( $value['network_icon'] ); ?>" height="<?php echo esc_attr( $value['network_icon_height'] ); ?>" width="<?php echo esc_attr( $value['network_icon_width'] ); ?>" alt="<?php echo esc_attr( $value['network_name'] ); ?>" /></a>
						<?php endif; ?>
					<?php endif; ?>

					<?php $icon_color_count++; ?>
					<?php $box_color_count++; ?>

				<?php endforeach; ?>

			</div>
		</div>

		<?php
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

		$instance['title']              = isset( $new_instance['title'] ) ? $new_instance['title'] : '';
		$instance['linktarget']         = isset( $new_instance['linktarget'] ) ? $new_instance['linktarget'] : '';
		$instance['icons_font_size']    = isset( $new_instance['icons_font_size'] ) ? $new_instance['icons_font_size'] : '';
		$instance['icon_color']         = isset( $new_instance['icon_color'] ) ? $new_instance['icon_color'] : '';
		$instance['boxed_icon']         = isset( $new_instance['boxed_icon'] ) ? $new_instance['boxed_icon'] : '';
		$instance['boxed_color']        = isset( $new_instance['boxed_color'] ) ? $new_instance['boxed_color'] : '';
		$instance['color_type']         = isset( $new_instance['color_type'] ) ? $new_instance['color_type'] : '';
		$instance['boxed_icon_radius']  = isset( $new_instance['boxed_icon_radius'] ) ? $new_instance['boxed_icon_radius'] : '';
		$instance['boxed_icon_padding'] = isset( $new_instance['boxed_icon_padding'] ) ? $new_instance['boxed_icon_padding'] : '';
		$instance['tooltip_pos']        = isset( $new_instance['tooltip_pos'] ) ? $new_instance['tooltip_pos'] : '';
		$instance['show_custom']        = isset( $new_instance['show_custom'] ) ? $new_instance['show_custom'] : '';
		$instance['fb_link']            = isset( $new_instance['fb_link'] ) ? $new_instance['fb_link'] : '';
		$instance['flickr_link']        = isset( $new_instance['flickr_link'] ) ? $new_instance['flickr_link'] : '';
		$instance['rss_link']           = isset( $new_instance['rss_link'] ) ? $new_instance['rss_link'] : '';
		$instance['telegram_link']      = isset( $new_instance['telegram_link'] ) ? $new_instance['telegram_link'] : '';
		$instance['tiktok_link']        = isset( $new_instance['tiktok_link'] ) ? $new_instance['tiktok_link'] : '';
		$instance['twitter_link']       = isset( $new_instance['twitter_link'] ) ? $new_instance['twitter_link'] : '';
		$instance['twitch_link']        = isset( $new_instance['twitch_link'] ) ? $new_instance['twitch_link'] : '';
		$instance['vimeo_link']         = isset( $new_instance['vimeo_link'] ) ? $new_instance['vimeo_link'] : '';
		$instance['youtube_link']       = isset( $new_instance['youtube_link'] ) ? $new_instance['youtube_link'] : '';
		$instance['instagram_link']     = isset( $new_instance['instagram_link'] ) ? $new_instance['instagram_link'] : '';
		$instance['pinterest_link']     = isset( $new_instance['pinterest_link'] ) ? $new_instance['pinterest_link'] : '';
		$instance['tumblr_link']        = isset( $new_instance['tumblr_link'] ) ? $new_instance['tumblr_link'] : '';
		$instance['dribbble_link']      = isset( $new_instance['dribbble_link'] ) ? $new_instance['dribbble_link'] : '';
		$instance['discord_link']       = isset( $new_instance['discord_link'] ) ? $new_instance['discord_link'] : '';
		$instance['digg_link']          = isset( $new_instance['digg_link'] ) ? $new_instance['digg_link'] : '';
		$instance['linkedin_link']      = isset( $new_instance['linkedin_link'] ) ? $new_instance['linkedin_link'] : '';
		$instance['blogger_link']       = isset( $new_instance['blogger_link'] ) ? $new_instance['blogger_link'] : '';
		$instance['skype_link']         = isset( $new_instance['skype_link'] ) ? $new_instance['skype_link'] : '';
		$instance['teams_link']         = isset( $new_instance['teams_link'] ) ? $new_instance['teams_link'] : '';
		$instance['forrst_link']        = isset( $new_instance['forrst_link'] ) ? $new_instance['forrst_link'] : '';
		$instance['myspace_link']       = isset( $new_instance['myspace_link'] ) ? $new_instance['myspace_link'] : '';
		$instance['deviantart_link']    = isset( $new_instance['deviantart_link'] ) ? $new_instance['deviantart_link'] : '';
		$instance['yahoo_link']         = isset( $new_instance['yahoo_link'] ) ? $new_instance['yahoo_link'] : '';
		$instance['reddit_link']        = isset( $new_instance['reddit_link'] ) ? $new_instance['reddit_link'] : '';
		$instance['paypal_link']        = isset( $new_instance['paypal_link'] ) ? $new_instance['paypal_link'] : '';
		$instance['dropbox_link']       = isset( $new_instance['dropbox_link'] ) ? $new_instance['dropbox_link'] : '';
		$instance['soundcloud_link']    = isset( $new_instance['soundcloud_link'] ) ? $new_instance['soundcloud_link'] : '';
		$instance['spotify_link']       = isset( $new_instance['spotify_link'] ) ? $new_instance['spotify_link'] : '';
		$instance['vk_link']            = isset( $new_instance['vk_link'] ) ? $new_instance['vk_link'] : '';
		$instance['wechat_link']        = isset( $new_instance['wechat_link'] ) ? $new_instance['wechat_link'] : '';
		$instance['whatsapp_link']      = isset( $new_instance['whatsapp_link'] ) ? $new_instance['whatsapp_link'] : '';
		$instance['xing_link']          = isset( $new_instance['xing_link'] ) ? $new_instance['xing_link'] : '';
		$instance['yelp_link']          = isset( $new_instance['yelp_link'] ) ? $new_instance['yelp_link'] : '';
		$instance['email_link']         = isset( $new_instance['email_link'] ) ? $new_instance['email_link'] : '';
		$instance['phone_link']         = isset( $new_instance['phone_link'] ) ? $new_instance['phone_link'] : '';
		$instance['use_to']             = isset( $new_instance['use_to'] ) ? $new_instance['use_to'] : '';

		if ( 0 < count( self::$custom_icons ) ) {
			foreach ( self::$custom_icons as $key => $value ) {
				$instance[ $key . '_link' ] = isset( $new_instance[ $key . '_link' ] ) ? $new_instance[ $key . '_link' ] : '';
			}
		}

		return $instance;

	}

	/**
	 * Outputs the settings update form.
	 *
	 * @access public
	 * @param array $instance Current settings.
	 */
	public function form( $instance ) {

		$defaults = [
			'title'              => __( 'Get Social', 'fusion-core' ),
			'linktarget'         => '',
			'icons_font_size'    => '16px',
			'icon_color'         => '',
			'boxed_icon'         => 'No',
			'boxed_color'        => '',
			'color_type'         => 'custom',
			'boxed_icon_radius'  => '4px',
			'boxed_icon_padding' => '8px',
			'tooltip_pos'        => 'top',
			'blogger_link'       => '',
			'deviantart_link'    => '',
			'discord_link'       => '',
			'digg_link'          => '',
			'dribbble_link'      => '',
			'dropbox_link'       => '',
			'email_link'         => '',
			'phone_link'         => '',
			'fb_link'            => '',
			'flickr_link'        => '',
			'forrst_link'        => '',
			'instagram_link'     => '',
			'linkedin_link'      => '',
			'myspace_link'       => '',
			'paypal_link'        => '',
			'pinterest_link'     => '',
			'reddit_link'        => '',
			'rss_link'           => '',
			'skype_link'         => '',
			'teams_link'         => '',
			'soundcloud_link'    => '',
			'spotify_link'       => '',
			'telegram_link'      => '',
			'tiktok_link'        => '',
			'tumblr_link'        => '',
			'twitter_link'       => '',
			'twitch_link'        => '',
			'vimeo_link'         => '',
			'vk_link'            => '',
			'whatsapp_link'      => '',
			'wechat_link'        => '',
			'xing_link'          => '',
			'yahoo_link'         => '',
			'yelp_link'          => '',
			'youtube_link'       => '',
			'show_custom'        => 'No',
			'use_to'             => 'No',
		];

		if ( 0 < count( self::$custom_icons ) ) {
			foreach ( self::$custom_icons as $key => $value ) {
				$defaults[ $key . '_link' ] = '';
			}
		}

		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'linktarget' ) ); ?>"><?php esc_attr_e( 'Link Target:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'linktarget' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'linktarget' ) ); ?>" value="<?php echo esc_attr( $instance['linktarget'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'icons_font_size' ) ); ?>"><?php esc_attr_e( 'Icons Font Size:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'icons_font_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'icons_font_size' ) ); ?>" value="<?php echo esc_attr( $instance['icons_font_size'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'color_type' ) ); ?>"><?php esc_attr_e( 'Icons Color Type:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'color_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'color_type' ) ); ?>" class="widefat fusion-social-color-type" style="width:100%;">
				<option value="custom" <?php echo ( 'custom' === $instance['color_type'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Custom Color', 'fusion-core' ); ?></option>
				<option value="brand" <?php echo ( 'brand' === $instance['color_type'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Brand Colors', 'fusion-core' ); ?></option>
			</select>
		</p>

		<p class="avada-widget-color-type-option-child">
			<label for="<?php echo esc_attr( $this->get_field_id( 'icon_color' ) ); ?>"><?php esc_attr_e( 'Icons Color Hex Code:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'icon_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'icon_color' ) ); ?>" value="<?php echo esc_attr( $instance['icon_color'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'boxed_icon' ) ); ?>"><?php esc_attr_e( 'Icons Boxed:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'boxed_icon' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'boxed_icon' ) ); ?>" class="widefat fusion-social-icons-boxed" style="width:100%;">
				<option value="No" <?php echo ( 'No' === $instance['boxed_icon'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'No', 'fusion-core' ); ?></option>
				<option value="Yes" <?php echo ( 'Yes' === $instance['boxed_icon'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Yes', 'fusion-core' ); ?></option>
			</select>
		</p>

		<p class="avada-widget-color-type-option-child avada-widget-boxed-icon-background">
			<label for="<?php echo esc_attr( $this->get_field_id( 'boxed_color' ) ); ?>"><?php esc_attr_e( 'Boxed Icons Background Color Hex Code:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'boxed_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'boxed_color' ) ); ?>" value="<?php echo esc_attr( $instance['boxed_color'] ); ?>" />
		</p>

		<p class="avada-widget-boxed-icon-option-child">
			<label for="<?php echo esc_attr( $this->get_field_id( 'boxed_icon_radius' ) ); ?>"><?php esc_attr_e( 'Boxed Icons Radius:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'boxed_icon_radius' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'boxed_icon_radius' ) ); ?>" value="<?php echo esc_attr( $instance['boxed_icon_radius'] ); ?>" />
		</p>

		<p class="avada-widget-boxed-icon-option-child">
			<label for="<?php echo esc_attr( $this->get_field_id( 'boxed_icon_padding' ) ); ?>"><?php esc_attr_e( 'Boxed Icons Padding:', 'fusion-core' ); ?></label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'boxed_icon_padding' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'boxed_icon_padding' ) ); ?>" value="<?php echo esc_attr( $instance['boxed_icon_padding'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'tooltip_pos' ) ); ?>"><?php esc_attr_e( 'Tooltip Position:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'tooltip_pos' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tooltip_pos' ) ); ?>" class="widefat" style="width:100%;">
				<option value="Top" <?php echo ( 'Top' === $instance['tooltip_pos'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Top', 'fusion-core' ); ?></option>
				<option value="Right" <?php echo ( 'Right' === $instance['tooltip_pos'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Right', 'fusion-core' ); ?></option>
				<option value="Bottom" <?php echo ( 'Bottom' === $instance['tooltip_pos'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Bottom', 'fusion-core' ); ?></option>
				<option value="Left" <?php echo ( 'Left' === $instance['tooltip_pos'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Left', 'fusion-core' ); ?></option>
				<option value="None" <?php echo ( 'None' === $instance['tooltip_pos'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'None', 'fusion-core' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_custom' ) ); ?>"><?php esc_attr_e( 'Show Custom Icons:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'show_custom' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_custom' ) ); ?>" class="widefat" style="width:100%;">
				<option value="No" <?php echo ( 'No' === $instance['show_custom'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'No', 'fusion-core' ); ?></option>
				<option value="Yes" <?php echo ( 'Yes' === $instance['show_custom'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Yes', 'fusion-core' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'use_to' ) ); ?>"><?php esc_attr_e( 'Use Global Options Links:', 'fusion-core' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'use_to' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'use_to' ) ); ?>" class="widefat" style="width:100%;">
				<option value="No" <?php echo ( 'No' === $instance['use_to'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'No', 'fusion-core' ); ?></option>
				<option value="Yes" <?php echo ( 'Yes' === $instance['use_to'] ) ? 'selected="selected"' : ''; ?>><?php esc_attr_e( 'Yes', 'fusion-core' ); ?></option>
			</select>
		</p>

		<?php
		// Create social network fields.
		$social_networks_full_array = FusionCore_Plugin::$fusion_library_exists ? Fusion_Data::fusion_social_icons( false, true ) : [];
		if ( 0 < count( self::$custom_icons ) ) {
			$social_networks_full_array = array_merge( $social_networks_full_array, self::$custom_icons );
		}

		foreach ( $social_networks_full_array as $key => $value ) {

			$key = ( 'facebook' === $key ) ? 'fb' : $key;

			echo '<p>';
			/* translators: The social-network label. */
			echo '<label for="' . esc_attr( $this->get_field_id( $key . '_link' ) ) . '">' . sprintf( esc_attr__( '%s Link:', 'fusion-core' ), esc_attr( $value['label'] ) ) . '</label>';
			echo '<input class="widefat" type="text" id="' . esc_attr( $this->get_field_id( $key . '_link' ) ) . '" name="' . esc_attr( $this->get_field_name( $key . '_link' ) ) . '" value="' . esc_attr( $instance[ $key . '_link' ] ) . '" />';
			echo '</p>';

		}
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				jQuery.fn.checkBoxedIcons = function() {
					var colorType = jQuery( this ).parents( 'form' ).find( '.fusion-social-color-type' ).val(),
						boxedIcon = jQuery( this ).parents( 'form' ).find( '.fusion-social-icons-boxed' ).val();

					if ( 'No' === boxedIcon ) {
						jQuery( this ).parents( 'form' ).find( '.avada-widget-boxed-icon-option-child' ).hide();
						jQuery( this ).parents( 'form' ).find( '.avada-widget-boxed-icon-background' ).hide();
					} else {
						jQuery( this ).parents( 'form' ).find( '.avada-widget-boxed-icon-option-child' ).show();

						if ( 'custom' === colorType ) {
							jQuery( this ).parents( 'form' ).find( '.avada-widget-boxed-icon-background' ).show();
						}
					}
				};

				jQuery.fn.checkColorType = function() {
					var colorType = jQuery( this ).parents( 'form' ).find( '.fusion-social-color-type' ).val(),
						boxedIcon = jQuery( this ).parents( 'form' ).find( '.fusion-social-icons-boxed' ).val();

					if ( 'brand' === colorType ) {
						jQuery( this ).parents( 'form' ).find( '.avada-widget-color-type-option-child' ).hide();
					} else {
						jQuery( this ).parents( 'form' ).find( '.avada-widget-color-type-option-child' ).show();

						if ( 'No' === boxedIcon ) {
							jQuery( this ).parents( 'form' ).find( '.avada-widget-boxed-icon-background' ).hide();
						}
					}
				};

				jQuery( '.fusion-social-color-type' ).each( function() {
					jQuery( this ).checkColorType();
				} );
				jQuery( '.fusion-social-icons-boxed' ).each( function() {
					jQuery( this ).checkBoxedIcons();
				} );

				jQuery( '.fusion-social-color-type' ).on( 'change', function() {
					jQuery( this ).checkColorType();
				});

				jQuery( '.fusion-social-icons-boxed' ).on( 'change', function() {
					jQuery( this ).checkBoxedIcons();
				});
			});
		</script>
		<?php
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
