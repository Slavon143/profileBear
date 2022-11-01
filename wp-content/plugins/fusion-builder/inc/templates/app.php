<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-app-template">
	<div id="fusion-loader"><span class="fusion-builder-loader"></span><span class="awb-studio-import-status"></span></div>
	<div id="content-error" title="{{{ fusionBuilderText.content_error_title }}}" style="display:none;">
		<p>{{{ fusionBuilderText.content_error_description }}}</p>
	</div>
	<div id="fusion_builder_controls">

		<ul id="fusion-page-builder-tabs">
			<li><a href="javascript:void(0)" class="fusion-builder-button">{{ fusionBuilderText.builder }}</a></li>
			<li><a href="#" class="fusion-builder-library-dialog">{{ fusionBuilderText.library }}</a></li>
		</ul>

		<div class="fusion-page-builder-controls">
			<a href="#" class="fusion-builder-layout-buttons fusion-builder-layout-buttons-toggle-containers" title="{{ fusionBuilderText.toggle_all_sections }}"><span class="dashicons-before dashicons-arrow-down"></span></a>
			<a href="#" class="fusion-builder-layout-buttons fusion-builder-layout-custom-css <?php echo esc_attr( $has_custom_css ); ?>" title="{{ fusionBuilderText.custom_css }}"><span class="fusiona-code"></span></a>
			<a href="#" class="fusion-builder-layout-buttons fusion-builder-template-buttons-save" title="{{ fusionBuilderText.save_page_layout }}"><span class="fusiona-drive"></span></a>
			<a href="#" class="fusion-builder-layout-buttons fusion-builder-layout-buttons-clear" title="{{ fusionBuilderText.delete_page_layout }}"><span class="fusiona-trash-o"></span></a>
			<a href="javascript:void(0)" class="fusion-builder-layout-buttons fusion-builder-layout-buttons-history" title="{{ fusionBuilderText.history }}">
				<span class="dashicons dashicons-backup"></span>
				<ul class="fusion-builder-history-list">
					<li class="fusion-empty-history fusion-history-active-state" data-state-id="1"><span class="dashicons dashicons-arrow-right-alt2"></span>{{ fusionBuilderText.empty }}</li>
				</ul>
			</a>
		</div>

		<div class="fusion-custom-css">
			<?php
			$echo_custom_css = '';
			if ( ! empty( $saved_custom_css ) ) {
				$echo_custom_css = $saved_custom_css;
			}
			?>
			<textarea name="_fusion_builder_custom_css" id="fusion-custom-css-field" placeholder="{{ fusionBuilderText.add_css_code_here }}"><?php echo wp_strip_all_tags( $echo_custom_css ); // phpcs:ignore WordPress.Security.EscapeOutput ?></textarea>
		</div>

	</div>

	<div id="fusion_builder_container">
		<?php do_action( 'fusion_builder_before_content' ); ?>
	</div>
	<?php do_action( 'fusion_builder_after_content' ); ?>

	<div id="fusion-builder-layouts">
		<?php Fusion_Builder_Library()->display_library_content(); ?>
	</div>

	<div id="fusion-google-font-holder" style="display:none">
		<?php
		$echo_google_fonts  = '';
		$saved_google_fonts = get_post_meta( $post->ID, '_fusion_google_fonts', true );
		if ( ! empty( $saved_google_fonts ) ) {
			$echo_google_fonts = $saved_google_fonts;
		}
		?>
		<textarea name="_fusion_google_fonts" id="fusion-google-fonts-field"><?php echo wp_json_encode( $echo_google_fonts ); // phpcs:ignore WordPress.Security.EscapeOutput ?></textarea>
		<div id="fusion-render-holder" style="display:none"></div>
	</div>
	<div id="fusion-studio-media-map-holdder" style="display:none">
		<?php
		$echo_media_map  = '';
		$saved_media_map = get_post_meta( $post->ID, 'avada_media', true );
		if ( ! empty( $saved_media_map ) ) {
			$echo_media_map = $saved_media_map;
		}
		?>
		<textarea name="avada_media" id="fusion-studio-media-map-field"><?php echo wp_json_encode( $echo_media_map ); // phpcs:ignore WordPress.Security.EscapeOutput ?></textarea>
	</div>
</script>
