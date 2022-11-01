<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_search-shortcode">
	{{{formStyles}}}
	<div {{{ _.fusionGetAttributes( wrapperAttr ) }}}>
		<form role="search" {{{ _.fusionGetAttributes( formAttr ) }}} method="get" action="<?php echo esc_url_raw( home_url( '/' ) ); ?>">
				<div class="fusion-search-form-content">
					<div class="fusion-search-field search-field">
						<label><span class="screen-reader-text"><?php esc_attr_e( 'Search for:', 'fusion-builder' ); ?></span>
							<# if ( values.live_search ) { #>
								<input type="search" class="s fusion-live-search-input" name="s" id="fusion-live-search-input-{{cid}}" autocomplete="off" placeholder="{{{values.placeholder}}}" required aria-required="true" aria-label="{{{values.placeholder}}}"/>
							<# } else { #>
								<input type="search" value="" name="s" class="s" placeholder="{{{values.placeholder}}}" required aria-required="true" aria-label="{{{values.placeholder}}}"/>
							<# } #>
						</label>
					</div>
					<div class="fusion-search-button search-button">
						<input type="submit" class="fusion-search-submit searchsubmit" aria-label="<?php esc_html_e( 'Search', 'fusion-builder' ); ?>" value="&#xf002;" />
						<# if ( values.live_search ) { #>
						<div class="fusion-slider-loading"></div>
						<# } #>
					</div>
				</div>
				<# if ( values.live_search ) { #>
					<div class="fusion-search-results-wrapper"><div class="fusion-search-results"></div></div>
				<# } #>
			</form>
	</div>
</script>
