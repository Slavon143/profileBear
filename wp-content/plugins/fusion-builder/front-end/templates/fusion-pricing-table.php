<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_pricing_table-shortcode">
	{{{ styles }}}
	<div {{{ _.fusionGetAttributes( tableData ) }}}></div>
</script>
<script type="text/html" id="tmpl-fusion_pricing_column-shortcode">
	<div class="panel-container">
		<div class="fusion-panel">
			<div class="panel-heading">
				<h3 {{{ _.fusionGetAttributes( titleAttr ) }}}>{{{ title }}}</h3>
			</div>

			<# if ( ! price ) { #>
				<div class="panel-body pricing-row"></div>
			<# } else { #>

				<div class="panel-body pricing-row">
					<div class="price<# if ( 1 < price.length ) { #> price-with-decimal<# } #>">
						<# if ( 'right' !== currencyPosition ) { #>
							<span class="currency">{{{ currency }}}</span>
						<# } #>

						<span class="integer-part">{{{ price[0] }}}</span>

						<# if ( 'undefined' !== typeof price[1] ) { #>
							<sup class="decimal-part">{{{ price[1] }}}</sup>
						<# } #>

						<# if ( 'right' === currencyPosition ) { #>

							<span {{{ _.fusionGetAttributes( currencyClasses ) }}}>{{{ currency }}}</span>

							<# if ( time ) { #>
								<span {{{ _.fusionGetAttributes( timeClasses ) }}}>{{{ time }}}</span>
							<# } #>
						<# } #>

						<# if ( time && 'right' !== currencyPosition ) { #>

							<span {{{ _.fusionGetAttributes( timeClasses ) }}}>{{{ time }}}</span>
						<# } #>
					</div>
				</div>

			<# } #>

			<# if ( 'object' === typeof featureRows ) { #>
				<ul class="list-group">
					<# _.each( featureRows, function( featureRow )  { #>
						<li class="list-group-item normal-row">{{{ FusionPageBuilderApp.renderContent( featureRow, cid, false ) }}}</li>
					<# } ); #>
				</ul>
			<# } #>
			<# if ( footerContent ) { #>
				<div class="panel-footer footer-row">{{{ FusionPageBuilderApp.renderContent( footerContent, cid, false ) }}}</div>
			<# } #>
		</div>
	</div>
</script>
