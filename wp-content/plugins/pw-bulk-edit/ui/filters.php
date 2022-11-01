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
<div class="pwbe-filter">
	<?php require( 'activation.php' ); ?>
	<div class="pwbe-filter-container">
		<span class="pwbe-filter-toolbar-right">
			<a href="<?php echo $settings_url; ?>" id="pwbe-settings" class="pwbe-link pwbe-settings-link"><i class="fa fa-fw fa-cog pwbe-link"></i> <?php _e( 'Settings', 'pw-bulk-edit' ); ?></a>
			<a href="<?php echo $help_url; ?>" target="_blank" id="pwbe-help" class="pwbe-link pwbe-help-link"><i class="fa fa-fw fa-life-ring pwbe-link"></i> <?php _e( 'Help', 'pw-bulk-edit' ); ?></a>
		</span>
		<div class="pwbe-filter-form">
			<div class="pwbe-pull-right">
				<span id="pwbe-hide-filters-button" class="pwbe-link pwbe-hidden" title="<?php _e( 'Hide Filters', 'pw-bulk-edit' ); ?>"><i class="fa fa-eye-slash fa-fw" aria-hidden="true"></i> <?php _e( 'Hide Filters', 'pw-bulk-edit' ); ?></span>
				<span id="pwbe-show-filters-button" class="pwbe-link pwbe-hidden" title="<?php _e( 'Show Filters', 'pw-bulk-edit' ); ?>"><i class="fa fa-eye fa-fw" aria-hidden="true"></i> <?php _e( 'Show Filters', 'pw-bulk-edit' ); ?></span>
			</div>
			<form id="pwbe-filters-form">
				<input type="hidden" id="pwbe-order-by" name="order_by" value="post_title" />
				<input type="hidden" id="pwbe-order-by-desc" name="order_by_desc" value="" />

				<div class="pwbe-filter-header">
					<span id="pwbe-header-multiple-filters" class="pwbe-pull-left">
						<?php _e( 'Find products that match', 'pw-bulk-edit' ); ?>
						<select id="pwbe-filter-group" name="main_group_type">
							<option value="pwbe_and"><?php _e( 'all', 'pw-bulk-edit' ); ?></option>
							<option value="pwbe_or"><?php _e( 'any', 'pw-bulk-edit' ); ?></option>
						</select>
						<?php _e( 'of the following rules:', 'pw-bulk-edit' ); ?>
					</span>
				</div>
				<div class="pwbe-filter-row-container">
					<hr class="pwbe-filter-container-break"/>
				</div>

				<button type="submit" id="pwbe-search-button" class="button"><i class="fa fa-search" aria-hidden="true"></i> <?php _e( 'Search', 'pw-bulk-edit' ); ?></button>
				<?php
					echo apply_filters( 'pwbe_html_after_search_button', '' );
				?>
			</form>
		</div>
	</div>
	<?php require( 'filters_help.php' ); ?>
</div>

<div class="pwbe-row-template-group pwbe-filter-row pwbe-filter-group-row" data-suffix="">
	<input type="hidden" name="row[]" value="group">

	<select name="filter_name" class="pwbe-filter-field pwbe-filter-name">
  		<option value="pwbe_and"><?php _e( 'all', 'pw-bulk-edit' ); ?></option>
		<option value="pwbe_or"><?php _e( 'any', 'pw-bulk-edit' ); ?></option>
	</select> <?php _e( 'of the following are true', 'pw-bulk-edit' ); ?>

	<input type="hidden" name="filter_type" class="pwbe-filter-type" value="" />

	<span class="pwbe-pull-right">
		<span class="pwbe-filter-link pwbe-filter-icon pwbe-filter-remove" title="<?php _e( 'Remove', 'pw-bulk-edit' ); ?>"><i class="fa fa-minus-square-o"></i></span>
		<span class="pwbe-filter-link pwbe-filter-icon pwbe-filter-add" title="<?php _e( 'Add a filter', 'pw-bulk-edit' ); ?>"><i class="fa fa-plus-square-o"></i></span>
	</span>

	<br />
	<span class="pwbe-filter-link pwbe-filter-criteria pwbe-filter-add" title="<?php _e( 'Add a filter', 'pw-bulk-edit' ); ?>"><i class="fa fa-plus-square-o"></i> <?php _e( 'Add a filter', 'pw-bulk-edit' ); ?></span>
	<span class="pwbe-filter-link pwbe-filter-criteria pwbe-filter-add-group" title="<?php _e( 'Add a group of filters', 'pw-bulk-edit' ); ?>"><i class="fa fa-plus-square-o"></i> <?php _e( 'Add a Group of Filters', 'pw-bulk-edit' ); ?></span>
	<span class="pwbe-filter-link pwbe-filter-criteria pwbe-filter-remove" title="<?php _e( 'Remove', 'pw-bulk-edit' ); ?>"><i class="fa fa-minus-square-o"></i> <?php _e( 'Remove', 'pw-bulk-edit' ); ?></span>
	<hr class="pwbe-filter-container-break"/>
