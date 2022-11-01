<?php
/**
 * The main Fusion_Panel class.
 *
 * @since 6.0
 * @package Avada
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Fusion_Panel' ) ) {
	include_once wp_normalize_path( dirname( __FILE__ ) . '/class-fusion-panel.php' );
}

add_action( 'fusion_load_internal_module', 'load_fusion_panel', 3 );

/**
 * Instantiate Fusion_Panel class.
 *
 * @since 6.0
 */
function load_fusion_panel() {
	Fusion_Panel::get_instance();
}

/**
 * Loop for TO options.
 *
 * @since 6.0
 * @param array $params An array of arguments.
 */
function fusion_customizer_front_options_loop( $params ) {
	$preferences        = Fusion_App()->preferences->get_preferences();
	$descriptions_class = '';
	$descriptions_css   = '';

	if ( isset( $preferences['descriptions'] ) && 'show' === $preferences['descriptions'] ) {
		$descriptions_class = ' active';
		$descriptions_css   = ' style="display: block;"';
	}
	?>
	<ul class="fusion-builder-customizer-settings">

		<#
		var responsiveIcons = {
			'large': 'desktop',
			'medium': 'tablet',
			'small': 'mobile'
		};
		_.each( <?php echo $params; // phpcs:ignore WordPress.Security.EscapeOutput ?>, function( param, id ) {

			if ( param.hidden || param.hide_on_front ) {
				return;
			}

			// Port TO format to FB.
			if ( 'select' === param.type && 'undefined' !== typeof param.multi && true === param.multi ) {
				param.type  = 'multiple_select';
			}

			option_type               = 'undefined' !== typeof param.location ? param.location.toUpperCase() : type.toUpperCase();
			option_map                = 'undefined' !== typeof param.map && 'function' !== typeof param.map ? param.map : '';
			option_class              = 'undefined' !== typeof param.class ? param.class : '';
			option_value              = ( 'TO' === option_type || 'FBE' === option_type ) ? FusionApp.settings[ id ] : FusionApp.data.postMeta[ id ];
			hasResponsive             = 'undefined' !== typeof param.responsive ? true : false;
			responsiveState           = 'undefined' !== typeof param.responsive ? 'responsive-state-' + param.responsive.state : '';
			supportsGlobalTypography  = 'typography' === param.type && 'undefined' !== typeof param.global;

			style = '';
			if ( -1 !== id.indexOf( 'important_note_info' ) && 'function' === typeof param.map ) {
				style = ' style="display:none;"';
			}

			if ( 'undefined' === typeof param.not_pyre && ( ( 'PO' === option_type && FusionApp.data.singular ) || ( ( 'TAXO' === option_type || 'PO' === option_type ) && FusionApp.data.is_archive ) ) ) {
				option_value = FusionApp.data.postMeta._fusion[ id ];
			}

			if ( 'undefined' !== typeof param.id && -1 !== param.id.indexOf( '[' ) && ( 'PO' === option_type || 'TAXO' === option_type ) ) {
				if ( 'undefined' !== typeof FusionApp.data.postMeta._fusion && 'undefined' !== typeof FusionApp.data.postMeta._fusion[ param.id.split( '[' )[0] ] && 'undefined' !== typeof FusionApp.data.postMeta._fusion[ param.id.split( '[' )[0] ][ param.id.split( '[' )[1].replace( ']', '' ) ] ) {
					option_value = FusionApp.data.postMeta._fusion[ param.id.split( '[' )[0] ][ param.id.split( '[' )[1].replace( ']', '' ) ];
				} else if ( 'undefined' !== typeof FusionApp.data.postMeta[ param.id.split( '[' )[0] ] && 'undefined' !== typeof FusionApp.data.postMeta[ param.id.split( '[' )[0] ][ param.id.split( '[' )[1].replace( ']', '' ) ] ) {
					option_value = FusionApp.data.postMeta[ param.id.split( '[' )[0] ][ param.id.split( '[' )[1].replace( ']', '' ) ];
				}
			}

			if ( 'PO' === option_type && true === param.not_pyre && FusionApp.data.singular && 'undefined' !== typeof param.value ) {
				option_value = param.value;
			}

			if ( 'string' === typeof param.location && 'PS' === param.location ) {
				option_value = FusionApp.getPost( id );
			}

			if ( 'undefined' === typeof option_value && 'slider' !== param.type && 'color' !== param.type && 'color-alpha' !== param.type && 'media_url' !== param.type ) {
				option_value = param.default;
			}
			if ( 'undefined' === typeof option_value ) {
				option_value = '';
			}
			option_id = 'object' !== typeof param.id ? param.id : 'po-dimension';

			// Make sure that post titles with HTML chars are correctly displayed.
			if ( 'post_title' === option_id ) {
				var txt = document.createElement( 'textarea' );
				txt.innerHTML = option_value;
				option_value = txt.value;
			}

			subset = ( 'undefined' !== typeof param.to_default && 'undefined' !== typeof param.to_default.subset ) ? param.to_default.subset : '';
			#>

			<li data-option-id="{{ option_id }}" class="fusion-builder-option {{ param.type }} {{option_map}} {{option_class}} {{responsiveState}}" data-type="{{ option_type }}" data-subset="{{ subset }}"{{{style}}}>

				<div class="option-details">
					<div class="option-details-inner">
						<# if ( 'undefined' !== typeof param.label && 'custom' !== param.type ) { #>
							<h3>{{{ param.label }}}</h3>
							<ul class="fusion-panel-options">
								<# if ( 'undefined' !== typeof param.description && 1 < param.description.length && 1 < param.label.length  ) { #>
									<li> <a href="JavaScript:void(0);" class="fusion-panel-description<?php echo esc_attr( $descriptions_class ); ?>"><i class="fusiona-question-circle" aria-hidden="true"></i></a> <span class="fusion-elements-option-tooltip fusion-tooltip-description">{{ fusionBuilderText.fusion_panel_desciption_toggle }}</span></li>
								<# }; #>
								<# if ( 'undefined' !== typeof param.to_default && '' !== param.to_default && param.to_default ) { #>
									<li><a href="JavaScript:void(0);"><span class="fusion-panel-shortcut" data-fusion-option="{{ param.to_default.id }}"><i class="fusiona-cog" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-global-settings"><?php esc_html_e( 'Global Options', 'fusion-builder' ); ?></span></li>
								<# } #>
								<# if( 'undefined' !== typeof param.description && 'undefined' !== typeof param.default && 'PO' === option_type && 'undefined' !== param.to_default && '' !== param.to_default && ( 'color-alpha' === param.type || 'slider' === param.type )  ) { #>
									<li class="fusion-builder-default-reset"> <a href="JavaScript:void(0);" class="fusion-range-default" data-default="{{ param.default }}"><i class="fusiona-undo" aria-hidden="true"></i></a> <span class="fusion-elements-option-tooltip fusion-tooltip-reset-defaults"><?php esc_html_e( 'Reset To Default', 'fusion-builder' ); ?></span></li>
								<# } #>
								<# if ( 'undefined' !== typeof param.preview ) { #>
									<#
										dataType     = 'undefined' !== typeof param.preview.type     ? param.preview.type       : '';
										dataSelector = 'undefined' !== typeof param.preview.selector ? param.preview.selector   : '';
										dataToggle   = 'undefined' !== typeof param.preview.toggle   ? param.preview.toggle     : '';
										dataAppend   = 'undefined' !== typeof param.preview.append   ? param.preview.append     : '';
									#>
								<li><a class="option-preview-toggle" href="JavaScript:void(0);" aria-label="<?php esc_attr_e( 'Preview', 'fusion-builder' ); ?>" data-type="{{ dataType }}" data-selector="{{ dataSelector }}" data-toggle="{{ dataToggle }}" data-append="{{ dataAppend }}"><i class="fusiona-eye" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-preview"><?php esc_html_e( 'Preview', 'fusion-builder' ); ?></span></li>
								<# }; #>
								<# if ( hasResponsive ) { #>
									<li class="fusion-responsive-panel"><a class="option-has-responsive" href="JavaScript:void(0);" aria-label="{{ fusionBuilderText.fusion_panel_responsive_toggle }}"><i class="fusiona-{{responsiveIcons[param.responsive.state]}}" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-preview">{{ fusionBuilderText.fusion_panel_responsive_toggle }}</span>
										<ul class="fusion-responsive-options">
											<li><a href="JavaScript:void(0);" data-indicator="desktop"><i class="fusiona-desktop" aria-hidden="true"></i></a></li>
											<li><a href="JavaScript:void(0);" data-indicator="tablet"><i class="fusiona-tablet" aria-hidden="true"></i></a></li>
											<li><a href="JavaScript:void(0);" data-indicator="mobile"><i class="fusiona-mobile" aria-hidden="true"></i></a></li>
										</ul>
									</li>
								<# } #>
								<# if ( supportsGlobalTypography ) { #>
								<li><a class="option-global-typography awb-quick-set" href="JavaScript:void(0);" aria-label="<?php esc_attr_e( 'Global Typography', 'fusion-builder' ); ?>"><i class="fusiona-globe" aria-hidden="true"></i></a><span class="fusion-elements-option-tooltip fusion-tooltip-preview"><?php esc_html_e( 'Global Typography', 'fusion-builder' ); ?></span></li>
							<# } #>
							</ul>

						<# }; #>
					</div>

					<# if ( 'undefined' !== typeof param.description ) { #>
						<# if ( 'custom' !== param.type ) { #>
							<p class="description"<?php echo $descriptions_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>{{{ param.description }}}</p>
						<# } else { #>
							<div class="important-description"<?php echo $descriptions_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								{{{ param.description }}}
							</div>
						<# } #>
					<# }; #>
				</div>

				<div class="option-field fusion-builder-option-container">
					<?php
					$fields = [
						'textarea',
						'textfield',
						'range',
						'colorpickeralpha',
						'colorpicker',
						'select',
						'ajax_select',
						'button',
						'radio_button_set',
						'checkbox_button_set',
						'switch',
						'upload_object',
						'upload',
						'upload_id',
						'code',
						'typography',
						'radio_image_set',
						'import',
						'export',
						'uploadfile',
						'uploadattachment',
						'tinymce',
						'iconpicker',
						'multiple_select',
						'multiple_upload',
						'checkbox_button_set',
						'link_selector',
						'date_time_picker',
						'upload_images',
						'repeater',
						'sortable',
						'color-palette',
						'typography-sets',
						'info',
						'hubspot_map',
						'mailchimp_map',
						'layout_conditions',
						'toggle',
					];

					// Redux on left, template on right.
					$field_replacement = [
						'text'               => 'textfield',
						'dimension'          => 'textfield',
						'slider'             => 'range',
						'color-alpha'        => 'colorpickeralpha',
						'color'              => 'colorpicker',
						'radio-buttonset'    => 'radio_button_set',
						'checkbox-buttonset' => 'checkbox_button_set',
						'spacing'            => 'dimension',
						'dimensions'         => 'dimension',
						'border_radius'      => 'dimension',
						'media'              => 'upload_object',
						'media_url'          => 'upload',
						'media_id'           => 'upload_id',
						'radio-image'        => 'radio_image_set',
						'preset'             => 'radio_image_set',
						'radio'              => 'select',
					];

					foreach ( $field_replacement as $redux => $option ) {
						$fields[] = [
							$redux,
							FUSION_LIBRARY_PATH . '/inc/fusion-app/templates/options/' . str_replace( '_', '-', $option ) . '.php',
						];
					}

					$fields = apply_filters( 'avada_app_panel_fields', $fields );
					?>
					<?php foreach ( $fields as $field_type ) : ?>
						<?php if ( is_array( $field_type ) && ! empty( $field_type ) ) : ?>
							<# if ( '<?php echo esc_attr( $field_type[0] ); ?>' === param.type ) { #>
							<?php include wp_normalize_path( $field_type[1] ); ?>
						<# }; #>
						<?php else : ?>
							<# if ( '<?php echo esc_attr( $field_type ); ?>' === param.type ) { #>
							<?php include FUSION_LIBRARY_PATH . '/inc/fusion-app/templates/options/' . str_replace( '_', '-', $field_type ) . '.php'; ?>
							<# } #>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</li>

		<# } ); #>

	</ul>
	<?php
}
