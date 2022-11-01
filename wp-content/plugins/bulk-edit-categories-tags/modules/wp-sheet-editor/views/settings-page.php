<div class="remodal-bg quick-setup-page-content" id="vgse-wrapper" data-nonce="<?php echo esc_attr($nonce); ?>">
	<div class="">
		<div class="">
			<h2 class="hidden"><?php _e('Sheet Editor', VGSE()->textname); ?></h2>
			<a href="https://wpsheeteditor.com/?utm_source=wp-admin&utm_medium=quick-setup-logo" target="_blank"><img src="<?php echo esc_url(VGSE()->logo_url); ?>" class="vg-logo"></a>
		</div>

		<?php $this->render_settings_form(); ?>
	</div>

</div>