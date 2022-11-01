<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_syntax_highlighter-shortcode">
<div {{{ _.fusionGetAttributes( syntaxAttr ) }}}>
	<# if ( 'yes' === copy_to_clipboard ) { #>
		<div class="syntax-highlighter-copy-code">
			<span {{{ _.fusionGetAttributes( syntaxHighlighterCopyCodeTitleAttr ) }}}>{{{ copy_to_clipboard_text }}}</span>
		</div>
	<# } #>

	<# if ( wp_enqueue_code_editor ) { #>
		<textarea {{{ _.fusionGetAttributes( textareaAttr ) }}}>{{{ output }}}</textarea>
	<# } else { #>
		<pre id="fusion_syntax_highlighter_{{ cid }}"> {{{ output }}}</pre>
	<# } #>
</div>
{{{ styles }}}
</script>
