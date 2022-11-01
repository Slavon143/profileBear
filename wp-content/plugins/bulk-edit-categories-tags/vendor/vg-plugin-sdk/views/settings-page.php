
<div class="vg-plugin-sdk-page" id="<?php echo esc_attr('vgfpsdk_settings_' . $this->args['opt_name']); ?>">
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
		<?php include __DIR__ . '/settings-form.php'; ?>
	</div>
</div>