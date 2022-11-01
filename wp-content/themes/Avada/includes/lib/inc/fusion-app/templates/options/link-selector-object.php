<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<div class="fusion-link-selector-object">
	<span class="fusion-builder-menu-item-type">{{ option_value.object }}</span>
	<input type="text" <# if ( 'custom' !== option_value.object ) { #> readonly <# } #> id="url" name="url" type="text" class="regular-text fusion-builder-link-field" value="{{ option_value.url }}" />
	<input type="hidden" id="object" name="object" type="text" class="fusion-builder-object-field" value="{{ option_value.object }}" />
	<input type="hidden" id="object_id" name="object_id" type="text" class="fusion-builder-object-id-field" value="{{ option_value.object_id }}" />
	<input type='button' class='button button-link-selector fusion-builder-link-button' value='{{ fusionBuilderText.select_link }}'/>
	<input type='button' class='button button-link-type-toggle' value='Use Custom' <# if ( 'custom' === option_value.object ) { #> style="display:none" <# } #> />
</div>
