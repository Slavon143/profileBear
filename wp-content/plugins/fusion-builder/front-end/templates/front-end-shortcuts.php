<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-front-end-keyboard-shortcuts">
	<div class="fusion-keyboard-shortcuts-wrapper">
		<ul class="fusion-shortcuts-list">
			<# if ( 'undefined' !== typeof FusionApp && 'off' === FusionApp.preferencesData.keyboard_shortcuts ) { #>
				<li class="fusion-front-end-important-notice">
					<div>
						<strong><?php esc_html_e( 'IMPORTANT NOTE:', 'fusion-builder' ); ?></strong>
						<?php
						// phpcs:disable WordPress.Security.EscapeOutput
						printf(
							/* translators: Link attributes. */
							__( 'Keyboard shortcuts are currently disabled in Avada Builder preferences. You can change setting by <a %s>clicking here.</a>', 'fusion-builder' ),
							'href="#" class="fusion-open-prefernces-panel"'
						);
						// phpcs:enable
						?>
					</div>
				</li>
			<# } #>
			<h3><?php esc_html_e( 'Toggle Hotkeys', 'fusion-builder' ); ?></h3>
			<li>
				<label><?php esc_html_e( 'Toggle Sidebar', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>Shift</p> </li>
					<li> + </li>
					<li> <p>T</p> </li>
				</ul>
			</li>

			<li>
				<label><?php esc_html_e( 'Toggle Preview', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>Shift</p> </li>
					<li> + </li>
					<li> <p>P</p> </li>
				</ul>
			</li>
		</ul>

		<ul class="fusion-shortcuts-list">
			<h3><?php esc_html_e( 'Action Hotkeys', 'fusion-builder' ); ?></h3>
			<li>
				<label><?php esc_html_e( 'Save Page', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>S</p> </li>
				</ul>
			</li>

			<li>
				<label><?php esc_html_e( 'Save Template', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>Shift</p> </li>
					<li> + </li>
					<li> <p>S</p> </li>
				</ul>
			</li>

			<li>
				<label><?php esc_html_e( 'Redo', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>Y</p> </li>
				</ul>
			</li>

			<li>
				<label><?php esc_html_e( 'Undo', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>Z</p> </li>
				</ul>
			</li>
			<li>
				<label><?php esc_html_e( 'Desktop View', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>1</p> </li>
				</ul>
			</li>

			<li>
				<label><?php esc_html_e( 'Mobile View Potrait', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>2</p> </li>
				</ul>
			</li>

			<li>
				<label><?php esc_html_e( 'Mobile View Landscape', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>3</p> </li>
				</ul>
			</li>

			<li>
				<label><?php esc_html_e( 'Tablet View Potrait', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>4</p> </li>
				</ul>
			</li>

			<li>
				<label><?php esc_html_e( 'Tablet View Landscape', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>5</p> </li>
				</ul>
			</li>

			<li>
				<label><?php esc_html_e( 'Clear Layout', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>D</p> </li>
				</ul>
			</li>

			<li>
				<label><?php esc_html_e( 'Close Modal', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>Enter</p> </li>
				</ul>
			</li>

			<li>
				<label><?php esc_html_e( 'Custom CSS Panel', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>Shift</p> </li>
					<li> + </li>
					<li> <p>C</p> </li>
				</ul>
			</li>

			<li>
				<label><?php esc_html_e( 'Exit Builder To Back-End', 'fusion-builder' ); ?></label>
				<ul>
					<li> <p>CMD / CTRL</p> </li>
					<li> + </li>
					<li> <p>Q</p> </li>
				</ul>
			</li>
		</ul>
	</div>
</script>
