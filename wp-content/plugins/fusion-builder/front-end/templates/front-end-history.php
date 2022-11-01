<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-front-end-history">
	<#
	var classes,
		items = [];

	_.each( steps, function( name, index ) {
		if ( currentStep === index ) {
			classes = ' fusion-history-active-state';
		} else {
			classes = '';
		}
		if ( 0 !== index ) {
			items.push( '<li class="fusion-empty-history' + classes + '" data-state-id="' + index + '"><span class="fusiona-arrow-right"></span>' + name + '</li>' );
		}
	});

	items.reverse();

	_.each( items, function( item ) { #>
		{{{ item }}}
	<# }); #>
</script>
