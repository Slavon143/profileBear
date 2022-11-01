<?php
/**
 * Comments template.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

do_action( 'avada_before_comments' );

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */

if ( post_password_required() ) {
	return;
}
?>

<?php $title_size = ( false === avada_is_page_title_bar_enabled( get_the_ID() ) ? '2' : '3' ); ?>
<?php if ( have_comments() ) : ?>

	<div id="comments" class="comments-container">
		<?php ob_start(); ?>
		<?php comments_number( esc_html__( 'No Comments', 'Avada' ), esc_html__( 'One Comment', 'Avada' ), esc_html( _n( '% Comment', '% Comments', get_comments_number(), 'Avada' ) ) ); ?>
		<?php Avada()->template->title_template( ob_get_clean(), $title_size ); ?>

		<ol class="comment-list commentlist">
			<?php wp_list_comments( 'callback=avada_comment' ); ?>
		</ol><!-- .comment-list -->

		<?php
		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) {
			echo '<nav class="fusion-pagination">';
			paginate_comments_links(
				apply_filters(
					'fusion_comment_pagination_args',
					[
						'prev_text' => '<span class="page-prev"></span><span class="page-text">' . esc_attr__( 'Previous', 'Avada' ) . '</span>',
						'next_text' => '<span class="page-text">' . esc_attr__( 'Next', 'Avada' ) . '</span><span class="page-next"></span>',
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
	<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'Avada' ); ?></p>
<?php endif; ?>

<?php if ( comments_open() ) : ?>
	<?php
	$commenter = wp_get_current_commenter();
	$req       = get_option( 'require_name_email' );
	$aria_req  = ( $req ) ? ' aria-required="true"' : '';
	$html_req  = ( $req ) ? ' required="required"' : '';
	$name      = ( $req ) ? __( 'Name (required)', 'Avada' ) : __( 'Name', 'Avada' );
	$email     = ( $req ) ? __( 'Email (required)', 'Avada' ) : __( 'Email', 'Avada' );
	$html5     = ( 'html5' === current_theme_supports( 'html5', 'comment-form' ) ) ? 'html5' : 'xhtml';
	$consent   = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';

	$fields = [];

	$fields['author'] = '<div id="comment-input"><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="' . esc_attr( $name ) . '" size="30"' . $aria_req . $html_req . ' aria-label="' . esc_attr( $name ) . '"/>';
	$fields['email']  = '<input id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_email'] ) . '" placeholder="' . esc_attr( $email ) . '" size="30" ' . $aria_req . $html_req . ' aria-label="' . esc_attr( $email ) . '"/>';
	$fields['url']    = '<input id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" placeholder="' . esc_html__( 'Website', 'Avada' ) . '" size="30" aria-label="' . esc_attr__( 'URL', 'Avada' ) . '" /></div>';
	if ( get_option( 'show_comments_cookies_opt_in' ) ) {
		$fields['cookies'] = '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . $consent . ' /><label for="wp-comment-cookies-consent">' . esc_html__( 'Save my name, email, and website in this browser for the next time I comment.', 'Avada' ) . '</label></p>';
	}

	$comments_args = [
		'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
		'comment_field'        => '<div id="comment-textarea"><label class="screen-reader-text" for="comment">' . esc_attr__( 'Comment', 'Avada' ) . '</label><textarea name="comment" id="comment" cols="45" rows="8" aria-required="true" required="required" tabindex="0" class="textarea-comment" placeholder="' . esc_html__( 'Comment...', 'Avada' ) . '"></textarea></div>',
		'title_reply'          => esc_html__( 'Leave A Comment', 'Avada' ),
		'title_reply_to'       => esc_html__( 'Leave A Comment', 'Avada' ),
		'title_reply_before'   => '<h' . esc_html( $title_size ) . ' id="reply-title" class="comment-reply-title">',
		'title_reply_after'    => '</h' . esc_html( $title_size ) . '>',
		/* translators: Opening and closing link tags. */
		'must_log_in'          => '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a comment.', 'Avada' ), '<a href="' . wp_login_url( apply_filters( 'the_permalink', get_permalink() ) ) . '">', '</a>' ) . '</p>',
		/* translators: %1$s: The username. %2$s and %3$s: Opening and closing link tags. */
		'logged_in_as'         => '<p class="logged-in-as">' . sprintf( esc_html__( 'Logged in as %1$s. %2$sLog out &raquo;%3$s', 'Avada' ), '<a href="' . admin_url( 'profile.php' ) . '">' . $user_identity . '</a>', '<a href="' . wp_logout_url( apply_filters( 'the_permalink', get_permalink() ) ) . '" title="' . esc_html__( 'Log out of this account', 'Avada' ) . '">', '</a>' ) . '</p>',
		'comment_notes_before' => '',
		'id_submit'            => 'comment-submit',
		'class_submit'         => 'fusion-button fusion-button-default fusion-button-default-size',
		'label_submit'         => esc_html__( 'Post Comment', 'Avada' ),
	];

	comment_form( $comments_args );

endif;
