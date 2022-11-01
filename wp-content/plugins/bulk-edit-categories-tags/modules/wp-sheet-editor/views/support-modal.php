
<div class="remodal remodal-support" data-remodal-id="modal-support" data-remodal-options="closeOnOutsideClick: false">

	<div class="modal-content">
		<h3><?php _e('Help', VGSE()->textname); ?></h3>

		<?php
		$support_links = VGSE()->get_support_links(null, '', 'sheet-toolbar-help');

		if (!empty($support_links)) {
			echo '<ol>';
			foreach ($support_links as $support_link) {
				?>
				<li><?php echo wp_kses_post($support_link['description']); ?> <a class="" target="_blank" href="<?php echo esc_url($support_link['url']); ?>"><?php echo esc_html($support_link['label']); ?></a></li> 
				<?php
			}
			echo '</ol>';
			?>
		<?php } ?>

	</div>
	<br>
	<button data-remodal-action="confirm" class="remodal-cancel"><?php _e('Close', VGSE()->textname); ?></button>
</div>