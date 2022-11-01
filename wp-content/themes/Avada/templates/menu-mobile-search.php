<?php
/**
 * Mobile Menu Search template.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.6
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>

<div class="fusion-clearfix"></div>
<div class="fusion-mobile-menu-search">
	<?php get_search_form( true ); ?>
</div>
