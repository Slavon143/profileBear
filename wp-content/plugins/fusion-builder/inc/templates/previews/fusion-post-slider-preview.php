<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-post-slider-preview-template">
	<h4 class="fusion_module_title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>

	<# var category = ( params.category === '' ) ? 'All' : params.category; #>
	<?php /* translators: The layout. */ ?>
	<?php printf( esc_html__( 'layout = %s', 'fusion-builder' ), '{{ params.layout }}' ); ?>
	<br />
	<# if ( params.layout !== 'attachments' ) { #>
		<?php /* translators: The category. */ ?>
		<?php printf( esc_html__( 'category = %s', 'fusion-builder' ), '{{ category }}' ); ?>
	<# } #>

</script>
