<?php
/**
 * Underscore.js template.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<?php
$settings = [];
if ( function_exists( 'wp_enqueue_code_editor' ) ) {
	$settings = wp_enqueue_code_editor( [] );
}
?>
<#
fieldId = 'undefined' === typeof param.param_name ? param.id : param.param_name;
mode    = 'undefined' !== typeof param.choices && 'undefined' !== param.choices.language ? param.choices.language : 'default';
mode    = 'default' === mode && 'undefined' !== typeof param.language ? param.language : mode;
#>
<textarea
	name="{{ fieldId }}"
	id="{{ fieldId }}"
	class="fusion-builder-code-block"
	cols="20"
	rows="5"
	data-language="{{ mode }}"
	<# if ( param.placeholder ) { #>
		data-placeholder="{{ param.value }}"
	<# } #>
>{{ option_value }}</textarea>
<textarea style="display: none;" class="hidden {{ fieldId }}"><?php echo wp_json_encode( $settings ); ?></textarea>
