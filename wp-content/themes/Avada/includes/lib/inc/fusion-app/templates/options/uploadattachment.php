<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<div class="fusion-upload-image">
	<input type="hidden" id="{{ param.param_name }}" name="{{ param.param_name }}" class="regular-text fusion-builder-upload-field" value="{{ option_value }}" />
	<input type='button' class='button button-upload fusion-builder-upload-button fusion-builder-attachment-upload hide-edit-buttons' value='{{ fusionBuilderText.attach_images }}' data-type="image" data-title="{{ fusionBuilderText.select_image }}"/>
</div>
