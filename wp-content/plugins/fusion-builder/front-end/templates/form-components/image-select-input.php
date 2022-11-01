<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 3.1
 */

?>
<script type="text/html" id="tmpl-fusion_form_image_select_input-shortcode">
	<input id="{{ labelId }}" type="{{ inputType }}" value="{{ value }}" name="{{ inputName }}" {{{ elementData.class }}} {{{ elementData.id }}} {{{ elementData.required }}} {{{ checked }}} />
	<label for="{{ labelId }}">
		<# if ( 'below' !== labelPosition ) { #>
			<span>{{{ label }}}</span>
		<# } #>

		<picture class="fusion-form-image-wrapper">
		<# if ( '' !== imageUrl ) { #>
			<img src="{{ imageUrl }}" />
		<# } else { #>
			<svg style="width:100%; height:auto;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 560"><path fill="#EAECEF" d="M0 0h1024v560H0z"/><g fill-rule="evenodd" clip-rule="evenodd"><path fill="#BBC0C4" d="M378.9 432L630.2 97.4c9.4-12.5 28.3-12.6 37.7 0l221.8 294.2c12.5 16.6.7 40.4-20.1 40.4H378.9z"/><path fill="#CED3D6" d="M135 430.8l153.7-185.9c10-12.1 28.6-12.1 38.7 0L515.8 472H154.3c-21.2 0-32.9-24.8-19.3-41.2z"/><circle fill="#FFF" cx="429" cy="165.4" r="55.5"/></g></svg>
		<# } #>
		</picture>
		<# if ( 'below' === labelPosition ) { #>
			{{{ label }}}
		<# } #>
	</label>
</script>
