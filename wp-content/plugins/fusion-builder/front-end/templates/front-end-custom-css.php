<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-front-end-css">
	<textarea id="fusion-page-css" name="fusion-page-css"><# if ( 'undefined' !== typeof FusionPageBuilderApp && 'undefined' !== typeof FusionApp.data.postMeta && 'undefined' !== typeof FusionApp.data.postMeta._fusion_builder_custom_css ) { #>{{{ FusionApp.data.postMeta._fusion_builder_custom_css }}}<# } #></textarea>
</script>
