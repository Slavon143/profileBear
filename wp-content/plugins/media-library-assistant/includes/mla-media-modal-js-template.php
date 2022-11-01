<?php
/**
 * Backbone/JavaScript template for Media Library Assistant Media Manager enhancements
 *
 * @package Media Library Assistant
 * @since 1.80
 */

/**
 * Harmless declaration to suppress phpDocumentor "No page-level DocBlock" error
 *
 * @global $post
 */
global $post;

$supported_taxonomies = MLACore::mla_supported_taxonomies('support');
if ( empty( $supported_taxonomies ) ) {
	$terms_style = 'style="display: none;"';
} else {
	$terms_style = 'style="display: inline;"';
}
?>
<script type="text/html" id="tmpl-mla-search-box">
		<label class="screen-reader-text" for="mla-media-search-input"><?php esc_html_e( 'Search Media', 'media-library-assistant' ); ?>:</label>
	    <input name="s[mla_search_value]" class="search mla-media-search-input" id="mla-media-search-input" type="search" value="{{ data.searchValue }}" placeholder="{{ data.searchBoxPlaceholder }}" />
	<input name="mla_search_submit" class="button media-button button-large mla-search-submit-button" id="mla-search-submit" type="submit" value="<?php esc_attr_e( 'Search', 'media-library-assistant' ); ?>"  /><br>
    <ul class="mla-search-options" style="{{ data.searchBoxControlsStyle }}">
        <li>
            <input type="radio" name="s[mla_search_connector]" value="AND" <# if ( 'OR' !== data.searchConnector ) { #>checked="checked"<# } #> />
            <?php esc_html_e( 'and', 'media-library-assistant' ); ?>
        </li>
        <li>
            <input type="radio" name="s[mla_search_connector]" value="OR" <# if ( 'OR' === data.searchConnector ) { #>checked="checked"<# } #> />
            <?php esc_html_e( 'or', 'media-library-assistant' ); ?>
        </li>
        <li>
            <input type="checkbox" name="s[mla_search_title]" id="search-title" value="title" <# if ( -1 != data.searchFields.indexOf( 'title' ) ) { #>checked<# } #> />
            <?php esc_html_e( 'Title', 'media-library-assistant' ); ?>
        </li>
        <li>
            <input type="checkbox" name="s[mla_search_name]" id="search-name" value="name" <# if ( -1 != data.searchFields.indexOf( 'name' ) ) { #>checked<# } #> />
            <?php esc_html_e( 'Name', 'media-library-assistant' ); ?>
        </li>
        <li>
            <input type="checkbox" name="s[mla_search_alt_text]" id="search-alt-text" value="alt-text" <# if ( -1 != data.searchFields.indexOf( 'alt-text' ) ) { #>checked<# } #> />
            <?php esc_html_e( 'ALT Text', 'media-library-assistant' ); ?>
        </li>
		<br style="clear: both">
        <li>
            <input type="checkbox" name="s[mla_search_excerpt]" id="search-excerpt" value="excerpt" <# if ( -1 != data.searchFields.indexOf( 'excerpt' ) ) { #>checked<# } #> />
            <?php esc_html_e( 'Caption', 'media-library-assistant' ); ?>
        </li>
        <li>
            <input type="checkbox" name="s[mla_search_content]" id="search-content" value="content" <# if ( -1 != data.searchFields.indexOf( 'content' ) ) { #>checked<# } #> />
            <?php esc_html_e( 'Description', 'media-library-assistant' ); ?>
        </li>
        <li>
            <input type="checkbox" name="s[mla_search_file]" id="search-file" value="file" <# if ( -1 != data.searchFields.indexOf( 'file' ) ) { #>checked<# } #> />
            <?php esc_html_e( 'File', 'media-library-assistant' ); ?>
        </li>
		<span <?php echo $terms_style; // phpcs:ignore ?>>
        <li>
            <input type="checkbox" name="s[mla_search_terms]" id="search-terms" value="terms" <# if ( -1 != data.searchFields.indexOf( 'terms' ) ) { #>checked<# } #> />
            <?php esc_html_e( 'Terms', 'media-library-assistant' ); ?>
        </li>
		</span>
    </ul>
</script>
<script type="text/html" id="tmpl-mla-terms-search-button">
	<input name="mla_terms_search" id="mla-terms-search" class="button media-button button-large mla-terms-search-button" type="button" value="<?php esc_attr_e( 'Terms Search', 'media-library-assistant' ); ?>"  />
</script>
<script type="text/html" id="tmpl-mla-simulate-search-button">
	<input style="display:none" type="button" name="mla_search_submit" id="mla-search-submit" class="button" value="<?php esc_attr_e( 'Search', 'media-library-assistant' ); ?>"  />
</script>