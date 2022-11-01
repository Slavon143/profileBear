<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-menu-anchor-preview-template">
	<h4 class="fusion_module_title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>
	<?php /* translators: The anchor name. */ ?>
	<?php printf( esc_html__( 'menu anchor name = %s', 'fusion-builder' ), '{{ params.name }}' ); ?>
</script>
