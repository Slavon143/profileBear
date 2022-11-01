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
<div class="wrap" id="pwbe-main">
    <div class="pwbe-title-container">
        <span class="pwbe-title">PW WooCommerce Bulk Edit </span>
        <span class="pwbe-version">v<?php echo $version; ?></span>

        <div>
            by <a href="https://www.pimwick.com" target="_blank" class="pwbe-link">Pimwick, LLC</a>
        </div>
    </div>

	<?php require( 'dropdown-templates.php' ); ?>

	<?php require( 'filters.php' ); ?>

	<div id="pwbe-message"></div>

	<?php require( 'processing.php' ); ?>

	<div id="pwbe-results">
		<img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/results-header.png'; ?>" height="176" width="800">
	</div>
</div>
