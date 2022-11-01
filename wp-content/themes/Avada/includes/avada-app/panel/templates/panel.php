<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-panel-template">
<ul>
	<a href="#" class="fusion-panel-link" data-label="{{ label }}" id="{{ id }}" <# if ( 'undefined' !== typeof icon ) {#> data-icon={{ icon }} <# } #> aria-expanded="false">
		<# if ( 'undefined' !== typeof icon ) { #>
			<i class="{{ icon }}" aria-hidden="true"></i>
		<# } #>
		{{{ label }}}
	</a>
	<# if ( 'object' === typeof fields ) {
		_.each( fields, function( subSection, subSectionId ) {
			if ( ( 'sub-section' !== subSection.type && 'accordion' !== subSection.type ) || subSection.hidden ) {
				return;
			}#>
		<li style="display:none">
			<a href="#" class="fusion-sub-section-link" data-label="{{ subSection.label }}" id="{{{ subSectionId }}}">{{{ subSection.label }}}</a>
		</li>
	<#
		});
	}#>
</ul>
</script>
