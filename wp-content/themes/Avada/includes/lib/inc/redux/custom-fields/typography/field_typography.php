<?php

/**
 * Class and Function List:
 * Function list:
 * - __construct()
 * - render()
 * - enqueue()
 * - makeGoogleWebfontLink()
 * - makeGoogleWebfontString()
 * - output()
 * - getGoogleArray()
 * - getVariants()
 * Classes list:
 * - FusionReduxFramework_typography
 */

if ( ! class_exists( 'FusionReduxFramework_typography' ) ) {
	class FusionReduxFramework_typography {

		private $user_fonts = true;

		protected $parent;
		protected $field;
		protected $value;

		/**
		 * Field Constructor.
		 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
		 *
		 * @since FusionReduxFramework 1.0.0
		 */
		function __construct( $field = array(), $value = '', $parent = null ) {
			$this->parent = $parent;
			$this->field  = $field;
			$this->value  = $value;

			// Shim out old arg to new
			if ( isset( $this->field['all_styles'] ) && ! empty( $this->field['all_styles'] ) ) {
				$this->field['all-styles'] = $this->field['all_styles'];
				unset ( $this->field['all_styles'] );
			}

			// Set field array defaults.  No errors please
			$defaults    = array(
				'font-family'     => true,
				'font-size'       => true,
				'font-weight'     => true,
				'font-style'      => true,
				'font-backup'     => false,
				'custom_fonts'    => true,
				'text-align'      => true,
				'text-transform'  => false,
				'font-variant'    => false,
				'text-decoration' => false,
				'color'           => true,
				'preview'         => true,
				'line-height'     => true,
				'multi' => array(
					'weight' => false,
				),
				'word-spacing'    => false,
				'letter-spacing'  => false,
				'google'          => true,
				'update_weekly'   => false,    // Enable to force updates of Google Fonts to be weekly
				'font_family_clear' => false,
				'margin-top'        => false,
				'margin-bottom'     => false,
				'allow-global'      => true,
			);
			$this->field = wp_parse_args( $this->field, $defaults );

			// Set value defaults.
			$defaults    = array(
				'font-family'     => '',
				'font-options'    => '',
				'font-backup'     => '',
				'text-align'      => '',
				'text-transform'  => '',
				'font-variant'    => '',
				'text-decoration' => '',
				'line-height'     => '',
				'word-spacing'    => '',
				'letter-spacing'  => '',
				'google'          => false,
				'font-script'     => '',
				'font-weight'     => '',
				'font-style'      => '',
				'color'           => '',
				'font-size'       => '',
				'margin-top'      => '',
				'margin-bottom'   => '',
			);
			$this->value = wp_parse_args( $this->value, $defaults );
			if ( ! $this->value['font-weight'] || 400 === $this->value['font-weight'] || '400' === $this->value['font-weight'] ) {
				$this->value['font-weight'] = '400';
			}

		}

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since FusionReduxFramework 1.0.0
		 */
		function render() {
			$allow_global = $this->field['allow-global'];
			?>
			<div class="awb-typography-container">
				<?php if ( $allow_global ) :?>
					<a class="option-global-typography awb-quick-set" href="JavaScript:void(0);" aria-label="<?php esc_html_e( 'Global Typography', 'Avada' ); ?>"><i class="fusiona-globe" aria-hidden="true"></i></a>
				<?php endif; ?>
				<div class="awb-typography" data-global="<?php echo ( $allow_global ? '1' : '0' ); ?>">
					<?php if ( ! $allow_global ) :?>
					<div class="input-wrapper">
						<div class="awb-typo-heading">
							<label><?php esc_html_e( 'Typography Set Name', 'Avada' ); ?></label>
						</div>
						<input type="text" name="<?php echo $this->field['name'] . $this->field['name_suffix']; ?>[label]" value="<?php echo $this->value['label']; ?>" placeholder="<?php esc_attr_e( 'Set Name', 'Avada' ); ?>" />
					</div>
				<?php endif; ?>

					<?php if ( true === $this->field['font-family'] ) : ?>
						<?php
						$default = '';
						if ( empty( $this->field['default']['font-family'] ) && ! empty( $this->field['font-family'] ) ) {
							$default = $this->value['font-family'];
						} else if ( ! empty( $this->field['default']['font-family'] ) ) {
							$default = $this->field['default']['font-family'];
						}
						?>
						<div class="input-wrapper family-selection awb-contains-global">
							<div class="awb-typo-heading">
								<label><?php esc_html_e( 'Font Family', 'Avada' ); ?></label>
								<?php if ( $allow_global ) :?>
									<span class="awb-global"><i class="fusiona-globe" aria-hidden="true"></i></span>
								<?php endif; ?>
							</div>
							<div class="fusion-skip-init fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>">
								<div class="fusion-select-preview-wrap">
									<span class="fusion-select-preview">
										<?php
										if ( ! empty( $this->value['font-family'] ) ) {
											if ( AWB_Adobe_Typography::is_adobe_font( $this->value['font-family'] ) ) {
												echo esc_html( AWB_Adobe_Typography::get_adobe_display_name( $this->value['font-family'] ) );
											} else {
												echo esc_html( $this->value['font-family'] );
											}
										} else if ( ! empty( $default ) ) {
											echo esc_html( $default );
										} else {
											echo '<span class="fusion-select-placeholder">' . esc_attr__( 'Select Font Family', 'Avada' ) . '</span>';
										}
										?>
									</span>
									<div class="fusiona-arrow-down"></div>
								</div>
								<div class="fusion-select-dropdown">
									<div class="fusion-select-search">
										<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="<?php esc_attr_e( 'Search Font Families', 'Avada' ); ?>" />
									</div>
									<div class="fusion-select-options"></div>
								</div>

								<input
									type="hidden"
									id="<?php echo esc_attr( $this->field['id'] ); ?>-font-family"
									name="<?php echo $this->field['name'] . $this->field['name_suffix']; ?>[font-family]"
									value="<?php echo esc_attr( $this->value['font-family'] ); ?>"
									data-default="<?php echo esc_attr( $default ); ?>"
									class="input-font_family fusion-select-option-value"
									data-subset="font-family"
								/>
							</div>
							<span class="awb-global-label"></span>
						</div>

						<div class="input-wrapper font-backup fusion-font-backup-wrapper">
							<div class="awb-typo-heading">
								<label><?php esc_html_e( 'Backup Font', 'Avada' ); ?></label>
							</div>

							<div class="fusion-skip-init fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>">
								<div class="fusion-select-preview-wrap">
									<span class="fusion-select-preview">
										<?php
										if ( ! empty( $this->value['font-backup'] ) ) {
											echo $this->value['font-backup'];
										} else {
											echo '<span class="fusion-select-placeholder">' . esc_attr__( 'Select Backup Font Family', 'Avada' ) . '</span>';
										}
										?>
									</span>
									<div class="fusiona-arrow-down"></div>
								</div>
								<div class="fusion-select-dropdown">
									<div class="fusion-select-search">
										<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="<?php esc_attr_e( 'Search Font Families', 'Avada' ); ?>" />
									</div>
									<div class="fusion-select-options"></div>
								</div>
								<input
									type="hidden"
									id="<?php echo esc_attr( $this->field['id'] ); ?>-backup"
									name="<?php echo $this->field['name'] . $this->field['name_suffix']; ?>[font-backup]"
									value="<?php echo esc_attr( $this->value['font-backup'] ); ?>"
									class="fusion-select-option-value"
								/>
							</div>
						</div>

						<?php
						$value = $this->value['font-weight'] . $this->value['font-style'];
						?>
						<div class="input-wrapper fusion-builder-typography">
							<div class="awb-typo-heading">
								<label><?php esc_html_e( 'Variant', 'Avada' ); ?></label>
							</div>
							<div class="input fusion-typography-select-wrapper">
								<select
									name="<?php echo $this->field['name'] . $this->field['name_suffix']; ?>[variant]"
									class="input-variant variant"
									id="<?php echo $this->field['id'];?>-variant"
									data-default="<?php echo esc_attr( $value ); ?>"
									data-subset="variant"></select>
								<div class="fusiona-arrow-down"></div>
								<input
									type="hidden"
									name="<?php echo $this->field['name'] . $this->field['name_suffix']; ?>[font-style]"
									id="<?php echo $this->field['id'];?>-font-style"
									value="<?php echo esc_attr( $this->value['font-style'] ); ?>"
									data-subset="font-style"
								/>
								<input
									type="hidden"
									name="<?php echo $this->field['name'] . $this->field['name_suffix']; ?>[font-weight]"
									id="<?php echo $this->field['id'];?>-font-weight"
									value="<?php echo esc_attr( $this->value['font-weight'] ); ?>"
									data-subset="font-weight"
								/>
							</div>
						</div>
					<?php endif; ?>

					<?php
					$string_map = [
						'font-size'      => esc_html__( 'Font Size', 'Avada' ),
						'line-height'    => esc_html__( 'Line Height', 'Avada' ),
						'letter-spacing' => esc_html__( 'Letter Spacing', 'Avada' ),
						'margin-top'     => esc_html__( 'Margin Top', 'Avada' ),
						'margin-bottom'  => esc_html__( 'Margin Bottom', 'Avada' ),
					];
					?>
					<?php foreach( [ 'font-size', 'line-height', 'letter-spacing', 'margin-top', 'margin-bottom' ] as $field ) : ?>
						<?php if ( true === $this->field[ $field ] ) : ?>
							<div class="input-wrapper<?php echo false === strpos( $field, 'margin' ) ? ' awb-contains-global' : '';?> third">
								<div class="awb-typo-heading">
									<label><?php echo $string_map[ $field ]; ?></label>
									<?php if ( $allow_global ) :?>
										<span class="awb-global"><i class="fusiona-globe" aria-hidden="true"></i></span>
									<?php endif; ?>
								</div>
								<div class="input">
									<input
										type="text"
										id="<?php echo $this->field['id'];?>-<?php echo $field; ?>"
										name="<?php echo $this->field['name'] . $this->field['name_suffix']; ?>[<?php echo $field; ?>]"
										value="<?php echo esc_attr( $this->value[ $field ] ); ?>"
										class="awb-typo-input"
										data-subset="<?php echo $field; ?>"
									/>
									<span class="awb-global-label"></span>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>

					<?php if ( true === $this->field['text-transform'] ) : ?>
						<div class="input-wrapper awb-contains-global">
							<div class="awb-typo-heading">
								<label><?php esc_attr_e( 'Text Transform', 'Avada' ); ?></label>
								<?php if ( $allow_global ) :?>
									<span class="awb-global"><i class="fusiona-globe" aria-hidden="true"></i></span>
								<?php endif; ?>
							</div>
							<div class="input radio-button-set ui-buttonset">
								<input
									type="hidden"
									id="<?php echo $this->field['id'];?>-transform"
									name="<?php echo $this->field['name'] . $this->field['name_suffix']; ?>[text-transform]"
									value="<?php echo esc_attr( $this->value['text-transform'] ); ?>"
									class="button-set-value"
									data-subset="text-transform"
								/>
								<?php
								$inherit_value = [];
								if ( ! isset( $this->field['text_transform_no_inherit'] ) || true !== $this->field['text_transform_no_inherit'] ) {
									$inherit_value = [
										''       => [
											'icon' => '<span class="fusiona-cog onlyIcon"></span>',
											'label' => esc_attr__( 'Default', 'Avada' ),
										],
									];
								}

								$values = [
									'none'       => [
										'icon' => '<span class="fusiona-minus onlyIcon"></span>',
										'label' => esc_attr__( 'None', 'Avada' ),
									],
									'uppercase'       => [
										'icon' => '<span class="fusiona-uppercase onlyIcon"></span>',
										'label' => esc_attr__( 'Uppercase', 'Avada' ),
									],
									'lowercase'       => [
										'icon' => '<span class="fusiona-lowercase onlyIcon"></span>',
										'label' => esc_attr__( 'Lowercase', 'Avada' ),
									],
									'capitalize'       => [
										'icon' => '<span class="fusiona-caps onlyIcon"></span>',
										'label' => esc_attr__( 'Capitalize', 'Avada' ),
									],
								];
								$values = array_merge( $inherit_value, $values );
								foreach ( $values as $value => $data ) {
									$selected = $value === $this->value['text-transform'] ? ' ui-state-active' : '';
									echo '<a href="#" class="ui-button buttonset-item' . $selected . ' has-tooltip" data-value="' . $value . '" aria-label="' . $data['label'] . '"><div class="fusion-button-set-title">' . $data['icon'] . '</div></a>';
								}
								?>
							</div>
							<span class="awb-global-label"></span>
						</div>
					<?php endif; ?>

					<?php if ( true === $this->field['color'] ) : ?>
						<?php
						$default = '';
						if ( empty( $this->field['default']['color'] ) && ! empty( $this->field['color'] ) ) {
							$default = $this->value['color'];
						} else if ( ! empty( $this->field['default']['color'] ) ) {
							$default = $this->field['default']['color'];
						}
						?>
						<div class="input-wrapper">
							<div class="awb-typo-heading">
								<label><?php esc_attr_e( 'Font Color', 'Avada' ); ?></label>
							</div>
							<input
								id="<?php echo esc_attr( $this->field['id'] ); ?>_color_picker"
								name="<?php echo $this->field['name'] . $this->field['name_suffix']; ?>[color]"
								class="fusion-builder-color-picker-hex color-picker"
								type="text"
								value="<?php echo esc_attr( $this->value['color'] ); ?>"
								data-alpha="true"
								data-default-color="<?php echo esc_attr( $default ); ?>"
							/>
						</div>
					<?php endif; ?>
				</div>

				<?php
				/* Font Preview */
				if ( ! isset( $this->field['preview'] ) || $this->field['preview'] !== false ) {
					if ( isset( $this->field['preview']['text'] ) ) {
						$g_text = $this->field['preview']['text'];
					} else {
						$g_text = '1 2 3 4 5 6 7 8 9 0 A B C D E F G H I J K L M N O P Q R S T U V W X Y Z a b c d e f g h i j k l m n o p q r s t u v w x y z';
					}

					$style = '';
					if ( isset( $this->field['preview']['always_display'] ) ) {
						if ( true === filter_var( $this->field['preview']['always_display'], FILTER_VALIDATE_BOOLEAN ) ) {
							if ( $isGoogleFont == true && isset( $fontFamily ) && is_array( $fontFamily ) && isset( $fontFamily[0] ) ) {
								$this->parent->typography_preview[ $fontFamily[0] ] = array(
									'font-style' => array( $this->value['font-weight'] . $this->value['font-style'] ),
								);

								$protocol = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) ? "https:" : "http:";

								wp_deregister_style( 'fusionredux-typography-preview' );
								wp_dequeue_style( 'fusionredux-typography-preview' );

								wp_register_style( 'fusionredux-typography-preview', $protocol . $this->makeGoogleWebfontLink( $this->parent->typography_preview ), '', time() );
								wp_enqueue_style( 'fusionredux-typography-preview' );
							}

							$style = 'display: block; font-family: ' . $this->value['font-family'] . '; font-weight: ' . $this->value['font-weight'] . ';';
						}
					}

					if ( isset( $this->field['preview']['font-size'] ) ) {
						$style .= 'font-size: ' . $this->field['preview']['font-size'] . ';';
						$inUse = '1';
					} else {
						//$g_size = '';
						$inUse = '0';
					}

					echo '<p data-preview-size="' . $inUse . '" class="clear ' . $this->field['id'] . '_previewer typography-preview" ' . 'style="' . $style . '">' . $g_text . '</p>';
				}
			echo '</div>'; // end typography container

		}  //function

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since FusionReduxFramework 1.0.0
		 */
		function enqueue() {

			if ( function_exists( 'AWB_Global_Typography' ) ) {
				AWB_Global_Typography()->enqueue();
			}
			wp_enqueue_script(
				'fusion-redux-field-typography-js',
				trailingslashit( FUSION_LIBRARY_URL ) . 'inc/redux/custom-fields/typography/field_typography.js',
				array( 'jquery', 'awb-color-picker', 'select3-js', 'fusionredux-js' ),
				time(),
				true
			);

			wp_localize_script(
				'fusion-redux-field-typography-js',
				'fusionredux_ajax_script',
				[
					'ajaxurl'          => admin_url( 'admin-ajax.php' ),
					'fusion_web_fonts' => Fusion_App()->get_googlefonts_ajax(),
				]
			);

		}  //function

		function output() {
		}

	}
}
