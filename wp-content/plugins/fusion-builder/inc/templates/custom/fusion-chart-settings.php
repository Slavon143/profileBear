<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

$fusion_settings = awb_get_fusion_settings();
$border_size     = $fusion_settings->get( 'chart_border_size' );
?>
<script type="text/template" id="fusion-builder-block-module-settings-chart-template">

	<div class="fusion-builder-modal-top-container">
		<# if ( 'undefined' !== typeof fusionAllElements[atts.element_type] ) { #>
				<h2>{{ fusionAllElements[ atts.element_type ].name }}</h2>
		<# }; #>

		<div class="fusion-builder-modal-close fusiona-plus2"></div>
		<ul class="fusion-tabs-menu">
			<li class=""><a href="#table-options">{{ fusionBuilderText.chart_options }}</a></li>
			<li class=""><a href="#table">{{ fusionBuilderText.chart }}</a></li>
		</ul>
	</div>

	<div class="fusion-builder-modal-bottom-container">
		<a href="#" class="fusion-builder-modal-save"><span>

			<# if ( true === FusionPageBuilderApp.shortcodeGenerator && true !== FusionPageBuilderApp.shortcodeGeneratorMultiElementChild ) { #>
				{{ fusionBuilderText.insert }}
			<# } else { #>
				{{ fusionBuilderText.save }}
			<# } #>

		</span></a>

		<a href="#" class="fusion-builder-modal-close">
			<span>
				{{ fusionBuilderText.cancel }}
			</span>
		</a>
	</div>

	<div class="fusion-builder-main-settings fusion-builder-main-settings-full has-group-options">
		<div class="fusion-tabs">

			<div id="table-options" class="fusion-tab-content">

				<?php fusion_element_options_loop( 'fusionAllElements[atts.element_type].params' ); ?>

			</div>

			<div id="table" class="fusion-tab-content">

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
					chart_bg_color        = '',
					padding_top           = '',
					padding_right         = '',
					padding_bottom        = '',
					padding_left          = '',
					chart_axis_text_color = '',
					chart_gridline_color  = '',
					default_chart_bg_color        = '<?php echo esc_js( $fusion_settings->get( 'chart_bg_color' ) ); ?>',
					default_chart_axis_text_color = '<?php echo esc_js( $fusion_settings->get( 'chart_axis_text_color' ) ); ?>',
					default_chart_gridline_color  = '<?php echo esc_js( $fusion_settings->get( 'chart_gridline_color' ) ); ?>';

				if( 'undefined' !== typeof atts.params.chart_bg_color ) {
					chart_bg_color = atts.params.chart_bg_color;
				}

				if( 'undefined' !== typeof atts.params.padding_top ) {
					padding_top = atts.params.padding_top;
				}

				if( 'undefined' !== typeof atts.params.padding_right ) {
					padding_right = atts.params.padding_right;
				}

				if( 'undefined' !== typeof atts.params.padding_bottom ) {
					padding_bottom = atts.params.padding_bottom;
				}

				if( 'undefined' !== typeof atts.params.padding_left ) {
					padding_left = atts.params.padding_left;
				}

				if( 'undefined' !== typeof atts.params.chart_axis_text_color ) {
					chart_axis_text_color = atts.params.chart_axis_text_color;
				}

				if( 'undefined' !== typeof atts.params.chart_gridline_color ) {
					chart_gridline_color = atts.params.chart_gridline_color;
				}

				if( 'undefined' !== typeof atts.params.x_axis_labels ) {
					chart_labels = atts.params.x_axis_labels.split( '|' );
				}

				if( 'undefined' !== typeof atts.params.bg_colors ) {
					bg_colors = atts.params.bg_colors.split( '|' );
				}

				if( 'undefined' !== typeof atts.params.border_colors ) {
					border_colors = atts.params.border_colors.split( '|' );
				}

				if( 'undefined' !== typeof atts.params.chart_border_size ) {
					border_size = '' !== atts.params.chart_border_size ? parseInt( atts.params.chart_border_size ) : '';
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
				if ( ( 'object' === typeof atts.params.chart_type || 'pie' === atts.params.chart_type || 'doughnut' === atts.params.chart_type || 'polarArea' === atts.params.chart_type ) || 'undefined' !== typeof chart_datasets && 1 === chart_datasets.length && ( 'bar' === atts.params.chart_type || 'horizontalBar' == atts.params.chart_type ) ) {
					table_class = 'showX';
				}

				wrapperClass = 'fusion-chart-' + ( 'object' !== typeof atts.params.chart_type ? atts.params.chart_type : 'bar' );
				#>

				<div class="fusion-table-builder fusion-table-builder-chart {{ wrapperClass }}">
					<div class="fusion-builder-layouts-header-info">
						<h2>{{ fusionBuilderText.chart_intro }}</h2>
						<h3>{{{ fusionBuilderText.chart_bars_note }}}</h3>
						<span class="fusion-table-builder-add-column fusion-builder-button-default ">{{ fusionBuilderText.add_chart_column }}</span>
						<span class="fusion-table-builder-add-row fusion-builder-button-default ">{{ fusionBuilderText.add_chart_row }}</span>
					</div>

					<table class="fusion-builder-table {{ table_class }}">
						<thead>
							<tr>
								<th class="th-1" data-th-id="1">
									<strong>Border Size</strong>
									<input type="text" value="{{ border_size }}" id="chart_border_size" />
								</th>
								<th class="th-2" data-th-id="2">{{ fusionBuilderText.legend_text_color }}</th>
								<th class="th-3" data-th-id="2">{{ fusionBuilderText.background_color }}</th>
								<th class="th-4" data-th-id="3">{{ fusionBuilderText.border_color }}</th>
								<#
								if ( null !== chart_labels && '' !== chart_labels ) {

									for ( c = columnOffset; c <= max_columns; c++ ) {

										var label_value = 'undefined' !== typeof chart_labels[ c - columnOffset ] && '' !== chart_labels[ c - columnOffset ] ? chart_labels[ c - columnOffset ] : ''; #>

										<th class="th-{{ c }}" data-th-id="{{ c }}">
											<div class="fusion-builder-table-hold">
												<div class="fusion-builder-table-column-options">
													<span class="fa fusiona-trash-o fusion-builder-table-delete-column" title="{{ fusionBuilderText.delete_column }}" data-column-id="{{ c }}" />
												</div>
											</div>
											<input type="text" placeholder="{{ fusionBuilderText.x_axis_label }} {{ c - 4 }}" value="{{ label_value }}" />
										</th>

									<# }

								} else { #>
									<th class="th-5" data-th-id="5">
										<div class="fusion-builder-table-hold">
											<div class="fusion-builder-table-column-options">
												<span class="fa fusiona-trash-o fusion-builder-table-delete-column" title="{{ fusionBuilderText.delete_column }}" data-column-id="5" />
											</div>
										</div>
										<input type="text" placeholder="{{ fusionBuilderText.x_axis_label }} 1" value="Val 1" />
									</th>

									<th class="th-6" data-th-id="6">
										<div class="fusion-builder-table-hold">
											<div class="fusion-builder-table-column-options">
												<span class="fa fusiona-trash-o fusion-builder-table-delete-column" title="{{ fusionBuilderText.delete_column }}" data-column-id="6" />
											</div>
										</div>
										<input type="text" placeholder="{{ fusionBuilderText.x_axis_label }} 2" value="Val 2" />
									</th>

									<th class="th-7" data-th-id="7">
										<div class="fusion-builder-table-hold">
											<div class="fusion-builder-table-column-options">
												<span class="fa fusiona-trash-o fusion-builder-table-delete-column" title="{{ fusionBuilderText.delete_column }}" data-column-id="7" />
											</div>
										</div>
										<input type="text" placeholder="{{ fusionBuilderText.x_axis_label }} 3" value="Val 3" />
									</th>
								<# } #>

							</tr>

							<#
							if ( null !== legend_text_colors && '' !== legend_text_colors ) { #>
								<tr>
									<th class="th-1" data-th-id="1">{{ fusionBuilderText.legend_text_color }}</th>
									<th class="th-2" data-th-id="2"></th>
									<th class="th-3" data-th-id="3"></th>
									<th class="th-4" data-th-id="4"></th>
									<#
									for ( c = columnOffset; c <= max_columns; c++ ) {
										var txt_color = 'undefined' !== typeof legend_text_colors[ c - columnOffset ] && '' !== legend_text_colors[ c - columnOffset ] ? legend_text_colors[ c - columnOffset ] : ''; #>
									<th class="th-{{ c }}" data-th-id="{{ c }}"><div class="option-field"><input type="text" value="{{ txt_color }}" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>
									<# } #>
								</tr>

							<# } else { #>

								<tr>
									<th class="th-1" data-th-id="1">{{ fusionBuilderText.legend_text_color }}</th>
									<th class="th-2" data-th-id="2"></th>
									<th class="th-3" data-th-id="3"></th>
									<th class="th-4" data-th-id="4"></th>
									<th class="th-5" data-th-id="5"><div class="option-field"><input type="text" value="#ffffff" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>
									<th class="th-6" data-th-id="6"><div class="option-field"><input type="text" value="#ffffff" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>
									<th class="th-7" data-th-id="7"><div class="option-field"><input type="text" value="#ffffff" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>
								</tr>

							<# } #>

							<#
							if ( null !== bg_colors && '' !== bg_colors ) { #>
								<tr>
									<th class="th-1" data-th-id="1">{{ fusionBuilderText.background_color }}</th>
									<th class="th-2" data-th-id="2"></th>
									<th class="th-3" data-th-id="3"></th>
									<th class="th-4" data-th-id="4"></th>
									<#
									for ( c = columnOffset; c <= max_columns; c++ ) {
										var bg_color = 'undefined' !== typeof bg_colors[ c - columnOffset ] && '' !== bg_colors[ c - columnOffset ] ? bg_colors[ c - columnOffset ] : ''; #>
									<th class="th-{{ c }}" data-th-id="{{ c }}"><div class="option-field"><input type="text" value="{{ bg_color }}" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>
									<# } #>
								</tr>

							<# } else { #>

								<tr>
									<th class="th-1" data-th-id="1">{{ fusionBuilderText.background_color }}</th>
									<th class="th-2" data-th-id="2"></th>
									<th class="th-3" data-th-id="3"></th>
									<th class="th-4" data-th-id="4"></th>
									<th class="th-5" data-th-id="5"><div class="option-field"><input type="text" value="#00bcd4" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>
									<th class="th-6" data-th-id="6"><div class="option-field"><input type="text" value="#8bc34a" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>
									<th class="th-7" data-th-id="7"><div class="option-field"><input type="text" value="#ff9800" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>
								</tr>

							<# } #>

							<#
							if ( null !== border_colors && '' !== border_colors ) { #>
								<tr>
									<th class="th-1" data-th-id="1">{{ fusionBuilderText.border_color }}</th>
									<th class="th-2" data-th-id="2"></th>
									<th class="th-3" data-th-id="3"></th>
									<th class="th-4" data-th-id="4"></th>
									<#
									for ( c = columnOffset; c <= max_columns; c++ ) {
										var border_color = 'undefined' !== typeof border_colors[ c - columnOffset ] && '' !== border_colors[ c - columnOffset ] ? border_colors[ c - columnOffset ] : ''; #>
									<th class="th-{{ c }}" data-th-id="{{ c }}"><div class="option-field"><input type="text" value="{{ border_color }}" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>
									<# } #>
								</tr>

							<# } else { #>

								<tr>
									<th class="th-1" data-th-id="1">{{ fusionBuilderText.border_color }}</th>
									<th class="th-2" data-th-id="2"></th>
									<th class="th-3" data-th-id="3"></th>
									<th class="th-4" data-th-id="4"></th>
									<th class="th-5" data-th-id="5"><div class="option-field"><input type="text" value="#00bcd4" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>
									<th class="th-6" data-th-id="6"><div class="option-field"><input type="text" value="#8bc34a" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>
									<th class="th-7" data-th-id="7"><div class="option-field"><input type="text" value="#ff9800" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>
								</tr>
							<# } #>

						</thead>

						<tbody>

							<#
							if ( null !== chart_datasets && '' !== chart_datasets ) {

								for ( i = 1; i <= chart_datasets.length; i++ ) { #>

									<tr class="fusion-table-row tr-{{ i }}" data-tr-id="{{ i }}">

										<td class="td-1" data-td-id="1"><input type="text" placeholder="{{ fusionBuilderText.legend_label }}" value="{{ td[ i ][1] }}" /><span class="fa fusiona-trash-o fusion-builder-table-delete-row" title="{{ fusionBuilderText.delete_row }}" data-row-id="{{ 1 }}" /></td>
										<td class="td-2" data-td-id="2"><div class="option-field"><input type="text" value="{{ td[ i ][2] }}" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></td>
										<td class="td-3" data-td-id="3"><div class="option-field"><input type="text" value="{{ td[ i ][3] }}" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></td>
										<td class="td-4" data-td-id="4"><div class="option-field"><input type="text" value="{{ td[ i ][4] }}" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></td>

										<# for ( c = columnOffset; c <= max_columns; c++ ) {

											if ( 'undefined' !== typeof td[i]  ) {

												var td_value = 'undefined' !== typeof td[ i ][ c ] && '' !== td[ i ][ c ] ? td[ i ][ c ] : ''; #>

												<td class="td-{{ c }}" data-td-id="{{ c }}"><input type="text" placeholder="{{ fusionBuilderText.enter_value }}" value="{{ td_value }}" /></td>

											<# } else { #>

												<td class="td-{{ c }}" data-td-id="{{ c }}"><input type="text" placeholder="{{ fusionBuilderText.enter_value }}" value="" /></td>

											<# } #>

										<# } #>

									</tr>

								<# }

							} else { #>

								<tr class="fusion-table-row tr-1" data-tr-id="1">
									<td class="td-1" data-td-id="1"><input type="text" placeholder="{{ fusionBuilderText.legend_label }}" value="Data Set 1" /><span class="fa fusiona-trash-o fusion-builder-table-delete-row" title="{{ fusionBuilderText.delete_row }}" data-row-id="{{ 1 }}" /></td>
									<td class="td-2" data-td-id="2"><div class="option-field"><input type="text" value="#ffffff" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></td>
									<td class="td-3" data-td-id="3"><div class="option-field"><input type="text" value="#00bcd4" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></td>
									<td class="td-4" data-td-id="4"><div class="option-field"><input type="text" value="#00bcd4" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></td>
									<td class="td-5" data-td-id="5"><input type="text" placeholder="{{ fusionBuilderText.enter_value }}" value="5" /></td>
									<td class="td-6" data-td-id="6"><input type="text" placeholder="{{ fusionBuilderText.enter_value }}" value="7" /></td>
									<td class="td-7" data-td-id="7"><input type="text" placeholder="{{ fusionBuilderText.enter_value }}" value="9" /></td>
								</tr>

							<# } #>

						</tbody>

					</table>

					<ul class="fusion-builder-module-settings fusion_chart">
						<li data-option-id="chart_bg_color" class="fusion-builder-option colorpicker">
							<div class="option-details">
								<h3>{{ fusionBuilderText.chart_bg_color_title }}</h3>
								<p class="description">{{{ fusionBuilderText.chart_bg_color_desc }}}</p>
							</div>
							<div class="option-field fusion-builder-option-container">
								<input id="chart_bg_color" name="chart_bg_color" class="fusion-builder-color-picker-hex wp-color-picker" data-alpha="true" type="text" value="{{ chart_bg_color }}" data-default="{{ default_chart_bg_color }}">
							</div>
						</li>
						<li data-option-id="chart_padding" class="fusion-builder-option dimension">
							<div class="option-details">
								<h3>{{ fusionBuilderText.chart_padding_title }}</h3>
									<p class="description">{{ fusionBuilderText.chart_padding_desc }}</p>
							</div>

							<div class="option-field fusion-builder-option-container">
								<div class="fusion-builder-dimension">
									<span class="add-on"><i class="dashicons dashicons-arrow-up-alt" aria-hidden="true"></i></span>
									<input type="text" name="padding_top" id="padding_top" value="{{ padding_top }}">
								</div>

								<div class="fusion-builder-dimension">
									<span class="add-on"><i class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></i></span>
									<input type="text" name="padding_right" id="padding_right" value="{{ padding_right }}">
								</div>

								<div class="fusion-builder-dimension">
									<span class="add-on"><i class="dashicons dashicons-arrow-down-alt" aria-hidden="true"></i></span>
									<input type="text" name="padding_bottom" id="padding_bottom" value="{{ padding_bottom }}">
								</div>

								<div class="fusion-builder-dimension">
									<span class="add-on"><i class="dashicons dashicons-arrow-left-alt" aria-hidden="true"></i></span>
									<input type="text" name="padding_left" id="padding_left" value="{{ padding_left }}">
								</div>
							</div>
						</li>
						<li data-option-id="chart_axis_text_color" class="fusion-builder-option colorpicker">
							<div class="option-details">
									<h3>{{ fusionBuilderText.chart_axis_text_color_title }}</h3>
									<p class="description">{{{ fusionBuilderText.chart_axis_text_color_desc }}}</p>
							</div>
							<div class="option-field fusion-builder-option-container">
								<input id="chart_axis_text_color" name="chart_axis_text_color" class="fusion-builder-color-picker-hex wp-color-picker" data-alpha="true" type="text" value="{{ chart_axis_text_color }}" data-default="{{ default_chart_axis_text_color }}">
							</div>
						</li>
						<li data-option-id="chart_gridline_color" class="fusion-builder-option colorpicker">

							<div class="option-details">
								<h3>{{ fusionBuilderText.chart_gridline_color_title }}</h3>
								<p class="description">{{{ fusionBuilderText.chart_gridline_color_desc }}}</p>
							</div>

							<div class="option-field fusion-builder-option-container">
								<input id="chart_gridline_color" name="chart_gridline_color" class="fusion-builder-color-picker-hex wp-color-picker" type="text" data-alpha="true" value="{{ chart_gridline_color }}" data-default="{{ default_chart_gridline_color }}">
							</div>
						</li>
					</ul>

				</div>

			</div>

		</div>

	</div>

</script>
