<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-widget-settings-template">
	<li class="fusion-widget-settings-form <# if ( coreWidget ) { #> open <# } #>">
		<div class="widget-inside">
			<form class="form">
				<input type="hidden" class="id_base" value="{{{ attributes.base }}}"/>
				<input type="hidden" class="widget-id" value="{{{ attributes.id }}}"/>
				<div class="widget-content">
					<# if ( 'undefined' !== typeof widgetData[ attributes.type ] ) { #>
						{{{ widgetData[ attributes.type ].form }}}
					<# } else { #>
						<?php esc_html_e( 'No widget data found', 'fusion-builder' ); ?>
					<# } #>
				</div>
			</form>
		</div>
	</li>
</script>
