<?php
/**
 * Comments template.
 *
 * @package Avada Builder
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$defaults = get_query_var( 'fusion_tb_comments_args' );

do_action( 'fusion_before_comments' );

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */

if ( post_password_required() ) {
	return;
}
?>

<?php if ( have_comments() ) : ?>

	<div id="comments" class="comments-container">
	<?php if ( 'show' === $defaults['headings'] ) : ?>
		<?php ob_start(); ?>
		<?php comments_number( esc_html__( 'No Comments', 'fusion-builder' ), esc_html__( 'One Comment', 'fusion-builder' ), esc_html( _n( '% Comment', '% Comments', get_comments_number(), 'fusion-builder' ) ) ); ?>
		<?php echo fusion_render_title( $defaults['heading_size'], ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>

		<ol class="comment-list commentlist">
			<?php wp_list_comments( 'callback=fusion_comment' ); ?>
		</ol><!-- .comment-list -->

		<?php
		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) {
			echo '<nav class="fusion-pagination">';
			paginate_comments_links(
				apply_filters(
					'fusion_comment_pagination_args',
					[
						'prev_text' => '<span class="page-prev"></span><span class="page-text">' . esc_attr__( 'Previous', 'fusion-builder' ) . '</span>',
						'next_text' => '<span class="page-text">' . esc_attr__( 'Next', 'fusion-builder' ) . '</span><span class="page-next"></span>',
						'type'      => 'plain',
					]
				)
			);
			echo '</nav>';
		}
		?>
	</div>

<?php endif; ?>

<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
	<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'fusion-builder' ); ?></p>
<?php endif; ?>

<?php if ( comments_open() ) : ?>
	<?php
	$commenter = wp_get_current_commenter();
	$req       = get_option( 'require_name_email' );
	$aria_req  = ( $req ) ? ' aria-required="true"' : '';
	$html_req  = ( $req ) ? ' required="required"' : '';
	$name      = ( $req ) ? __( 'Name (required)', 'fusion-builder' ) : __( 'Name', 'fusion-builder' );
	$email     = ( $req ) ? __( 'Email (required)', 'fusion-builder' ) : __( 'Email', 'fusion-builder' );
	$html5     = ( 'html5' === current_theme_supports( 'html5', 'comment-form' ) ) ? 'html5' : 'xhtml';
	$consent   = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';

	$fields = [];

	$fields['author']  = '<div id="comment-input"><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="' . esc_attr( $name ) . '" size="30"' . $aria_req . $html_req . ' aria-label="' . esc_attr( $name ) . '"/>';
	$fields['email']   = '<input id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_email'] ) . '" placeholder="' . esc_attr( $email ) . '" size="30" ' . $aria_req . $html_req . ' aria-label="' . esc_attr( $email ) . '"/>';
	$fields['url']     = '<input id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" placeholder="' . esc_html__( 'Website', 'fusion-builder' ) . '" size="30" aria-label="' . esc_attr__( 'URL', 'fusion-builder' ) . '" /></div>';
	$fields['cookies'] = '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . $consent . ' /><label for="wp-comment-cookies-consent">' . esc_html__( 'Save my name, email, and website in this browser for the next time I comment.', 'fusion-builder' ) . '</label></p>';

	$comments_args = [
		'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
		'comment_field'        => '<div id="comment-textarea"><label class="screen-reader-text" for="comment">' . esc_attr__( 'Comment', 'fusion-builder' ) . '</label><textarea name="comment" id="comment" cols="45" rows="8" aria-required="true" required="required" tabindex="0" class="textarea-comment" placeholder="' . esc_html__( 'Comment...', 'fusion-builder' ) . '"></textarea></div>',
		'title_reply'          => '',
		'title_reply_to'       => esc_html__( 'Leave A Comment', 'fusion-builder' ),
		'title_reply_before'   => '',
		'title_reply_after'    => '',
		/* translators: Opening and closing link tags. */
		'must_log_in'          => '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a comment.', 'fusion-builder' ), '<a href="' . wp_login_url( apply_filters( 'the_permalink', get_permalink() ) ) . '">', '</a>' ) . '</p>',
		/* translators: %1$s: The username. %2$s and %3$s: Opening and closing link tags. */
		'logged_in_as'         => '<p class="logged-in-as">' . sprintf( esc_html__( 'Logged in as %1$s. %2$sLog out &raquo;%3$s', 'fusion-builder' ), '<a href="' . admin_url( 'profile.php' ) . '">' . $user_identity . '</a>', '<a href="' . wp_logout_url( apply_filters( 'the_permalink', get_permalink() ) ) . '" title="' . esc_html__( 'Log out of this account', 'fusion-builder' ) . '">', '</a>' ) . '</p>',
		'comment_notes_before' => '',
		'id_submit'            => 'comment-submit',
		'class_submit'         => 'fusion-button fusion-button-default fusion-button-default-size',
		'label_submit'         => esc_html__( 'Post Comment', 'fusion-builder' ),
	];

	if ( 'show' === $defaults['headings'] ) {

		$size_array = [
			'1' => 'one',
			'2' => 'two',
			'3' => 'three',
			'4' => 'four',
			'5' => 'five',
			'6' => 'six',
		];

		$comments_args['title_reply']        = esc_html__( 'Leave A Comment', 'fusion-builder' );
		$comments_args['title_reply_before'] = '<div class="fusion-title fusion-title-size-' . $size_array[ $defaults['heading_size'] ] . '"><h' . esc_html( $defaults['heading_size'] ) . ' id="reply-title" class="comment-reply-title">';
		$comments_args['title_reply_after']  = '</h' . esc_html( $defaults['heading_size'] ) . '></div>';
	}

	comment_form( $comments_args );

endif;
