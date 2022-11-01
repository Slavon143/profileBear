<script type="text/html" id="tmpl-fusion_privacy-shortcode">
<div {{{ _.fusionGetAttributes( attr ) }}}>
<div {{{ _.fusionGetAttributes( contentAttr ) }}}> {{{ FusionPageBuilderApp.renderContent( output, cid, false ) }}} </div>
	<# if ( embeds ) { #>
		<form {{{ _.fusionGetAttributes( formAttr ) }}}>
			<ul>
					<# _.each( embeds, function( embed ) { #>
						<li>
							<label for="{{ embed.id }}">
								<input name="consents[]" type="checkbox" value="{{ embed.id }}" {{ embed.selected }} id="{{ embed.id }}">
								{{ embed.label }}
							</label>
						</li>
					<# } ); #>

			</ul>
			<input class="fusion-button fusion-button-default fusion-button-default-size" onclick="event.preventDefault();" type="submit" value="{{ buttonString }}" >
		</form>
	<# } #>
</div>
</script>
