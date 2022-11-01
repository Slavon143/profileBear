<?php
/**
 * Details template.
 *
 * @package Avada Core
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$terms_skills     = get_the_term_list( $post->ID, 'portfolio_skills', '', '<br />', '' );
$terms_category   = get_the_term_list( $post->ID, 'portfolio_category', '', '<br />', '' );
$terms_tags       = get_the_term_list( $post->ID, 'portfolio_tags', '', '<br />', '' );
$project_url      = fusion_data()->post_meta( $post->ID )->get( 'project_url' );
$project_url_text = fusion_data()->post_meta( $post->ID )->get( 'project_url_text' );
$copy_url         = fusion_data()->post_meta( $post->ID )->get( 'copy_url' );
$copy_url_text    = fusion_data()->post_meta( $post->ID )->get( 'copy_url_text' );
$portfolio_author = $this->args['author'];
$link_target      = 'yes' === Avada()->settings->get( 'portfolio_link_icon_target' );
?>
<div class="project-info-element">
	<div class="project-info">
		<?php if ( 'yes' === $this->args['heading_enable'] ) : ?>
			<?php echo fusion_render_title( $this->args['heading_size'], apply_filters( 'fusion_portfolio_post_project_description_label', esc_html__( 'Project Details', 'fusion-core' ), $this->post_type ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php endif; ?>

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

		<?php if ( $project_url && $project_url_text ) : ?>
			<div class="project-info-box">
				<?php
				$project_project_url_title = esc_html__( 'Project URL:', 'fusion-core' );
				$project_project_url_tag   = 'h4';
				echo apply_filters( 'fusion_portfolio_post_project_url_label', '<' . $project_project_url_tag . '>' . $project_project_url_title . '</' . $project_project_url_tag . '>', $project_project_url_title, $project_project_url_tag ); // phpcs:ignore WordPress.Security
				?>
				<span><a href="<?php echo esc_url_raw( $project_url ); ?>"<?php echo $link_target ? 'target="_blank" rel="noopener noreferrer"' : ''; // phpcs:ignore WordPress.Security ?>><?php echo $project_url_text; ?></a></span>
			</div>
		<?php endif; ?>

		<?php if ( $copy_url && $copy_url_text ) : ?>
			<div class="project-info-box">
				<?php
				$project_copyright_title = esc_html__( 'Copyright:', 'fusion-core' );
				$project_copyright_tag   = 'h4';
				echo apply_filters( 'fusion_portfolio_post_copyright_label', '<' . $project_copyright_tag . '>' . $project_copyright_title . '</' . $project_copyright_tag . '>', $project_copyright_title, $project_copyright_tag ); // phpcs:ignore WordPress.Security
				?>
				<span><a href="<?php echo esc_url_raw( $copy_url ); ?>"<?php echo $link_target ? 'target="_blank" rel="noopener noreferrer"' : ''; // phpcs:ignore WordPress.Security ?>><?php echo $copy_url_text; ?></a></span>
			</div>
		<?php endif; ?>

		<?php if ( 'yes' === $portfolio_author ) : ?>
			<div class="project-info-box<?php echo ( Avada()->settings->get( 'disable_date_rich_snippet_pages' ) && Avada()->settings->get( 'disable_rich_snippet_author' ) ) ? ' vcard' : ''; ?>">
				<?php
				$project_author_title = esc_html__( 'By:', 'fusion-core' );
				$project_author_tag   = 'h4';
				echo apply_filters( 'fusion_portfolio_post_author_label', '<' . $project_author_tag . '>' . $project_author_title . '</' . $project_author_tag . '>', $project_author_title, $project_author_tag ); // phpcs:ignore WordPress.Security
				?>
				<span<?php echo ( Avada()->settings->get( 'disable_date_rich_snippet_pages' ) && Avada()->settings->get( 'disable_rich_snippet_author' ) ) ? ' class="fn"' : ''; ?>><?php the_author_posts_link(); ?></span>
			</div>
		<?php endif; ?>
	</div>
</div>
