<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-builder-block-module-settings-chart-table-template">

<div class="fusion-builder-main-settings">
	<div class="fusion-table-builder-chart">
		<#
		var chart_datasets = FusionPageBuilderApp.findShortcodeMatches( atts.params.element_content, 'fusion_chart_dataset' ),
			chart_labels         = null,
			bg_colors            = null;
			border_colors        = null,
			border_size          = null,
			legend_text_colors   = null,
			table_class          = 'showY',
			wrapperClass         = '',
			columnOffset         = 5,
			sampleColors         = ['#03a9f4', '#8bc34a', '#ff9800'];

		if( 'undefined' !== typeof atts.params.x_axis_labels ) {
			chart_labels = atts.params.x_axis_labels.split( '|' );
		}

		if( 'undefined' !== typeof atts.params.bg_colors ) {
			bg_colors = atts.params.bg_colors.split( '|' );
		}

		if( 'undefined' !== typeof atts.params.border_colors ) {
			border_colors = atts.params.border_colors.split( '|' );
		}

		if( 'undefined' !== typeof atts.params.legend_text_colors ) {
			legend_text_colors = atts.params.legend_text_colors.split( '|' );
		}

		// Parse chart data sets.
		if ( null !== chart_datasets ) {
			column_counter = 0;
			max_columns = 0;
			td = [];

			_.each( chart_datasets, function ( chart_dataset ) {
				var
				chart_dataset_element    = chart_dataset.match( FusionPageBuilderApp.regExpShortcode( 'fusion_chart_dataset' ) ),
				chart_dataset_attributes = '' !== chart_dataset_element[3] ? window.wp.shortcode.attrs( chart_dataset_element[3] ) : '',
				values;

				column_counter++;
				td[ column_counter ] = [];

				td[ column_counter ][1] = chart_dataset_attributes.named.title;
				td[ column_counter ][2] = chart_dataset_attributes.named.legend_text_color;
				td[ column_counter ][3] = chart_dataset_attributes.named.background_color;
				td[ column_counter ][4] = chart_dataset_attributes.named.border_color;
				values                  = chart_dataset_attributes.named.values.split( '|' );

				for ( i = 0; i < values.length; i++ ) {
					td[ column_counter ].push( values[ i ] );
				}

				if ( max_columns < values.length + 4 ) {
					max_columns = values.length + 4;
				}

			} );
		}

		// Note: atts.params.chart_type is object when element is just created.
		if ( ( 'object' === typeof atts.params.chart_type || 'pie' === atts.params.chart_type || 'doughnut' === atts.params.chart_type || 'polarArea' === atts.params.chart_type ) || ( 'undefined' !== typeof chart_datasets && 1 === chart_datasets.length || '' === chart_datasets ) && ( 'bar' === atts.params.chart_type || 'horizontalBar' == atts.params.chart_type ) ) {
			table_class = 'showX';
		}

		wrapperClass = 'fusion-chart-' + ( 'object' !== typeof atts.params.chart_type ? atts.params.chart_type : 'bar' );
		#>

		<div class="fusion-table-builder fusion-table-builder-chart {{ wrapperClass }}">
			<div class="fusion-builder-layouts-header-info">
				<h2>{{ fusionBuilderText.chart_intro_fe }}</h2>
				<a href="#" class="fusion-builder-chart-button fusion-table-builder-add-column">{{ fusionBuilderText.add_chart_column }}</a>
				<a href="#" class="fusion-builder-chart-button fusion-table-builder-add-row">{{ fusionBuilderText.add_chart_row }}</a>
			</div>

			<div class="fusion-builder-table-wrap">
				<table class="fusion-builder-table {{ table_class }}">
					<thead>
						<tr>
							<th class="th-1" data-th-id="1">
							</th>
							<th class="th-2" data-th-id="2" colspan="3">{{ fusionBuilderText.chart_value_set_styling }}</th>
							<#
							if ( null !== chart_labels && '' !== chart_labels ) {

								for ( c = columnOffset; c <= max_columns; c++ ) {

									var label_value = 'undefined' !== typeof chart_labels[ c - columnOffset ] && '' !== chart_labels[ c - columnOffset ] ? chart_labels[ c - columnOffset ] : ''; #>

									<th class="th-{{ c }} fusion-builder-option" data-th-id="{{ c }}" data-option-id="fake-chart-option">
										<div class="fusion-builder-table-hold">
											<div class="fusion-builder-table-column-options">
												<span class="fa fusiona-trash-o fusion-builder-table-delete-column" title="{{ fusionBuilderText.delete_column }}" data-column-id="{{ c }}" />
											</div>
										</div>
										<input type="text" placeholder="{{ fusionBuilderText.x_axis_label }} {{ c - 4 }}" value="{{ label_value }}" class="fusion-debounce-change" />
									</th>

								<# }

							} else { #>
								<th class="th-5 fusion-builder-option" data-th-id="5" data-option-id="fake-chart-option">
									<div class="fusion-builder-table-hold">
										<div class="fusion-builder-table-column-options">
											<span class="fa fusiona-trash-o fusion-builder-table-delete-column" title="{{ fusionBuilderText.delete_column }}" data-column-id="5" />
										</div>
									</div>
									<input type="text" placeholder="{{ fusionBuilderText.x_axis_label }} 1" value="Val 1" class="fusion-debounce-change" />
								</th>

								<th class="th-6 fusion-builder-option" data-th-id="6" data-option-id="fake-chart-option">
									<div class="fusion-builder-table-hold">
										<div class="fusion-builder-table-column-options">
											<span class="fa fusiona-trash-o fusion-builder-table-delete-column" title="{{ fusionBuilderText.delete_column }}" data-column-id="6" />
										</div>
									</div>
									<input type="text" placeholder="{{ fusionBuilderText.x_axis_label }} 2" value="Val 2" class="fusion-debounce-change" />
								</th>

								<th class="th-7 fusion-builder-option" data-th-id="7" data-option-id="fake-chart-option">
									<div class="fusion-builder-table-hold">
										<div class="fusion-builder-table-column-options">
											<span class="fa fusiona-trash-o fusion-builder-table-delete-column" title="{{ fusionBuilderText.delete_column }}" data-column-id="7" />
										</div>
									</div>
									<input type="text" placeholder="{{ fusionBuilderText.x_axis_label }} 3" value="Val 3" class="fusion-debounce-change" />
								</th>
							<# } #>

						</tr>

						<tr>
							<th class="th-1" data-th-id="1" rowspan="3">{{ fusionBuilderText.chart_dataset_styling }}</th>
							<th class="th-2" data-th-id="2" rowspan="3"></th>
							<th class="th-3" data-th-id="3" rowspan="3"></th>
							<th class="th-4" data-th-id="4" rowspan="3"></th>
						<#
						if ( null !== legend_text_colors && '' !== legend_text_colors ) { #>
								<#
								for ( c = columnOffset; c <= max_columns; c++ ) {
									var txt_color = 'undefined' !== typeof legend_text_colors[ c - columnOffset ] && '' !== legend_text_colors[ c - columnOffset ] ? legend_text_colors[ c - columnOffset ] : '',
										bg_color = 'undefined' !== typeof bg_colors[ c - columnOffset ] && '' !== bg_colors[ c - columnOffset ] ? bg_colors[ c - columnOffset ] : '',
										border_color = 'undefined' !== typeof border_colors[ c - columnOffset ] && '' !== border_colors[ c - columnOffset ] ? border_colors[ c - columnOffset ] : '';
								#>
								<th class="th-{{ c }}" data-th-id="{{ c }}">

									<?php /* Legend text color */ ?>
									<div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">
										<a href="#" class="fusion-builder-open-colorpicker" style="background-color: {{ txt_color }}"><span aria-label="{{ fusionBuilderText.legend_text_color }}" class="fusiona-color-dropper"></span></a>
										<div class="option-field fusion-builder-option-container">
											<span class="fusion-builder-colorpicker-title">{{ fusionBuilderText.legend_text_color }}</span>
											<div class="fusion-colorpicker-container">
												<input type="text" value="{{ txt_color }}" data-globals="false" data-hide="false" class="fusion-builder-color-picker-hex color-picker" data-allow-picker-toggle="false" data-alpha="true" />
											</div>
										</div>
									</div>

									<?php /* Background Color */ ?>
									<div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">
										<a href="#" class="fusion-builder-open-colorpicker" style="background-color: {{ bg_color }}"><span aria-label="{{ fusionBuilderText.background_color }}" class="fusiona-color-dropper"></span></a>
										<div class="option-field fusion-builder-option-container">
											<span class="fusion-builder-colorpicker-title">{{ fusionBuilderText.background_color }}</span>
											<div class="fusion-colorpicker-container">
												<input type="text" value="{{ bg_color }}" data-globals="false" data-hide="false" class="fusion-builder-color-picker-hex color-picker" data-allow-picker-toggle="false" data-alpha="true" />
											</div>
										</div>
								</div>

								<?php /* Border Color */ ?>
								<div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">
									<a href="#" class="fusion-builder-open-colorpicker" style="background-color: {{ border_color }}"><span aria-label="{{ fusionBuilderText.border_color }}" class="fusiona-color-dropper"></span></a>
									<div class="option-field fusion-builder-option-container">
										<span class="fusion-builder-colorpicker-title">{{ fusionBuilderText.border_color }}</span>
										<div class="fusion-colorpicker-container">
											<input type="text" value="{{ border_color }}" data-globals="false" data-hide="false" class="fusion-builder-color-picker-hex color-picker" data-allow-picker-toggle="false" data-alpha="true" />
										</div>
									</div>
								</div>

							</th>
							<# } #>

						<# } else { #>

							<# for ( c = 5; c <= 7; c++ ) { #>
							<th class="th-{{ c }}" data-th-id="{{ c }}">

								<?php /* Legend text color */ ?>
								<div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">
									<a href="#" class="fusion-builder-open-colorpicker" style="background-color: #ffffff"><span aria-label="{{ fusionBuilderText.legend_text_color }}" class="fusiona-color-dropper"></span></a>
									<div class="option-field fusion-builder-option-container">
										<span class="fusion-builder-colorpicker-title">{{ fusionBuilderText.legend_text_color }}</span>
										<div class="fusion-colorpicker-container">
											<input type="text" value="#ffffff" data-globals="false" data-hide="false" class="fusion-builder-color-picker-hex color-picker" data-allow-picker-toggle="false" data-alpha="true" />
										</div>
									</div>
								</div>

								<?php /* Background Color */ ?>
								<div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">
									<a href="#" class="fusion-builder-open-colorpicker" style="background-color: {{ sampleColors[c-5] }}"><span aria-label="{{ fusionBuilderText.background_color }}" class="fusiona-color-dropper"></span></a>
									<div class="option-field fusion-builder-option-container">
										<span class="fusion-builder-colorpicker-title">{{ fusionBuilderText.background_color }}</span>
										<div class="fusion-colorpicker-container">
											<input type="text" value="{{ sampleColors[c-5] }}" data-globals="false" data-hide="false" class="fusion-builder-color-picker-hex color-picker" data-allow-picker-toggle="false" data-alpha="true" />
										</div>
									</div>
							</div>

							<?php /* Border Color */ ?>
							<div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">
								<a href="#" class="fusion-builder-open-colorpicker" style="background-color: {{ sampleColors[c-5] }}"><span aria-label="{{ fusionBuilderText.border_color }}" class="fusiona-color-dropper"></span></a>
								<div class="option-field fusion-builder-option-container">
									<span class="fusion-builder-colorpicker-title">{{ fusionBuilderText.border_color }}</span>
									<div class="fusion-colorpicker-container">
										<input type="text" value="{{ sampleColors[c-5] }}" data-globals="false" data-hide="false" class="fusion-builder-color-picker-hex color-picker" data-allow-picker-toggle="false" data-alpha="true" />
									</div>
								</div>
							</div>

							</th>
							<# } #>

						<# } #>
						</tr>

					</thead>


					<tbody>

						<#
						if ( null !== chart_datasets && '' !== chart_datasets ) {

							for ( i = 1; i <= chart_datasets.length; i++ ) { #>

								<tr class="fusion-table-row tr-{{ i }}" data-tr-id="{{ i }}">

									<td class="td-1 fusion-builder-option" data-td-id="1" data-option-id="fake-chart-option"><input type="text" class="fusion-always-update" placeholder="{{ fusionBuilderText.legend_label }}" value="{{ td[ i ][1] }}" /><span class="fa fusiona-trash-o fusion-builder-table-delete-row" title="{{ fusionBuilderText.delete_row }}" data-row-id="{{ 1 }}" /></td>
									<td class="td-2" data-td-id="2">
										<div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">
											<a href="#" class="fusion-builder-open-colorpicker" style="background-color: {{ td[ i ][2] }}"><span aria-label="{{ fusionBuilderText.legend_text_color }}" class="fusiona-color-dropper"></span></a>
											<div class="option-field fusion-builder-option-container">
												<span class="fusion-builder-colorpicker-title">{{ fusionBuilderText.legend_text_color }}</span>
												<div class="fusion-colorpicker-container">
													<input type="text" value="{{ td[ i ][2] }}" data-globals="false" data-hide="false" class="fusion-builder-color-picker-hex color-picker" data-allow-picker-toggle="false" data-alpha="true" />
												</div>
											</div>
										</div>
									</td>
									<td class="td-3" data-td-id="3">
										<div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">
											<a href="#" class="fusion-builder-open-colorpicker" style="background-color: {{ td[ i ][3] }}"><span aria-label="{{ fusionBuilderText.background_color }}" class="fusiona-color-dropper"></span></a>
											<div class="option-field fusion-builder-option-container">
												<span class="fusion-builder-colorpicker-title">{{ fusionBuilderText.background_color }}</span>
												<div class="fusion-colorpicker-container">
													<input type="text" value="{{ td[ i ][3] }}" data-globals="false" data-hide="false" class="fusion-builder-color-picker-hex color-picker" data-allow-picker-toggle="false" data-alpha="true" />
												</div>
											</div>
										</div>
									</td>
									<td class="td-4" data-td-id="4">
										<div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">
											<a href="#" class="fusion-builder-open-colorpicker" style="background-color: {{ td[ i ][4] }}"><span aria-label="{{ fusionBuilderText.border_color }}" class="fusiona-color-dropper"></span></a>
											<div class="option-field fusion-builder-option-container">
												<span class="fusion-builder-colorpicker-title">{{ fusionBuilderText.border_color }}</span>
												<div class="fusion-colorpicker-container">
													<input type="text" value="{{ td[ i ][4] }}" data-globals="false" data-hide="false" class="fusion-builder-color-picker-hex color-picker" data-allow-picker-toggle="false" data-alpha="true" />
												</div>
											</div>
										</div>
									</td>

									<# for ( c = columnOffset; c <= max_columns; c++ ) {

										if ( 'undefined' !== typeof td[i]  ) {

											var td_value = 'undefined' !== typeof td[ i ][ c ] && '' !== td[ i ][ c ] ? td[ i ][ c ] : ''; #>

											<td class="td-{{ c }} fusion-builder-option" data-td-id="{{ c }}" data-option-id="fake-chart-option"><input type="text" placeholder="{{ fusionBuilderText.enter_value }}" value="{{ td_value }}" class="fusion-debounce-change" /></td>

										<# } else { #>

											<td class="td-{{ c }} fusion-builder-option" data-td-id="{{ c }}" data-option-id="fake-chart-option"><input type="text" placeholder="{{ fusionBuilderText.enter_value }}" value="" class="fusion-debounce-change" /></td>

										<# } #>

									<# } #>

								</tr>

							<# }

						} else { #>

							<tr class="fusion-table-row tr-1" data-tr-id="1">
								<td class="td-1 fusion-builder-option" data-td-id="1" data-option-id="fake-chart-option"><input type="text" placeholder="{{ fusionBuilderText.legend_label }}" value="Data Set 1" /><span class="fa fusiona-trash-o fusion-builder-table-delete-row" title="{{ fusionBuilderText.delete_row }}" data-row-id="{{ 1 }}" /></td>
								<td class="td-2" data-td-id="2">
									<div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">
										<a href="#" class="fusion-builder-open-colorpicker" style="background-color: #ffffff"><span aria-label="{{ fusionBuilderText.legend_text_color }}" class="fusiona-color-dropper"></span></a>
										<div class="option-field fusion-builder-option-container">
											<span class="fusion-builder-colorpicker-title">{{ fusionBuilderText.legend_text_color }}</span>
											<div class="fusion-colorpicker-container">
												<input type="text" value="#ffffff" data-globals="false" data-hide="false" class="fusion-builder-color-picker-hex color-picker" data-allow-picker-toggle="false" data-alpha="true" />
											</div>
										</div>
									</div>
								</td>
								<td class="td-3" data-td-id="3">
									<div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">
										<a href="#" class="fusion-builder-open-colorpicker" style="background-color: #03a9f4"><span aria-label="{{ fusionBuilderText.background_color }}" class="fusiona-color-dropper"></span></a>
										<div class="option-field fusion-builder-option-container">
											<span class="fusion-builder-colorpicker-title">{{ fusionBuilderText.background_color }}</span>
											<div class="fusion-colorpicker-container">
												<input type="text" value="#03a9f4" data-globals="false" data-hide="false" class="fusion-builder-color-picker-hex color-picker" data-allow-picker-toggle="false" data-alpha="true" />
											</div>
										</div>
									</div>
								</td>
								<td class="td-4" data-td-id="4">
									<div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">
										<a href="#" class="fusion-builder-open-colorpicker" style="background-color: #03a9f4"><span aria-label="{{ fusionBuilderText.border_color }}" class="fusiona-color-dropper"></span></a>
										<div class="option-field fusion-builder-option-container">
											<span class="fusion-builder-colorpicker-title">{{ fusionBuilderText.border_color }}</span>
											<div class="fusion-colorpicker-container">
												<input type="text" value="#03a9f4" data-globals="false" data-hide="false" class="fusion-builder-color-picker-hex color-picker" data-allow-picker-toggle="false" data-alpha="true" />
											</div>
										</div>
									</div>
								</td>
								<td class="td-5 fusion-builder-option" data-td-id="5" data-option-id="fake-chart-option"><input type="text" placeholder="{{ fusionBuilderText.enter_value }}" value="5" class="fusion-debounce-change" /></td>
								<td class="td-6 fusion-builder-option" data-td-id="6" data-option-id="fake-chart-option"><input type="text" placeholder="{{ fusionBuilderText.enter_value }}" value="7" class="fusion-debounce-change" /></td>
								<td class="td-7 fusion-builder-option" data-td-id="7" data-option-id="fake-chart-option"><input type="text" placeholder="{{ fusionBuilderText.enter_value }}" value="9" class="fusion-debounce-change" /></td>
							</tr>

						<# } #>

					</tbody>

				</table>
			</div>
		</div>
	</div>
</div>

</script>
