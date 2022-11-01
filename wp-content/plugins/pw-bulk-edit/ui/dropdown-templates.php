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

/*
 * Pre-fill some select boxes that can be reused on the grid and other forms.
 *
 */

$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
$categories = array();
$this->sort_terms_hierarchically( $terms, $categories );
$category_options = $this->hierarchical_select( $categories );

$tag_options = '';
$tags = get_terms( array( 'taxonomy' => 'product_tag', 'hide_empty' => false ) );
foreach ( $tags as $tag ) {
	if ( !empty( $tag ) ) {
		$tag_options .= "<option value='{$tag->slug}'>{$tag->name}</option>\n";
	}
}

?>
<div class="pwbe-dropdown-templates">
	<?php
		$select_options = PWBE_Select_Options::get();

		foreach ( $select_options as $field_name => $values ) {
			if ( count( $values ) > 0 ) {
				?>
				<select class="pwbe-dropdown-template-<?php echo $field_name; ?>">
					<?php
						foreach ( $values as $value => $option ) {
							echo "<option value='$value' class='pwbe-dropdown-visibility-$option[visibility]'>$option[name]</option>\n";
						}
					?>
				</select>
				<?php
			}
		}
	?>

	<select class="pwbe-dropdown-template-categories">
		<?php
			echo $category_options;
		?>
	</select>

	<select class="pwbe-dropdown-template-tags">
		<?php
			echo $tag_options;
		?>
	</select>
</div>
