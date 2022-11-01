<?php
/**
 * Underscore.js template.
 *
 * @since 3.3
 * @package fusion-library
 */

?>
<#
	var fieldId     = 'undefined' === typeof param.param_name ? param.id : param.param_name,
		defaultParam  = 'undefined' === typeof param.default ? '' : param.default,
		option_value  = _.isEmpty( option_value ) ? defaultParam : option_value,
		choices       = 'undefined' === typeof param.choices ? [] : param.choices,
		options       = option_value ? JSON.parse( FusionPageBuilderApp.base64Decode( option_value ) ) : [],
		placeholder   = 'undefined' === typeof param.placeholder ? [] : param.placeholder,
		comparisons   = 'undefined' === typeof param.comparisons ? [] : param.comparisons;

		if ( ! choices.length ) {
			// Filter map to only get form elements.
			formElements = _.filter( FusionPageBuilderApp.collection.models, function( element ) {
				var params = element.get( 'params' );
				if ( 'object' !== typeof params ) {
					return false;
				}
				return element.get( 'element_type' ).includes( 'fusion_form' ) && 'fusion_form_submit' !== element.get( 'element_type' ) && 'fusion_form_image_select_input' !== element.get( 'element_type' ) && 'string' === typeof params.label && 'string' === typeof params.name;
			} );

			_.each( formElements, function( formElement ) {
				var params     = formElement.get( 'params' ),
					inputLabel   = 'string' === typeof params.label && '' !== params.label ? params.label : params.name,
					elementType  = formElement.get( 'element_type' ),
					arrayType    = 'fusion_form_checkbox' === elementType || 'fusion_form_image_select' === elementType ? '[]' : '',
					options      = {};

				if ( ( 'undefined' !== typeof atts && atts.cid !== formElement.get( 'cid' ) ) && ( '' !== params.name || '' !== inputLabel ) ) {
					options = {
						'id' : params.name + arrayType,
						'title' : inputLabel,
						'type' : 'text',
						'comparisons' : comparisons
					};
					choices.push( options );
				}
			} );
		}
		choices.unshift( placeholder );
