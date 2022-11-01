<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/template" id="fusion-builder-column-template">
	<div class="fusion-droppable fusion-droppable-vertical target-before fusion-column-target"></div>

	<div class="fusion-builder-module-controls-container fusion-builder-module-controls-type-column">
		<div class="column-sizes">
			<h4>{{ fusionBuilderText.columns }}</h4>
			<div class="column-size column-size-1_6" data-column-size="1_6">1/6</div>
			<div class="column-size column-size-1_5" data-column-size="1_5">1/5</div>
			<div class="column-size column-size-1_4" data-column-size="1_4">1/4</div>
			<div class="column-size column-size-1_3" data-column-size="1_3">1/3</div>
			<div class="column-size column-size-2_5" data-column-size="2_5">2/5</div>
			<div class="column-size column-size-1_2" data-column-size="1_2">1/2</div>
			<div class="column-size column-size-3_5" data-column-size="3_5">3/5</div>
			<div class="column-size column-size-2_3" data-column-size="2_3">2/3</div>
			<div class="column-size column-size-3_4" data-column-size="3_4">3/4</div>
			<div class="column-size column-size-4_5" data-column-size="4_5">4/5</div>
			<div class="column-size column-size-5_6" data-column-size="5_6">5/6</div>
			<div class="column-size column-size-1_1" data-column-size="1_1">1/1</div>
		</div>
		<div class="fusion-builder-controls fusion-builder-module-controls fusion-builder-column-controls">
			<div class="fusion-builder-module-controls-inner fusion-builder-column-controls-inner">
				<a href="#" class="fusion-builder-column-settings fusion-builder-module-control"><span class="fusiona-pen"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Column Options', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-insert-column fusion-builder-module-control"><span class="fusiona-add-columns"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Add Columns', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-column-size fusion-builder-module-control"><span class="fusion-column-size-label">{{{ layout }}}</span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Column Size', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-column-clone fusion-builder-module-control"><span class="fusiona-file-add"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Clone Column', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-column-save fusion-builder-module-control" data-focus="#fusion-builder-save-element-input" data-target="#fusion-builder-layouts-columns"><span class="fusiona-drive"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text"><?php esc_attr_e( 'Save Column', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-column-remove fusion-builder-module-control"><span class="fusiona-trash-o"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Delete Column', 'fusion-builder' ); ?></span></span></a>
				<a href="#" class="fusion-builder-column-drag fusion-builder-module-control"><span class="fusiona-icon-move"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text"><?php esc_html_e( 'Drag Column', 'fusion-builder' ); ?></span></span></a>
			</div>
			<# if ( 'undefined' !== typeof isGlobal && 'yes' === isGlobal ) { #>
				<a href="#" class="fusion-builder-column-global fusion-builder-unglobal-tooltip fusion-builder-module-control" data-cid={{cid}}><span class="fusiona-globe"></span><span class="fusion-column-tooltip"><span class="fusion-tooltip-text">{{{ fusionBuilderText.global_column }}}</span></span></a>
			<# } #>
		</div>
	</div>

	<{{{ column_tag }}} {{{ _.fusionGetAttributes( wrapperAttr ) }}}>

		<# if ( 'yes' === center_content && ! isFlex ) { #>
			<div class="fusion-column-content-centered">
				<div class="fusion-column-content">
		<# } #>

		<div class="fusion-builder-column-content{{ nestedClass }}" data-cid="{{ cid }}">
			<span class="fusion-builder-empty-column">
				<span class="fusion-builder-module-controls-container">
					<span class="fusion-builder-controls fusion-builder-module-controls">
						<a href="#" class="fusion-builder-add-element fusion-builder-module-control">
							<span class="fusiona-plus"></span>
							<span class="fusion-column-tooltip">
								<span class="fusion-tooltip-text">{{{ fusionBuilderText.add_element }}}</span>
							</span>
						</a>
					</span>
				</span>
				<div class="fusion-droppable fusion-droppable-horizontal fusion-element-target target-replace fusion-element-target-column"></div>
			</span>
		</div>

		<# if ( 'yes' === center_content && ! isFlex  ) { #>
				</div>
			</div>
		<# } #>

		<div class="fusion-clearfix"></div>

		<div class="fusion-column-spacers">
			<# if ( 'undefined' !== typeof hideMargin && ! hideMargin ) { #>
			<div class="fusion-column-margin-top fusion-element-spacing">
				<div class="fusion-spacing-value">
					<div class="fusion-spacing-tooltip"></div>
				</div>
			</div>
			<div class="fusion-column-margin-right fusion-element-spacing">
				<div class="fusion-spacing-value">
					<div class="fusion-spacing-tooltip"></div>
				</div>
			</div>
			<div class="fusion-column-margin-bottom fusion-element-spacing">
				<div class="fusion-spacing-value">
					<div class="fusion-spacing-tooltip"></div>
				</div>
			</div>
			<div class="fusion-column-margin-left fusion-element-spacing">
				<div class="fusion-spacing-value">
					<div class="fusion-spacing-tooltip"></div>
				</div>
			</div>
			<# } #>
			<div class="fusion-column-padding-top fusion-element-spacing">
				<div class="fusion-spacing-value">
					<div class="fusion-spacing-tooltip"></div>
				</div>
			</div>
			<div class="fusion-column-padding-right fusion-element-spacing">
				<div class="fusion-spacing-value">
					<div class="fusion-spacing-tooltip"></div>
				</div>
			</div>
			<div class="fusion-column-padding-bottom fusion-element-spacing">
				<div class="fusion-spacing-value">
					<div class="fusion-spacing-tooltip"></div>
				</div>
			</div>
			<div class="fusion-column-padding-left fusion-element-spacing">
				<div class="fusion-spacing-value">
					<div class="fusion-spacing-tooltip"></div>
				</div>
			</div>
		</div>
	</{{{ column_tag }}}>

	<div class="fusion-column-margins">
		<div class="fusion-column-margin-top fusion-element-spacing">
			<div class="fusion-spacing-value">
				<div class="fusion-spacing-tooltip"></div>
			</div>
		</div>
		<div class="fusion-column-margin-bottom fusion-element-spacing">
			<div class="fusion-spacing-value">
				<div class="fusion-spacing-tooltip"></div>
			</div>
		</div>
	</div>

	<# if ( hoverOrLink ) { #>
		<span {{{ _.fusionGetAttributes( hoverWrapperAttr ) }}}>
			<a {{{ _.fusionGetAttributes( anchorAttr ) }}}>
				<span {{{ _.fusionGetAttributes( hoverInnerWrapperAttr ) }}}></span>
			</a>
		</span>
	<# } #>

	<a href="#" class="fusion-builder-add-element fusion-builder-module-control"><span class="fusiona-plus"></span>{{ fusionBuilderText.element }}<span class="fusion-column-tooltip"><span class="fusion-tooltip-text">{{ fusionBuilderText.add_element }}</span></span></a>

	<div class="fusion-column-styles-holder">
		<style type="text/css">{{{ styles }}}</style>
		<style type="text/css" class="fusion-column-responsive-styles">{{{ responsiveStyles }}}</style>
		{{{ filterStyle }}}
		{{{ transformStyle }}}
	</div>

	<div class="fusion-column-spacing">
		<div class="fusion-spacing-value">
			<div class="fusion-spacing-tooltip"></div>
		</div>
	</div>
	<div class="fusion-droppable fusion-droppable-vertical target-after fusion-column-target"></div>
</script>
