<style>

	.vg-sheet-editor-usage-stats {
		text-align: center;
		overflow: auto;

	}
	.vg-sheet-editor-usage-stats p {
		font-size: 14px;
	}
	.vg-sheet-editor-usage-stats li {
		float: left;
	}
	.vg-sheet-editor-usage-stats .stats-list li {
		width: 100%;
		max-width: 165px;
		font-size: 16px;
		margin-bottom: 20px;
	}
	.vg-sheet-editor-usage-stats .count {
		font-size: 40px;
		color: green;
		line-height: 40px;
	}
	.vg-sheet-editor-usage-stats .vg-logo {
		display: block;
		margin: 0 auto;
	}
	.vg-sheet-editor-usage-stats hr {
		margin: 20px auto 0;
	}
	.vg-sheet-editor-usage-stats .post-types-enabled {
		margin-top: 10px;
	}
	.vg-sheet-editor-usage-stats .vg-logo {
		width: 150px;
	}

	.postbox .inside .vg-sheet-editor-usage-stats h2 {
		padding: 0;
		font-size: 19px;
	}

	.vg-sheet-editor-usage-stats hr {
		margin: 2px auto 9px;
	}
</style>
<div class="vg-sheet-editor-usage-stats">
	<a href="https://wpsheeteditor.com/?utm_source=wp-admin&utm_medium=usage-widget-logo" target="_blank"><img src="<?php echo esc_url(VGSE()->logo_url); ?>" class="vg-logo"></a>
	<?php
	if (current_user_can('manage_options')) {
		?>
		<p><?php _e('Thank you for using our spreadsheet editor', VGSE()->textname); ?></p>

		<?php
		$editions_count = (int) get_option('vgse_editions_counter', 0);
		$processed_count = (int) get_option('vgse_processed_counter', 0);

		if ($processed_count > 0) {
			?>
			<h2><?php _e('Usage stats', VGSE()->textname); ?></h2>

			<?php
			$minutes_saved = ( $editions_count * 1.4 ) / 60;
			$stats = array(
				'total_editions' => array(
					'label' => __('Modified rows', VGSE()->textname),
					'count' => number_format($processed_count),
				),
				'time_saved' => array(
					'label' => __('Time saved <br/>(estimated)', VGSE()->textname),
					'count' => ( $minutes_saved > 60 ) ? intval($minutes_saved / 60) . ' hr' : intval($minutes_saved) . ' mins.',
				),
				'clicks_avoided' => array(
					'label' => __('Clicks avoided <br/>(estimated)', VGSE()->textname),
					'count' => number_format($editions_count * 3),
				),
			); 

			$stats = apply_filters('vg_sheet_editor/usage_stats/stats', $stats, $editions_count, $processed_count);

			if (!empty($stats)) {
				?>
				<ul class="stats-list">
					<?php foreach ($stats as $key => $stat) { ?>
						<li><div class="count"><?php echo is_numeric($stat['count']) ? (int) $stat['count'] : sanitize_text_field($stat['count']); ?></div><div class="label"><?php echo wp_kses_post($stat['label']); ?></div></li>
					<?php } ?>
				</ul>
				<?php
			}
		}
		?>
		<div class="clear"></div>
		<hr>
		<h2><?php _e('Extend the spreadsheet', VGSE()->textname); ?></h2>
		<p><?php _e('Edit WooCommerce products, WooCommerce Variations and Attributes.<br/>Edit hundreds of posts at once using formulas, copy information between posts,<br/>Edit custom post types and custom fields, Edit User Profiles, and More', VGSE()->textname); ?> </p>
		<a href="<?php echo VGSE()->get_trigger_link('extensions', admin_url('admin.php?page=vg_sheet_editor_extensions&vgse_only_inactive=1'), 'usage-widget'); ?>" class="button button-primary" style="margin-bottom: 20px; display: inline-block;"><?php _e('View extensions', VGSE()->textname); ?></a>

		<div class="clear"></div>
		<hr>
	<?php } ?>
	<h2><?php _e('Open the Spreadsheet Editor', VGSE()->textname); ?></h2>
	<div class="post-types-enabled">
		<?php
		$post_types = VGSE()->helpers->get_enabled_post_types();

		if (!empty($post_types)) {
			foreach ($post_types as $key => $post_type_name) {
				if (is_numeric($key)) {
					$key = $post_type_name;
				}
				?>
				<a class="button post-type-<?php echo esc_attr($key); ?>" href="<?php echo VGSE()->helpers->get_editor_url($key);
				?>"><?php _e('Edit ' . esc_html($post_type_name) . 's', VGSE()->textname); ?></a>		
				   <?php
			   }
		   }
		   ?>
	</div>
	<div class="clear"></div>
	<?php
	// Only admins can request help from WP Sheet Editor
	if (current_user_can('manage_options')) {
		?>
		<h2><?php _e('Help', VGSE()->textname); ?></h2>
		<?php
		$support_links = VGSE()->get_support_links(null, '', 'usage-stats-help');

		if (!empty($support_links)) {
			foreach ($support_links as $support_link) {
				?>
				<a class="button button-secondary button-secondary" target="_blank" href="<?php echo esc_url($support_link['url']); ?>"><?php echo esc_html($support_link['label']); ?></a>
				<?php
			}
			?>
		<?php } ?>


		<div class="clear"></div>
	<?php } ?>
</div>

<script>
	if (typeof jQuery === 'function') {
		jQuery(document).ready(function () {
			// Equalize stats items height
			var $statsItem = jQuery('.stats-list li');
			var tallest = 0;
			$statsItem.each(function () {
				if (jQuery(this).height() > tallest) {
					tallest = jQuery(this).height();
				}
			});

			$statsItem.height(tallest);
		});
	}
</script>