#>
<div class="fusion-builder-option-logics fusion-option-{{ fieldId }}">
	<# if ( choices.length ) { #>
		<a href="#" class="fusion-builder-add-sortable-child"><span class="fusiona-plus"></span><span class="add-sortable-child-text">{{ fusionBuilderText.add_new_logic }}</span></a>
		<div class="options-grid">
			<ul class="fusion-logics">
				<# _.each( options, function( option ) {
					var operator = option.operator,
						value	= option.value,
						hasOr = 'or' === operator ? 'has-or' : '',
						field	= option.field,
						additionals = 'undefined' !== typeof option.additionals ? option.additionals: null,
						comparison = option.comparison.split( ' ' ).join( '-' ).toLowerCase(),
						currentChoice = choices.find( ( { id } ) => id === field );

						// return eaerly if option no longer exists.
						if ( 'undefined' === typeof currentChoice ) {
							return;
						}
				#>
					<li class="fusion-logic {{hasOr}}" aria-label-or="{{fusionBuilderText.logic_separator_text}}">
						<div class="fusion-logic-controller-head">
							<div class="logic-edit">
								<a href="#" class="fusion-sortable-edit" tabIndex="-1" aria-label="<?php esc_attr_e( 'Move Row', 'fusion-builder' ); ?>">
									<span class="fusiona-pen"></span>
								</a>
							</div>
							<h4 class="logic-title">{{currentChoice.title}}</h4>
							<div class="logic-remove">
								<a href="#" class="fusion-sortable-remove" tabIndex="-1" aria-label="<?php esc_attr_e( 'Remove Row', 'fusion-builder' ); ?>">
									<span class="fusiona-trash-o"></span>
								</a>
							</div>
						</div>
						<div class="fusion-logic-controller-content">
						<div class="logic-field">
							<div class="logic-field-wrapper">
								<# if ( 'undefined' !== typeof FusionApp ) { #>
									<div class="fusion-select-wrapper">
										<select id="{{ fieldId }}" name="{{ fieldId }}" class="fusion-dont-update fusion-logic-choices fusion-hide-from-atts">
										<# _.each( choices, function( choice, key ) { #>
											<#
												choiceName    = 'object' === typeof choice.title ? choice.title[0] : choice.title;
												choiceValue   = Number.isInteger( choice.id ) ? parseInt( choice.id ) : choice.id;
												isPlaceholder = 'placeholder' === choice.id ? 'disabled selected hidden' : '';
											#>
											<option value="{{ choiceValue }}" {{isPlaceholder}} {{ typeof( choiceValue ) !== 'undefined' && field === choiceValue ?  ' selected="selected"' : '' }} >{{ choiceName }}</option>
										<# }); #>
										</select>
										<span class="fusiona-arrow-down"></span>
									</div>

								<# } else { #>
								<div class="select_arrow"></div>
								<select id="{{ fieldId }}" name="{{ fieldId }}" class="fusion-hide-from-atts fusion-logic-choices fusion-select-field<# if ( skipDebounce ) { #> fusion-skip-debounce<# } #><?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>"<?php echo ( is_rtl() ) ? ' data-dir="rtl"' : ''; ?>>
								<# _.each( choices, function( choice, key ) { #>
									<#
									choiceName    = 'object' === typeof choice.title ? choice.title[0] : choice.title;
									choiceValue   = Number.isInteger( choice.id ) ? parseInt( choice.id ) : choice.id;
									isPlaceholder = 'placeholder' === choice.id ? 'disabled selected hidden' : '';
									#>
									<option value="{{ choiceValue }}" {{isPlaceholder}} {{ typeof( choiceValue ) !== 'undefined' && field === choiceValue ?  ' selected="selected"' : '' }} >{{ choiceName }}</option>
								<# }); #>
								</select>

								<# } #>
							</div>
							<# if ( null !== additionals ) { #>
								<div class="logic-additionals">
									<# if ( 'text' === currentChoice.additionals.type ) { #>
										<input type="text" value="{{ additionals }}" placeholder="{{currentChoice.additionals.placeholder}}" class="fusion-hide-from-atts fusion-logic-additionals-field" />
									<# } else if ( 'select' === currentChoice.additionals.type ) { #>
										<# if ( 'undefined' !== typeof FusionApp ) { #>
											<div class="fusion-select-wrapper">
												<select id="{{ fieldId }}" name="{{ fieldId }}" class="fusion-dont-update fusion-logic-option fusion-hide-from-atts">
													<# _.each( currentChoice.additionals.options, function( choiceName, choiceValue ) {
														choiceName  = 'object' === typeof choiceName ? choiceName[0] : choiceName;
														choiceValue = Number.isInteger( choiceValue ) ? parseInt( choiceValue ) : choiceValue;
													#>
													<option value="{{ choiceValue }}" {{ typeof( additionals ) !== 'undefined' && additionals === choiceValue ?  ' selected="selected"' : '' }} >{{ choiceName }}</option>
													<# }); #>
												</select>
												<span class="fusiona-arrow-down"></span>
											</div>
											<# } else { #>
												<div class="select_arrow"></div>
												<select id="{{ fieldId }}" name="{{ fieldId }}" class="fusion-hide-from-atts fusion-logic-additionals-field fusion-select-field<# if ( skipDebounce ) { #> fusion-skip-debounce<# } #><?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>"<?php echo ( is_rtl() ) ? ' data-dir="rtl"' : ''; ?>>
													<# _.each( currentChoice.additionals.options, function( choiceName, choiceValue ) { #>
														<#
														choiceName  = 'object' === typeof choiceName ? choiceName[0] : choiceName;
														choiceValue = Number.isInteger( choiceValue ) ? parseInt( choiceValue ) : choiceValue;
														#>
														<option value="{{ choiceValue }}" {{ typeof( additionals ) !== 'undefined' && additionals === choiceValue ?  ' selected="selected"' : '' }} >{{ choiceName }}</option>
													<# }); #>
												</select>
											<# }
										} #>
								</div>
							<# } #>
						</div>
						<div class="fusion-logic-controller">
							<div class="logic-comparison-dropdown">
								<select class="logic-comparison-selection fusion-hide-from-atts">
									<# _.each( currentChoice.comparisons, function( comparisonName, comparisonValue ) { #>
										<#
											isSelected    = comparison === comparisonValue ? 'selected="selected"' : '';
										#>
										<option value="{{comparisonValue}}" {{isSelected}}>{{comparisonName}}</option>
									<# }); #>
								</select>
							</div>
						</div>
						<div class="logic-value">
							<div class="logic-value-field">
								<# if ( 'text' === currentChoice.type ) { #>
										<input type="text" value="{{ value }}" class="fusion-hide-from-atts fusion-logic-option" />
									<# } else if ( 'select' === currentChoice.type ) { #>
										<# if ( 'undefined' !== typeof FusionApp ) { #>
											<div class="fusion-select-wrapper">
												<select id="{{ fieldId }}" name="{{ fieldId }}" class="fusion-dont-update fusion-logic-option fusion-hide-from-atts">
												<# _.each( currentChoice.options, function( choiceName, choiceValue ) { #>
													<#
														choiceName  = 'object' === typeof choiceName ? choiceName[0] : choiceName;
														choiceValue = Number.isInteger( choiceValue ) ? parseInt( choiceValue ) : choiceValue;
													#>
													<option value="{{ choiceValue }}" {{ typeof( value ) !== 'undefined' && value === choiceValue ?  ' selected="selected"' : '' }} >{{ choiceName }}</option>
												<# }); #>
												</select>
												<span class="fusiona-arrow-down"></span>
											</div>
										<# } else { #>
										<div class="select_arrow"></div>
										<select id="{{ fieldId }}" name="{{ fieldId }}" class="fusion-hide-from-atts fusion-logic-option fusion-select-field<# if ( skipDebounce ) { #> fusion-skip-debounce<# } #><?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>"<?php echo ( is_rtl() ) ? ' data-dir="rtl"' : ''; ?>>
										<# _.each( currentChoice.options, function( choiceName, choiceValue ) { #>
											<#
												choiceName  = 'object' === typeof choiceName ? choiceName[0] : choiceName;
												choiceValue = Number.isInteger( choiceValue ) ? parseInt( choiceValue ) : choiceValue;
											#>
											<option value="{{ choiceValue }}" {{ typeof( choiceValue ) !== 'undefined' && value === choiceValue ?  ' selected="selected"' : '' }} >{{ choiceName }}</option>
										<# }); #>
										</select>
										<# }
									} #>
								</div>
						</div>
						<div class="logic-operator">
							<span class="fusion-sortable-operator {{operator}}" tabIndex="-1" aria-label-and="<?php esc_attr_e( 'AND', 'fusion-builder' ); ?>" aria-label-or="<?php esc_attr_e( 'OR', 'fusion-builder' ); ?>"></span>
						</div>
						</div>
					</li>
				<# }); #>
			</ul>
			<input class="logic-values skip-update" type="hidden" id="{{ param.param_name }}" name="{{ param.param_name }}" value="{{ option_value }}">
		</div>
		<div class="fusion-logic-template" style="display:none;" >
			<div class="fusion-logic-controller-head">
				<div class="logic-edit">
					<a href="#" class="fusion-sortable-edit" tabIndex="-1" aria-label="<?php esc_attr_e( 'Move Row', 'fusion-builder' ); ?>">
						<span class="fusiona-pen"></span>
					</a>
				</div>
				<h4 class="logic-title">{{ typeof( choices[0] ) === 'object' ?  choices[0].title : '' }}</h4>
				<div class="logic-remove">
					<a href="#" class="fusion-sortable-remove" tabIndex="-1" aria-label="<?php esc_attr_e( 'Remove Row', 'fusion-builder' ); ?>">
						<span class="fusiona-trash-o"></span>
					</a>
				</div>
			</div>
			<div class="fusion-logic-controller-content" style="display:block">
			<div class="logic-field">
				<div class="logic-field-wrapper">
					<# if ( 'undefined' !== typeof FusionApp ) { #>
					<div class="fusion-select-wrapper">
						<select id="{{ fieldId }}" name="{{ fieldId }}" class="fusion-hide-from-atts fusion-logic-choices fusion-dont-update">
						<# _.each( choices, function( choice, key ) { #>
							<#
								isPlaceholder = 'placeholder' === choice.id ? 'disabled selected hidden' : '';
							#>
							<option value="{{ choice.id }}" {{isPlaceholder}} {{ typeof( value ) !== 'undefined' && field === choice.id ?  ' selected="selected"' : '' }}>{{ choice.title }}</option>
						<# }); #>
						</select>
						<span class="fusiona-arrow-down"></span>
					</div>
					<# } else { #>
					<div class="select_arrow"></div>
					<select id="{{ fieldId }}" name="{{ fieldId }}" class="fusion-hide-from-atts fusion-logic-choices fusion-select-field fusion-skip-init<# if ( skipDebounce ) { #> fusion-skip-debounce<# } #><?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>"<?php echo ( is_rtl() ) ? ' data-dir="rtl"' : ''; ?>>
					<# _.each( choices, function( choice, key ) { #>
						<#
							isPlaceholder = 'placeholder' === choice.id ? 'disabled selected hidden' : '';
						#>
						<option value="{{ choice.id }}" {{isPlaceholder}} {{ typeof( value ) !== 'undefined' && field === choice.id ?  ' selected="selected"' : '' }}>{{ choice.title }}</option>
					<# }); #>
					</select>
					<# } #>
				</div>
			</div>

			<div class="fusion-logic-controller">
				<div class="logic-comparison-dropdown">
					<select class="logic-comparison-selection fusion-hide-from-atts">
						<# if ( 'object' === typeof choices[0] ) {
							_.each( choices[0]['comparisons'], function( comparisonName, comparisonValue ) { #>
								<#
									isSelected    = 'equal' === comparisonValue ? 'active' : '';
								#>
								<option value="{{comparisonValue}}" {{isSelected}}>{{comparisonName}}</option>
							<# });
						}  #>
					</select>
				</div>

			</div>
			<div class="logic-value">
				<div class="logic-value-field">
					<# if ( 'object' === typeof choices[0] ) {
						if ( 'text' === choices[0]['type'] ) { #>
							<input type="text" value="" disabled placeholder="{{fusionBuilderText.condition_value}}" class="fusion-hide-from-atts fusion-logic-option" />
						<# } else if ( 'select' === choices[0]['type'] ) { #>
							<# if ( 'undefined' !== typeof FusionApp ) { #>
								<div class="fusion-select-wrapper">
									<select id="{{ fieldId }}" name="{{ fieldId }}" class="fusion-dont-update fusion-logic-option fusion-hide-from-atts">
									<# _.each( choices[0]['options'], function( choiceName, choiceValue ) { #>
										<#
											choiceName  = 'object' === typeof choiceName ? choiceName[0] : choiceName;
											choiceValue = Number.isInteger( choiceValue ) ? parseInt( choiceValue ) : choiceValue;
										#>
										<option value="{{ choiceValue }}" {{ typeof( value ) !== 'undefined' && field === choiceValue ?  ' selected="selected"' : '' }} >{{ choiceName }}</option>
									<# }); #>
									</select>
									<span class="fusiona-arrow-down"></span>
								</div>
							<# } else { #>
							<div class="select_arrow"></div>
							<select id="{{ fieldId }}" name="{{ fieldId }}" class="fusion-hide-from-atts fusion-logic-option fusion-select-field<# if ( skipDebounce ) { #> fusion-skip-debounce<# } #><?php echo ( is_rtl() ) ? ' fusion-select-field-rtl' : ''; ?>"<?php echo ( is_rtl() ) ? ' data-dir="rtl"' : ''; ?>>
							<# _.each( choices[0]['options'], function( choiceName, choiceValue ) { #>
								<#
									choiceName  = 'object' === typeof choiceName ? choiceName[0] : choiceName;
									choiceValue = Number.isInteger( choiceValue ) ? parseInt( choiceValue ) : choiceValue;
								#>
								<option value="{{ choiceValue }}" {{ typeof( choiceValue ) !== 'undefined' && field === choiceValue ?  ' selected="selected"' : '' }} >{{ choiceName }}</option>
							<# }); #>
							</select>
							<# }
						}
					} #>
				</div>
			</div>
			<div class="logic-operator">
				<span href="#" class="fusion-sortable-operator and" tabIndex="-1" aria-label-and="<?php esc_attr_e( 'AND', 'fusion-builder' ); ?>" aria-label-or="<?php esc_attr_e( 'OR', 'fusion-builder' ); ?>"></span>
			</div>
		</div>
		</div>
		<div class="fusion-logics-all-choices" style="display:none">{{JSON.stringify( choices )}}</div>
	<# } else { #>
		<div class="no-fields">{{fusionBuilderText.no_logic_field}}</div>
	<# } #>
</div>
