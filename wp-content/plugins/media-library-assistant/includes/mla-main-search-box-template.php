<?php
/**
 * PHP "template" for Media/Assistant submenu table Search Media box
 *
 * @package Media Library Assistant
 * @since 1.90
 */

/**
 * Harmless declaration to suppress phpDocumentor "No page-level DocBlock" error
 *
 * @global $post
 */
global $post;

if ( !empty( $_REQUEST['s'] ) ) {
	$search_value = trim( wp_kses( wp_unslash( $_REQUEST['s'] ), 'post' ) );
	$search_fields = isset ( $_REQUEST['mla_search_fields'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['mla_search_fields'] ) ) : array();
	$search_connector = isset ( $_REQUEST['mla_search_connector'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mla_search_connector'] ) ) : 'AND';
} else {
	$search_value = MLACore::mla_get_option( MLACoreOptions::MLA_SEARCH_MEDIA_FILTER_DEFAULTS );
	$search_fields = $search_value['search_fields'];
	$search_connector = $search_value['search_connector'];
	$search_value = '';
}

if ( 'checked' == MLACore::mla_get_option( MLACoreOptions::MLA_SEARCH_MEDIA_FILTER_SHOW_CONTROLS ) ) {
	$controls_style = 'style="float: left;"';
} else {
	$controls_style = 'style="display: none;"';
}

$supported_taxonomies = MLACore::mla_supported_taxonomies('support');
if ( empty( $supported_taxonomies ) ) {
	$terms_style = 'style="display: none;"';
	unset( $search_fields['terms'] );
} else {
	$terms_style = 'style="display: inline;"';
}
?>
<p class="search-box">
<label class="screen-reader-text" for="mla-media-search-input"><?php esc_html_e( 'Search Media', 'media-library-assistant' ); ?></label>
<input name="s" id="mla-media-search-input" type="search" size="45" value="<?php echo esc_attr( $search_value ) ?>" />
<input name="mla-search-submit" class="button" id="search-submit" type="submit" value="<?php esc_attr_e( 'Search Media', 'media-library-assistant' ); ?>" /><br />
<span <?php echo wp_kses( $controls_style, 'post' ) ?>>
<span id="search-title-span">
<input name="mla_search_fields[]" id="search-title" type="checkbox" <?php echo ( in_array( 'title', $search_fields ) ) ? 'checked="checked"' : ''; ?> value="title" /><?php esc_html_e( 'Title', 'media-library-assistant' )?>&nbsp;</span>
<span id="search-title-span">
<input name="mla_search_fields[]" id="search-name" type="checkbox" <?php echo ( in_array( 'name', $search_fields ) ) ? 'checked="checked"' : ''; ?> value="name" /><?php esc_html_e( 'Name', 'media-library-assistant' )?>&nbsp;</span>
<span id="search-alt-text-span">
<input name="mla_search_fields[]" id="search-alt-text" type="checkbox" <?php echo ( in_array( 'alt-text', $search_fields ) ) ? 'checked="checked"' : ''; ?> value="alt-text" /><?php esc_html_e( 'ALT Text', 'media-library-assistant' )?>&nbsp;</span>
<span id="search-excerpt-span">
<input name="mla_search_fields[]" id="search-excerpt" type="checkbox" <?php echo ( in_array( 'excerpt', $search_fields ) ) ? 'checked="checked"' : ''; ?> value="excerpt" /><?php esc_html_e( 'Caption', 'media-library-assistant' )?>&nbsp;</span>
<span id="search-content-span">
<input name="mla_search_fields[]" id="search-content" type="checkbox" <?php echo ( in_array( 'content', $search_fields ) ) ? 'checked="checked"' : ''; ?> value="content" /><?php esc_html_e( 'Description', 'media-library-assistant' )?>&nbsp;</span>
<span id="search-file-span">
<input name="mla_search_fields[]" id="search-file" type="checkbox" <?php echo ( in_array( 'file', $search_fields ) ) ? 'checked="checked"' : ''; ?> value="file" /><?php esc_html_e( 'File', 'media-library-assistant' )?>&nbsp;</span>
<span id="search-terms-span" <?php echo wp_kses( $terms_style, 'post' ) ?>><input name="mla_search_fields[]" id="terms-search" type="checkbox" <?php echo ( in_array( 'terms', $search_fields ) ) ? 'checked="checked"' : ''; ?> value="terms" /><?php esc_html_e( 'Terms', 'media-library-assistant' )?></span>
<br />
<input name="mla_search_connector" type="radio" <?php echo ( 'OR' === $search_connector ) ? '' : 'checked="checked"'; ?> value="AND" /><?php esc_html_e( 'and', 'media-library-assistant' ); ?>&nbsp;
<input name="mla_search_connector" type="radio" <?php echo ( 'OR' === $search_connector ) ? 'checked="checked"' : ''; ?> value="OR" /><?php esc_html_e( 'or', 'media-library-assistant' ); ?>
</span>
</p>
