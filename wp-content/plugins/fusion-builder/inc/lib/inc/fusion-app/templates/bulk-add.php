<?php
/**
 * Underscore.js template.
 *
 * @since 2.1
 * @package fusion-builder
 */

?>

<script type="text/template" id="fusion-builder-bulk-add-template">
	<div>
		<div class="fusion-builder-bulk-add-label">
			{{ fusionBuilderText.bulk_add_instructions }}
		</div>
		<div class="fusion-builder-bulk-sections" style="display: flex;">
			<div class="fusion-builder-bulk-section-left">
				<div class="bulk-section-title">{{ fusionBuilderText.bulk_add }}</div>
				<textarea style="width:100%;" placeholder="<?php esc_html_e( 'Examples:', 'fusion-builder' ); ?>&#10;Green|1 &#10;Apple" name="textarea" rows="10" cols="50"></textarea>
			</div>
			<div class="fusion-builder-bulk-section-right">
				<div class="bulk-section-title">{{ fusionBuilderText.bulk_add_predefined }}</div>
				<div class="predefined-choices">
				<# _.each( choices, function( choice, index ) { #>
					<div class="predefined-choice" data-value="{{index}}" >{{{ choice.name }}}</div>
				<# } ); #>
				</div>
			</div>
		<div>
	</div>
</script>
