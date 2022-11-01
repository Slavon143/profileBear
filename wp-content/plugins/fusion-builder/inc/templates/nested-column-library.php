<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-nested-column-library-template">
	<div class="fusion-builder-modal-top-container fusion-has-close-on-top">
		<h2 class="fusion-builder-settings-heading">
				{{ fusionBuilderText.insert_columns }}
		</h2>
		<div class="fusion-builder-modal-close fusiona-plus2"></div>
	</div>
	<div class="fusion-builder-main-settings fusion-builder-main-settings-full fusion-builder-main-settings-advanced">
		<div class="fusion-builder-all-elements-container">
			<?php echo fusion_builder_inner_column_layouts(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
</script>
