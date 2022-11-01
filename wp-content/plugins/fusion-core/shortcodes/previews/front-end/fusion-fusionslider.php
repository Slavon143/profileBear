<script type="text/html" id="tmpl-fusion_fusionslider-shortcode">
<#
if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.sliders ) {
#>
	<div {{{  _.fusionGetAttributes( attr ) }}}>
		{{{ slider }}}
	</div>
<# } else if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.placeholder ) { #>
	{{{ query_data.placeholder }}}
<# } #>
</script>
