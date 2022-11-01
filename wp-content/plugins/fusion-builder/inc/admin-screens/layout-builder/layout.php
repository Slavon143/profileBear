<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-layout-template">
	<# highlightClass = 'undefined' !== typeof highlight ? ' awb-layout-highlight' : ''; #>
	<div class="layoutbox fusion-layout{{ highlightClass }}" data-id="{{ id }}">
		<!-- heading -->
		<div class="layoutbox-heading">
			<div class="layoutbox-controls layoutbox-controls-left">
				<a class="control cancel-select fusiona-back" href="#" aria-label="<?php esc_attr_e( 'Delete Layout', 'fusion-builder' ); ?>"></a>
			</div>
			<div class="layoutbox-title">
				<# if ( 'global' !== id ) { #>
					<input type="text" value="{{ title }}" />
				<# } else { #>
					<?php esc_attr_e( 'Global Layout', 'fusion-builder' ); ?>
				<# } #>
			</div>
			<div class="layoutbox-controls layoutbox-controls-right">
			<# if ( 'global' !== id ) { #>
				<a class="control open-options fusiona-cog" href="#" aria-label="<?php esc_attr_e( 'Layout Options', 'fusion-builder' ); ?>"></a>
				<a class="control remove-layout fusiona-trash-o" href="#" aria-label="<?php esc_attr_e( 'Delete Layout', 'fusion-builder' ); ?>"></a>
			<# } #>
			</div>
		</div>
		<!-- content -->
		<# var postEditLink = '<?php echo esc_url( get_admin_url() ); ?>post.php?post=ID&action=edit'; #>
		<div class="layoutbox-content">
			<div class="layout-templates">

				<?php $types = Fusion_Template_Builder()->get_template_terms(); ?>
				<?php foreach ( $types as $type_name => $type ) : ?>
					<#
						var isActive = terms.<?php echo esc_attr( $type_name ); ?>;
						var editLink = isActive ? postEditLink.replace( 'ID', terms.<?php echo esc_attr( $type_name ); ?>.ID ) : '';

						if ( isActive && 'live' === builderType ) {
							editLink = terms.<?php echo esc_attr( $type_name ); ?>.guid + '?fb-edit=1';
						}
					#>
					<div>
						<div class="select-template-container {{ isActive ? 'active' : '' }}" data-type="<?php echo esc_attr( $type_name ); ?>">
							<div class="icon-container">
								<i class="<?php echo ( isset( $type['icon'] ) ? esc_attr( $type['icon'] ) : 'fusiona-content' ); ?>" aria-hidden="true"></i>
							</div>
							<div class="template-type-label" title="{{ isActive ? terms.<?php echo esc_attr( $type_name ); ?>.post_title : '' }}">
								<?php /* translators: The label. */ ?>
								{{ isActive ? terms.<?php echo esc_attr( $type_name ); ?>.post_title : '<?php printf( esc_html__( 'Select %s', 'fusion-builder' ), esc_html( $type['label'] ) ); ?>' }}
							</div>
							<div class="controls">
								<# if ( isActive ) { #>
									<a data-type="<?php echo esc_attr( $type_name ); ?>" class="control fusiona-pen" href="{{ editLink }}" aria-label="<?php esc_attr_e( 'Edit Section', 'fusion-builder' ); ?>" rel="noopener noreferrer" target="_blank"></a>
									<a data-type="<?php echo esc_attr( $type_name ); ?>" class="control remove-template fusiona-unlink-solid" href="#" aria-label="<?php esc_attr_e( 'Deselect Section', 'fusion-builder' ); ?>"></a>
									<# } else { #>
										<a data-type="<?php echo esc_attr( $type_name ); ?>" class="control select-template fusiona-plus2" href="#" aria-label="<?php esc_attr_e( 'Select Section', 'fusion-builder' ); ?>"></a>
								<# } #>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<# var hasConditions = 'object' === typeof conditions && 0 < Object.keys( conditions ).length ? 'has-conditions' : ''; #>
			<# var isGlobal      = 'global' === id ? 'global-layout' : ''; #>
			<div class="fusion-condition-control {{hasConditions}} {{isGlobal}}" aria-label-condition="<?php esc_html_e( 'Manage Conditions', 'fusion-builder' ); ?>" aria-label="<?php esc_html_e( 'Add a Condition', 'fusion-builder' ); ?>">
				<ul class="fusion-condtions">
					<# if ( 'global' === id ) { #>
					<li class="global-condition"><?php esc_html_e( 'Sections here appear globally.', 'fusion-builder' ); ?></li>
					<# } #>
					<li class="no-condition-select"><span><?php esc_html_e( 'No condition selected', 'fusion-builder' ); ?></span></li>
					<# if ( 'object' === typeof conditions && 0 < Object.keys( conditions ).length ) {
						_.each( conditions, function( condition ) { #>
							<li class="{{ condition.mode }}"><span>{{{ condition.label }}}</span></li>
						<# }); #>
					<# } #>
				</ul>
			</div>
			<div class="layout-select">
				<ul class="fusion-tabs-menu">
					<li><a href="#default-columns"><?php esc_html_e( 'New Section', 'fusion-builder' ); ?></a></li>
					<li><a href="#custom-sections"><?php esc_html_e( 'Existing Section', 'fusion-builder' ); ?></a></li>
				</ul>

				<div class="fusion-tabs">
					<div id="default-columns" class="fusion-tab-content">
						<form class="form-create">
							<label for="new-template-name-{{ id }}"><?php esc_html_e( 'Section Name', 'fusion-builder' ); ?></label>
							<input type="text" name="name" id="new-template-name-{{ id }}" required>
							<button type="submit"><?php esc_html_e( 'Create New Section', 'fusion-builder' ); ?></div>
						</form>
					</div>

					<div id="custom-sections" class="custom-sections fusion-tab-content fusion-select-template" data-no_template="<?php echo esc_attr( __( 'No section available', 'fusion-builder' ) ); ?>">

					</div>
				<div>
			</div>
		</div>
	</div>
	<!-- loader -->
	<div class="layoutbox-loader"><div class="fusion-builder-loader"></div></div>

	<!-- confirmation modal -->
	<div class="layoutbox-prompt confirmation">
		<i class="fusiona-layout-close cancel-delete" aria-hidden="true"></i>
		<h3><?php esc_html_e( 'Remove Layout', 'fusion-builder' ); ?></h3>
		<p><?php esc_html_e( 'Are you sure you want to delete this layout?', 'fusion-builder' ); ?></p>
		<button class="confirm-remove-layout"><?php esc_html_e( 'Delete Layout', 'fusion-builder' ); ?></button>
	</div>
</script>
