<?php
/**
 * Portfolio Template.
 *
 * @package Avada-Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
if ( ! class_exists( 'Avada' ) ) {
	exit( 'This feature requires the Avada theme.' );
}
?>

<?php get_header(); ?>
<div id="content" <?php Avada()->layout->add_class( 'content_class' ); ?> <?php Avada()->layout->add_style( 'content_style' ); ?>>
	<?php
	$nav_categories = ( isset( $_GET['portfolioCats'] ) ) ? wp_unslash( $_GET['portfolioCats'] ) : ''; // phpcs:ignore WordPress.Security
	?>

	<?php if ( fusion_get_option( 'portfolio_pn_nav' ) ) : ?>
		<div class="single-navigation clearfix">
			<?php
			if ( $nav_categories ) {
				$prev_args = [
					'format'      => '%link',
					'link'        => esc_html__( 'Previous', 'fusion-core' ),
					'in_same_tax' => 'portfolio_category',
					'in_cats'     => $nav_categories,
					'return'      => 'href',
				];
			} else {
				$prev_args = [
					'format' => '%link',
					'link'   => esc_html__( 'Previous', 'fusion-core' ),
					'return' => 'href',
				];
				// PolyLang tweak.
				if ( function_exists( 'pll_default_language' ) ) {
					$prev_args['in_same_tax'] = 'language';
				}
			}
			$previous_post_link = fusion_previous_post_link_plus( apply_filters( 'fusion_builder_portfolio_prev_args', $prev_args ) );
			?>

			<?php if ( $previous_post_link ) : ?>
				<?php if ( $nav_categories ) : ?>
					<?php $previous_post_link = function_exists( 'fusion_add_url_parameter' ) ? fusion_add_url_parameter( $previous_post_link, 'portfolioCats', $nav_categories ) : $previous_post_link; ?>
				<?php endif; ?>
				<a href="<?php echo esc_url_raw( $previous_post_link ); ?>" rel="prev"><?php esc_html_e( 'Previous', 'fusion-core' ); ?></a>
			<?php endif; ?>

			<?php
			if ( $nav_categories ) {
				$next_args = [
					'format'      => '%link',
					'link'        => esc_html__( 'Next', 'fusion-core' ),
					'in_same_tax' => 'portfolio_category',
					'in_cats'     => $nav_categories,
					'return'      => 'href',
				];
			} else {
				$next_args = [
					'format' => '%link',
					'link'   => esc_html__( 'Next', 'fusion-core' ),
					'return' => 'href',
				];
				// PolyLang tweak.
				if ( function_exists( 'pll_default_language' ) ) {
					$next_args['in_same_tax'] = 'language';
				}
			}
			$next_post_link = fusion_next_post_link_plus( apply_filters( 'fusion_builder_portfolio_next_args', $next_args ) );
			?>

			<?php if ( $next_post_link ) : ?>
				<?php if ( $nav_categories ) : ?>
					<?php $next_post_link = fusion_add_url_parameter( $next_post_link, 'portfolioCats', $nav_categories ); ?>
				<?php endif; ?>
				<a href="<?php echo esc_url_raw( $next_post_link ); ?>" rel="next"><?php esc_html_e( 'Next', 'fusion-core' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<?php the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<?php avada_singular_featured_image(); ?>

			<?php
			$portfolio_width          = ( 'half' === fusion_get_option( 'portfolio_featured_image_width', 'width', $post->ID ) ) ? 'half' : 'full';
			$portfolio_width          = ( ! Avada()->settings->get( 'portfolio_featured_images' ) && 'half' === $portfolio_width ) ? 'full' : $portfolio_width;
			$project_desc             = fusion_get_option( 'portfolio_project_desc_title' );
			$project_desc_width_style = ( 'full' === $portfolio_width && ! fusion_get_option( 'portfolio_project_details' ) ) ? ' width:100%;' : '';
			$project_details          = fusion_get_option( 'portfolio_project_details' );
			?>
			<div class="project-content">
				<?php echo fusion_render_rich_snippets_for_pages(); // phpcs:ignore WordPress.Security ?>
				<div class="project-description post-content<?php echo ( $project_details ) ? ' fusion-project-description-details' : ''; ?>" style="<?php echo esc_attr( $project_desc_width_style ); ?>">
					<?php if ( ! post_password_required( $post->ID ) && $project_desc ) : ?>
						<?php
						$project_desc_title = esc_html__( 'Project Description', 'fusion-core' );
						$project_desc_tag   = 'h3';
						echo apply_filters( 'fusion_portfolio_post_project_description_label', '<' . $project_desc_tag . '>' . $project_desc_title . '</' . $project_desc_tag . '>', $project_desc_title, $project_desc_tag, $project_desc_width_style ); // phpcs:ignore WordPress.Security
						?>
					<?php endif; ?>
					<?php the_content(); ?>
					<?php
					if ( function_exists( 'fusion_link_pages' ) ) {
						fusion_link_pages();
					}
					?>
				</div>

				<?php if ( ! post_password_required( $post->ID ) && $project_details ) : ?>
					<div class="project-info">
						<?php do_action( 'fusion_before_portfolio_side_content' ); ?>
						<?php
						$project_details_title = esc_html__( 'Project Details', 'fusion-core' );
						$project_details_tag   = 'h3';
						echo apply_filters( 'fusion_portfolio_post_project_details_label', '<' . $project_details_tag . '>' . $project_details_title . '</' . $project_details_tag . '>', $project_details_title, $project_details_tag ); // phpcs:ignore WordPress.Security
						?>

						<?php $terms_skills = get_the_term_list( $post->ID, 'portfolio_skills', '', '<br />', '' ); ?>
						<?php if ( $terms_skills && ! is_wp_error( $terms_skills ) ) : ?>
							<div class="project-info-box">
								<?php
								$project_skills_title = esc_html__( 'Skills Needed:', 'fusion-core' );
								$project_skills_tag   = 'h4';
								echo apply_filters( 'fusion_portfolio_post_skills_label', '<' . $project_skills_tag . '>' . $project_skills_title . '</' . $project_skills_tag . '>', $project_skills_title, $project_skills_tag ); // phpcs:ignore WordPress.Security
								?>
								<div class="project-terms">
									<?php echo $terms_skills; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
							</div>
						<?php endif; ?>

						<?php $terms_category = get_the_term_list( $post->ID, 'portfolio_category', '', '<br />', '' ); ?>
						<?php if ( $terms_category && ! is_wp_error( $terms_category ) ) : ?>
							<div class="project-info-box">
								<?php
								$project_categories_title = esc_html__( 'Categories:', 'fusion-core' );
								$project_categories_tag   = 'h4';
								echo apply_filters( 'fusion_portfolio_post_categories_label', '<' . $project_categories_tag . '>' . $project_categories_title . '</' . $project_categories_tag . '>', $project_categories_title, $project_categories_tag ); // phpcs:ignore WordPress.Security
								?>
								<div class="project-terms">
									<?php echo $terms_category; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
							</div>
						<?php endif; ?>

						<?php $terms_tags = get_the_term_list( $post->ID, 'portfolio_tags', '', '<br />', '' ); ?>
						<?php if ( $terms_tags && ! is_wp_error( $terms_tags ) ) : ?>
							<div class="project-info-box">
								<?php
								$project_tags_title = esc_html__( 'Tags:', 'fusion-core' );
								$project_tags_tag   = 'h4';
								echo apply_filters( 'fusion_portfolio_post_tags_label', '<' . $project_tags_tag . '>' . $project_tags_title . '</' . $project_tags_tag . '>', $project_tags_title, $project_tags_tag ); // phpcs:ignore WordPress.Security.EscapeOutput
								?>
								<div class="project-terms">
									<?php echo $terms_tags; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
							</div>
						<?php endif; ?>

						<?php
						$project_url      = fusion_get_option( 'project_url' );
						$project_url_text = fusion_get_option( 'project_url_text' );
						?>

						<?php if ( $project_url && $project_url_text ) : ?>
							<?php $link_target = fusion_get_option( 'portfolio_link_icon_target' ) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
							<div class="project-info-box">
								<?php
								$project_project_url_title = esc_html__( 'Project URL:', 'fusion-core' );
								$project_project_url_tag   = 'h4';
								echo apply_filters( 'fusion_portfolio_post_project_url_label', '<' . $project_project_url_tag . '>' . $project_project_url_title . '</' . $project_project_url_tag . '>', $project_project_url_title, $project_project_url_tag ); // phpcs:ignore WordPress.Security
								?>
								<span><a href="<?php echo esc_url_raw( $project_url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security ?>><?php echo $project_url_text; ?></a></span>
							</div>
						<?php endif; ?>

						<?php
						$copy_url      = fusion_get_option( 'copy_url' );
						$copy_url_text = fusion_get_option( 'copy_url_text' );
						?>

						<?php if ( $copy_url && $copy_url_text ) : ?>
							<?php $link_target = fusion_get_option( 'portfolio_link_icon_target' ) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
							<div class="project-info-box">
								<?php
								$project_copyright_title = esc_html__( 'Copyright:', 'fusion-core' );
								$project_copyright_tag   = 'h4';
								echo apply_filters( 'fusion_portfolio_post_copyright_label', '<' . $project_copyright_tag . '>' . $project_copyright_title . '</' . $project_copyright_tag . '>', $project_copyright_title, $project_copyright_tag ); // phpcs:ignore WordPress.Security
								?>
								<span><a href="<?php echo esc_url_raw( $copy_url ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security ?>><?php echo $copy_url_text; ?></a></span>
							</div>
						<?php endif; ?>

						<?php $portfolio_author = fusion_get_option( 'portfolio_author' ); ?>
						<?php if ( ( Avada()->settings->get( 'portfolio_author' ) && 'no' !== $portfolio_author ) || ( ! Avada()->settings->get( 'portfolio_author' ) && 'yes' === $portfolio_author ) ) : ?>
							<div class="project-info-box<?php echo ( Avada()->settings->get( 'disable_date_rich_snippet_pages' ) && Avada()->settings->get( 'disable_rich_snippet_author' ) ) ? ' vcard' : ''; ?>">
								<?php
								$project_author_title = esc_html__( 'By:', 'fusion-core' );
								$project_author_tag   = 'h4';
								echo apply_filters( 'fusion_portfolio_post_author_label', '<' . $project_author_tag . '>' . $project_author_title . '</' . $project_author_tag . '>', $project_author_title, $project_author_tag ); // phpcs:ignore WordPress.Security
								?>
								<span<?php echo ( Avada()->settings->get( 'disable_date_rich_snippet_pages' ) && Avada()->settings->get( 'disable_rich_snippet_author' ) ) ? ' class="fn"' : ''; ?>><?php the_author_posts_link(); ?></span>
							</div>
						<?php endif; ?>
						<?php do_action( 'fusion_after_portfolio_side_content' ); ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="portfolio-sep"></div>
			<?php if ( ! post_password_required( $post->ID ) ) : ?>
				<?php do_action( 'fusion_before_additional_portfolio_content' ); ?>
				<?php avada_render_social_sharing( 'portfolio' ); ?>
				<?php echo avada_render_related_posts( 'avada_portfolio' ); // phpcs:ignore WordPress.Security ?>

				<?php $portfolio_comments = fusion_get_option( 'portfolio_comments' ); ?>
				<?php if ( ( Avada()->settings->get( 'portfolio_comments' ) && 'no' !== $portfolio_comments ) || ( ! Avada()->settings->get( 'portfolio_comments' ) && 'yes' === $portfolio_comments ) ) : ?>
					<?php comments_template(); ?>
				<?php endif; ?>
				<?php do_action( 'fusion_after_additional_portfolio_content' ); ?>
			<?php endif; ?>
		</article>
	<?php endif; ?>
</div>
<?php do_action( 'avada_after_content' ); ?>
<?php
get_footer();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
