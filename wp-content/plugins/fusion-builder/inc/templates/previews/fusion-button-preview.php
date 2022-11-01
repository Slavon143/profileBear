<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

$fusion_settings = awb_get_fusion_settings();

$type         = strtolower( $fusion_settings->get( 'button_type' ) );
$gradient_top = $gradient_bottom = $accent_color = $border_color = $border_width = $border_radius = '';

$gradient_top    = Fusion_Color::new_color( $fusion_settings->get( 'button_gradient_top_color' ) )->is_needs_adjustment() ? '#f8f8f8' : $fusion_settings->get( 'button_gradient_top_color' );
$gradient_bottom = Fusion_Color::new_color( $fusion_settings->get( 'button_gradient_bottom_color' ) )->is_needs_adjustment() ? '#f8f8f8' : $fusion_settings->get( 'button_gradient_bottom_color' );
$accent_color    = Fusion_Color::new_color( $fusion_settings->get( 'button_accent_color' ) )->is_needs_adjustment() ? '#f8f8f8' : $fusion_settings->get( 'button_accent_color' );
$border_color    = Fusion_Color::new_color( $fusion_settings->get( 'button_border_color' ) )->is_needs_adjustment() ? '#f8f8f8' : $fusion_settings->get( 'button_border_color' );
$border_width    = $fusion_settings->get( 'button_border_width', 'top' ) . ' ' . $fusion_settings->get( 'button_border_width', 'right' ) . ' ' . $fusion_settings->get( 'button_border_width', 'bottom' ) . ' ' . $fusion_settings->get( 'button_border_width', 'left' );
$text_transform  = $fusion_settings->get( 'button_text_transform' );
?>

