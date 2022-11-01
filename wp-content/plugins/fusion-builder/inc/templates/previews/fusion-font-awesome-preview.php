<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

$fusion_settings   = awb_get_fusion_settings();
$icon_circle       = $fusion_settings->get( 'icon_circle' );
$icon_color        = $fusion_settings->get( 'icon_color' );
$icon_circle_color = $fusion_settings->get( 'icon_circle_color' );
?>
<script type="text/template" id="fusion-builder-block-module-font-awesome-preview-template">

	<#
	var
	icon_color = '',
	icon_color = params.iconcolor,
	icon = params.icon,
	circle_background = '',
	circle = '' !== params.circle ? params.circle : '<?php echo esc_html( $icon_circle ); ?>';

	if ( '' === params.iconcolor ||  ! params.iconcolor ) {
		icon_color = '<?php echo esc_attr( $icon_color ); ?>';
	} else {
		icon_color = params.iconcolor;
	}

	if ( '' === params.circlecolor || ! params.circlecolor ) {
		circle_background = '<?php echo esc_attr( $icon_circle_color ); ?>';
	} else {
		circle_background = params.circlecolor;
	}

	if ( ( 'no' === circle || 'yes' === circle && ( '#ffffff' === circle_background || -1 !== circle_background.indexOf( 'rgba(255,255,255' ) ) ) && ( '#ffffff' === icon_color || -1 !== icon_color.indexOf( 'rgba(255,255,255' ) ) ) {
		icon_color = '#dddddd';
	}

	if ( 'undefined' !== typeof icon ) {
		if ( 'fusion-prefix-' === icon.substr( 0, 14 ) ) {
			icon = icon.replace( 'fusion-prefix-', '' );
		} else if ( -1 === icon.trim().indexOf( ' ' ) && -1 !== icon.indexOf( 'fa-' ) ) {
			icon = 'fa ' + icon;
		}
	}
	#>

	<# if ( circle === 'yes' ) { #>
		<div class="fusion-icon-circle-preview" style="background: {{ circle_background }}">
	<# } #>
		<span class="fa-preview {{ icon }}" style="color: {{ icon_color }}"></span>
	<# if ( circle === 'yes' ) { #>
		</div>
	<# } #>

</script>
