<script type="text/html" id="tmpl-fusion_faq-shortcode">
<#
// If Query Data is set, use it and continue.  If not, echo HTML.
if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.faq_items ) {
#>
	<style type="text/css">{{{ styles }}}</style>
	<div {{{ _.fusionGetAttributes( attr ) }}}>
		{{{ faqFilters }}}

		<div class="fusion-faqs-wrapper" style="display:block;">
			<div class="accordian fusion-accordian">
				<div {{{ _.fusionGetAttributes( attrWrapper ) }}}>
					{{{ faqList }}}
				</div>
			</div>
		</div>
	</div>
<#
} else if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.placeholder ) {
#>
{{{ query_data.placeholder }}}
<# } #>
</script>
