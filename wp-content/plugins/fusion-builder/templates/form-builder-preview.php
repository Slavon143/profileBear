<?php
/**
 * Form builder preview template.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<!DOCTYPE html>
<html class="<?php avada_the_html_class(); ?>" <?php language_attributes(); ?>>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<?php Avada()->head->the_viewport(); ?>
	<?php Fusion::get_instance()->dynamic_js->init(); ?>

	<?php wp_head(); ?>

	<?php
	/**
	 * The setting below is not sanitized.
	 * In order to be able to take advantage of this,
	 * a user would have to gain access to the database
	 * in which case this is the least of your worries.
	 */
	echo apply_filters( 'avada_space_head', Avada()->settings->get( 'space_head' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	?>
</head>

<body <?php body_class(); ?> <?php fusion_element_attributes( 'body' ); ?>>

	<div id="boxed-wrapper">
		<div class="fusion-sides-frame"></div>
		<div id="wrapper" class="fusion-wrapper">
			<div id="home" style="position:relative;top:-1px;"></div>
			<?php
				do_action( 'avada_before_main_container' );
			?>
			<main id="main" class="clearfix">
				<div class="fusion-row">

				<?php
				// Handle preview for either Draft elements or Published ones.
				wp_verify_nonce( 'preview_nonce' );
				if ( isset( $_GET['preview'] ) ) {
					$form_id      = false;
					$content      = '';
					$form_preview = false;
					$args         = [];
					// Post has published state.
					if ( isset( $_GET['preview_id'] ) ) {
						$form_id      = sanitize_text_field( wp_unslash( $_GET['preview_id'] ) );
						$form_preview = wp_get_post_autosave( $form_id );
						$form_preview = sanitize_post( $form_preview );
						$content      = $form_preview->post_content;
						$args         = [
							'form_post_id' => $form_id,
							'use_content'  => true,
						];
						// Post has draft state.
					} elseif ( isset( $_GET['p'] ) ) {
						$form_id = sanitize_text_field( wp_unslash( $_GET['p'] ) );
						$args    = [
							'form_post_id' => $form_id,
						];
					}
					// Call Fusion Form shortcode directly.
					$shortcode_content = call_user_func( $shortcode_tags['fusion_form'], $args, $content );
					?>
						<style type="text/css">
						.fusion-button.form-form-submit {
							pointer-events: none;
						}
						</style>
						<div id="fusion-form-preview">
							<?php echo $shortcode_content; // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
					<?php
					// Handle for form live editor and direct view.
				} else {
					?>
						<section id="content" class="full-width">
						<?php while ( have_posts() ) : ?>
								<?php the_post(); ?>
								<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
									<div class="post-content">
										<?php the_content(); ?>
									</div>
								</div>
							<?php endwhile; ?>
						</section>

					<?php
				}

				?>

				</div>  <!-- fusion-row -->
			</main>  <!-- #main -->
		</div> <!-- wrapper -->
	</div> <!-- #boxed-wrapper -->

	<div class="avada-footer-scripts">
		<?php wp_footer(); ?>
	</div>

</body>
</html>
