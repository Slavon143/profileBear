<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-blog-preview-template">
	<h4 class="fusion_module_title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>

	<?php /* translators: The Layout. */ ?>
	<?php printf( esc_html__( 'Layout = %s', 'fusion-builder' ), '{{ params.layout }}' ); ?>
	<# if ( ( 'grid' === params.layout || 'masonry' === params.layout ) && '' !== params.blog_grid_columns ) { #>
		<br />
		<?php /* translators: The Columns. */ ?>
		<?php printf( esc_html__( 'Columns = %s', 'fusion-builder' ), '{{ params.blog_grid_columns }}' ); ?>
	<# } #>

</script>
