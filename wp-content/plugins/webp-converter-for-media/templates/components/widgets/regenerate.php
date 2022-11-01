<?php
/**
 * Widget displayed on plugin settings page.
 *
 * @var string     $api_paths_url      URL of REST API endpoint.
 * @var string     $api_regenerate_url URL of REST API endpoint.
 * @var string     $api_stats_url      URL of REST API endpoint.
 * @var string[][] $output_formats     Data about output formats for regeneration.
 *
 * @package Converter for Media
 */

?>
<div class="webpcPage__widget">
	<h3 class="webpcPage__widgetTitle">
		<?php echo esc_html( __( 'Bulk Optimization of Images', 'webp-converter-for-media' ) ); ?>
	</h3>
	<div class="webpcLoader webpcContent"
		data-api-paths="<?php echo esc_url( $api_paths_url ); ?>"
		data-api-regenerate="<?php echo esc_url( $api_regenerate_url ); ?>"
		data-api-error-message="<?php echo esc_html( __( 'An error occurred while connecting to REST API. Please try again.', 'webp-converter-for-media' ) ); ?>"
	>
		<div class="webpcPage__widgetRow">
			<p>
				<?php
				echo wp_kses_post( __( 'Optimize all your images with just one click!', 'webp-converter-for-media' ) );
				echo ' ';
				echo wp_kses_post(
					sprintf(
					/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
						__( '%1$sClick here%2$s to learn more about how our plugin works.', 'webp-converter-for-media' ),
						'<a href="https://url.mattplugins.com/converter-regeneration-widget-tutorial-link" target="_blank">',
						'</a>'
					)
				);
				?>
			</p>
		</div>
		<div class="webpcPage__widgetRow">
			<div class="webpcTree">
				<p class="webpcTree__headline">
					<?php echo wp_kses_post( __( 'List of files that can be optimized:', 'webp-converter-for-media' ) ); ?>
				</p>
				<div class="webpcTree__output" data-tree>
					<p class="webpcContent__loader">
						<?php echo wp_kses_post( sprintf( __( 'Loading, please wait', 'webp-converter-for-media' ) ) ); ?>
					</p>
					<div class="webpcTree__error" data-api-stats-error hidden></div>
				</div>
			</div>
		</div>
		<div class="webpcPage__widgetRow">
			<div class="webpcPage__widgetNotice">
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
						/* translators: %1$s: open strong tag, %2$s: close strong tag */
							__( 'Converting images to WebP and AVIF simultaneously guarantees the lowest weight of your images and compatibility with all browsers. By using the AVIF format you will reduce the weight of the images even more compared to WebP.', 'webp-converter-for-media' ),
							'<strong>',
							'</strong>'
						)
					);
					?>
				</p>
			</div>
		</div>
		<div class="webpcPage__widgetRow">
			<div class="webpcLoader__columns">
				<?php foreach ( $output_formats as $format_key => $format_data ) : ?>
					<div class="webpcLoader__column"
						data-counter="<?php echo esc_attr( $format_key ); ?>">
						<div class="webpcLoader__columnInner">
							<svg class="webpcLoader__columnCircle" viewBox="0 0 200 200"
								preserveAspectRatio="xMinYMin meet">
								<g>
									<circle cx="50%" cy="50%" r="95" />
								</g>
							</svg>
							<div class="webpcLoader__columnOverlay">
								<div class="webpcLoader__columnOverlayTitle">
									<?php
									echo wp_kses_post(
										sprintf(
										/* translators: %1$s: percent value, %2$s: output format */
											__( '%1$s converted to %2$s', 'webp-converter-for-media' ),
											'<strong><span data-counter-percent>0</span>%</strong>',
											esc_html( $format_data['label'] )
										)
									);
									?>
								</div>
								<div class="webpcLoader__columnOverlayDesc webpcLoader__columnOverlayDesc--active">
									<?php
									echo wp_kses_post(
										sprintf(
										/* translators: %s: images count */
											__( '%s images remaining', 'webp-converter-for-media' ),
											'<span data-counter-left>0</span>'
										)
									);
									?>
								</div>
								<div class="webpcLoader__columnOverlayDesc webpcLoader__columnOverlayDesc--loading">
									<?php
									echo wp_kses_post(
										sprintf(
										/* translators: %s: break line tag */
											__( 'Calculating, %splease wait', 'webp-converter-for-media' ),
											'<br>'
										)
									);
									?>
								</div>
							</div>
						</div>
						<?php if ( $format_data['desc'] ) : ?>
							<div class="webpcLoader__columnDesc">
								<?php echo wp_kses_post( $format_data['desc'] ); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="webpcPage__widgetRow">
			<div class="webpcField">
				<input type="checkbox"
					name="regenerate_force"
					value="1"
					id="webpc-regenerate-force"
					class="webpcField__input webpcField__input--toggle">
				<label for="webpc-regenerate-force"></label>
				<span class="webpcField__label">
					<?php echo esc_html( __( 'Force the conversion of all images again', 'webp-converter-for-media' ) ); ?>
				</span>
			</div>
			<button type="button"
				class="webpcLoader__button webpcButton webpcButton--blue webpcButton--bg"
				<?php echo ( apply_filters( 'webpc_server_errors', [], true ) ) ? 'disabled' : ''; ?>
				data-submit>
				<?php echo esc_html( __( 'Start Bulk Optimization', 'webp-converter-for-media' ) ); ?>
			</button>
			<div class="webpcLoader__status" data-status hidden>
				<div class="webpcLoader__statusContent webpcLoader__statusContent--small">
					<?php echo wp_kses_post( __( 'This is a process that can take anywhere from several minutes to many hours, depending on the number of files. During this process, please do not close your browser window.', 'webp-converter-for-media' ) ); ?>
				</div>
				<div class="webpcLoader__statusProgress" data-status-progress data-percent="0">
					<div class="webpcLoader__statusProgressCount"></div>
				</div>
				<div class="webpcLoader__statusContent">
					<?php
					echo sprintf(
					/* translators: %s progress value */
						wp_kses_post( __( 'Saving the weight of your images: %s', 'webp-converter-for-media' ) ),
						'<strong data-status-count-size>0 kB</strong>'
					);
					?>
					<br>
					<?php
					echo sprintf(
					/* translators: %s images count */
						wp_kses_post( __( 'Successfully converted files: %s', 'webp-converter-for-media' ) ),
						'<strong data-status-count-success>0</strong>'
					);
					?>
					<br>
					<?php
					echo sprintf(
					/* translators: %s images count */
						wp_kses_post( __( 'Failed or skipped file conversion attempts: %s', 'webp-converter-for-media' ) ),
						'<strong data-status-count-error>0</strong>'
					);
					?>
				</div>
			</div>
			<div class="webpcLoader__success" data-success hidden>
				<div class="webpcLoader__successInner">
					<div class="webpcLoader__successContent">
						<?php echo wp_kses_post( __( 'The process was completed successfully. Your images have been converted!', 'webp-converter-for-media' ) ); ?>
						<?php echo wp_kses_post( __( 'Please flush cache if you use caching plugin or caching via hosting.', 'webp-converter-for-media' ) ); ?>
						<br>
						<?php
						echo wp_kses_post(
							sprintf(
							/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
								__( 'Do you want to know how a plugin works and how to check if it is working properly? Read %1$sour manual%2$s.', 'webp-converter-for-media' ),
								'<a href="https://url.mattplugins.com/converter-regeneration-success-message-instruction" target="_blank">',
								'</a>'
							)
						);
						?>
					</div>
				</div>
			</div>
			<div class="webpcLoader__errors" data-errors hidden>
				<div class="webpcLoader__errorsInner">
					<div class="webpcLoader__errorsTitle">
						<?php echo esc_html( __( 'Additional informations about process:', 'webp-converter-for-media' ) ); ?>
					</div>
					<div class="webpcLoader__errorsContent" data-errors-output></div>
				</div>
			</div>
		</div>
	</div>
</div>