</div>

<div class="pwbe-row-template pwbe-filter-row" data-suffix="">
	<input type="hidden" name="row[]" value="">

	<select name="filter_name" class="pwbe-filter-field pwbe-filter-name">
		<?php
			foreach (PWBE_Filters::get() as $filter_name => $criteria) {
				$name = $criteria['name'];
				$type = $criteria['type'];

				echo "<option value=\"$filter_name\" data-type=\"$type\">$name</option>\n";
			}
		?>
	</select>

	<select name="filter_type" class="pwbe-filter-field pwbe-filter-type"></select>

	<input name="filter_value" class="pwbe-filter-field pwbe-filter-field-input pwbe-filter-value" type="text" value="" autocomplete="off" />

	<span class="pwbe-filter-required">
		* <?php _e( 'required', 'pw-bulk-edit' ); ?>
	</span>

	<span class="pwbe-pull-right">
		<span class="pwbe-filter-link pwbe-filter-icon pwbe-filter-remove" title="<?php _e( 'Remove', 'pw-bulk-edit' ); ?>"><i class="fa fa-minus-square-o"></i></span>
		<span class="pwbe-filter-link pwbe-filter-icon pwbe-filter-add" title="<?php _e( 'Add a filter', 'pw-bulk-edit' ); ?>"><i class="fa fa-plus-square-o"></i></span>
	</span>

	<br />
	<span class="pwbe-filter-link pwbe-filter-criteria pwbe-filter-add" title="<?php _e( 'Add a filter', 'pw-bulk-edit' ); ?>"><i class="fa fa-plus-square-o"></i> <?php _e( 'Add a filter', 'pw-bulk-edit' ); ?></span>
	<span class="pwbe-filter-link pwbe-filter-criteria pwbe-filter-add-group" title="<?php _e( 'Add a group of filters', 'pw-bulk-edit' ); ?>"><i class="fa fa-plus-square-o"></i> <?php _e( 'Add a group of filters', 'pw-bulk-edit' ); ?></span>
	<span class="pwbe-filter-link pwbe-filter-criteria pwbe-filter-remove" title="<?php _e( 'Remove', 'pw-bulk-edit' ); ?>"><i class="fa fa-minus-square-o"></i> <?php _e( 'Remove', 'pw-bulk-edit' ); ?></span>
	<hr class="pwbe-filter-container-break"/>
</div>

<input name="filter_value" class="pwbe-filter-value-template pwbe-filter-field pwbe-filter-field-input pwbe-filter-value" type="text" value="" autocomplete="off" />

<span class="pwbe-filter-value2-template pwbe-filter-value2-container">
	to <input name="filter_value2" class="pwbe-filter-field pwbe-filter-field-input pwbe-filter-value2" type="text" value="" autocomplete="off" />
</span>

<span class="pwbe-filter-attributes-template pwbe-multiselect pwbe-filter-attributes-container">
	<select name="filter_select[]" class="pwbe-filter-field pwbe-filter-select pwbe-filter-value" multiple="multiple" ></select>
</span>

<span class="pwbe-multiselect pwbe-filter-categories-container pwbe-filter-categories-template">
	<select name="filter_select[]" class="pwbe-filter-field pwbe-filter-select pwbe-filter-value" multiple="multiple" ></select>
</span>

<span class="pwbe-multiselect pwbe-filter-tags-container pwbe-filter-tags-template">
	<select name="filter_select[]" class="pwbe-filter-field pwbe-filter-select pwbe-filter-value" multiple="multiple" ></select>
</span>
