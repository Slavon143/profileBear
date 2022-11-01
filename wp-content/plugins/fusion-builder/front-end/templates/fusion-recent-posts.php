<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_recent_posts-shortcode">
	{{{ style }}}
	<# // If Query Data is set, use it and continue.  If not, echo HTML. #>
	<# if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.posts ) { #>
		<#
		var count = 1,
			metaData;
		#>
		<div {{{ _.fusionGetAttributes( recentPostsShortcode ) }}}>
			<section {{{ _.fusionGetAttributes( recentPostsShortcodeSection ) }}}>
				<# _.each( query_data.posts, function( post ) { #>
					<div {{{ _.fusionGetAttributes( recentPostsShortcodeColumn ) }}}>

						<# if ( 'date-on-side' === values.layout ) { #>

							<div class="fusion-date-and-formats">
								<div class="fusion-date-box updated">
									<span class="fusion-date">{{{ post.alternate_date_format_day }}}</span>
									<span class="fusion-month-year">{{{ post.alternate_date_format_month_year }}}</span>
								</div>
								<div class="fusion-format-box">

									<# if ( 'gallery' === post.format ) { #>
										<i class="awb-icon-images" aria-hidden="true"></i>
									<# } else if ( 'link' === post.format || 'image' === post.format ) { #>
										<i class="awb-icon-{{ post.format }}" aria-hidden="true"></i>
									<# } else if ( 'quote' === post.format ) { #>
										<i class="awb-icon-quotes-left" aria-hidden="true"></i>
									<# } else if ( 'video' === post.format ) { #>
										<i class="awb-icon-film" aria-hidden="true"></i>
									<# } else if ( 'audio' === post.format ) { #>
										<i class="awb-icon-headphones" aria-hidden="true"></i>
									<# } else if ( 'chat' === post.format ) { #>
										<i class="awb-icon-bubbles" aria-hidden="true"></i>
									<# } else { #>
										<i class="awb-icon-pen" aria-hidden="true"></i>
									<# } #>

								</div>
							</div>

						<# } #>

						<# if ( 'yes' === values.thumbnail  && 'date-on-side' !== values.layout && ! post.password_required && ( post.thumbnail || post.multiple_featured_images ) ) { #>

							<div {{{ _.fusionGetAttributes( recentPostsShortcodeSlideshow ) }}}>
								<ul class="slides">

									<# if ( post.thumbnail || post.video ) { #>
										<# if ( post.video ) { #>
											<li><div class="full-video">{{{ post.video }}}</div></li>
										<# } #>

										<# if ( post.thumbnail ) { #>
											<li>
												<a href="{{{ post.permalink }}}" {{{ _.fusionGetAttributes( recentPostsShortcodeImgLink ) }}}>
													{{{ post.thumbnail[ values.image_size ] }}}
												</a>
											</li>
										<# } #>

										<# _.each( post.multiple_featured_images, function( featured_image ) { #>
											<li>
												<a href="{{{ post.permalink }}}" {{{ _.fusionGetAttributes( recentPostsShortcodeImgLink ) }}}>
													{{{ featured_image[ values.image_size ] }}}
												</a>
											</li>
										<# } ); #>

									<# } #>

								</ul>
							</div>

						<# } #>

						<div class="recent-posts-content">

							<# if ( 'yes' === values.title ) { #>

								{{{ post.rich_snippet.yes }}}
								<{{ titleTag }} <# if ( extras.disable_date_rich_snippet_pages ) { #>class="entry-title"<# } #>>
									<a href="{{{ post.permalink }}}">{{{ post.title }}}</a>
								</{{ titleTag }}>

							<# } else { #>

								{{{ post.rich_snippet.no }}}

							<# } #>

							<# if ( 'yes' === values.meta ) { #>
								<# metaData = _.fusionRenderPostMetadata( 'recent_posts', metaInfoSettings, post.meta_data ); #>

								<p class="meta">
									{{{ metaData }}}
								</p>

							<# } #>

							<# if ( 'yes' === values.excerpt ) { #>
								{{{ _.fusionGetFixedContent( post.content, 'yes', ( 'undefined' !== values.excerpt_length && '' !== values.excerpt_length ) ? values.excerpt_length : values.excerpt_words, ( 'yes' === values.strip_html ) ) }}}
							<# } else if ( 'full' === values.excerpt ) { #>
								{{{ _.fusionGetFixedContent( post.content, 'no', ( 'undefined' !== values.excerpt_length && '' !== values.excerpt_length ) ? values.excerpt_length : values.excerpt_words, ( 'yes' === values.strip_html ) ) }}}
							<# } #>

						</div>
					</div>

					<# count++; #>

				<# } ); #>

			</section>

			<# if ( 'no' !== values.scrolling ) { #>

				{{{ _.fusionPagination( query_data.max_num_pages, query_data.paged, extras.pagination_range_global, values.scrolling, '', extras.pagination_start_end_range_global ) }}}

				<# if ( 'load_more_button' === values.scrolling && 1 < query_data.max_num_pages ) { #>
					<button class="fusion-load-more-button fusion-blog-button fusion-clearfix">
						<?php echo apply_filters( 'avada_load_more_posts_name', esc_attr__( 'Load More Posts', 'fusion-builder' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</button>
				<# } #>
			<# } #>

		</div>

	<# } else if ( 'undefined' !== typeof query_data && 'undefined' !== typeof query_data.placeholder ) { #>

		<# // Query Data and placeholder are set. #>
		{{{ query_data.placeholder }}}

	<# } #>

</script>
