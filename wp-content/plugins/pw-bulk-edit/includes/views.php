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

if ( ! class_exists( 'PWBE_Views' ) ) :

final class PWBE_Views {

	public static function get() {
		$views = array(
			'pwbeview_all' => array(),
			'pwbeview_default' => array()
		);

		$columns = PWBE_Columns::get();
		foreach( $columns as $column ) {
			if ( isset( $column['views'] ) ) {
				if ( !in_array( 'all', $column['views'] ) ) {
					$views['pwbeview_all'][] = $column['field'];
				}

				if ( !in_array( 'standard', $column['views'] ) ) {
					$views['pwbeview_default'][] = $column['field'];
				}
			}
		}

		$saved_views = maybe_unserialize( get_option( 'pwbe_views' ) );

		if ( !empty( $saved_views ) && is_array( $saved_views ) ) {
			foreach( $saved_views as $key => $view ) {
				$saved_views[ $key ] = json_decode( $view );
			}

			$views = array_merge( $views, $saved_views );
		}

		return apply_filters( 'pwbe_views', $views );
	}
}

endif;

?>