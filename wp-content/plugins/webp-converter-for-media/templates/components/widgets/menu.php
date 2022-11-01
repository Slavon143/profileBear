<?php
/**
 * Widget displayed on plugin settings page.
 *
 * @var string[][] $menu_items Tabs on plugin settings page.
 *
 * @package Converter for Media
 */

?>
<div class="webpcMenu">
	<div class="webpcMenu__wrapper">
		<div class="webpcMenu__items">
			<?php foreach ( $menu_items as $menu_item ) : ?>
				<?php if ( $menu_item['url'] !== null ) : ?>
					<div class="webpcMenu__item">
						<a href="<?php echo esc_attr( $menu_item['url'] ); ?>"
							class="webpcMenu__itemLink <?php echo ( $menu_item['is_active'] ) ? 'webpcMenu__itemLink--active' : ''; ?>">
							<?php echo esc_attr( $menu_item['title'] ); ?>
						</a>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>
