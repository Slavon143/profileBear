<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-section-separator-preview-template">
	<#
	var sep_type       = 'undefined' !== typeof params.divider_type ? params.divider_type.replace( '_', ' ' ) : '',
		vertical_pos   = 'undefined' !== typeof params.divider_candy ? params.divider_candy : '',
		horizontal_pos = 'undefined' !== typeof params.divider_position ? params.divider_position : '';
	#>
	<h4 class="fusion_module_title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>
	<# if ( type ) { #>
		<?php /* translators: The type of the section separator. */ ?>
		<?php printf( esc_html__( 'Type = %s', 'fusion-builder' ), '{{ sep_type }}' ); ?>
		<# if ( 'clouds' !== params.divider_type ) { #>
			<br />
			<?php /* translators: The vertical position of the section separator. */ ?>
			<?php printf( esc_html__( 'Vertical Position = %s', 'fusion-builder' ), '{{ vertical_pos }}' ); ?>
		<# } #>

		<# if ( -1 !== ['slant', 'big_triangle', 'curved', 'waves', 'waves_opacity'].indexOf( params.divider_type ) ) { #>
			<br />
			<?php /* translators: The horizontal position of the section separator. */ ?>
			<?php printf( esc_html__( 'Horizontal Position = %s', 'fusion-builder' ), '{{ horizontal_pos }}' ); ?>
		<# } #>
	<# } #>
</script>
