<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-layout-child-option">
	<div class="layout-child-option">
		<input id="{{ id }}-include" data-parent="{{ parent }}"  data-type="{{ type }}" data-label="{{ label }}" type="checkbox" name="{{ id }}" <# print( 'include' === checked ? 'checked' : '' ) #> value="include">
		<label for="{{ id }}-include" class="option-include">
			<i class="fusiona-checkmark" aria-hidden="true"></i>
		</label>
		<input id="{{ id }}-exclude" data-parent="{{ parent }}"  data-type="{{ type }}" data-label="{{ label }}" type="checkbox" name="{{ id }}" <# print( 'exclude' === checked ? 'checked' : '' ) #> value="exclude">
		<label for="{{ id }}-exclude" class="option-exclude">
			<i class="fusiona-cross" aria-hidden="true"></i>
		</label>
		<span id="{{ id }}" class="layout-option-label" <# print( 'undefined' !== typeof slug ? 'title="Slug: ' + slug + '"' : '' ) #>>{{{ label }}}</span>
	</div>
</script>
