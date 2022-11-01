<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.1
 */

?>
<script type="text/template" id="fusion-builder-dynamic-selection">
<div class="fusion-skip-init fusion-open fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>">
	<div class="fusion-select-preview-wrap">
		<span class="fusion-select-preview">
			<span class="fusion-select-placeholder"><?php esc_attr_e( 'Select Dynamic Content', 'fusion-builder' ); ?></span>
		</span>
		<div class="fusiona-arrow-down"></div>
	</div>
	<div class="fusion-select-dropdown">
		<div class="fusion-select-search">
			<input type="text" class="fusion-hide-from-atts fusion-dont-update" placeholder="<?php esc_attr_e( 'Search Dynamic Content', 'fusion-builder' ); ?>" />
		</div>
		<div class="fusion-select-options">
			<# _.each( params, function( values, groupId ) { #>
				<div class="fusion-select-optiongroup" data-group="{{ groupId }}">{{ values.label }}</div>
				<# _.each( values.params, function( value, id ) { #>
					<#
						var label    = 'string' === typeof value.label ? value.label : id,
							supports = 'object' === typeof value.options ? _.values( value.options ) : false,
							support  = ! supports ? true : -1 !== _.indexOf( supports, option );
					#>
					<# if ( support ) { #>
						<label class="fusion-select-label" data-value="{{ id }}">{{{ label }}}</label>
					<# } #>
				<# }); #>
			<# }); #>
		</div>
	</div>
</div>
</script>
