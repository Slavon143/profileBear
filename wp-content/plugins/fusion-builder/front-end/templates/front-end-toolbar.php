<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-front-end-toolbar">
	<li class="pagename-link fb">
		<a class="fusion-preview-only-link" href="{{ FusionApp.getPreviewUrl() }}">
			{{ FusionApp.getPost( 'post_type_name' ) }}: <strong>{{{ FusionApp.getPost( 'post_title' ) }}}</strong>
			<span class="icon"><i class="fusiona-external-link-alt" aria-hidden="true"></i></span>
		</a>
	</li>
	<li class="admin-tools fb">
		<ul>
			<# if ( ( true === FusionApp.data.is_singular || true === FusionApp.data.is_shop ) && 'undefined' !== typeof FusionApp.data.postDetails && FusionApp.isEditable() && FusionApp.hasEditableContent ) { #>
				<li class="open-library">
					<a href="#" class="fusion-builder-open-library has-tooltip" aria-label="<?php esc_attr_e( 'Library', 'fusion-builder' ); ?>">
						<span class="fusiona-drive"></span>
					</a>
				</li>


				<li class="fusion-builder-history-container has-submenu">
					<a href="#" class="fusion-builder-history has-tooltip trigger-submenu-toggling" id="fusion-builder-toolbar-history-menu" aria-label="<?php esc_attr_e( 'History', 'fusion-builder' ); ?>">
						<span class="history-change-indicator"></span>
						<span class="fusiona-clock"></span>
					</a>
				</li>
			<# } #>

			<li class="fusion-builder-preferences">
				<a href="#" class="has-tooltip" aria-label="<?php esc_html_e( 'Preferences', 'fusion-builder' ); ?>">
					<span>
						<i class="fusiona-preferences" aria-hidden="true"></i>
					</span>
				</a>
			</li>

			<?php $allowed_post_types = FusionBuilder()->allowed_post_types(); ?>
			<?php if ( is_array( $allowed_post_types ) && ( current_user_can( 'publish_pages' ) || current_user_can( 'publish_posts' ) ) ) : ?>
				<li id="fusion-builder-toolbar-new-post" class="add-new has-submenu">
					<a href="#" class="fusion-builder-add-new has-tooltip trigger-submenu-toggling" aria-label="<?php esc_attr_e( 'Add New', 'fusion-builder' ); ?>">
						<span class="fusiona-plus"></span>
					</a>
					<ul class="fusion-builder-new-list submenu-trigger-target" aria-expanded="false">
						<?php foreach ( $allowed_post_types as $allowed_post_type ) : ?>
							<?php
							if ( 'fusion_template' === $allowed_post_type || 'fusion_element' === $allowed_post_type ) {
								continue;
							}


							$allowed_post_type_object = get_post_type_object( $allowed_post_type );
							$capability_type          = 'post';
							if ( is_object( $allowed_post_type ) && isset( $allowed_post_type_object->capability_type ) ) {
								$capability_type = $allowed_post_type_object->capability_type;
							}

							if ( 'post' === $capability_type && ! current_user_can( 'publish_posts' ) ) {
								continue;
							}
							if ( 'page' === $capability_type && ! current_user_can( 'publish_pages' ) ) {
								continue;
							}
							?>
							<?php if ( is_object( $allowed_post_type_object ) && current_user_can( $allowed_post_type_object->cap->publish_posts, false ) ) : ?>
								<li class="add-new" data-post-type="<?php echo esc_attr( $allowed_post_type ); ?>">
									<?php echo esc_html( $allowed_post_type_object->labels->singular_name ); ?>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endif; ?>

			<?php if ( current_user_can( 'publish_pages' ) || current_user_can( 'publish_posts' ) ) : ?>
				<# if ( ( true === FusionApp.data.is_singular || true === FusionApp.data.is_shop ) && 'undefined' !== typeof FusionApp.data.postDetails && FusionApp.isEditable() && FusionApp.hasEditableContent ) { #>
					<li>
						<a href="#" class="fusion-builder-clear-layout has-tooltip" aria-label="<?php esc_attr_e( 'Clear Layout', 'fusion-builder' ); ?>">
							<span class="fusiona-trash-o"></span>
						</a>
					</li>
				<# } #>
			<?php endif; ?>
		</ul>
	</li>

	<# if ( ( true === FusionApp.data.is_singular || true === FusionApp.data.is_shop ) && 'undefined' !== typeof FusionApp.data.postDetails && FusionApp.isEditable() && FusionApp.hasEditableContent ) { #>

		<li class="builder-main-tools fb">
			<ul>

				<?php
				/**
				 * This icons is hidden but must NOT be removed.
				 * We keep it around so that the Ctrl/Cmd + Shift + B hotkey works.
				 */
				?>
				<li>
					<a href="#" class="hidden fusion-builder-save-template has-tooltip" data-focus="#new_template_name" data-target="#fusion-builder-layouts-templates" aria-label="<?php esc_attr_e( 'Save as Template', 'fusion-builder' ); ?>" style="display:none !important;">
						<?php esc_attr_e( 'Save as Template', 'fusion-builder' ); ?>
					</a>
				</li>
			</ul>
		</li>

	<# } #>

</script>
