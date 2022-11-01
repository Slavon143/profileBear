<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_tb_project_details-shortcode">
	<div {{{ _.fusionGetAttributes( wrapperAttr ) }}}>
		<div class="project-info">
			{{{ titleElement }}}
			<#
			// If Query Data is set, use it and continue.  If not, echo HTML.
			if ( 'undefined' !== typeof query_data ) {
				#>
				<# if ( query_data.terms_skills ) { #>
				<div class="project-info-box">
					<?php
					printf(
						/* Translators: Categories list. */
						__( '<h4>Skills Needed:</h4> %s', 'fusion-core' ), // phpcs:ignore WordPress.Security.EscapeOutput
						'<div class="project-terms">{{{ query_data.terms_skills }}}</div>'
					);
					?>
				</div>
				<# } #>

				<# if ( query_data.terms_category ) { #>
					<div class="project-info-box">
						<?php
						printf(
							/* Translators: Categories list. */
							__( '<h4>Categories:</h4> %s', 'fusion-core' ), // phpcs:ignore WordPress.Security.EscapeOutput
							'<div class="project-terms">{{{ query_data.terms_category }}}</div>'
						);
						?>
					</div>
				<# } #>

				<# if ( query_data.terms_tags ) { #>
					<div class="project-info-box">
						<?php
						printf(
							/* Translators: List of tags */
							__( '<h4>Tags:</h4> %s', 'fusion-core' ), // phpcs:ignore WordPress.Security.EscapeOutput
							'<div class="project-terms">{{{ query_data.terms_tags }}}</div>'
						);
						?>
					</div>
				<# } #>

				<# if ( query_data.project_url && query_data.project_url_text ) { #>
					<div class="project-info-box">
						<?php
						printf(
							/* Translators: Link. */
							__( '<h4>Project URL:</h4> %s', 'fusion-core' ), // phpcs:ignore WordPress.Security.EscapeOutput
							'<span><a href="{{{ query_data.project_url }}}" target="_blank" rel="noopener noreferrer">{{{ query_data.project_url_text }}}</a></span>'
						);
						?>
					</div>
				<# } #>

				<# if ( query_data.copy_url && query_data.copy_url_text ) { #>
					<div class="project-info-box">
						<?php
						printf(
							/* translators: Link */
							__( '<h4>Copyright:</h4> %s', 'fusion-core' ), // phpcs:ignore WordPress.Security.EscapeOutput
							'<span><a href="{{{query_data.copy_url}}}" target="_blank" rel="noopener noreferrer">{{{ query_data.copy_url_text }}}</a></span>'
						);
						?>
					</div>
				<# } #>

				<# if ( author ) { #>
					<div class="project-info-box vcard">
						<?php
						printf(
							/* translators: The author name. */
							__( '<h4>By:</h4> %s', 'fusion-core' ), // phpcs:ignore WordPress.Security.EscapeOutput
							'{{{ query_data.author }}}'
						);
						?>
					</div>
				<# } #>
			<# } #>
		</div>
	</div>
</script>
