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

if ( get_option( 'pwbe_help_dismiss_intro' ) != 'true' ) {
	?>
	<div class="pwbe-intro">
		<p class="pwbe-intro-p">
			<i class="fa fa-rocket fa-3x fa-pull-left fa-fw" style="color: red;" aria-hidden="true"></i>
			<?php _e( 'PW WooCommerce Bulk Edit is a tremendously powerful way to update your WooCommerce product catalog. Finally, no more tedious clicking through countless pages making the same change to all products!', 'pw-bulk-edit' ); ?>
		</p>
		<p class="pwbe-intro-p">
			<i class="fa fa-lightbulb-o fa-3x fa-pull-left fa-fw" style="color: #D9D255;" aria-hidden="true"></i>
			<?php _e( 'Built with ease of use in mind, PW WooCommerce Bulk Edit is incredibly intuitive. Changes are visible and only applied when you are ready. PW WooCommerce Bulk Edit Pro lets you save your filters to make future updates a snap.', 'pw-bulk-edit' ); ?>
		</p>
		<p class="pwbe-intro-p">
			<i class="fa fa-coffee fa-3x fa-pull-left fa-fw" style="color: green;" aria-hidden="true"></i>
			<?php _e( 'Relax! You are in control of your WooCommerce product catalog with the power of PW WooCommerce Bulk Edit.', 'pw-bulk-edit' ); ?>
		</p>
		<button class="button button-primary pwbe-begin-button"><?php _e( 'Let\'s Begin!', 'pw-bulk-edit' ); ?></button>
		<span class="pwbe-pull-right">
			<input type="checkbox" id="pwbe-dismiss-intro" /><label for="pwbe-dismiss-intro"><?php _e( 'Don\'t show this message again', 'pw-bulk-edit' ); ?></label>
		</span>
	</div>
	<?php
}

?>