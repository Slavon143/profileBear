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
<div class="pwbe-processing pwbe-hidden">
	<p>
		<span class="pwbe-processing-message"><?php _e( 'Processing...', 'pw-bulk-edit' ); ?></span><br />
		<img src="<?php echo plugins_url( 'assets/images/processing.gif', dirname( __FILE__ ) ); ?>" width="220" height="19" />
	</p>
</div>