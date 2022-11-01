<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

$fusion_settings     = awb_get_fusion_settings();
$theme_options_style = strtolower( $fusion_settings->get( 'title_style_type' ) );
$text_transform      = $fusion_settings->get( 'button_text_transform' );
?>
<script type="text/template" id="fusion-builder-block-module-title-preview-template">

<# if ( 'undefined' !== typeof params.title_type && 'text' !== params.title_type ) { #>
	<h4 class="fusion_module_title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>
	<?php
	/* translators: The title type. */
	printf( esc_html__( 'Type = %s', 'fusion-builder' ), '{{ params.title_type }}' );
	?>
	<# if ( 'rotating' === params.title_type ) { #>
		<br />
		<?php
		/* translators: The effect type. */
		printf( esc_html__( 'Effect = %s', 'fusion-builder' ), '{{ params.rotation_effect }}' );
		?>
	<# } else if ( 'highlight' === params.title_type ) { #>
		<br />
		<?php
		/* translators: The effect type. */
		printf( esc_html__( 'Effect = %s', 'fusion-builder' ), '{{ params.highlight_effect }}' );
		?>
	<# } #>
<# } else { #>
	<div class="fusion-title-preview">
		<#
		var style_type = ( params.style_type ) ? params.style_type.replace( ' ', '_' ) : 'default',
			content = params.element_content,
			text_blocks       = jQuery.parseHTML( content ),
			shortcode_content = '',
			text_color        = params.text_color,
			text_transform    = '' !== params.text_transform ? params.text_transform : '<?php echo esc_attr( $text_transform ); ?>',
			styleTag          = '';

		if ( 'none' !== text_transform ) {
			styleTag += 'text-transform: ' + text_transform + ';';
		}

		if ( 'default' === params.style_type ) {
			style_type = '<?php echo esc_attr( $theme_options_style ); ?>';
			style_type = style_type.replace( ' ', '_' );
		}

		if ( text_color && ( -1 !== text_color.indexOf( 'var(--' ) ) ) {
			text_color = getComputedStyle( document.documentElement ).getPropertyValue( text_color.replace( 'var(', '' ).replace( ')', '' ) );
		}

		if ( text_color && ( -1 !== text_color.replace( /\s/g, '' ).indexOf( 'rgba(255,255,255' ) || '#ffffff' === text_color ) ) {
			text_color = '#dddddd';
		}

		jQuery(text_blocks).each(function() {
			shortcode_content += jQuery(this).text();
		});

		var align = 'align-' + params.content_align;
		if ( params.sep_color && '' !== params.sep_color ) {
			styleTag += 'border-color: ' + params.sep_color + ';';
		}

		if ( text_color ) {
			styleTag += 'color: ' + text_color + ';';
		}
		#>

		<span class="{{ style_type }}" style="{{{ styleTag }}}"><sub class="title_text {{ align }}">{{ shortcode_content }}</sub></span>
	</div>
<# } #>
</script>
