<?php
/**
 * The toolbar template file.
 *
 * @since 2.0
 * @package fusion-library
 */

?>
<script type="text/template" id="fusion-app-front-end-toolbar">
	<div class="fusion-builder-live-toolbar fusion-top-frame">
		<ul class="fusion-toolbar-nav">
			<li class="fusion-branding">
				<?php $version = defined( 'AVADA_VERSION' ) ? 'v' . AVADA_VERSION : 'v' . FUSION_BUILDER_VERSION; ?>		
				<div class="fusion-builder-logo-wrapper has-tooltip" aria-label="<?php echo esc_attr( apply_filters( 'avada_db_version', $version ) ); ?>">
					<i class="fusiona-avada-logo"></i>
				</div>
			</li>

			<li class="admin-tools">
				<ul class="global-options">
					<li>
						<a class="has-tooltip open-panel<# if ( sidebarOpen ) { #> active<# } #>" id="fusion-frontend-builder-toggle-global-panel" data-context="global-settings" href="#" aria-label="<?php esc_attr_e( 'Toggle Sidebar', 'fusion-builder' ); ?>"><i class="fusiona-sidebar-icon" aria-hidden="true"></i></a>
					</li>
				</ul>
			</li>

			<li class="fusion-exit-builder has-submenu">
				<a class="has-tooltip trigger-submenu-toggling" aria-label="<?php esc_attr_e( 'Exit', 'fusion-builder' ); ?>">
					<i class="fusiona-close-fb" aria-hidden="true"></i>
				</a>

				<ul class="fusion-exit-builder-list submenu-trigger-target" aria-expanded="false">
					<li class="exit-to-front-end">
						<a href="<?php echo esc_url( get_permalink() ); ?>" data-admin-url="<?php echo esc_url( admin_url() ); ?>" target="_self"><?php esc_html_e( 'Exit to page front-end', 'fusion-builder' ); ?></a>
					</li>
					<li class="exit-to-back-end">
						<a href="<?php echo esc_url( admin_url( 'post.php?post=' . get_the_ID() . '&action=edit' ) ); ?>" data-admin-url="<?php echo esc_url( admin_url( 'post.php' ) ); ?>" target="_self"><?php esc_html_e( 'Exit to page back-end', 'fusion-builder' ); ?></a>
					</li>
					<li class="exit-to-dashboard">
						<?php $dashboard_url = current_user_can( 'switch_themes' ) ? 'admin.php?page=avada' : ''; ?>
						<a href="<?php echo esc_url( admin_url( $dashboard_url ) ); ?>" target="_self"><?php esc_html_e( 'Exit to dashboard', 'fusion-builder' ); ?></a>
					</li>
				</ul>
			</li>

			<li class="save-wrapper fb">
				<ul>
					<#
						// TODO: Questionable whether this should be here or in FB only.
						if ( true === FusionApp.data.is_singular && 'undefined' !== typeof postStatus ) {
					#>
					<li class="post-status">
						<div class="options">
							<span class="option">
								<# var checked = ( 'draft' !== postStatus ) ? 'checked' : ''; #>
								<input id="fusion-post-status-publish" type="radio" name="post-status" value="publish" {{ checked }}>
								<label class="has-tooltip" for="fusion-post-status-publish" aria-label="<?php esc_attr_e( 'Publish', 'fusion-builder' ); ?>">
									<i class="fusiona-published" aria-hidden="true"></i>
									<span class="screen-reader-text"><?php esc_html_e( 'Publish', 'fusion-builder' ); ?></span>
								</label>
							</span>
							<span class="option">
								<# var checked = ( 'draft' === postStatus ) ? 'checked' : ''; #>
								<input id="fusion-post-status-draft" type="radio" name="post-status" value="draft" {{ checked }}>
								<label class="has-tooltip" for="fusion-post-status-draft" aria-label="<?php esc_attr_e( 'Draft', 'fusion-builder' ); ?>">
									<i class="fusiona-draft" aria-hidden="true"></i>
									<span class="screen-reader-text"><?php esc_html_e( 'Draft', 'fusion-builder' ); ?></span>
								</label>
							</span>
						</div>
					</li>
					<# } #>
					<li>
						<# 
						if ( postChanged ) {
							var disabledButton = 'false';
						} else {
							var disabledButton = 'true';
						}
						#>
						<a href="#" class="fusion-builder-save-page" data-disabled="{{{ disabledButton }}}">
							<span class="save-label"><?php esc_html_e( 'Save', 'fusion-builder' ); ?></span>
							<span class="success-icon"><i class="fusiona-check" aria-hidden="true"></i></span>
							<span class="failed-icon"><i class="fusiona-exclamation-triangle" aria-hidden="true"></i></span>
						</a>
					</li>
				</ul>
			</li>

			<li class="additional-tools">
				<ul>
					<li class="toolbar-toggle">
						<a href="#" aria-label="<?php esc_attr_e( 'Toggle Toolbar', 'fusion-builder' ); ?>">
							<span class="screen-reader-text"><?php esc_html_e( 'Toggle Toolbar', 'fusion-builder' ); ?></span>
							<span class="up">
								<i class="fusiona-arrow-up-alt" aria-hidden="true"></i>
							</span>
							<span class="down">
								<i class="fusiona-arrow-down-alt" aria-hidden="true"></i>
							</span>
						</a>
					</li>
					<li class="support has-submenu">
						<a href="#" class="fusion-builder-support trigger-submenu-toggling has-tooltip" aria-label="<?php esc_attr_e( 'Support', 'fusion-builder' ); ?>">
							<i class="fusiona-question-circle" aria-hidden="true"></i>
						</a>
						<ul class="submenu-trigger-target" aria-expanded="false">
							<li>
								<a href="https://theme-fusion.com/support/starter-guide/" target="_blank">
									<span class="icon-big"><i class="fusiona-play-circle" aria-hidden="true"></i></span>
									<span class="label"><?php esc_html_e( 'Get Started', 'fusion-builder' ); ?></span>
								</a>
							</li>
							<li>
								<a href="https://theme-fusion.com/support/" target="_blank">
									<span class="icon-big"><i class="fusiona-file-alt-solid" aria-hidden="true"></i></span>
									<span class="label"><?php esc_html_e( 'Help Center', 'fusion-builder' ); ?></span>
								</a>
							</li>
							<li>
								<a href="#" class="fusion-builder-keyboard-shortcuts">
									<span class="icon-big"><i class="fusiona-keyboard" aria-hidden="true"></i></span>
									<span class="label"><?php esc_html_e( 'Shortcuts', 'fusion-builder' ); ?></span>
								</a>
							</li>
						</ul>
					</li>
					<li class="fusion-builder-preview-viewport has-submenu">
						<a class="viewport-indicator trigger-submenu-toggling has-tooltip" aria-label="<?php esc_attr_e( 'Responsive', 'fusion-builder' ); ?>">
							<span class="active" data-indicate-viewport="desktop"><i class="fusiona-desktop" aria-hidden="true"></i></span>
							<span class="portrait" data-indicate-viewport="tablet-portrait-custom"><i class="fusiona-tablet" aria-hidden="true"></i></span>
							<span class="portrait" data-indicate-viewport="mobile-portrait-custom"><i class="fusiona-mobile" aria-hidden="true"></i></span>
						</a>
						<ul class="submenu-trigger-target" aria-expanded="false">
							<li>
								<a href="#" class="toggle-viewport fusion-builder-preview-mobile portrait" data-viewport="mobile-portrait-custom" aria-label="<?php esc_attr_e( 'Preview Mobile - Portrait Mode', 'fusion-builder' ); ?>"><i class="fusiona-mobile" aria-hidden="true"></i></a>
							</li>
							<li>
								<a href="#" class="toggle-viewport fusion-builder-preview-tablet portrait" data-viewport="tablet-portrait-custom" aria-label="<?php esc_attr_e( 'Preview Tablet - Portrait Mode', 'fusion-builder' ); ?>"><i class="fusiona-tablet" aria-hidden="true"></i></a>
							</li>
							<li>
								<a href="#" class="toggle-viewport fusion-builder-preview-desktop" data-viewport="desktop" aria-label=<?php esc_attr_e( 'Preview Desktop', 'fusion-builder' ); ?>><i class="fusiona-desktop" aria-hidden="true"></i></a>
							</li>
						</ul>
					</li>

					<li class="preview">
						<a href="#" class="has-tooltip" aria-label="<?php esc_attr_e( 'Preview', 'fusion-builder' ); ?>">
							<span class="on"><i class="fusiona-eye" aria-hidden="true"></i></span>
							<span class="off"><i class="fusiona-eye-slash" aria-hidden="true"></i></span>
						</a>
					</li>
				</ul>
			</li>
			<# if ( switcher && _.size( switcher ) ) {
				activeId    = 'undefined' !== typeof FusionApp.data.language ? FusionApp.data.language : false;
				activeData  = switcher[ activeId ];
				activeFlag  = '<?php esc_html_e( 'Select Language', 'fusion-builder' ); ?>';
				activeLabel = '<?php esc_html_e( 'Default', 'fusion-builder' ); ?>';

				if ( activeId ) {
					activeFlag  = FusionApp.toolbarView.getLanguageFlag( activeData, activeId );
					activeLabel = FusionApp.toolbarView.getLanguageLabel( activeData, activeId ).toUpperCase();;
				}
				#>
				<li class="fusion-language-switcher has-submenu">
					<a href="#" class="trigger-submenu-toggling" data-language="{{ activeId }}">{{{ activeFlag }}}
						<span>
							<p><b>{{activeLabel}}</b></p>
							<p><?php echo esc_html_e( 'Switch Language', 'fusion-builder' ); ?></p>
						</span>
					</a>
					<ul class="fusion-language-switcher-dropdown submenu-trigger-target" aria-expanded="false">
						<# _.each( switcher, function( language, languageCode ) {
							if ( languageCode !== activeId ) {
								languageFlag  = FusionApp.toolbarView.getLanguageFlag( language, languageCode );
								languageLabel = FusionApp.toolbarView.getLanguageLabel( language, languageCode );
								languageLink  = FusionApp.toolbarView.getLanguageLink( language, languageCode ); #>
								<li data-language="{{ languageCode }}" data-link="{{ languageLink }}">{{{languageFlag}}} {{{ languageLabel }}}</li>
							<# }
						} ); #>
					</ul>
				</li>
			<# } #>
		</ul>
	</div>
	<div id="fusion-builder-confirmation-modal-dark-overlay"></div>
	<div id="fusion-builder-confirmation-modal" style="display:none;">
		<div class="inner">
			<span class="icon"></span>
			<h3 class="title"></h3>
			<span class="content"></span>
		</div>
		<div class="actions"></div>
	</div>
</script>