<script type="text/template" id="fusion-builder-block-module-button-preview-template">

	<#
	var button_style  = '';
	var button_icon   = '';
	var border_radius, border_radius_top_left, border_radius_top_right, border_radius_bottom_right, border_radius_bottom_left;


	if ( 'undefined' !== typeof params.border_radius_top_left && '' !== params.border_radius_top_left ) {
		border_radius_top_left = params.border_radius_top_left;
	} else {
		border_radius_top_left = '<?php echo esc_attr( $fusion_settings->get( 'button_border_radius', 'top_left' ) ); ?>';
	}
	if ( 'undefined' !== typeof params.border_radius_top_right && '' !== params.border_radius_top_right ) {
		border_radius_top_right = params.border_radius_top_right;
	} else {
		border_radius_top_right = '<?php echo esc_attr( $fusion_settings->get( 'button_border_radius', 'top_right' ) ); ?>';
	}
	if ( 'undefined' !== typeof params.border_radius_bottom_right && '' !== params.border_radius_bottom_right ) {
		border_radius_bottom_right = params.border_radius_bottom_right;
	} else {
		border_radius_bottom_right = '<?php echo esc_attr( $fusion_settings->get( 'button_border_radius', 'bottom_right' ) ); ?>';
	}
	if ( 'undefined' !== typeof params.border_radius_bottom_left && '' !== params.border_radius_bottom_left ) {
		border_radius_bottom_left = params.border_radius_bottom_left;
	} else {
		border_radius_bottom_left = '<?php echo esc_attr( $fusion_settings->get( 'button_border_radius', 'bottom_left' ) ); ?>';
	}
	border_radius = border_radius_top_left + ' ' + border_radius_top_right + ' ' + border_radius_bottom_right + ' ' + border_radius_bottom_left;

	if ( '' === params.type ) {
		var button_type = '<?php echo esc_attr( $type ); ?>';
	} else {
		var button_type = params.type;
	}

	if ( '' === params.size || ! params.size ) {
		var button_size = 'large';
	} else {
		var button_size = params.size;
	}

	if ( 'default' === params.color ) {
		var accent_color      = '<?php echo esc_attr( $accent_color ); ?>';
		var border_color      = '<?php echo esc_attr( $border_color ); ?>';
		var border_width      = '<?php echo esc_attr( $border_width ); ?>';
		var button_background = 'linear-gradient(<?php echo esc_attr( $gradient_top ); ?>, <?php echo esc_attr( $gradient_bottom ); ?>)';

	} else if ( 'custom' === params.color ) {
		var accent_color = ( params.accent_color ) ? params.accent_color : '<?php echo esc_attr( $accent_color ); ?>';
		var accent_color = ( params.border_color ) ? params.border_color : '<?php echo esc_attr( $border_color ); ?>';
		var border_color = accent_color;
		var border_width, border_top, border_right, border_bottom, border_left;

		if ( 'undefined' !== typeof params.border_top && '' !== params.border_top ) {
			border_top = params.border_top;
		} else {
			border_top = '<?php echo esc_attr( $fusion_settings->get( 'button_border_width', 'top' ) ); ?>';
		}
		if ( 'undefined' !== typeof params.border_right && '' !== params.border_right ) {
			border_right = params.border_right;
		} else {
			border_right = '<?php echo esc_attr( $fusion_settings->get( 'button_border_width', 'right' ) ); ?>';
		}
		if ( 'undefined' !== typeof params.border_bottom && '' !== params.border_bottom ) {
			border_bottom = params.border_bottom;
		} else {
			border_bottom = '<?php echo esc_attr( $fusion_settings->get( 'button_border_width', 'bottom' ) ); ?>';
		}
		if ( 'undefined' !== typeof params.border_left && '' !== params.border_left ) {
			border_left = params.border_left;
		} else {
			border_left = '<?php echo esc_attr( $fusion_settings->get( 'button_border_width', 'left' ) ); ?>';
		}
		border_width = border_top + ' ' + border_right + ' ' + border_bottom + ' ' + border_left;

		var gradient_top = ( params.button_gradient_top_color ) ? params.button_gradient_top_color : '<?php echo esc_attr( $gradient_top ); ?>';
		var gradient_bottom = ( params.button_gradient_bottom_color ) ? params.button_gradient_bottom_color : '<?php echo esc_attr( $gradient_bottom ); ?>';

		if ( '' !== gradient_top && '' !== gradient_bottom ) {
			var button_background = 'linear-gradient(' + gradient_top + ', ' + gradient_bottom + ')';
		} else {
			var button_background = gradient_top;
		}

		if ( ( '' === button_background || ( -1 !== gradient_top.indexOf( 'rgba(255,255,255' ) && -1 !== gradient_bottom.indexOf( 'rgba(255,255,255' ) ) ) && ( '#ffffff' === accent_color || -1 !== accent_color.indexOf( 'rgba(255,255,255' ) ) ) {
			button_background = '#dddddd';
		}

	} else {
		var button_color = params.color;
	}

	if ( 'undefined' !== typeof params.icon && '' !== params.icon ) {
		var button_icon = params.icon;
	} else {
		var button_icon = 'no-icon';
	}

	if ( 'undefined' !== typeof button_icon && -1 === button_icon.trim().indexOf( ' ' ) ) {
		button_icon = 'fa ' + button_icon;
	}

	if ( '' === params.text_transform ) {
		var text_transform = '<?php echo esc_attr( $text_transform ); ?>';
	} else {
		var text_transform = params.text_transform;
	}
	#>

	<#
	if ( 'left' === params.icon_position ) {
		var buttonContent = '<span class="fusion-module-icon ' + button_icon + '"></span>' + params.element_content;
	} else {
		var buttonContent = params.element_content + '<span class="fusion-module-icon ' + button_icon + '" style="margin-left:0.5em;margin-right:0;"></span>';
	}
	#>

	<# if ( 'custom' === params.color || 'default' === params.color ) { #>

		<a class="fusion-button button-default button-{{ button_type }} button-{{ button_size }}" style="background: {{ button_background }}; border-radius: {{ border_radius }}; border-style: solid; border-width: {{ border_width }}; border-color: {{ border_color }}; color: {{ accent_color }}; text-transform: {{ text_transform }};"><span class="fusion-button-text">{{{ buttonContent }}}</span></a>

	<# } else { #>

		<a class="fusion-button button-default button-{{ button_type }} button-{{ button_size }} button-{{ button_color }}" style="border-radius: {{ border_radius }}; text-transform: {{ text_transform }};"><span class="fusion-button-text">{{{ buttonContent }}}</span></a>

	<# }#>
</script>
