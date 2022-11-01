<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

$fusion_settings          = awb_get_fusion_settings();
$column_min_width_default = $fusion_settings->get( 'text_column_min_width' );
$column_spacing_default   = $fusion_settings->get( 'text_column_spacing' );
$rule_style_default       = $fusion_settings->get( 'text_rule_style' );
$rule_size_default        = $fusion_settings->get( 'text_rule_size' );
$rule_color_default       = $fusion_settings->get( 'text_rule_color' );
?>
<script type="text/template" id="fusion-builder-block-module-text-preview-template">

	<#
	var
	content = params.element_content,
	text_block      = jQuery.parseHTML( content ),
	text_block_html = '',
	columnMinWidth = '',
	columnSpacing = '',
	ruleStyle = '',
	ruleSize = '',
	ruleColor = '',
	style = '';

	jQuery(text_block).each(function() {

		if ( jQuery(this).get(0).tagName != 'IMG' && typeof jQuery(this).get(0).tagName != 'undefined' ) {
			var childrens = jQuery(jQuery(this).get(0)).find('*');
			var child_img = false;
			if(childrens.length >= 1) {
				jQuery.each(childrens, function() {
					if(jQuery(this).get(0).tagName == 'IMG') {
						child_img = true;
					}
				});
			}
			if(child_img == true) {
				text_block_html += jQuery(this).outerHTML();
			} else {
				text_block_html += jQuery(this).text();
			}
		} else {
			text_block_html += jQuery(this).outerHTML();
		}
	});

	if ( 'none' !== params.text_transform ) {
		style += 'text-transform: ' + params.text_transform + ';';
	}

	if ( 1 < parseInt( params.columns ) ) {

		jQuery.each( [ '-webkit-', '-moz-', '' ], function( index, value ) {

			style += ' ' + value + 'column-count:' +  params.columns + ';';

			columnMinWidth = params.column_min_width;
			if ( '' === columnMinWidth ) {
				columnMinWidth = '<?php echo esc_attr( $column_min_width_default ); ?>';
			}
			style +=  ' ' + value + 'column-width:' + columnMinWidth + ';';

			columnSpacing = params.column_spacing;
			if ( '' === columnSpacing ) {
				columnSpacing = '<?php echo esc_attr( $column_spacing_default ); ?>';
			}
			style += ' ' + value + 'column-gap:' + columnSpacing + ';';

			ruleStyle = params.rule_style;
			if ( 'default' === ruleStyle ) {
				ruleStyle = '<?php echo esc_attr( $rule_style_default ); ?>';
			}

			ruleSize = params.rule_size;
			if ( '' === ruleSize ) {
				ruleSize = '<?php echo esc_attr( $rule_size_default ); ?>';
			}

			ruleColor = params.rule_color;
			if ( '' === ruleColor ) {
				ruleColor = '<?php echo esc_attr( $rule_color_default ); ?>';
			}

			if  ( 'none' !== ruleStyle ) {
				style += ' ' + value + 'column-rule:' + ruleSize + 'px ' + ruleStyle + ' ' + ruleColor + ';';
			}
		});

		if ( style ) {
			style += ' text-align:initial;';
		}
	}
	#>

	<# if ( style ) { #>
		<div class="fusion-text-block-styles" style="{{style}}">{{ text_block_html }}</div>
	<# } else { #>
		{{ text_block_html }}
	<# } #>

</script>
