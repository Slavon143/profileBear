<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<#
	var fieldId  = 'undefined' === typeof param.param_name ? param.id : param.param_name;
	try {
		object_value = 'object' === typeof option_value ? option_value : JSON.parse( option_value );
		option_value = 'object' === typeof option_value ? JSON.stringify( option_value ) : option_value;
	} catch( e ) {
		if ( window.awbPalette && awbPalette.data ) {
			object_value = awbPalette.data;
			option_value = JSON.stringify( awbPalette.data )
		}
	}

	object_value = Object.assign( {}, param.default, object_value );
#>
<# var renderColorItem = function( colorSlug, colorData ) { #>
	<# var isRemovableColor = ( param.default[ colorSlug ] ? false : true ); #>
	<li class="fusion-color-palette-item" data-slug="{{ colorSlug }}">
		<div class="awb-palette-title">
			<span class="preview" style="background-color:{{ colorData.color }};"></span>
			<span class="label">{{{ colorData.label }}}</span>
			<div class="actions">
				<span class="fusiona-pen"></span>
				<# if ( isRemovableColor ) { #>
					<span class="fusiona-trash-o"></span>
				<# } #>
			</div>
		</div>
		<div class="awb-palette-content">
			<label for="name-{{ colorSlug }}" class="color-name-label"><?php esc_html_e( 'Color Name', 'Avada' ); ?></label>
			<input class="color-name awb-ignore" name="{{ colorSlug }}[label]" id="name-{{ colorSlug }}" type="text" value="{{ colorData.label }}"/>

			<label for="color-{{ colorSlug }}" class="color-code-label"><?php esc_html_e( 'Color Code', 'Avada' ); ?></label>
			<input id="color-{{ colorSlug }}" class="color-picker awb-picker awb-palette-picker awb-ignore" type="text" value="{{ colorData.color }}" data-alpha="true" data-global="false" name="{{ colorSlug }}[color]">
		</div>
	</li>
<# }; #>

<# var renderColorTemplate = function() {
	var colorSlug = "___color_slug___",
		colorData = { 'color': '#ffffff', 'label': '<?php esc_html_e( 'New Color', 'Avada' ); ?>' };

	#> <!-- <# renderColorItem( colorSlug, colorData ); #> --> <#
}; #>

<div class="fusion-color-palette-options {{ fieldId }}">
	<div class="awb-color-palette-color-template" style="display:none !important">
		<# renderColorTemplate(); #>
	</div>

	<ul id="{{ fieldId }}-list" class="awb-color-palette-list">
		<#
			_.each( object_value, function( colorData, colorSlug ) {
				renderColorItem( colorSlug, colorData );
			});
		#>
	</ul>

	<div class="awb-color-palette-add-btn-wrapper">
		<button class="button button-primary awb-color-palette-add-btn">
			<span class="fusiona-plus"></span>
			<?php esc_html_e( 'Add New Color', 'Avada' ); ?>
		</button>
	</div>

	<input class="awb-palette-save" name="{{ fieldId }}" type="hidden" value="{{ option_value }}" />
</div>
