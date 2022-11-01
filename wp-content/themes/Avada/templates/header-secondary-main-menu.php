<?php
/**
 * Template for the secondary menu in header.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
	<div class="fusion-secondary-main-menu">
		<div class="fusion-row">
			<?php avada_main_menu(); ?>
			<?php avada_mobile_menu_search(); ?>
		</div>
	</div>
</div> <!-- end fusion sticky header wrapper -->
