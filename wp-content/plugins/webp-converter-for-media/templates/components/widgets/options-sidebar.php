<?php
/**
 * Widget displayed in sidebar on plugin settings page.
 *
 * @var mixed[] $form_sidebar_options     Settings options in sidebar.
 * @var string  $form_input_name          Name of hidden field with form ID.
 * @var string  $form_sidebar_input_value ID of settings form.
 * @var string  $nonce_input_name         Name of hidden field with WordPress Nonce value.
 * @var string  $nonce_input_value        WordPress Nonce value.
 * @var bool    $token_valid_status       Status of PRO version.
 *
 * @package Converter for Media
 */

?>
<div class="webpcPage__widget webpcPage__widget--border">
	<h3 class="webpcPage__widgetTitle webpcPage__widgetTitle--logo">
		<?php echo esc_html( __( 'PRO version', 'webp-converter-for-media' ) ); ?>
	</h3>
	<form method="post" action="" class="webpcContent">
		<input type="hidden" name="<?php echo esc_attr( $form_input_name ); ?>"
			value="<?php echo esc_attr( $form_sidebar_input_value ); ?>">
		<input type="hidden" name="<?php echo esc_attr( $nonce_input_name ); ?>"
			value="<?php echo esc_attr( $nonce_input_value ); ?>">
		<?php foreach ( $form_sidebar_options as $index => $option ) : ?>
			<div class="webpcPage__widgetRow">
				<ul class="webpcPage__widgetColumns">
					<li class="webpcPage__widgetColumn">
						<h4><?php echo esc_html( $option['label'] ); ?></h4>
						<?php include dirname( __DIR__ ) . '/fields/' . $option['type'] . '.php'; ?>
					</li>
					<?php if ( $option['notice_lines'] ) : ?>
						<li class="webpcPage__widgetColumn">
							<div class="webpcPage__widgetNotice">
								<?php foreach ( $option['notice_lines'] as $line ) : ?>
									<p><?php echo wp_kses_post( $line ); ?></p>
								<?php endforeach; ?>
							</div>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		<?php endforeach; ?>
		<div class="webpcPage__widgetRow">
			<?php if ( ! $token_valid_status ) : ?>
				<button type="submit" class="webpcButton webpcButton--blue webpcButton--bg">
					<?php echo esc_html( __( 'Activate Token', 'webp-converter-for-media' ) ); ?>
				</button>
			<?php else : ?>
				<button type="submit" class="webpcButton webpcButton--red">
					<?php echo esc_html( __( 'Deactivate Token', 'webp-converter-for-media' ) ); ?>
				</button>
			<?php endif; ?>
		</div>
	</form>
</div>
