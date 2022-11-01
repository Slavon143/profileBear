<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.5
 */

?>
<script type="text/html" id="tmpl-fusion_instagram-shortcode">
<div {{{ _.fusionGetAttributes( atts ) }}}>
	<div class="instagram-posts">
		<# if ( instagramItems ) { #>
			{{{ instagramItems }}}
		<# } else { #>
			<div class="fusion-loading-container fusion-clearfix">
				<div class="fusion-loading-spinner">
					<div class="fusion-spinner-1"></div>
					<div class="fusion-spinner-2"></div>
					<div class="fusion-spinner-3"></div>
				</div>
			</div>
		<# } #>
	</div>
	<# if ( 'button' === values.load_more || 'yes' === values.follow_btn ) { 
		let buttons_span_class = '';
		if ( 'default' !== values.buttons_span ) {
			buttons_span_class = ' fusion-button-span-' + values.buttons_span;
		}
	#>
		<div class="awb-instagram-buttons">
			<# if ( 'button' === values.load_more ) { #>
				<a href="#" class="fusion-button button-flat button-default fusion-button-default-size awb-instagram-load-more-btn{{{buttons_span_class}}}">{{{values.load_more_btn_text}}}</a>
			<# } #>
			<# if ( 'yes' === values.follow_btn ) { #>
				<a href="#" target="_blank" class="fusion-button button-flat button-default fusion-button-default-size awb-instagram-follow-btn{{{buttons_span_class}}}"><i class="awb-icon-instagram button-icon-left"></i>{{{values.follow_btn_text}}}</a>
			<# } #>
		</div>
	<# } #>
</div>
</script>
