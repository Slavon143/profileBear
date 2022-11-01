<?php
/**
 * Prebuilt Website Tag Selector.
 *
 * @package Avada
 */

?>
<div class="avada-importer-tags-selector">
	<h2><?php esc_html_e( 'Filter Sites', 'Avada' ); ?></h2>
	<input id="avada-demos-search" class="avada-db-demos-search" type="text" placeholder="<?php esc_attr_e( 'Search prebuilt sites', 'Avada' ); ?>"/>
	<ul>
		<li data-tag="all">
			<button class="button avada-db-demos-filter current-filter" data-tag="all">
				<span class="avada-db-demos-filter-text"><?php esc_html_e( 'All Prebuilt Sites', 'Avada' ); ?></span>
				<span class="count">(<?php echo esc_html( count( $demos ) ); ?>)</span>
			</button>
		</li>
		<li data-tag="all">
			<button class="button avada-db-demos-filter avada-db-demos-filter-imported" data-tag="imported" data-count="<?php echo esc_attr( count( $imported_demos_count ) ); ?>">
				<span class="avada-db-demos-filter-text"><?php esc_html_e( 'Imported', 'Avada' ); ?></span>
				<span class="count">(<?php echo esc_html( count( $imported_demos_count ) ); ?>)</span>
			</button>
		</li>

		<?php foreach ( $all_tags as $key => $tag_data ) : ?>
			<li>
				<button class="button avada-db-demos-filter" data-tag="<?php echo esc_attr( $key ); ?>">
					<span class="avada-db-demos-filter-text">
					<?php
					printf(
						/* Translators: Tag name (string) */
						esc_html( $tag_data['name'] )
					);
					?>
					</span>
					<span class="count">(<?php echo esc_html( absint( $tag_data['count'] ) ); ?>)</span>
				</button>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
