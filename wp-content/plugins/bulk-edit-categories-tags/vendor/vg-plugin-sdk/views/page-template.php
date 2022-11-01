<?php
/**
 * Template used for the quick setup page.
 */
$nonce = wp_create_nonce($this->settings['plugin_prefix'] . 'nonce');

if (empty($page_id)) {
	$page_id = 'vg-plugin-sdk-page';
}
?>
<div class="vg-plugin-sdk-page" id="<?php echo esc_attr($this->settings['plugin_prefix'] . $page_id); ?>" data-nonce="<?php echo $nonce; ?>">
	<div class="">
		<h2 class="hidden"><?php echo esc_html($this->settings['plugin_name']); ?></h2>

		<?php if (!empty($this->settings['website'])) { ?>
			<a href="<?php echo esc_url($this->settings['website']); ?>" target="_blank">
			<?php } ?>
			<img src="<?php echo esc_url($this->settings['logo']); ?>" class="vg-logo">
			<?php if (!empty($this->settings['website'])) { ?>
			</a>
		<?php } ?>
	</div>
	<?php if (empty($this->settings['logo'])) { ?>
		<h2><?php echo esc_attr($this->settings['plugin_name']); ?></h2>
	<?php } ?>
	<div class="page-content">
		<?php echo $content; ?>
		<?php do_action('vg_plugin_sdk/' . $page_id . '/quick_setup_screen/after_welcome_content'); ?>


		<?php if (!empty($this->settings['settings_page_url'])) { ?>
			<hr>
			<p><a class="button settings-button" href="<?php echo esc_url($this->settings['settings_page_url']); ?>"><i class="fa fa-cog"></i> <?php _e('Settings', $this->textname); ?></a></p>
		<?php } ?>


		<?php if (!empty($upgrade_message)) { ?>
			<div class="clear"></div>
			<hr>
			<?php echo $upgrade_message; ?>
			<a href="<?php echo esc_url($this->settings['buy_link']); ?>" class="button button-primary button-large" style="margin-bottom: 20px; display: inline-block;"> <?php echo esc_url($this->settings['buy_link_text']); ?> </a>
		<?php } ?>

	</div>
	<?php do_action('vg_plugin_sdk/' . $page_id . '/after_content'); ?>
</div>
<script>
	jQuery('.install-plugin-trigger').click(function (e) {
		return !window.open(this.href, 'Install plugin', 'width=500,height=500');
	});
</script>
			<?php
		