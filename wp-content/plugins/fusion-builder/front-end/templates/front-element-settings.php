<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

$icon_map = apply_filters(
	'fusion_builder_icon_map',
	[
		'background' => 'fusiona-background-style',
		'general'    => 'fusiona-general-options',
		'children'   => 'fusiona-child-items',
		'design'     => 'fusiona-design-options',
		'extras'     => 'fusiona-extras',
		'main'       => 'fusiona-top-level-style',
		'submenu'    => 'fusiona-dropdown-style',
		'mobile'     => 'fusiona-mobile-menu',
		'variations' => 'fusiona-variation',
		'details'    => 'fusiona-details',
		'cart'       => 'fusiona-woo-add-to-cart',
		'caption'    => 'fusiona-comments',
		'links'      => 'fusiona-link',
	]
);
?>
<script type="text/template" id="fusion-builder-block-module-settings-template">
	<#  group_options = {};
		if ( 'undefined' !== typeof atts.multi && 'multi_element_parent' === atts.multi && ( 'undefined' === typeof atts.child_ui || atts.child_ui ) ) {
			group_options['children'] = {};
		}
		group_options['general'] = {};
		var editingChild     = 'multi_element_child' === atts.multi,
			generatedElement = 'generated_element' === atts.type ? true : false
			sidebarEditing   = 'dialog' !== FusionApp.preferencesData.editing_mode && ! generatedElement ? true : false,
			inlineElement    = 'undefined' !== typeof atts.inlineElement ? true : false,
			menuLabel        = '';
	#>

	<# _.each( fusionAllElements[atts.element_type].params, function( param ) {
		if ( 'undefined' !== typeof param.group ) {
			var group_tag = param.group.toLowerCase().replace(/ /g, '-');
			if ( 'undefined' == typeof group_options[ group_tag ] ) {
				group_options[ group_tag ] = {};
			}
			if ( 'undefined' !== typeof param.subgroup ) {
				if ( 'undefined' == typeof group_options[ group_tag ][param.subgroup.name]['subgroups'] ) {
					group_options[ group_tag ][param.subgroup.name]['subgroups'] = {};
				}
				if ( 'undefined' == typeof group_options[ group_tag ][param.subgroup.name]['subgroups'][param.subgroup.tab] ) {
					group_options[ group_tag ][param.subgroup.name]['subgroups'][param.subgroup.tab] = {};
				}
				group_options[ group_tag ][param.subgroup.name]['subgroups'][param.subgroup.tab][ param.param_name ] = param;
			} else {
				group_options[ group_tag ][ param.param_name ] = param;
			}
		} else {
			group_options['general'][ param.param_name ] = param;
		}

	} ); #>

	<div class="fusion-builder-modal-top-container <# if ( 2 > Object.keys(group_options).length && ! sidebarEditing ) { #>fusion-settings-no-tabs<# } #>">

		<# if ( sidebarEditing ) { #>
			<div class="ui-dialog-titlebar">

				<h2>
					<# if ( 'fusion_builder_container' === atts.element_type ) { #>
						<div class="fusion-builder-element-name-wrapper fusion-builder-option textfield" data-option-id="admin_label">
							<# var adminLabel = _.unescape( atts.params.admin_label ); #>
							<input class="fusion-builder-section-name" name="admin_label" value="{{adminLabel}}" type="text" placeholder="{{fusionAllElements[ atts.element_type ].name}}">
						</div>
					<# }; #>
					<# if ( editingChild ) { #>
						<a href="#" data-parent="{{ parent }}" class="fusion-builder-go-back" title="Back" aria-label="<?php esc_attr_e( 'Back', 'fusion-builder' ); ?>">
							<svg version="1.1" width="18" height="18" viewBox="0 0 32 32"><path d="M12.586 27.414l-10-10c-0.781-0.781-0.781-2.047 0-2.828l10-10c0.781-0.781 2.047-0.781 2.828 0s0.781 2.047 0 2.828l-6.586 6.586h19.172c1.105 0 2 0.895 2 2s-0.895 2-2 2h-19.172l6.586 6.586c0.39 0.39 0.586 0.902 0.586 1.414s-0.195 1.024-0.586 1.414c-0.781 0.781-2.047 0.781-2.828 0z"></path></svg>
						</a>
					<# } #>
					{{{ atts.title }}}
				</h2>

				<# if ( ! editingChild && ! inlineElement && ! generatedElement ) { #>
				<div class="fusion-utility-menu-wrap">
					<span class="fusion-utility-menu fusiona-ellipsis"></span>
				</div>
				<# } #>
				<button id="fusion-close-element-settings" type="button" class="fusiona-close-fb" aria-label="<?php esc_attr_e( 'Close', 'fusion-builder' ); ?>" role="button" title="<?php esc_attr_e( 'Close', 'fusion-builder' ); ?>">
			</div>
		<# } #>

		<!-- If there is more than one group found show tabs -->
		<# if ( Object.keys(group_options).length > 1 ) { #>
			<ul class="fusion-tabs-menu">
				<# _.each( group_options, function( options, group ) { #>
					<#
					menuLabel = _.fusionUcFirst( group.replace(/-/g, ' ') );

					var menuTooltip = menuLabel;

					if ( 'main' === group ) {
						menuTooltip = fusionBuilderText.main_menu;
					}

					var icons = <?php echo wp_json_encode( $icon_map ); ?>,
						icon  = 'string' === typeof icons[ group ] ? icons[ group ] : false;
					#>
					<li>
						<a href="#{{ group }}" class="has-tooltip" aria-label="{{ menuTooltip }}">
							<# if ( icon ) { #>
								<span class="{{ icon }}"></span>
								<span>{{ menuLabel }}</span>
							<# } else { #>
								{{ menuLabel }}
							<# } #>
						</a>
					</li>
				<# }); #>
			</ul>
		<# }; #>
	</div>

	<# if ( 'undefined' !== typeof atts.multi && atts.multi == 'multi_element_parent' ) {
		advanced_module_class = ' fusion-builder-main-settings-advanced';
	} else {
		advanced_module_class = '';
	}
	#>
	<#
	var postCardEdit = 'string' === typeof FusionApp.data.template_category && 'post_cards' === FusionApp.data.template_category ? ' post-card-editing' : '';
	#>
	<div class="fusion-builder-main-settings <# if ( sidebarEditing ) { #>fusion-builder-customizer-settings<# } #> fusion-builder-main-settings-full <# if ( Object.keys(group_options).length > 1 ) { #>has-group-options<# } #>{{ advanced_module_class }}{{ postCardEdit }}">
		<# if ( 'undefined' !== typeof fusionAllElements[atts.element_type] ) { #>
			<# if ( _.isObject ( fusionAllElements[atts.element_type].params ) ) { #>
				<# if ( 'fusion_builder_container' === atts.element_type && ! sidebarEditing ) { #>
					<div class="fusion-builder-element-name-wrapper fusion-builder-option textfield" data-option-id="admin_label">
						<input class="fusion-builder-section-name" name="admin_label" value="{{atts.params.admin_label}}" type="text" placeholder="{{fusionAllElements[ atts.element_type ].name}}">
					</div>
				<# }; #>

				<!-- If there is more than one group found show tabs -->
				<# if ( Object.keys(group_options).length > 1 ) { #>

					<!-- Show group options -->
					<div class="fusion-tabs">
						<# _.each( group_options, function( options, group ) { #>
							<div id="{{ group }}" class="fusion-tab-content">
								<# if ( 'children' !== group ) { #>
									<?php fusion_element_front_options_loop( 'options' ); ?>
								<# } else { #>
									<div class="fusion-child-sortables"></div>
								<# } #>
							</div>
						<# } ); #>
					</div>

				<# } else { #>

					<?php fusion_element_front_options_loop( 'fusionAllElements[atts.element_type].params' ); ?>

				<# }; #>

			<# }; #>

		<# } else { #>

			{{ atts.element_type }} - <?php esc_html_e( 'Undefined Element', 'fusion-builder' ); ?>

		<# }; #>

	</div>
</script>
