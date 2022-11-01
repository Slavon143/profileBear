<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_title-shortcode">
{{{ style }}}
<# if ( 'rotating' === title_type ) { #>
	<div {{{ _.fusionGetAttributes( attr ) }}}>
		<{{ title_tag }} {{{ _.fusionGetAttributes( headingAttr ) }}}>
			<span class="fusion-highlighted-text-prefix">{{before_text}}</span>
			<# if ( 0 < rotation_text.length ) { #>
				<span {{{ _.fusionGetAttributes( animatedAttr ) }}}>
					<span class="fusion-animated-texts">
						<# _.each( rotation_text, function( text ) {
							if ( '' !==  text ) { #>
								<span {{{ _.fusionGetAttributes( rotatedAttr ) }}} >{{text}}</span>
							<# }
						} ); #>
					</span>
				</span>
			<# } #>
			<span class="fusion-highlighted-text-postfix">{{after_text}}</span>
		</{{ title_tag }}>
	</div>
<# } else if ( 'highlight' === title_type ) { #>
	<div {{{ _.fusionGetAttributes( attr ) }}}>
		<{{ title_tag }} {{{ _.fusionGetAttributes( headingAttr ) }}}>
			<span class="fusion-highlighted-text-prefix">{{before_text}}</span>
			<# if ( '' !== highlight_text ) { #>
				<span class="fusion-highlighted-text-wrapper">
					<span {{{ _.fusionGetAttributes( animatedAttr ) }}}>{{highlight_text}}</span>
				</span>
			<# } #>
			<span class="fusion-highlighted-text-postfix">{{after_text}}</span>
		</{{ title_tag }}>
	</div>
<# } else if ( -1 !== style_type.indexOf( 'underline' ) || -1 !== style_type.indexOf( 'none' ) ) { #>
<div {{{ _.fusionGetAttributes( attr ) }}}>
	<{{ title_tag }} {{{ _.fusionGetAttributes( headingAttr ) }}}>
		<# if ( 'off' !== title_link ) { #>
			<a href="#"> {{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}} </a>
		<# } else { #>
			{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}
		<# } #>
	</{{ title_tag }}>
</div>
<# } else { #>
	<# if ( 'right' == content_align && ! isFlex ) { #>
<div {{{ _.fusionGetAttributes( attr ) }}}>
	<div class="title-sep-container">
		<div {{{ _.fusionGetAttributes( separatorAttr ) }}}></div>
	</div>
	<span class="awb-title-spacer"></span>
	<{{ title_tag }} {{{ _.fusionGetAttributes( headingAttr ) }}}>
	<# if ( 'off' !== title_link ) { #>
		<a href="#"> {{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}} </a>
	<# } else { #>
		{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}
	<# } #>
	</{{ title_tag }}>
</div>
	<# } else if ( 'center' == content_align || isFlex ) { #>
<div {{{ _.fusionGetAttributes( attr ) }}}>
	<#
		var leftClasses            = 'title-sep-container title-sep-container-left',
			rightClasses           = 'title-sep-container title-sep-container-right',
			additionalLeftClasses  = '',
			additionalRightClasses = '';

		_.each( ['large', 'medium', 'small' ], function( responsiveSize ) {
			if ( ! content_align_sizes[ responsiveSize ] || 'center' === content_align_sizes[ responsiveSize ] ) {
				return;
			}
			if ( 'left' == content_align_sizes[ responsiveSize ] ) {
				additionalLeftClasses += ' fusion-no-' + responsiveSize + '-visibility';
			} else {
				additionalRightClasses += ' fusion-no-' + responsiveSize + '-visibility';
			}
		} );

		leftClasses  += additionalLeftClasses;
		rightClasses += additionalRightClasses;
	#>
	<div class="{{{ leftClasses }}}">
		<div {{{ _.fusionGetAttributes( separatorAttr ) }}}></div>
	</div>
	<span class="awb-title-spacer{{{ additionalLeftClasses }}}"></span>
	<{{ title_tag }} {{{ _.fusionGetAttributes( headingAttr ) }}}>
		<# if ( 'off' !== title_link ) { #>
			<a href="#"> {{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}} </a>
		<# } else { #>
			{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}
		<# } #>
	</{{ title_tag }}>
	<span class="awb-title-spacer{{{ additionalRightClasses }}}"></span>
	<div class="{{{ rightClasses }}}">
		<div {{{ _.fusionGetAttributes( separatorAttr ) }}}></div>
	</div>
</div>
	<# } else { #>
<div {{{ _.fusionGetAttributes( attr ) }}}>
	<{{ title_tag }} {{{ _.fusionGetAttributes( headingAttr ) }}}>
		<# if ( 'off' !== title_link ) { #>
			<a href="#"> {{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}} </a>
		<# } else { #>
			{{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}}
		<# } #>
	</{{ title_tag }}>
	<span class="awb-title-spacer"></span>
	<div class="title-sep-container">
		<div {{{ _.fusionGetAttributes( separatorAttr ) }}}></div>
	</div>
</div>
	<# } #>
<# } #>
</script>
