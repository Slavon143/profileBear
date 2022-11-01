<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-chart-preview-template">
	<h4 class="fusion_module_title"><span class="fusion-module-icon {{ fusionAllElements[ element_type ].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>

	<# if ( '' !== params.title ) { #>
		<?php

			/* translators: %s: Title of chart. */
			printf( esc_html__( 'Chart Name: %s', 'fusion-builder' ), '{{ params.title }}' );
		?>
		<br />
	<# } #>

	<# if ( '' !== params.chart_type ) { #>
		<?php

			/* translators: %s: Type of chart. */
			printf( esc_html__( 'Type: %s', 'fusion-builder' ), '{{ params.chart_type }}' );
		?>
	<# } #>
</script>
