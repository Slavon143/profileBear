<?php

/*
Copyright (C) Pimwick, LLC

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php
	if ( get_option( 'pwbe_help_minimize_filter_help' ) != 'true' ) {
		?>
		<div id="pwbe-filters-help-container" class="pwbe-filters-help-container">
			<span class="pwbe-filters-help-header pwbe-pull-left">
				<i class="fa fa-lightbulb-o" aria-hidden="true"></i> <?php _e( 'Tips', 'pw-bulk-edit' ); ?>
			</span>
			<span id="pwbe-filters-help-dismiss" class="pwbe-pull-right">
				<i class="fa fa-close pwbe-link"></i>
			</span>

			<ul class="fa-ul pwbe-filters-help-list">
				<li class="pwbe-filters-help-list-li"><i class="fa-li fa fa-magic fa-fw"></i><?php _e( 'Click any header to bulk edit.', 'pw-bulk-edit' ); ?></li>
				<li class="pwbe-filters-help-list-li"><i class="fa-li fa fa-percent fa-fw"></i><?php _e( 'Use a percent sign as a wildcard in text filters.', 'pw-bulk-edit' ); ?></li>
				<li class="pwbe-filters-help-list-li"><i class="fa-li fa fa-external-link fa-fw"></i><?php _e( 'Click the link icon to view a product in WooCommerce.', 'pw-bulk-edit' ); ?></li>
				<li class="pwbe-filters-help-list-li"><i class="fa-li fa fa-keyboard-o fa-fw fa-pull-left"></i><?php _e( 'Use the Tab or Enter keys while editing to move around without clicking. Shift goes backwards.', 'pw-bulk-edit' ); ?></li>
				<li class="pwbe-filters-help-list-li"><i class="fa-li fa fa-check-square-o fa-fw fa-pull-left"></i><?php _e( 'Hold the Shift key while clicking a checkbox to select a range of products.', 'pw-bulk-edit' ); ?></li>
				<li class="pwbe-filters-help-list-li"><i class="fa-li fa fa-heart fa-fw fa-pull-left"></i><?php _e( 'Love the plugin?', 'pw-bulk-edit' ); ?> <a href="https://wordpress.org/support/plugin/pw-bulk-edit/reviews/" target="_blank"><?php _e( 'Leave a review!', 'pw-bulk-edit' ); ?></a></li>
			</ul>
		</div>
		<?php
	}
?>