<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.1
 */

?>
<script type="text/template" id="fusion-builder-dynamic-selection">
<div class="select_arrow"></div>
<select id="fusion-dynamic-selection-{{ option }}" class="fusion-select-field<?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>">
	<option value=""><?php esc_attr_e( 'Select Dynamic Content', 'fusion-builder' ); ?></option>
	<# _.each( params, function( values, groupId ) { #>
		<optgroup label="{{ values.label }}">
			<# _.each( values.params, function( value, id ) { #>
				<#
					var label    = 'string' === typeof value.label ? value.label : id,
						supports = 'object' === typeof value.options ? _.values( value.options ) : false,
						support  = ! supports ? true : -1 !== _.indexOf( supports, option );
				#>
				<# if ( support ) { #>
					<option value="{{ id }}" >{{{ label }}}</option>
				<# } #>
			<# }); #>
		</optgroup>
	<# }); #>
</select>
</script>
