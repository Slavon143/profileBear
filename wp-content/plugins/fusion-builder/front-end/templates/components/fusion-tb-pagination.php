<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_tb_pagination-shortcode">
	{{{styles}}}
	<div {{{ _.fusionGetAttributes( wrapperAttr ) }}}>
	<# if ( 'sticky' !== values.layout ) { #>
		<div class="fusion-tb-previous">
			<a href="#" rel="prev">{{ fusionBuilderText.previous }}</a>
			<# if ( 'preview' === values.layout ) { #>
				<div class="fusion-pagination-preview-wrapper">
					<span class="fusion-item-media"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 560"><g fill-rule="evenodd" clip-rule="evenodd"><path fill="#BBC0C4" d="M378.9 432L630.2 97.4c9.4-12.5 28.3-12.6 37.7 0l221.8 294.2c12.5 16.6.7 40.4-20.1 40.4H378.9z"/><path fill="#CED3D6" d="M135 430.8l153.7-185.9c10-12.1 28.6-12.1 38.7 0L515.8 472H154.3c-21.2 0-32.9-24.8-19.3-41.2z"/><circle fill="#FFF" cx="429" cy="165.4" r="55.5"/></g></svg></span>
					<span class="fusion-item-title">{{fusionBuilderText.post_title}}</span>
			</div>
			<# } #>
		</div>
		<div class="fusion-tb-next">
			<a href="#" rel="next">{{ fusionBuilderText.next }}</a>
			<# if ( 'preview' === values.layout ) { #>
				<div class="fusion-pagination-preview-wrapper">
					<span class="fusion-item-media"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 560"><g fill-rule="evenodd" clip-rule="evenodd"><path fill="#BBC0C4" d="M378.9 432L630.2 97.4c9.4-12.5 28.3-12.6 37.7 0l221.8 294.2c12.5 16.6.7 40.4-20.1 40.4H378.9z"/><path fill="#CED3D6" d="M135 430.8l153.7-185.9c10-12.1 28.6-12.1 38.7 0L515.8 472H154.3c-21.2 0-32.9-24.8-19.3-41.2z"/><circle fill="#FFF" cx="429" cy="165.4" r="55.5"/></g></svg></span>
					<span class="fusion-item-title">{{fusionBuilderText.post_title}}</span>
			</div>
			<# } #>
		</div>
	<# } else { #>
		<div class="fusion-control-navigation prev">
			<a href="" rel="prev">
			<span class="fusion-item-title">
				<i class="awb-icon-angle-left" aria-hidden="true"></i>
				<p>{{fusionBuilderText.post_title}}</p>
			</span>
			<span class="fusion-item-media"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 560"><g fill-rule="evenodd" clip-rule="evenodd"><path fill="#BBC0C4" d="M378.9 432L630.2 97.4c9.4-12.5 28.3-12.6 37.7 0l221.8 294.2c12.5 16.6.7 40.4-20.1 40.4H378.9z"/><path fill="#CED3D6" d="M135 430.8l153.7-185.9c10-12.1 28.6-12.1 38.7 0L515.8 472H154.3c-21.2 0-32.9-24.8-19.3-41.2z"/><circle fill="#FFF" cx="429" cy="165.4" r="55.5"/></g></svg></span>
			</a>
		</div>
		<div class="fusion-control-navigation next">
			<a href="" rel="next">
			<span class="fusion-item-media"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 560"><g fill-rule="evenodd" clip-rule="evenodd"><path fill="#BBC0C4" d="M378.9 432L630.2 97.4c9.4-12.5 28.3-12.6 37.7 0l221.8 294.2c12.5 16.6.7 40.4-20.1 40.4H378.9z"/><path fill="#CED3D6" d="M135 430.8l153.7-185.9c10-12.1 28.6-12.1 38.7 0L515.8 472H154.3c-21.2 0-32.9-24.8-19.3-41.2z"/><circle fill="#FFF" cx="429" cy="165.4" r="55.5"/></g></svg></span>
			<span class="fusion-item-title">
				<p>{{fusionBuilderText.post_title}}</p>
				<i class="awb-icon-angle-right" aria-hidden="true"></i>
			</span>
			</a>
		</div>
	<# } #>
	</div>
	<# if ( 'sticky' === values.layout ) { #>
	<div class="fusion-builder-placeholder-preview fusion-tb-pagination-placeholder">
		<i class="{{ icon }}" aria-hidden="true"></i> {{ label }}
	</div>
	<# } #>
</script>
